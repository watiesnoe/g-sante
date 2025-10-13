<?php

namespace App\Services\Referentiel;

use App\Models\FicheMedicament;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Import du guide MSF « Médicaments essentiels — guide pratique d'utilisation »
 * dans le référentiel des médicaments.
 *
 * Le texte est extrait avec pdftotext (mise en page conservée). Chaque fiche
 * commence en haut d'une page par son nom et sa voie, puis suit des rubriques
 * fixes (Action thérapeutique, Indications, Présentation, Posologie…). Les
 * tableaux de posologie sont gardés tels quels : on ne recalcule aucune dose.
 * Toute fiche nouvelle ou modifiée est « à valider » dans chaque établissement.
 */
class ImportGuideMsf
{
    /** Rubriques de la source → colonne du référentiel. */
    private const RUBRIQUES = [
        'Action thérapeutique'                                  => 'action_therapeutique',
        'Indications'                                           => 'indications',
        'Présentation'                                          => 'presentation',
        "Présentation et voie d'administration"                 => 'presentation',
        "Composition, présentation et voie d'administration"    => 'presentation',
        'Composition'                                           => 'presentation',
        'Posologie'                                             => 'posologie',
        'Posologie et durée'                                    => 'posologie',
        'Posologie et schéma vaccinal'                          => 'posologie',
        'Utilisation'                                           => 'posologie',
        "Technique d'administration"                            => 'posologie',
        'Durée'                                                 => 'duree',
        'Contre-indications, effets indésirables, précautions'  => 'precautions',
        'Remarques'                                             => 'remarques',
        'Conservation'                                          => 'conservation',
    ];

    /** Parties du guide → voie par défaut et catégorie. */
    private const PARTIES = [
        'Médicaments oraux'                                => ['oral', 'Médicaments oraux', false],
        'Médicaments injectables'                          => ['injectable', 'Médicaments injectables', false],
        'Solutions de perfusion'                           => ['perfusion', 'Solutions de perfusion', false],
        'Vaccins, immunoglobulines et sérums'              => ['vaccin', 'Vaccins, immunoglobulines et sérums', false],
        // Titre parfois coupé sur deux lignes dans le corps du guide (« … externe et » / « antiseptiques »)
        'Médicaments à usage externe'                      => ['externe', 'Médicaments à usage externe et antiseptiques', false],
        'Médicaments potentiellement dangereux'            => [null, 'Médicaments potentiellement dangereux, obsolètes ou inefficaces', true],
    ];

    private const VOIES_TITRE = [
        'oral' => 'oral', 'injectable' => 'injectable', 'aérosol-doseur' => 'oral', 'nébulisation' => 'oral',
    ];

    /** @return array{creees:int, modifiees:int, inchangees:int, retirees:int, conservees:int, fiches:int} */
    public function importer(string $fichier, string $source): array
    {
        $lignes = explode("\n", $this->texte($fichier));
        $fiches = $this->analyser($lignes);
        if (count($fiches) < 50) {
            throw new RuntimeException('Seulement ' . count($fiches) . ' fiche(s) reconnue(s) : ce document ne semble pas être le guide MSF attendu.');
        }

        $compte = ['creees' => 0, 'modifiees' => 0, 'inchangees' => 0, 'retirees' => 0, 'conservees' => 0, 'fiches' => count($fiches)];
        DB::transaction(function () use ($fiches, $source, &$compte) {
            $presentes = [];
            foreach ($fiches as $donnees) {
                $donnees['source'] = $source;
                $presentes[] = $donnees['nom'] . '|' . $donnees['voie'];
                $fiche = FicheMedicament::where('source', $source)->where('nom', $donnees['nom'])->where('voie', $donnees['voie'])->first();
                if (!$fiche) {
                    FicheMedicament::create($donnees);
                    $compte['creees']++;
                    continue;
                }
                $fiche->fill($donnees);
                if ($fiche->isDirty()) {
                    // Contenu changé : les validations des établissements tombent d'elles-mêmes (empreinte)
                    $fiche->save();
                    $compte['modifiees']++;
                } else {
                    $compte['inchangees']++;
                }
            }

            // Fiches de cette source absentes de la nouvelle édition : retirées, sauf si un stock y renvoie
            foreach (FicheMedicament::where('source', $source)->get() as $ancienne) {
                if (in_array($ancienne->nom . '|' . $ancienne->voie, $presentes, true)) {
                    continue;
                }
                if (\App\Models\Medicament::sansCloisonnement()->where('fiche_medicament_id', $ancienne->id)->exists()) {
                    $compte['conservees']++;
                } else {
                    $ancienne->delete();
                    $compte['retirees']++;
                }
            }
        });

        return $compte;
    }

