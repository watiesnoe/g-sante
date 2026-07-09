<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Maladie;
use App\Models\Medicament;
use App\Models\ProtocoleTraitement;
use App\Models\ProtocoleMedicament;

/**
 * PASSE 1 : associe les médicaments de MedicamentsSeeder aux protocoles créés
 * par ProtocoleTraitementsSeeder. Doit être exécuté après ces deux seeders.
 */
class ProtocoleMedicamentSeeder extends Seeder
{
    public function run(): void
    {
        // [maladie => [ [medicament, type, posologie, duree], ... ]]
        $liens = [
            "Sinusite aiguë" => [["Amoxicilline", "principal", "1 g 3 fois/jour", "7 jours"]],
            "Angine (pharyngite) aiguë" => [["Benzathine benzylpénicilline", "alternatif", "600 000-1 200 000 UI IM dose unique", "dose unique"]],
            "Diphtérie" => [["Benzathine benzylpénicilline", "principal", "dose unique IM", "dose unique"]],
            "Laryngotrachéite et laryngotrachéobronchite (croup)" => [
                ["Dexaméthasone", "principal", "0,6 mg/kg IM dose unique", "dose unique"],
                ["Épinéphrine (adrénaline)", "alternatif", "0,5 ml/kg en nébulisation (max 5 ml)", "selon besoin"],
            ],
            "Épiglottite" => [["Ceftriaxone", "principal", "50-80 mg/kg/jour (enfant) ; 1-2 g/jour (adulte)", "7-10 jours"]],
            "Otite moyenne aiguë (OMA)" => [["Amoxicilline", "principal", "80-90 mg/kg/jour en 2 prises (enfant)", "5 jours"]],
            "Coqueluche" => [["Azithromycine", "principal", "10 mg/kg/j J1 puis 5 mg/kg/j", "5 jours"]],
            "Bronchite aiguë" => [["Paracétamol", "adjuvant", "selon poids, toutes les 6h si besoin", "si fièvre"]],
            "Pneumonie chez l'enfant de moins de 5 ans" => [
                ["Amoxicilline", "principal", "80-90 mg/kg/jour en 2 prises", "5 jours"],
                ["Ceftriaxone", "alternatif", "80 mg/kg/jour", "forme sévère"],
            ],
            "Pneumonie chez l'enfant de plus de 5 ans et l'adulte" => [["Amoxicilline", "principal", "1 g 3 fois/jour (adulte)", "7 jours"]],
            "Asthme aigu (crise d'asthme)" => [
                ["Salbutamol", "principal", "selon protocole d'inhalation/nébulisation", "répété selon réponse"],
                ["Prednisolone", "adjuvant", "1-2 mg/kg/jour", "3-5 jours"],
            ],
            "Tuberculose pulmonaire" => [
                ["Isoniazide", "principal", "5 mg/kg/jour (max 300 mg)", "2 mois puis 4 mois"],
                ["Rifampicine", "principal", "10 mg/kg/jour (max 600 mg)", "2 mois puis 4 mois"],
                ["Pyrazinamide", "principal", "25 mg/kg/jour", "2 mois (phase intensive)"],
                ["Éthambutol", "principal", "15-20 mg/kg/jour", "2 mois (phase intensive)"],
            ],

            "Diarrhée aiguë" => [
                ["Sels de réhydratation orale (SRO)", "principal", "selon plan OMS A/B/C", "jusqu'à arrêt de la diarrhée"],
                ["Sulfate de zinc", "adjuvant", "10-20 mg/jour selon âge", "10 jours"],
            ],
            "Shigellose" => [["Ciprofloxacine", "principal", "500 mg 2 fois/jour (adulte)", "3 jours"]],
            "Amibiase" => [["Métronidazole", "principal", "500-750 mg 3 fois/jour (adulte)", "7-10 jours"]],
            "Candidose orale ou oropharyngée" => [
                ["Nystatine", "principal", "100 000 UI 4 fois/jour", "7 jours"],
                ["Fluconazole", "alternatif", "selon poids, une fois/jour", "7-14 jours si forme étendue"],
            ],

            "Gale" => [["Perméthrine", "principal", "application unique 8-12h, à renouveler à J7-14", "2 applications"]],
            "Poux (pédiculoses)" => [["Perméthrine", "principal", "lotion 1%, application unique", "renouvelée à J7-10"]],
            "Impétigo" => [["Amoxicilline/Acide clavulanique (co-amoxiclav)", "principal", "selon poids", "7 jours"]],
            "Furoncle et anthrax staphylococcique" => [["Cloxacilline", "principal", "selon poids/voie", "7 jours"]],
            "Erysipèle et cellulite" => [["Amoxicilline/Acide clavulanique (co-amoxiclav)", "principal", "selon poids", "7-10 jours"]],
            "Lèpre" => [["Rifampicine", "principal", "600 mg une fois/mois (supervisée)", "6-12 mois"]],
            "Zona" => [["Aciclovir", "principal", "800 mg 5 fois/jour", "7 jours"]],
            "Eczéma" => [["Hydrocortisone", "principal", "application 1-2 fois/jour", "sur les poussées"]],
            "Urticaire" => [
                ["Hydroxyzine", "principal", "25 mg 2 fois/jour", "selon évolution"],
                ["Prednisolone", "alternatif", "selon poids", "forme sévère"],
            ],

            "Xérophtalmie (carence en vitamine A)" => [["Rétinol (vitamine A)", "principal", "200 000 UI (adapté à l'âge) à J1, J2, J8", "3 doses"]],
            "Conjonctivite du nouveau-né" => [["Ceftriaxone", "principal", "dose unique IM", "dose unique"]],
            "Trachome" => [["Azithromycine", "principal", "20 mg/kg dose unique (max 1 g)", "dose unique"]],
            "Cellulite périorbitaire et orbitaire" => [["Amoxicilline/Acide clavulanique (co-amoxiclav)", "principal", "selon poids", "7-10 jours"]],

            "Paludisme" => [
                ["Artéméther/Luméfantrine (AL)", "principal", "selon poids, 2 prises/jour", "3 jours"],
                ["Artésunate", "alternatif", "2,4 mg/kg à H0, H12, H24 puis 1 fois/jour", "forme sévère"],
                ["Chloroquine", "assos", "10 mg base/kg J1-J2, 5 mg base/kg J3", "P. vivax/ovale/malariae/knowlesi"],
            ],
            "Schistosomiases" => [["Praziquantel", "principal", "40 mg/kg", "dose unique"]],
            "Nématodoses" => [
                ["Albendazole", "principal", "400 mg", "dose unique"],
                ["Mébendazole", "alternatif", "100 mg 2 fois/jour", "3 jours"],
            ],
            "Filarioses lymphatiques (FL)" => [
                ["Ivermectine", "principal", "selon poids", "dose unique"],
                ["Albendazole", "principal", "400 mg", "dose unique"],
            ],

            "Méningite bactérienne" => [["Ceftriaxone", "principal", "100 mg/kg/jour (enfant) ; 2 g 2 fois/jour (adulte)", "7-10 jours"]],
            "Tétanos" => [
                ["Métronidazole", "principal", "selon poids, IV/PO", "7-10 jours"],
                ["Diazépam", "adjuvant", "selon protocole de contrôle des spasmes", "selon évolution"],
            ],
            "Fièvres entériques (typhoïde et paratyphoïde)" => [["Ceftriaxone", "principal", "selon poids", "7-14 jours"]],
            "Brucellose" => [
                ["Doxycycline", "principal", "100 mg 2 fois/jour", "6 semaines"],
                ["Gentamicine", "adjuvant", "selon poids", "7 jours"],
            ],

            "Rougeole" => [
                ["Rétinol (vitamine A)", "principal", "dose selon âge à J1 et J2", "2 doses"],
                ["Amoxicilline", "alternatif", "selon poids", "5 jours (préventif < 5 ans)"],
                ["Paracétamol", "adjuvant", "selon poids", "si fièvre"],
            ],
            "Dengue" => [["Paracétamol", "principal", "selon poids", "si fièvre/douleur"]],

            "Cystite aiguë" => [["Ciprofloxacine", "principal", "500 mg 2 fois/jour", "3-7 jours"]],
            "Pyélonéphrite aiguë" => [["Ceftriaxone", "principal", "selon poids puis relais PO", "10-14 jours"]],
            "Syndrome néphrotique chez l'enfant" => [["Prednisolone", "principal", "60 mg/m²/jour phase d'attaque", "2-4 mois au total"]],

            "Brûlures" => [["Paracétamol", "principal", "selon poids", "selon douleur"]],
            "Abcès cutané" => [["Cloxacilline", "alternatif", "selon poids", "5-7 jours"]],

            "Anxiété" => [
                ["Hydroxyzine", "principal", "25-50 mg 2 fois/jour (max 100 mg/j)", "1 semaine puis réévaluation"],
                ["Diazépam", "alternatif", "2,5-5 mg 2 fois/jour", "max 2-3 semaines"],
            ],
            "Insomnie" => [
                ["Prométhazine", "principal", "25 mg au coucher", "7-10 jours"],
                ["Diazépam", "alternatif", "2-5 mg au coucher", "max 7 jours"],
            ],
            "Dépression" => [["Fluoxétine", "principal", "20 mg une fois/jour", "plusieurs mois selon évolution"]],
            "Épisode psychotique aigu" => [
                ["Halopéridol", "principal", "selon protocole IM", "selon évolution"],
                ["Diazépam", "adjuvant", "selon agitation", "si besoin"],
            ],

            "Drépanocytose" => [["Paracétamol", "principal", "selon poids/intensité", "selon crise"]],
            "Épilepsie" => [["Phénobarbital", "principal", "3-5 mg/kg/jour (enfant) ; 2-3 mg/kg/jour (adulte)", "traitement de fond au long cours"]],
            "Diabète de type 2 chez l'adulte" => [
                ["Metformine", "principal", "500 mg 2-3 fois/jour, à adapter", "au long cours"],
                ["Insuline", "alternatif", "selon protocole d'insulinothérapie", "si échec des ADO"],
            ],
            "Hypertension artérielle essentielle de l'adulte (HTA)" => [["Énalapril", "principal", "5-20 mg une fois/jour", "au long cours"]],
            "Insuffisance cardiaque chronique" => [
                ["Furosémide", "principal", "à adapter selon réponse clinique", "au long cours"],
                ["Énalapril", "principal", "à adapter selon réponse clinique", "au long cours"],
            ],
        ];

        // 1. Charger et parser public/index.html pour trouver les liens protocole-médicament
        $htmlPath = base_path('public/index.html');
        if (file_exists($htmlPath)) {
            $html = file_get_contents($htmlPath);
            if (preg_match('/const DATA = \[(.*?)\];/s', $html, $matches)) {
                $dataStr = trim($matches[1]);
                $lines = explode("\n", $dataStr);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    $nom = '';
                    $meds = [];
                    if (preg_match('/t:"([^"]*)"/', $line, $mTitle)) $nom = $mTitle[1];
                    if (preg_match('/m:\[([^\]]*)\]/', $line, $mMeds)) {
                        $medsStr = $mMeds[1];
                        if (!empty($medsStr)) {
                            $parts = explode(',', $medsStr);
                            foreach ($parts as $part) {
                                $medName = trim($part, ' "');
                                if (!empty($medName)) {
                                    $meds[] = [$medName, 'principal', 'Selon protocole', 'Selon protocole'];
                                }
                            }
                        }
                    }
                    
                    if (!empty($nom) && !empty($meds)) {
                        if (!isset($liens[$nom])) {
                            $liens[$nom] = $meds;
                        } else {
                            foreach ($meds as $newMed) {
                                $exists = false;
                                foreach ($liens[$nom] as $existingMed) {
                                    if (strcasecmp($existingMed[0], $newMed[0]) === 0) {
                                        $exists = true;
                                        break;
                                    }
                                }
                                if (!$exists) {
                                    $liens[$nom][] = $newMed;
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($liens as $maladieNom => $meds) {
            $maladie = Maladie::where('nom', $maladieNom)->first();
            if (!$maladie) {
                continue;
            }
            $protocole = ProtocoleTraitement::where('maladie_id', $maladie->id)->first();
            if (!$protocole) {
                continue;
            }
            foreach ($meds as [$medNom, $type, $posologie, $duree]) {
                $medicament = Medicament::where('nom', $medNom)->first();
                if (!$medicament) {
                    continue;
                }
                ProtocoleMedicament::firstOrCreate(
                    [
                        'protocole_id' => $protocole->id,
                        'medicament_id' => $medicament->id,
                        'type' => $type,
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'posologie' => $posologie,
                        'duree' => $duree,
                    ]
                );
            }
        }
    }
}
