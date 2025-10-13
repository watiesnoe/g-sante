<?php

namespace App\Services\Referentiel;

use App\Models\Medicament;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Relie les produits du stock (noms commerciaux : « DAFALGAN 500 mg ») aux fiches du
 * guide des médicaments (DCI : « PARACÉTAMOL »), en deux étapes :
 *   1. nom commercial → DCI, par la table Vidal 2018 du classeur du guide clinique ;
 *      sinon, le nom du produit est pris comme DCI (« Cétirizine », « Phénytoïne (oral) ») ;
 *   2. DCI → fiche, comme pour les protocoles (jamais une fiche déconseillée).
 * Prudent : un nom commercial qui correspond à plusieurs DCI reste non relié ; un
 * produit de forme connue (gel, suppositoire, injectable…) n'est relié qu'à une fiche
 * de la même voie ; une fiche ophtalmique ou gynécologique, qu'à un produit de ce type.
 * Les produits déjà reliés (à la main ou avant) ne sont jamais modifiés.
 */
class RapprochementStock
{
    public const ONGLET_VIDAL = 'Comparaison Vidal-Dorosz';

    /** Formes d'usage particulier : une fiche qui en porte une exige un produit de la même forme. */
    private const FORMES_PARTICULIERES = [
        '/ophtalm|collyre/iu'     => '/ophtalm|collyre|oculaire|\byeux?\b|\bœil\b/iu',
        '/gyn[ée]co|vagin|ovule/iu' => '/gyn[ée]co|vagin|ovule/iu',
        '/auricul/iu'             => '/auricul|oreille/iu',
        '/nasal/iu'               => '/nasal|\bnez\b/iu',
    ];

    /** nom Vidal normalisé → DCI ; nom Vidal normalisé → nom d'origine ; premier mot (marque) → [DCI…] */
    private array $vidal = [];
    private array $libellesVidal = [];
    private array $marques = [];

    public function __construct(private RapprochementProtocoles $protocoles) {}

    /**
     * @return array{relies:int, deja:int, non_relies:array<string,int>, par_methode:array<string,int>}
     */
    public function relier(string $classeur, bool $essai = false): array
    {
        $this->chargerVidal($classeur);

        $resultat = ['relies' => 0, 'deja' => 0, 'non_relies' => [], 'par_methode' => ['vidal' => 0, 'marque' => 0, 'nom' => 0]];
        $produits = Medicament::sansCloisonnement()->with(['unites' => fn($q) => $q->withoutGlobalScopes()])->get();

        foreach ($produits as $produit) {
            if ($produit->fiche_medicament_id) {
                $resultat['deja']++;
                continue;
            }
            [$fiche, $methode] = $this->fichePour($produit->nom, (string) $produit->unites->firstWhere('is_default', true)?->nom);
            if ($fiche) {
                $resultat['relies']++;
                $resultat['par_methode'][$methode]++;
                if (!$essai) {
                    $produit->forceFill(['fiche_medicament_id' => $fiche])->saveQuietly();
                }
            } else {
                $resultat['non_relies'][$produit->nom] = ($resultat['non_relies'][$produit->nom] ?? 0) + 1;
            }
        }
        ksort($resultat['non_relies']);

        return $resultat;
    }

    /** @return array{0: ?int, 1: ?string} fiche et méthode (vidal, marque, nom) */
    public function fichePour(string $nom, string $unite = ''): array
    {
        $cle = $this->normaliser($nom);
        $description = $nom . ' ' . $unite . ' ' . ($this->libellesVidal[$cle] ?? '');
        $voie = $this->voie($description);

        if (isset($this->vidal[$cle])) {
            [$dci, $methode] = [$this->vidal[$cle], 'vidal'];
        } else {
            // « LASILIX BUVABLE » : la marque seule, si elle ne désigne qu'une DCI
            $dcis = $this->marques[explode(' ', $cle)[0]] ?? [];
            if (count($dcis) > 1) {
                return [null, null];
            }
            [$dci, $methode] = $dcis ? [reset($dcis), 'marque'] : [trim(preg_replace('/\(.*?\)|\s\d.*$/u', ' ', $nom)), 'nom'];
        }

        // Voie rectale : seule une fiche « … rectal » convient (ex. ARTÉSUNATE rectal)
        $fiche = $voie === 'rectal' ? $this->protocoles->fichePour($dci . ' rectal', null) : $this->protocoles->fichePour($dci, $voie);
        if (!$fiche || !$this->formeCompatible($fiche, $description)) {
            return [null, null];
        }

        return [$fiche, $methode];
    }

