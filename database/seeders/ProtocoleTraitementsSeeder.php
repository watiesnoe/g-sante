<?php

namespace Database\Seeders;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProtocoleTraitementSeeder extends Seeder
{
    public function run(): void
    {
        $maladieIds = DB::table('maladies')->pluck('id', 'nom')->toArray();
        
        $protocoles = [
            // ==================== PALUDISME ====================
            [
                'maladie_id' => $maladieIds['Paludisme'],
                'titre' => 'Traitement du paludisme non compliqué à P. falciparum',
                'signes' => 'Fièvre, céphalées, fatigue, frissons, sueurs, douleurs musculaires et articulaires',
                'diagnostics' => 'Goutte épaisse ou test de diagnostic rapide (TDR) positif',
                'germes_nourrisson' => 'Plasmodium falciparum, Plasmodium vivax, Plasmodium ovale, Plasmodium malariae',
                'germes_adulte' => 'Plasmodium falciparum, Plasmodium vivax, Plasmodium ovale, Plasmodium malariae',
                'traitement_principal' => 'Artéméther/Luméfantrine (ACT)',
                'posologie_principale' => 'Selon le poids : 2 prises/jour pendant 3 jours. 5-15 kg: 1 cp 20/120mg par prise; 15-25 kg: 2 cp; 25-35 kg: 3 cp; ≥35 kg: 4 cp',
                'traitement_alternatif' => 'Artésunate/Amodiaquine (AS/AQ)',
                'posologie_alternative' => 'Selon le poids : 1 fois/jour pendant 3 jours. 4,5-9 kg: 1 cp 25/67,5mg; 9-18 kg: 1 cp 50/135mg; 18-36 kg: 1 cp 100/270mg; ≥36 kg: 2 cp 100/270mg',
                'remarques' => 'Prendre les comprimés au cours des repas ou avec une boisson riche en graisse (lait). En cas de vomissements dans les 30 minutes, reprendre la même dose.',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'maladie_id' => $maladieIds['Paludisme'],
                'titre' => 'Traitement du paludisme grave (après artésunate IV)',
                'signes' => 'Altération de la conscience, détresse respiratoire, convulsions, choc, ictère, hémoglobinurie',
                'diagnostics' => 'Goutte épaisse positive + signes de gravité',
                'germes_nourrisson' => 'Plasmodium falciparum',
                'germes_adulte' => 'Plasmodium falciparum',
                'traitement_principal' => 'Artéméther/Luméfantrine (ACT)',
                'posologie_principale' => 'Même posologie que paludisme non compliqué, à démarrer 8-12h après la dernière injection d\'artésunate',
                'traitement_alternatif' => 'Quinine',
                'posologie_alternative' => 'Adulte: 600mg 3x/jour pendant 7 jours; Enfant: 10mg/kg 3x/jour pendant 7 jours',
                'remarques' => 'Ne pas utiliser la quinine si le patient a déjà reçu de la quinine ou méfloquine dans les 24h',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            
            // ==================== INFECTIONS RESPIRATOIRES ====================
            [
                'maladie_id' => $maladieIds['Infection respiratoire'],
                'titre' => 'Pneumonie sans signes de gravité',
                'signes' => 'Toux, fièvre, tachypnée, signes de lutte respiratoire (tirage, gémissements)',
                'diagnostics' => 'Examen clinique, saturation O2 > 90%, pas de signes de détresse respiratoire sévère',
                'germes_nourrisson' => 'Streptococcus pneumoniae, Haemophilus influenzae, Staphylococcus aureus, virus respiratoires',
                'germes_adulte' => 'Streptococcus pneumoniae, Mycoplasma pneumoniae, Chlamydia pneumoniae, virus',
                'traitement_principal' => 'Amoxicilline',
                'posologie_principale' => 'Adulte: 1g 3x/jour pendant 5 jours; Enfant: 30mg/kg 3x/jour pendant 5 jours',
                'traitement_alternatif' => 'Co-trimoxazole',
                'posologie_alternative' => 'Adulte: 800/160mg 2x/jour pendant 5 jours; Enfant: 20/4mg/kg 2x/jour pendant 5 jours',
                'remarques' => 'En cas d\'allergie aux pénicillines, utiliser le co-trimoxazole ou l\'azithromycine',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'maladie_id' => $maladieIds['Infection respiratoire'],
                'titre' => 'Pneumonie sévère (hospitalisation)',
                'signes' => 'Détresse respiratoire sévère, hypoxémie (SpO2 < 90%), cyanose, impossibilité de boire',
                'diagnostics' => 'Examen clinique + saturation O2, radiographie thoracique',
                'germes_nourrisson' => 'Streptococcus pneumoniae, Staphylococcus aureus, Klebsiella pneumoniae',
                'germes_adulte' => 'Streptococcus pneumoniae, Legionella pneumophila, bactéries Gram négatif',
                'traitement_principal' => 'Ceftriaxone IV puis relais Amoxicilline',
                'posologie_principale' => 'Ceftriaxone: 50-100mg/kg/jour IV pendant 3-5 jours puis Amoxicilline: 1g 3x/jour PO pour compléter 7-10 jours',
                'traitement_alternatif' => 'Céfotaxime IV puis relais Co-trimoxazole',
                'posologie_alternative' => 'Céfotaxime: 50mg/kg toutes les 8h IV pendant 3-5 jours puis Co-trimoxazole: 800/160mg 2x/jour PO',
                'remarques' => 'Oxygénothérapie si SpO2 < 90%',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            
            // ==================== HYPERTENSION ARTÉRIELLE ====================
            [
                'maladie_id' => $maladieIds['Hypertension artérielle'],
                'titre' => 'Traitement de l\'hypertension artérielle essentielle',
                'signes' => 'PA > 140/90 mmHg au repos, céphalées occipitales, vertiges, parfois asymptomatique',
                'diagnostics' => 'Mesure de la pression artérielle à plusieurs reprises, bilan biologique (créatininémie, glycémie, bilan lipidique)',
                'germes_nourrisson' => null,
                'germes_adulte' => null,
                'traitement_principal' => 'Amlodipine ou Énalapril',
                'posologie_principale' => 'Amlodipine: 5-10mg 1x/jour; Énalapril: 5-20mg 1-2x/jour',
                'traitement_alternatif' => 'Hydrochlorothiazide ou Bisoprolol',
                'posologie_alternative' => 'Hydrochlorothiazide: 12,5-25mg 1x/jour; Bisoprolol: 5-10mg 1x/jour',
                'remarques' => 'Traitement à vie. Surveillance de la TA, fonction rénale et électrolytes. Mesures hygiéno-diététiques associées.',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'maladie_id' => $maladieIds['Hypertension artérielle'],
                'titre' => 'Hypertension gravidique / Pré-éclampsie',
                'signes' => 'PA ≥ 140/90 mmHg après 20 semaines de grossesse, protéinurie, œdèmes',
                'diagnostics' => 'Mesure TA, bandelette urinaire (protéinurie), bilan biologique',
                'germes_nourrisson' => null,
                'germes_adulte' => null,
                'traitement_principal' => 'Labétalol ou Méthyldopa',
                'posologie_principale' => 'Labétalol: 100-200mg 2x/jour; Méthyldopa: 250mg 2-3x/jour jusqu\'à 1,5g/jour',
                'traitement_alternatif' => 'Nifédipine (forme à libération prolongée)',
                'posologie_alternative' => 'Nifédipine LP: 20-60mg 1x/jour',
                'remarques' => 'Surveillance étroite de la TA maternelle et du rythme cardiaque fœtal. Ne pas utiliser d\'IEC (contre-indiqués pendant la grossesse)',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            
            // ==================== DIABÈTE ====================
            [
                'maladie_id' => $maladieIds['Diabète de type 2'],
                'titre' => 'Traitement du diabète de type 2',
                'signes' => 'Polyurie, polydipsie, polyphagie, amaigrissement, fatigue, troubles visuels',
                'diagnostics' => 'Glycémie à jeun ≥ 7,0 mmol/L ou glycémie aléatoire ≥ 11,1 mmol/L ou HbA1c ≥ 6,5%',
                'germes_nourrisson' => null,
                'germes_adulte' => null,
                'traitement_principal' => 'Metformine',
                'posologie_principale' => '500mg 1x/jour le matin (semaine 1) puis 500mg 2x/jour (semaine 2) puis augmenter jusqu\'à 1g 2x/jour si nécessaire',
                'traitement_alternatif' => 'Glibenclamide ou Gliclazide',
                'posologie_alternative' => 'Glibenclamide: 2,5-5mg 1-2x/jour; Gliclazide: 40-80mg 1-2x/jour',
                'remarques' => 'Associer mesures hygiéno-diététiques et activité physique. Surveillance de la glycémie et de l\'HbA1c.',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            
            // ==================== GASTRO-ENTÉRITE ====================
            [
                'maladie_id' => $maladieIds['Gastro-entérite'],
                'titre' => 'Traitement de la gastro-entérite aiguë',
                'signes' => 'Diarrhée aqueuse, vomissements, douleurs abdominales, nausées, fièvre modérée',
                'diagnostics' => 'Examen clinique, évaluation du degré de déshydratation',
                'germes_nourrisson' => 'Rotavirus, Escherichia coli, norovirus',
                'germes_adulte' => 'Campylobacter jejuni, Salmonella, Shigella, norovirus',
                'traitement_principal' => 'SRO + Zinc (enfant)',
                'posologie_principale' => 'SRO: selon le plan A ou B OMS; Zinc: 20mg/jour (10mg si <6 mois) pendant 10 jours',
                'traitement_alternatif' => 'Métronidazole (si Giardia ou C. difficile suspecté)',
                'posologie_alternative' => 'Adulte: 500mg 3x/jour pendant 5-7 jours; Enfant: 15mg/kg 3x/jour',
                'remarques' => 'La réhydratation est la priorité. Ne pas utiliser d\'antidiarrhéiques chez l\'enfant. Éviter le lopéramide sauf cas particuliers.',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            
            // ==================== TYPHOÏDE ====================
            [
                'maladie_id' => $maladieIds['Typhoïde'],
                'titre' => 'Traitement de la fièvre typhoïde',
                'signes' => 'Fièvre élevée prolongée en plateau, céphalées, douleurs abdominales, constipation puis diarrhée, taches rosées',
                'diagnostics' => 'Hémoculture, coproculture, test de Widal-Felix (moins fiable)',
                'germes_nourrisson' => 'Salmonella enterica serovar Typhi, Salmonella Paratyphi',
                'germes_adulte' => 'Salmonella enterica serovar Typhi, Salmonella Paratyphi',
                'traitement_principal' => 'Ciprofloxacine (si souche sensible)',
                'posologie_principale' => 'Adulte: 500mg 2x/jour pendant 10-14 jours; Enfant: 15mg/kg 2x/jour',
                'traitement_alternatif' => 'Ceftriaxone IV ou Azithromycine',
                'posologie_alternative' => 'Ceftriaxone: 50-80mg/kg/jour IV pendant 10-14 jours; Azithromycine: 1g J1 puis 500mg/jour J2-J7',
                'remarques' => 'La résistance aux fluoroquinolones est fréquente. Adapter l\'antibiothérapie à l\'antibiogramme.',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            
            // ==================== DRÉPANOCYTOSE ====================
            [
                'maladie_id' => $maladieIds['Drépanocytose'],
                'titre' => 'Prévention des infections chez le drépanocytaire',
                'signes' => 'Anémie hémolytique, crises vaso-occlusives douloureuses, asplénie fonctionnelle',
                'diagnostics' => 'Électrophorèse de l\'hémoglobine (HbS > 50%)',
                'germes_nourrisson' => 'Streptococcus pneumoniae, Haemophilus influenzae, Salmonella, Mycoplasma',
                'germes_adulte' => 'Streptococcus pneumoniae, Salmonella, Escherichia coli',
                'traitement_principal' => 'Pénicilline V ou Amoxicilline (prophylaxie)',
                'posologie_principale' => 'Enfant < 3 ans: 125mg 2x/jour; Enfant 3-5 ans: 250mg 2x/jour; >5 ans: 500mg 2x/jour',
                'traitement_alternatif' => 'Érythromycine (si allergie pénicilline)',
                'posologie_alternative' => 'Enfant: 250mg 2x/jour',
                'remarques' => 'Prophylaxie recommandée jusqu\'à 5 ans minimum, à vie dans certaines recommandations. Vaccination anti-pneumocoque et anti-Hib obligatoire.',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'maladie_id' => $maladieIds['Drépanocytose'],
                'titre' => 'Traitement de la crise vaso-occlusive drépanocytaire',
                'signes' => 'Douleurs osseuses, articulaires, abdominales ou thoraciques intenses, fièvre possible',
                'diagnostics' => 'Examen clinique, NFS, bilan inflammatoire',
                'germes_nourrisson' => null,
                'germes_adulte' => null,
                'traitement_principal' => 'Paracétamol + Ibuprofène + Hydratation',
                'posologie_principale' => 'Paracétamol: 1g 4x/jour; Ibuprofène: 400mg 3x/jour; Hydratation: 2-3L/jour PO ou IV',
                'traitement_alternatif' => 'Morphine (douleurs sévères)',
                'posologie_alternative' => 'Morphine LI: 10mg toutes les 4h selon la douleur',
                'remarques' => 'Hospitaliser si douleurs sévères, fièvre, syndrome thoracique aigu ou anémie sévère.',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            
            // ==================== VIH/SIDA ====================
            [
                'maladie_id' => $maladieIds['VIH/SIDA'],
                'titre' => 'Traitement antirétroviral de première ligne (adulte)',
                'signes' => 'Symptômes variables selon le stade: fièvre, adénopathies, amaigrissement, infections opportunistes',
                'diagnostics' => 'Sérologie VIH positive, CD4 < 500/mm³, charge virale élevée',
                'germes_nourrisson' => null,
                'germes_adulte' => 'VIH-1',
                'traitement_principal' => 'Ténofovir + Lamivudine + Dolutégravir (TLD)',
                'posologie_principale' => '1 comprimé (TDF 300mg + 3TC 300mg + DTG 50mg) 1x/jour',
                'traitement_alternatif' => 'Ténofovir + Lamivudine + Éfavirenz (TLE)',
                'posologie_alternative' => 'TDF 300mg + 3TC 300mg + EFV 600mg 1x/jour',
                'remarques' => 'Traitement à vie. Surveillance de la charge virale, CD4, fonction rénale (ténofovir). Adapter si co-infections.',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            
            // ==================== MÉNINGITE ====================
            [
                'maladie_id' => $maladieIds['Méningite'],
                'titre' => 'Traitement de la méningite bactérienne',
                'signes' => 'Fièvre élevée, céphalées intenses, raideur de la nuque, vomissements, photophobie, troubles de la conscience',
                'diagnostics' => 'Ponction lombaire, analyse du LCR, examen direct, culture, antigènes',
                'germes_nourrisson' => 'Streptococcus agalactiae, Escherichia coli, Listeria monocytogenes',
                'germes_adulte' => 'Neisseria meningitidis, Streptococcus pneumoniae, Haemophilus influenzae',
                'traitement_principal' => 'Ceftriaxone',
                'posologie_principale' => 'Adulte: 2g 2x/jour IV pendant 7-14 jours; Enfant: 50-100mg/kg 2x/jour IV',
                'traitement_alternatif' => 'Céfotaxime + Gentamicine (si allergie ceftriaxone)',
                'posologie_alternative' => 'Céfotaxime: 50mg/kg toutes les 6h IV; Gentamicine: 5-6mg/kg 1x/jour IV',
                'remarques' => 'Corticothérapie (dexaméthasone) recommandée avant la première dose d\'antibiotique en cas de suspicion de méningite à pneumocoque',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            
            // ==================== HÉPATITE B ====================
            [
                'maladie_id' => $maladieIds['Hépatite B'],
                'titre' => 'Traitement de l\'hépatite B chronique',
                'signes' => 'Fatigue, ictère, hépatomégalie, asymptomatique souvent',
                'diagnostics' => 'AgHBs positif > 6 mois, transaminases élevées, charge virale > 2000 UI/mL',
                'germes_nourrisson' => 'Virus de l\'hépatite B (VHB)',
                'germes_adulte' => 'Virus de l\'hépatite B (VHB)',
                'traitement_principal' => 'Ténofovir',
                'posologie_principale' => 'TDF 300mg 1x/jour',
                'traitement_alternatif' => 'Lamivudine (si ténofovir indisponible)',
                'posologie_alternative' => '3TC 100mg 1x/jour',
                'remarques' => 'Surveillance fonction hépatique, charge virale, marqueurs viraux. Traitement à vie.',
                'uuid' => (string) Str::uuid(), 'created_at' => now(),
                'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ];
        
        DB::table('protocole_traitements')->insert($protocoles);
    }
}