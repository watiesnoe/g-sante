<?php

namespace App\Services\Referentiel;

use App\Models\FicheMedicament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Relie les médicaments des protocoles de traitement aux fiches du guide des
 * médicaments, par leur nom (DCI). Prudent : une association de plusieurs
 * molécules ne se relie qu'à une fiche coformulée du même nom ; sans
 * correspondance sûre, la ligne reste non reliée (nom seul).
 */
class RapprochementProtocoles
{
    /** @return array{reliees:int, non_reliees:int, non_reliees_noms:array<int,string>} */
    public function relier(bool $remplacer = false): array
    {
        $index = $this->index();
        $lignes = DB::table('protocole_medicament')->whereNotNull('nom_medicament')
            ->when(!$remplacer, fn($q) => $q->whereNull('fiche_medicament_id'))->get(['id', 'nom_medicament']);

        $reliees = 0;
        $manquants = [];
        foreach ($lignes as $ligne) {
            [$cle, $voie] = $this->analyser($ligne->nom_medicament);
            $candidats = $index[$cle] ?? [];
            $fiche = $candidats[$voie] ?? (count($candidats) === 1 ? reset($candidats) : null);
            if ($fiche) {
                DB::table('protocole_medicament')->where('id', $ligne->id)->update(['fiche_medicament_id' => $fiche]);
                $reliees++;
            } else {
                $manquants[] = $ligne->nom_medicament;
            }
        }

        return ['reliees' => $reliees, 'non_reliees' => count($manquants), 'non_reliees_noms' => array_values(array_unique($manquants))];
    }

    /**
     * Fiche du guide pour une DCI (« paracétamol », « amoxicilline, acide clavulanique »).
     * Voie connue : seule une fiche de cette voie convient. Voie inconnue (null) : la fiche
     * orale, sinon la seule fiche de la molécule, sinon aucune.
     */
    public function fichePour(string $dci, ?string $voie): ?int
    {
        $this->indexCache ??= $this->index();
        $candidats = $this->indexCache[$this->normaliser(str_replace(', ', '/', $dci))] ?? [];

        if ($voie !== null) {
            return $candidats[$voie] ?? null;
        }

        return $candidats['oral'] ?? (count($candidats) === 1 ? reset($candidats) : null);
    }

    private ?array $indexCache = null;

    /** DCI normalisée → [voie => id de fiche], fiches déconseillées exclues. */
    private function index(): array
    {
        $index = [];
        foreach (FicheMedicament::where('deconseille', false)->get(['id', 'nom', 'voie']) as $fiche) {
            // « AMOXICILLINE/Acide CLAVULANIQUE = CO-AMOXICLAV » : chaque appellation est une clé
            $appellations = explode('=', $fiche->nom);
            // « PREDNISOLONE et PREDNISONE » : une fiche pour deux molécules
            if (preg_match('/^\s*(\S+) et (\S+)\s*$/u', $appellations[0], $m)) {
                array_push($appellations, $m[1], $m[2]);
            }
            foreach ($appellations as $appellation) {
                $cle = $this->normaliser(preg_replace('/,.*$/u', '', $appellation));
                if ($cle !== '') {
                    $index[$cle][$fiche->voie] ??= $fiche->id;
                }
            }
        }

        return $index;
    }

    /** « Amoxicilline 500mg (oral) » → ['amoxicilline', 'oral'] */
    private function analyser(string $nom): array
    {
        // Voie citée en premier : « (IV/oral) » → injectable, « (oral/IV) » → orale
        $positions = array_filter([
            'injectable' => preg_match('/\b(IV|IM|SC|IVD|injectable|injection|perfusion)\b/iu', $nom, $m, PREG_OFFSET_CAPTURE) ? $m[0][1] : null,
            'oral'       => preg_match('/\b(oral|orale|buvable|sirop|comprim)/iu', $nom, $m, PREG_OFFSET_CAPTURE) ? $m[0][1] : null,
            'externe'    => preg_match('/\b(pommade|lotion|cr[èe]me|gouttes|collyre|gel|spray)\b/iu', $nom, $m, PREG_OFFSET_CAPTURE) ? $m[0][1] : null,
        ], fn($p) => $p !== null);
        asort($positions);
        $voie = array_key_first($positions) ?? 'oral';
        $base = preg_replace('/\(.*?\)/u', ' ', $nom);
        $base = preg_replace('/\s\d.*$/u', '', $base); // dosage et forme
        $base = preg_replace('/\b(comprim[ée]s?|g[ée]lules?|sirop|suspension|injectable|solution|capsule.*)$/iu', '', trim($base));

        return [$this->normaliser($base), $voie];
    }

    private function normaliser(string $texte): string
    {
        $texte = Str::lower(Str::ascii($texte));
        $texte = preg_replace('/\s*[+\/]\s*/', '/', $texte);

        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9\/ -]/', ' ', $texte)));
    }
}
