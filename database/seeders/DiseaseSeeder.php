<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiseaseSeeder extends Seeder
{
    /**
     * Seed the diseases (maladies) table with standardized names.
     */
    public function run(): void
    {
        $now = now();
        $diseases = [
            // ANESTHÉSIE
            "Anesthésie générale", "Anesthésie locale", "Prémédication", "Sédation consciencieuse",
            
            // ANALGÉSIE
            "Douleur légère à modérée", "Douleur modérée (inflammatoire)", "Rhumatisme articulaire aigu",
            "Maladie de Kawasaki", "Douleur sévère", "Douleur neuropathique",
            
            // ALLERGIE / ANAPHYLAXIE
            "Réaction allergique (urticaire)", "Anaphylaxie (choc)", "Allergie sévère / Œdème cérébral",
            "Réaction allergique sévère", "Inflammation allergique chronique",
            
            // INTOXICATIONS
            "Intoxication médicamenteuse aiguë", "Intoxication au paracétamol", "Intoxication aux organophosphorés",
            "Intoxication au sulfate de magnésium", "Intoxication aux opioïdes",
            "Intoxication aux métaux lourds (Pb, As, Hg)", "Intoxication au plomb", "Surcharge en fer (thalassémie)",
            
            // ÉPILEPSIE
            "Épilepsie (crises partielles)", "Épilepsie (crises généralisées tonico-cloniques)",
            "Épilepsie (crises généralisées)", "Épilepsie (absence, myoclonies)", "Épilepsie (crises d'absence typique)",
            "État de mal convulsif",
            
            // HELMINTHIASES
            "Ascaridiose (Ascaris)", "Oxyurose (Enterobius)", "Oxyurose", "Trichocéphalose (Trichuris)",
            "Téniasis (Taenia)", "Hyménolépiase (H. nana)",
            
            // FILARIOSES
            "Filariose lymphatique", "Onchocercose",
            
            // SCHISTOSOMIASES
            "Schistosomiase", "Fasciolose",
            
            // INFECTIONS BACTÉRIENNES
            "Angine streptococcique", "Angine streptococcique (prophylaxie RAA)", "Otite moyenne aiguë",
            "Otite moyenne (échec)", "Pneumonie (non grave)", "Méningite bactérienne", "Méningite néonatale",
            "Septicémie néonatale", "Infection urinaire (fille)", "Cystite aiguë", "Impétigo",
            "Infection staphylococcique cutanée", "Infection à Clostridium (anaérobie)", "Choléra", "Peste",
            "Brucellose", "Typhoïde (fièvre typhoïde)", "Shigellose", "Coqueluche", "Diphtérie",
            "Trachome (conjonctivite chronique)", "Syphilis", "Infections nosocomiales (SARM)",
            
            // LÈPRE
            "Lèpre paucibacillaire (PB)", "Lèpre multibacillaire (MB)",
            
            // TUBERCULOSE
            "Tuberculose (pulmonaire)", "Tuberculose latente", "Tuberculose multirésistante (MDR-TB)",
            
            // INFECTIONS FONGIQUES
            "Candidose oropharyngée (muguet)", "Candidose œsophagienne", "Cryptococcose (neuroméningée)",
            "Teignes (tinea capitis)", "Dermatophytose cutanée", "Leishmaniose viscérale (Kala-azar)", "Sporotrichose",
            
            // INFECTIONS VIRALES
            "Herpès simplex (HSV)", "Zona (Herpès zoster)", "Grippe (H1N1)",
            
            // VIH / SIDA
            "VIH (nourrisson)", "VIH (enfant >3 ans ou >10 kg)", "VIH (boosté par IP)", "VIH (association fixe)",
            "PTME (prévention mère‑enfant)",
            
            // PARASITOSES INTESTINALES
            "Amibiase intestinale", "Giardiase",
            
            // PALUDISME
            "Paludisme non compliqué (P. falciparum)", "Paludisme grave (pré‑hospitalier)",
            "Paludisme grave (hospitalier)", "Paludisme grave (alternative)", "Paludisme à P. vivax",
            "P. vivax / P. ovale (guérison radicale)", "Prophylaxie paludisme (>5 kg)",
            
            // CANCÉROLOGIE
            "Leucémie lymphoblastique aiguë (LLA)", "Lymphome de Burkitt", "Tumeur de Wilms (néphroblastome)",
            "Hyperuricémie (post‑chimiothérapie)", "Cystite hémorragique (post‑cyclophosphamide)",
            
            // HÉMATOLOGIE
            "Anémie ferriprive", "Anémie mégaloblastique (carence B9)", "Anémie pernicieuse (carence B12)",
            "Hémorragie du nouveau‑né (MK)", "Drépanocytose",
            
            // CARDIOLOGIE
            "Hypertension artérielle (HTA)", "Insuffisance cardiaque", "Œdèmes aigus", "Choc cardiogénique",
            
            // PNEUMOLOGIE
            "Crise d'asthme", "Crise d'asthme sévère", "Asthme chronique (entretien)",
            
            // OPHTALMOLOGIE
            "Kératite herpétique", "Conjonctivite bactérienne", "Glaucome chronique", "Anesthésie de la cornée",
            
            // DERMATOLOGIE
            "Gale (scabiose)", "Poux (pédiculose du cuir chevelu)", "Gale (alternative >2 ans)",
            "Eczéma atopique", "Brûlures (superficielles)", "Impétigo (superficiel)", "Acné (vulgaris)",
            "Psoriasis (chronique)", "Condylomes (anogénitaux)",
            
            // GASTROENTÉROLOGIE
            "Ulcère gastroduodénal", "Nausées / vomissements (post‑chimiothérapie)",
            "Nausées / vomissements (communs)", "Constipation (fonctionnelle)",
            "Déshydratation (diarrhée aiguë)", "Diarrhée aiguë (adjuvant)",
            
            // ENDOCRINOLOGIE
            "Diabète de type 1", "Diabète de type 2 (>10 ans)", "Hypoglycémie sévère",
            "Insuffisance surrénale (cortisol)", "Insuffisance surrénale (aldostérone)", "Hypothyroïdie",
            
            // NÉONATOLOGIE
            "Apnée du prématuré", "Persistance du canal artériel", "Détresse respiratoire du prématuré",
            
            // VITAMINES / CARENCES
            "Rachitisme (carence Vitamine D)", "Scorbut (carence Vitamine C)", "Béribéri (carence Vitamine B1)",
            "Neuropathie (isoniazide)", "Xérophtalmie (carence Vitamine A)", "Goitre endémique (carence iode)",
            
            // ORL
            "Otite externe (aiguë)", "Rhinite allergique (persistante)", "Congestion nasale (rhume)",
            
            // RÉANIMATION / HYDRO-ÉLECTROLYTES
            "Choc hypovolémique (urgences)", "Hypokaliémie modérée (≤ 3 mEq/L)",
            "Hypokaliémie sévère (arythmique)", "Hypoglycémie (sévière, symptomatique)",
            "Acidose métabolique sévère (pH <7,1)", "Déshydratation hypernatrémique (perdus)",
        ];

        foreach ($diseases as $name) {
            DB::table('maladies')->updateOrInsert(
                ['nom' => $name],
                [
                    'uuid' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'description' => "Pathologie standardisée : " . $name
                ]
            );
        }
    }
}