    /** @return array<int, array<string, mixed>> */
    public function analyser(array $lignes): array
    {
        // Sauts de page (\f) laissés par l'extraction en tête de ligne
        $lignes = array_map(fn($l) => str_replace("\f", '', $l), $lignes);

        // Le corps du guide : de la 2e occurrence de « Médicaments oraux » (après le sommaire) à la « Deuxième partie »
        $debut = $this->occurrence($lignes, 'Médicaments oraux', 2);
        $fin = $this->occurrence($lignes, 'Deuxième partie', 2) ?? count($lignes);
        if ($debut === null) {
            throw new RuntimeException('Début du guide introuvable (« Médicaments oraux »).');
        }

        // Page de chaque ligne : un pied « Page N / T » termine la page N
        $pages = [];
        $page = 1;
        foreach ($lignes as $i => $ligne) {
            $pages[$i] = $page;
            if ($this->estPied($ligne)) {
                $page = (int) preg_replace('/\D.*$/', '', trim(preg_replace('/^\s*Page\s+/', '', $ligne))) + 1;
            }
        }

        // Partie (voie par défaut, catégorie) de chaque ligne
        $partie = self::PARTIES['Médicaments oraux'];
        $parties = [];
        for ($i = $debut; $i < $fin; $i++) {
            foreach (self::PARTIES as $titre => $definition) {
                if (str_starts_with(trim($lignes[$i]), $titre) && !str_starts_with($lignes[$i], ' ')) {
                    $partie = $definition;
                }
            }
            $parties[$i] = $partie;
        }

        // Une fiche commence en haut d'une page : un nom de médicament (en majuscules),
        // puis sa première rubrique (« Action thérapeutique », ou « Indications » /
        // « Composition » pour les solutions de perfusion et les vaccins)
        $ancres = [];
        $pied = $debut;
        $rubriqueVue = false;
        for ($i = $debut; $i < $fin; $i++) {
            if ($this->estPied($lignes[$i])) {
                $pied = $i;
                $rubriqueVue = false;
                continue;
            }
            if ($rubriqueVue || !isset(self::RUBRIQUES[trim($lignes[$i])]) || str_starts_with($lignes[$i], ' ')) {
                continue;
            }
            $rubriqueVue = true;
            $bloc = array_slice($lignes, $pied + 1, $i - $pied - 1);
            $entete = $this->entete($bloc);
            if ($entete['titre'] !== '' && $this->ressembleAUnTitre($bloc, $entete['titre'])) {
                $ancres[] = ['bloc' => $pied + 1, 'action' => $i];
            }
        }

        $fiches = [];
        foreach ($ancres as $k => $ancre) {
            $finFiche = $ancres[$k + 1]['bloc'] ?? $fin;
            $entete = $this->entete(array_slice($lignes, $ancre['bloc'], $ancre['action'] - $ancre['bloc']));
            if (!$entete['titre']) {
                continue;
            }
            [$voieDefaut, $categorie, $deconseille] = $parties[$ancre['action']];
            // Voie absente du titre dans la partie « déconseillés » : non précisée, plutôt que devinée
            [$nom, $voie] = $this->nomEtVoie($entete['titre'], $voieDefaut ?? 'autre');

            $fiches[] = [
                'nom'                => $nom,
                'voie'               => $voie,
                'categorie'          => $categorie,
                'deconseille'        => $deconseille,
                'avertissement'      => $entete['avertissement'],
                'mention'            => $entete['mention'],
                'mise_a_jour_source' => $entete['maj'],
                'page'               => $pages[$ancre['bloc']] ?? null,
            ] + $this->rubriques(array_slice($lignes, $ancre['action'], $finFiche - $ancre['action']));
        }

        // Une même fiche ne doit apparaître qu'une fois (nom + voie)
        return collect($fiches)->unique(fn($f) => $f['nom'] . '|' . $f['voie'])->values()->all();
    }

    private function entete(array $lignes): array
    {
        $titre = [];
        $avertissement = [];
        $mention = null;
        $maj = null;
        $suitePartie = false;
        foreach ($lignes as $ligne) {
            $t = trim($ligne);
            if ($t === '' || $this->estPied($ligne)) {
                continue;
            }
            // Titre de partie, et sa 2e ligne éventuelle (« antiseptiques », « ou obsolètes ou inefficaces »)
            if ($this->estPartie($t)) {
                $suitePartie = !preg_match('/(antiseptiques|inefficaces|oraux|injectables|perfusion|sérums)$/u', $t);
                continue;
            }
            if ($suitePartie) {
                $suitePartie = false;
                continue;
            }
            if (str_starts_with($t, 'Dernière mise à jour')) {
                $maj = trim(substr($t, strpos($t, ':') + 1));
                continue;
            }
            if (str_starts_with($t, 'Prescription sous') || str_starts_with($t, 'Prescription médicale')) {
                $mention = $t;
                continue;
            }
            if (!$avertissement && ($titre === [] || $this->suiteDeTitre(implode(' ', $titre), $t))) {
                $titre[] = $t;
            } else {
                $avertissement[] = $t;
            }
        }
        $texteTitre = '';
        foreach ($titre as $morceau) {
            // « CO- » + « AMOXICLAV » → « CO-AMOXICLAV »
            $texteTitre .= (str_ends_with($texteTitre, '-') ? '' : ' ') . $morceau;
        }

        return [
            'titre'         => trim(preg_replace('/\s+/', ' ', $texteTitre)),
            'avertissement' => $avertissement ? implode(' ', $avertissement) : null,
            'mention'       => $mention,
            'maj'           => $maj,
        ];
    }