    /**
     * Voie d'après le nom du produit, son unité et son nom Vidal ; null si rien ne
     * l'indique (la fiche orale est alors retenue, ou la seule fiche de la molécule).
     */
    public function voie(string $texte): ?string
    {
        return match (true) {
            (bool) preg_match('/suppo|rectal/iu', $texte) => 'rectal',
            // « Solution » seule : buvable, cutanée ou injectable ? Aucune fiche n'est retenue
            (bool) preg_match('/\bsolution\b/iu', $texte) && !preg_match('/buvable|injectable|perfusion|\b(IV|IM|oral[e]?)\b|cutan|nébuli/iu', $texte) => 'indeterminee',
            (bool) preg_match('/\b(IV|IM|SC|IVD|injectable|injection|perfusion|ampoule|seringue|stylo|auto-?injecteur|lyophilisat)\b/iu', $texte) => 'injectable',
            (bool) preg_match('/\b(cr[èe]me|pommade|gel|lotion|collyre|gouttes|spray|shampo\w*|patch|ovule|vaginal|emplâtre|pansement|mousse|vernis|cutan[ée]e?|ophtalm\w*|auriculaire|nasal)\b/iu', $texte) => 'externe',
            (bool) preg_match('/\b(comprim\w*|cp|g[ée]lules?|sirop|buvable|sachets?|oral[e]?|capsules?|suspension buvable|pastilles?)\b/iu', $texte) => 'oral',
            default => null,
        };
    }

    private function formeCompatible(int $fiche, string $produit): bool
    {
        $this->nomsFiches ??= \App\Models\FicheMedicament::pluck('nom', 'id')->all();
        foreach (self::FORMES_PARTICULIERES as $formeFiche => $formeProduit) {
            if (preg_match($formeFiche, $this->nomsFiches[$fiche] ?? '') && !preg_match($formeProduit, $produit)) {
                return false;
            }
        }

        return true;
    }

    private ?array $nomsFiches = null;

    private function chargerVidal(string $classeur): void
    {
        if (!is_file($classeur)) {
            throw new \RuntimeException("Fichier introuvable : $classeur");
        }
        $lecteur = IOFactory::createReaderForFile($classeur);
        $lecteur->setReadDataOnly(true);
        $lecteur->setLoadSheetsOnly([self::ONGLET_VIDAL]);
        $feuille = $lecteur->load($classeur)->getSheetByName(self::ONGLET_VIDAL)
            ?? throw new \RuntimeException('Onglet « ' . self::ONGLET_VIDAL . ' » absent du fichier.');

        foreach (array_slice($feuille->toArray(null, false, false, false), 1) as $ligne) {
            $dci = trim((string) ($ligne[1] ?? ''));
            if ($dci === '' || str_starts_with($dci, '(')) {
                continue;
            }
            $cle = $this->normaliser((string) $ligne[0]);
            $this->vidal[$cle] = $dci;
            $this->libellesVidal[$cle] = (string) $ligne[0];
            $this->marques[explode(' ', $cle)[0]][mb_strtolower($dci)] = $dci;
        }
    }

    private function normaliser(string $nom): string
    {
        $nom = Str::lower(Str::ascii(str_replace(['®', '™'], '', $nom)));

        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9 ]/', ' ', $nom)));
    }
}
