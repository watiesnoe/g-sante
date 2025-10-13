<?php

namespace App\Services\Referentiel;

use App\Models\AbreviationClinique;
use App\Models\DciReference;
use App\Models\FicheBerrebi;
use App\Models\Maladie;
use App\Models\ProtocoleTraitement;
use App\Models\Symptome;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Import du fichier « Maladies, symptômes et traitements » (guide clinique MSF,
 * relié à la liste OMS, au Vidal 2018 et au Dorosz 2022).
 *
 * Onglets lus : Maladies, Maladie → médicaments, Index des médicaments,
 * Posologies de référence, Abréviations ; et, s'ils sont présents (classeur
 * « Base médicaments et maladies »), Berrebi – fiches et Berrebi – ordonnances.
 * Les symptômes sont tirés de la colonne « Symptômes » des maladies, découpés et
 * débarrassés des abréviations. Les colonnes « Alerte OMS 2025 » sont reprises.
 */
class ImportGuideClinique
{
    public const SOURCE = 'MSF — Guide clinique et thérapeutique (en ligne, octobre 2026)';

    /** Abréviations développées dans les libellés de symptômes (les textes longs les gardent, avec info-bulle). */
    private const DEVELOPPER = [
        'E.' => 'enfant', 'A.' => 'adulte', 'Nrs' => 'nourrisson', 'NN' => 'nouveau-né', 'FE' => 'femme enceinte',
        'SA' => 'sujet âgé', 'DR' => 'détresse respiratoire', 'respi.' => 'respiratoire', 'inf.' => 'infection',
        'hospit.' => 'hospitalisation', 'av.' => 'avec', 'ss' => 'sans', 'ds' => 'dans', 'pdt' => 'pendant',
        'qq' => 'quelques', 'glyc.' => 'glycémie', 'gross.' => 'grossesse', 'sympt.' => 'symptômes',
        'allait.' => 'allaitement', 'hab.' => 'habituellement', 'tjrs' => 'toujours', 'jsq' => "jusqu'à",
        'ttt' => 'traitement', 'évol.' => 'évolution', 'progr.' => 'progressivement', 'IC' => 'insuffisance cardiaque', 'IR' => 'insuffisance rénale',
    ];