    /**
     * Haut de page d'une nouvelle fiche, et non suite d'une fiche : la première ligne
     * commence en début de ligne (le contenu des rubriques est indenté), sans
     * « : », et le nom comporte un mot entier en capitales (« Sulfate de ZINC »).
     */
    private function ressembleAUnTitre(array $bloc, string $titre): bool
    {
        foreach ($bloc as $ligne) {
            $ligne = str_replace("\f", '', $ligne);
            if (trim($ligne) === '' || $this->estPartie(trim($ligne))) {
                continue;
            }
            if (preg_match('/^\s/u', $ligne) || str_contains($ligne, ' : ')) {
                return false;
            }
            break;
        }

        return (bool) preg_match('/\b\p{Lu}{3,}\b/u', str_replace(' ', '  ', $titre)) || (bool) preg_match('/\p{Lu}{3,}/u', $titre);
    }

    private function suiteDeTitre(string $precedent, string $ligne): bool
    {
        // Parenthèse ouverte dans le titre : la ligne suivante le complète
        if (substr_count($precedent, '(') > substr_count($precedent, ')')) {
            return true;
        }
        if (preg_match('/[=\-\/]$|\sde$|\sDE$/u', $precedent) || str_starts_with($ligne, '(') || isset(self::VOIES_TITRE[$ligne]) || in_array($ligne, ['injectable', 'oral'], true)) {
            return true;
        }
        $lettres = preg_replace('/[^\p{L}]/u', '', $ligne);
        $majuscules = preg_replace('/[^\p{Lu}]/u', '', $ligne);

        return $lettres !== '' && mb_strlen($majuscules) / mb_strlen($lettres) >= 0.5;
    }

    /** Sépare « AMOXICILLINE oral » en nom et voie. */
    private function nomEtVoie(string $titre, string $voieDefaut): array
    {
        foreach (['injectable', 'oral'] as $voie) {
            if (preg_match('/\s' . $voie . '$/u', $titre)) {
                return [trim(preg_replace('/\s' . $voie . '$/u', '', $titre)), $voie];
            }
        }

        return [$titre, $voieDefaut];
    }

    /** @return array<string, ?string> */
    private function rubriques(array $lignes): array
    {
        $contenu = array_fill_keys(array_unique(array_values(self::RUBRIQUES)), []);
        $courante = null;
        foreach ($lignes as $ligne) {
            if ($this->estPied($ligne)) {
                continue;
            }
            $t = trim($ligne);
            if (isset(self::RUBRIQUES[$t]) && !str_starts_with($ligne, ' ')) {
                $courante = self::RUBRIQUES[$t];
                // Sous-rubrique ajoutée à une rubrique déjà remplie (ex. « Technique d'administration »)
                if ($contenu[$courante] !== [] ) {
                    $contenu[$courante][] = '';
                    $contenu[$courante][] = $t;
                }
                continue;
            }
            if ($courante) {
                $contenu[$courante][] = $ligne;
            }
        }

        return array_map(fn($l) => $this->nettoyer($l), $contenu);
    }

    private function nettoyer(array $lignes): ?string
    {
        // Pictogrammes de conservation rendus en tirets par l'extraction
        $lignes = array_map(fn($l) => rtrim(preg_replace('/^(\s*)(–\s+)+–?\s*/u', '$1', $l)), $lignes);
        // Retrait de l'indentation commune, lignes vides superflues
        $indentation = collect($lignes)->filter(fn($l) => trim($l) !== '')->map(fn($l) => strlen($l) - strlen(ltrim($l)))->min() ?? 0;
        $texte = implode("\n", array_map(fn($l) => substr($l, min($indentation, strlen($l) - strlen(ltrim($l)))), $lignes));
        $texte = trim(preg_replace("/\n{3,}/", "\n\n", $texte));

        return $texte === '' ? null : $texte;
    }

    private function estPied(string $ligne): bool
    {
        return (bool) preg_match('/^\s*Page\s+\d+\s*\/\s*\d+\s*$/', $ligne);
    }

    private function estPartie(string $ligne): bool
    {
        foreach (array_keys(self::PARTIES) as $titre) {
            if (str_starts_with($ligne, $titre)) {
                return true;
            }
        }

        return false;
    }

    private function occurrence(array $lignes, string $texte, int $rang): ?int
    {
        $vu = 0;
        foreach ($lignes as $i => $ligne) {
            if (trim($ligne) === $texte && ++$vu === $rang) {
                return $i;
            }
        }

        return null;
    }

    private function texte(string $fichier): string
    {
        if (!is_file($fichier)) {
            throw new RuntimeException("Fichier introuvable : {$fichier}");
        }
        if (str_ends_with(strtolower($fichier), '.txt')) {
            return file_get_contents($fichier);
        }
        $process = new Process(['pdftotext', '-layout', $fichier, '-']);
        $process->setTimeout(300)->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException('Extraction impossible : pdftotext est-il installé (paquet poppler-utils) ? ' . $process->getErrorOutput());
        }

        return $process->getOutput();
    }
}