    /** Lit le classeur et renvoie les données prêtes à importer, sans rien enregistrer. */
    public function analyser(string $fichier): array
    {
        if (!is_file($fichier)) {
            throw new \RuntimeException("Fichier introuvable : $fichier");
        }

        $classeur = IOFactory::load($fichier);
        $onglet = function (string $nom, bool $facultatif = false) use ($classeur): array {
            $feuille = $classeur->getSheetByName($nom);
            if (!$feuille && $facultatif) {
                return [];
            }
            $feuille ??= throw new \RuntimeException("Onglet « $nom » absent du fichier.");
            $lignes = $feuille->toArray(null, true, false, false);
            array_shift($lignes);

            return array_values(array_filter($lignes, fn($l) => filled($l[0] ?? null)));
        };

        $maladies = [];
        foreach ($onglet('Maladies') as $l) {
            $maladies[] = [
                'chapitre'       => $this->texte($l[0]),
                'nom'            => $this->texte($l[1]),
                'symptomes_texte'=> $this->texte($l[2]),
                'symptomes'      => $this->symptomes((string) $l[2]),
                'signes_gravite' => $this->texte($l[3]),
                'examens'        => $this->texte($l[4]),
                'traitement'     => $this->texte($l[5]),
                'prevention'     => preg_match('/^non abordée (ds|dans) la fiche$/u', (string) $this->texte($l[6])) ? null : $this->texte($l[6]),
                'medicaments'    => $this->texte($l[7]),
                'source_url'     => $this->texte($l[8]),
                'berrebi'        => FicheBerrebi::liste($l[9] ?? null),
                'alerte_oms'     => $this->texte($l[10] ?? null),
            ];
        }

        $maladies = $this->ajouterSymptomesDeBase($maladies);

        $lignes = [];
        foreach ($onglet('Maladie → médicaments') as $l) {
            $lignes[$this->texte($l[0])][] = ['dci' => $this->texte($l[2]), 'posologie' => $this->texte($l[8]), 'alerte' => $this->texte($l[12] ?? null)];
        }

        // Contre-indications par molécule, une entrée par forme (oral, injectable…)
        $ci = [];
        foreach ($onglet('Posologies de référence') as $l) {
            if (filled($l[4] ?? null)) {
                $ci[mb_strtolower($this->texte($l[0]))][] = '[' . $this->texte($l[1]) . '] ' . $this->texte($l[4]);
            }
        }

        $dcis = [];
        foreach ($onglet('Index des médicaments') as $l) {
            $dci = $this->texte($l[0]);
            $dcis[] = [
                'dci'                 => $dci,
                'liste_oms'           => $this->texte($l[3]),
                'guide_msf'           => $this->texte($l[4]),
                'noms_commerciaux'    => $this->texte($l[5]),
                'noms_arretes'        => $this->texte($l[6]),
                'dorosz'              => $this->texte($l[7]),
                'posologie_reference' => $this->texte($l[8]),
                'contre_indications'  => isset($ci[mb_strtolower($dci)]) ? implode(' ‖ ', $ci[mb_strtolower($dci)]) : null,
                'posologie_vidal'     => $this->texte($l[9]),
                'fiche_url'           => $this->texte($l[10]),
            ];
        }

        $abreviations = [];
        foreach ($onglet('Abréviations') as $l) {
            if (filled($l[1] ?? null)) {
                $abreviations[$this->texte($l[0])] = $this->texte($l[1]);
            }
        }

        $berrebi = [];
        foreach ($onglet('Berrebi – fiches', true) as $l) {
            $berrebi[$this->texte($l[1])] = [
                'specialite' => $this->texte($l[0]), 'titre' => $this->texte($l[1]), 'pages' => $this->texte($l[2]),
                'pages_lisibles' => $this->texte($l[3]), 'demarche' => $this->texte($l[4]),
                'medicaments_cites' => $this->texte($l[5]), 'traitement' => $this->texte($l[6]),
                'maladies' => FicheBerrebi::liste($l[7] ?? null), 'ordonnances' => [],
            ];
        }
        foreach ($onglet('Berrebi – ordonnances', true) as $l) {
            if (isset($berrebi[$this->texte($l[1])])) {
                $berrebi[$this->texte($l[1])]['ordonnances'][] = [
                    'dci' => $this->texte($l[2]), 'nom_commercial' => $this->texte($l[3]), 'posologie_ocr' => $this->texte($l[4]),
                    'nom_vidal' => $this->texte($l[5]), 'statut_2022' => $this->texte($l[6]), 'liste_oms' => $this->texte($l[7]),
                    'guide_msf' => $this->texte($l[8]), 'posologie_reference' => $this->texte($l[9]), 'statut_2025' => $this->texte($l[10]),
                ];
            }
        }

        return compact('maladies', 'lignes', 'dcis', 'abreviations', 'berrebi');
    }

    /**
     * Importe le guide. Avec $remplacer, tout ce qui ne vient pas du fichier
     * (maladies, symptômes, protocoles) est archivé : masqué, mais conservé pour
     * les consultations déjà enregistrées.
     */
    public function importer(string $fichier, bool $remplacer = false): array
    {
        $donnees = $this->analyser($fichier);

        return DB::transaction(function () use ($donnees, $remplacer) {
            foreach ($donnees['abreviations'] as $abreviation => $signification) {
                AbreviationClinique::updateOrCreate(['abreviation' => $abreviation], ['signification' => $signification]);
            }
            foreach ($donnees['dcis'] as $dci) {
                DciReference::updateOrCreate(['dci' => $dci['dci']], $dci);
            }

            $maladiesImportees = $symptomesImportes = $protocolesImportes = [];
            $lignesMedicaments = 0;

            foreach ($donnees['maladies'] as $m) {
                $maladie = Maladie::firstOrNew(['nom' => $m['nom']]);
                $maladie->fill([
                    'chapitre' => $m['chapitre'], 'description' => $m['symptomes_texte'], 'signes_gravite' => $m['signes_gravite'],
                    'examens' => $m['examens'], 'traitement' => $m['traitement'], 'prevention' => $m['prevention'],
                    'alerte_oms' => $m['alerte_oms'], 'source' => self::SOURCE, 'source_url' => $m['source_url'], 'actif' => true,
                ])->save();
                $maladiesImportees[] = $maladie->id;

                $ids = [];
                foreach ($m['symptomes'] as $nom) {
                    // Collation insensible à la casse et aux accents : « Fièvre » retrouve « fievre »
                    $symptome = Symptome::where('nom', $nom)->first() ?? Symptome::create(['nom' => $nom]);
                    $symptome->update(['actif' => true, 'nom' => $nom]);
                    $ids[] = $symptome->id;
                }
                $maladie->symptomes()->sync(array_unique($ids));
                $symptomesImportes = array_merge($symptomesImportes, $ids);

                // Un seul protocole par maladie (contrainte en base) : il est réécrit
                $protocole = ProtocoleTraitement::firstOrNew(['maladie_id' => $maladie->id]);
                $protocole->fill([
                    'titre' => 'Guide MSF — ' . $m['nom'],
                    'signes' => null, 'diagnostics' => null, 'germes_nourrisson' => null, 'germes_adulte' => null,
                    'traitement_principal' => Str::limit((string) $m['medicaments'], 250),
                    'posologie_principale' => $m['traitement'],
                    'traitement_alternatif' => null, 'posologie_alternative' => null,
                    'remarques' => $m['prevention'] ? 'Prévention : ' . $m['prevention'] : null,
                    'actif' => true,
                ])->save();
                $protocolesImportes[] = $protocole->id;

                $protocole->lignes()->delete();
                foreach ($donnees['lignes'][$m['nom']] ?? [] as $ligne) {
                    $protocole->lignes()->create(['nom_medicament' => self::nomLigne($ligne['dci']), 'type' => 'option', 'posologie' => $ligne['posologie'], 'alerte' => $ligne['alerte']]);
                    $lignesMedicaments++;
                }
            }

            $fichesBerrebi = $this->importerBerrebi($donnees['berrebi'], $donnees['maladies']);

            $archives = ['maladies' => 0, 'symptomes' => 0, 'protocoles' => 0];
            $supprimes = ['maladies' => 0, 'symptomes' => 0, 'protocoles' => 0];
            if ($remplacer) {
                // Absents du fichier : supprimés si aucune consultation ni fiche ne s'y réfère, sinon archivés
                $maladiesUtilisees = DB::table('consultation_maladie')->pluck('maladie_id')
                    ->merge(DB::table('consultations')->whereNotNull('maladie_id')->pluck('maladie_id'))
                    ->merge(DB::table('consultation_suggestions')->pluck('pathologie_id'))
                    ->merge(DB::table('fiches_syntheses')->whereNotNull('maladie_id')->pluck('maladie_id'))->unique()->all();
                $protocolesUtilises = DB::table('consultations')->whereNotNull('protocole_id')->pluck('protocole_id')->unique()->all();
                $symptomesUtilises = DB::table('consultation_symptome')->pluck('symptome_id')->unique()->all();

                $supprimes['protocoles'] = ProtocoleTraitement::whereNotIn('id', $protocolesImportes)->whereNotIn('id', $protocolesUtilises)
                    ->whereNotIn('maladie_id', $maladiesUtilisees)->delete();
                $supprimes['maladies'] = Maladie::whereNotIn('id', $maladiesImportees)->whereNotIn('id', $maladiesUtilisees)->delete();
                $supprimes['symptomes'] = Symptome::whereNotIn('id', array_unique($symptomesImportes))->whereNotIn('id', $symptomesUtilises)->delete();

                $archives['maladies'] = Maladie::whereNotIn('id', $maladiesImportees)->where('actif', true)->update(['actif' => false]);
                $archives['symptomes'] = Symptome::whereNotIn('id', array_unique($symptomesImportes))->where('actif', true)->update(['actif' => false]);
                $archives['protocoles'] = ProtocoleTraitement::whereNotIn('id', $protocolesImportes)->where('actif', true)->update(['actif' => false]);
            }

            // Relie chaque molécule à sa fiche du guide des médicaments
            app(RapprochementProtocoles::class)->relier();
            foreach (DciReference::whereNull('fiche_medicament_id')->get() as $reference) {
                $fiche = DB::table('protocole_medicament')->where('nom_medicament', self::nomLigne($reference->dci))->whereNotNull('fiche_medicament_id')->value('fiche_medicament_id');
                if ($fiche) {
                    $reference->update(['fiche_medicament_id' => $fiche]);
                }
            }

            return [
                'maladies' => count($maladiesImportees),
                'symptomes' => count(array_unique($symptomesImportes)),
                'medicaments' => count($donnees['dcis']),
                'lignes' => $lignesMedicaments,
                'abreviations' => count($donnees['abreviations']),
                'berrebi' => $fichesBerrebi,
                'archives' => $archives,
                'supprimes' => $supprimes,
            ];
        });
    }

    /**
     * Fiches Berrebi : créées ou mises à jour par titre, ordonnances réécrites,
     * reliées aux maladies MSF citées (dans la fiche ou dans la maladie).
     */
    private function importerBerrebi(array $fiches, array $maladies): int
    {
        if (!$fiches) {
            return 0;
        }
        $idsMaladies = Maladie::pluck('id', 'nom');
        $liens = [];
        foreach ($maladies as $m) {
            foreach ($m['berrebi'] as $titre) {
                $liens[$titre][] = $m['nom'];
            }
        }

        $gardees = [];
        foreach ($fiches as $titre => $f) {
            $fiche = FicheBerrebi::updateOrCreate(['titre' => $titre], collect($f)->except(['maladies', 'ordonnances'])->all());
            $fiche->ordonnances()->delete();
            foreach ($f['ordonnances'] as $ligne) {
                $fiche->ordonnances()->create($ligne);
            }
            $noms = array_unique(array_merge($f['maladies'], $liens[$titre] ?? []));
            $fiche->maladies()->sync(collect($noms)->map(fn($n) => $idsMaladies[$n] ?? null)->filter()->values()->all());
            $gardees[] = $fiche->id;
        }
        FicheBerrebi::whereNotIn('id', $gardees)->delete();

        return count($gardees);
    }

    /** « amoxicilline, acide clavulanique » (association) → « amoxicilline + acide clavulanique ». */
    public static function nomLigne(string $dci): string
    {
        return str_replace(', ', ' + ', $dci);
    }

    /** Inverse de nomLigne() : retrouve la DCI du fichier depuis le nom d'une ligne de protocole. */
    public static function dciDeLigne(string $nom): string
    {
        return str_replace(' + ', ', ', $nom);
    }

    /**
     * Découpe la colonne « Symptômes » en symptômes cochables :
     * « paludisme simple : fièvre, frissons, sueurs; chez l'E. dlr abdominales »
     * → Fièvre, Frissons, Sueurs, Douleurs abdominales.
     */
    public function symptomes(string $texte): array
    {
        $resultat = [];
        foreach (explode(';', $texte) as $element) {
            // « anémie. Paludisme grave : … » contient deux phrases
            foreach (preg_split('/\.\s+(?=\p{Lu})/u', trim($element)) as $partie) {
                foreach ($this->decouper($partie) as $symptome) {
                    $symptome = $this->nettoyer($symptome);
                    $cle = Str::lower(Str::ascii($symptome));
                    if (mb_strlen($symptome) >= 3 && mb_strlen($symptome) <= 120 && !isset($resultat[$cle])) {
                        $resultat[$cle] = $symptome;
                    }
                }
            }
        }

        return array_values($resultat);
    }

    /**
     * « Fièvre souvent > 39 °C » relie aussi la maladie au symptôme de base « Fièvre »,
     * pour que la recherche par symptômes la retrouve. Symptômes de base : ceux d'un ou
     * deux mots cités seuls ailleurs. Les libellés négatifs (« Fièvre absente… ») sont exclus.
     */
    private function ajouterSymptomesDeBase(array $maladies): array
    {
        $base = [];
        foreach ($maladies as $m) {
            foreach ($m['symptomes'] as $symptome) {
                if (count(preg_split('/\s+/u', $symptome)) <= 2) {
                    $base[Str::lower(Str::ascii($symptome))] = $symptome;
                }
            }
        }
        uksort($base, fn($a, $b) => strlen($b) <=> strlen($a));

        foreach ($maladies as &$m) {
            $cles = array_map(fn($x) => Str::lower(Str::ascii($x)), $m['symptomes']);
            foreach ($m['symptomes'] as $symptome) {
                $cle = Str::lower(Str::ascii($symptome));
                if (isset($base[$cle]) || preg_match('/\b(absente?s?|pas|sans|ni|aucune?)\b/', $cle)) {
                    continue;
                }
                foreach ($base as $cleBase => $libelle) {
                    if (preg_match('/^' . preg_quote($cleBase, '/') . '(\s|,|$)/', $cle)) {
                        if (!in_array($cleBase, $cles, true)) {
                            $m['symptomes'][] = $libelle;
                            $cles[] = $cleBase;
                        }
                        break;
                    }
                }
            }
        }

        return $maladies;
    }

    /** « en-tête : a, b, c » → [a, b, c] ; « description : précision » → [description]. */
    private function decouper(string $partie): array
    {
        $deuxPoints = $this->horsParentheses($partie, ':');
        if ($deuxPoints === null) {
            // Une vraie énumération (au moins 3 éléments courts) est séparée : « dlr abdominales, diarrhée, vomissements »
            $morceaux = $this->separerVirgules($partie);
            $courts = count(array_filter($morceaux, fn($m) => count(preg_split('/\s+/u', trim($m))) <= 4));

            return count($morceaux) >= 3 && $courts === count($morceaux) ? $morceaux : [$partie];
        }

        $avant = trim(mb_substr($partie, 0, $deuxPoints));
        $apres = trim(mb_substr($partie, $deuxPoints + 1));

        if ($this->horsParentheses($apres, ',') !== null) {
            return $this->separerVirgules($apres);
        }

        return str_word_count(Str::ascii($avant)) <= 3 ? [$apres] : [$avant];
    }

    private function separerVirgules(string $texte): array
    {
        $morceaux = [];
        $niveau = 0;
        $courant = '';
        foreach (mb_str_split($texte) as $c) {
            $niveau += $c === '(' ? 1 : ($c === ')' ? -1 : 0);
            if ($c === ',' && $niveau === 0) {
                $morceaux[] = $courant;
                $courant = '';
            } else {
                $courant .= $c;
            }
        }
        $morceaux[] = $courant;

        return $morceaux;
    }

    /** Position (en caractères) du premier $signe hors parenthèses, sinon null. */
    private function horsParentheses(string $texte, string $signe): ?int
    {
        $niveau = 0;
        foreach (mb_str_split($texte) as $i => $c) {
            $niveau += $c === '(' ? 1 : ($c === ')' ? -1 : 0);
            if ($c === $signe && $niveau === 0) {
                return $i;
            }
        }

        return null;
    }

    private function nettoyer(string $symptome): string
    {
        // trim() travaille octet par octet : la flèche (multi-octets) est retirée par expression régulière
        $s = preg_replace('/^[\s→]+|[\s→]+$/u', '', $symptome);
        // « chez l'E. dlr abdominales » : le terrain est retiré, le symptôme reste
        $s = preg_replace('/^chez (l\'|le |la |les )?(E\.|A\.|Nrs|NN|FE|SA|enfant|adulte|nourrisson|nouveau-né|femme enceinte|grand E\.)\s*/u', '', $s);

        // « dlr » devient « douleurs » devant un mot au pluriel
        $s = preg_replace('/(?<![\p{L}])dlr(?= \p{L}+s\b)/u', 'douleurs', $s);
        foreach (self::DEVELOPPER + ['dlr' => 'douleur'] as $abreviation => $sens) {
            $s = preg_replace('/(?<![\p{L}\p{N}])' . preg_quote($abreviation, '/') . '(?![\p{L}\p{N}])/u', $sens, $s);
        }
        $s = preg_replace(['/\s+/u', '/^[\s.,]+|[\s.,]+$/u'], [' ', ''], $s);

        return Str::ucfirst($s);
    }

    private function texte($valeur): ?string
    {
        $valeur = trim((string) $valeur);

        return $valeur === '' ? null : $valeur;
    }
}
