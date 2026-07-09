<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Maladie;
use App\Models\Symptome;
use Illuminate\Support\Facades\DB;

class MaladieSymptomeSeederCustom extends Seeder
{
    public function run(): void
    {
        $liens = [
            "Obstruction aiguë des voies aériennes supérieures" => ["Stridor", "Dyspnée", "Toux", "Altération de la conscience"],
            "Rhinite (rhume) et rhinopharyngite" => ["Écoulement nasal", "Toux", "Fièvre", "Mal de gorge"],
            "Sinusite aiguë" => ["Écoulement nasal", "Céphalées", "Fièvre"],
            "Angine (pharyngite) aiguë" => ["Mal de gorge", "Fièvre", "Adénopathies"],
            "Diphtérie" => ["Mal de gorge", "Fièvre", "Stridor", "Adénopathies"],
            "Laryngotrachéite et laryngotrachéobronchite (croup)" => ["Toux", "Stridor", "Fièvre", "Dyspnée"],
            "Épiglottite" => ["Fièvre", "Stridor", "Dyspnée", "Mal de gorge"],
            "Trachéite bactérienne" => ["Fièvre", "Stridor", "Toux", "Dyspnée"],
            "Otite externe aiguë" => ["Otalgie"],
            "Otite moyenne aiguë (OMA)" => ["Otalgie", "Fièvre"],
            "Otite moyenne chronique suppurée (OMCS)" => ["Otalgie"],
            "Coqueluche" => ["Toux", "Vomissements"],
            "Bronchite aiguë" => ["Toux", "Fièvre", "Dyspnée"],
            "Bronchite chronique" => ["Toux", "Dyspnée", "Amaigrissement"],
            "Bronchiolite" => ["Toux", "Dyspnée", "Sifflements respiratoires", "Fièvre"],
            "Pneumonie chez l'enfant de moins de 5 ans" => ["Toux", "Fièvre", "Dyspnée"],
            "Pneumonie chez l'enfant de plus de 5 ans et l'adulte" => ["Toux", "Fièvre", "Dyspnée", "Douleur"],
            "Pneumonie traînante" => ["Toux", "Fièvre", "Amaigrissement"],
            "Staphylococcie pleuro-pulmonaire" => ["Toux", "Fièvre", "Dyspnée"],
            "Asthme aigu (crise d'asthme)" => ["Dyspnée", "Sifflements respiratoires", "Toux"],
            "Asthme chronique" => ["Dyspnée", "Sifflements respiratoires", "Toux"],
            "Tuberculose pulmonaire" => ["Toux", "Sueurs nocturnes", "Amaigrissement", "Fièvre"],

            "Diarrhée aiguë" => ["Diarrhée", "Vomissements", "Déshydratation", "Fièvre"],
            "Shigellose" => ["Diarrhée", "Fièvre", "Douleur abdominale"],
            "Amibiase" => ["Diarrhée", "Douleur abdominale"],
            "Reflux gastro-œsophagien" => ["Douleur abdominale"],
            "Ulcères gastro-duodénaux chez l'adulte" => ["Douleur abdominale", "Vomissements"],
            "Troubles dyspeptiques" => ["Douleur abdominale"],
            "Candidose orale ou oropharyngée" => ["Mal de gorge"],
            "Herpès buccal" => ["Éruption cutanée", "Fièvre"],
            "Stomatite du scorbut (carence en vitamine C)" => ["Éruption cutanée"],

            "Gale" => ["Prurit", "Éruption cutanée"],
            "Poux (pédiculoses)" => ["Prurit"],
            "Mycoses superficielles" => ["Prurit", "Éruption cutanée"],
            "Impétigo" => ["Éruption cutanée", "Fièvre"],
            "Furoncle et anthrax staphylococcique" => ["Douleur", "Fièvre", "Éruption cutanée"],
            "Erysipèle et cellulite" => ["Fièvre", "Douleur", "Éruption cutanée"],
            "Charbon cutané" => ["Éruption cutanée", "Fièvre"],
            "Tréponématoses endémiques" => ["Éruption cutanée", "Ulcération génitale"],
            "Lèpre" => ["Éruption cutanée"],
            "Herpès cutané" => ["Éruption cutanée", "Douleur"],
            "Zona" => ["Éruption cutanée", "Douleur"],
            "Eczéma" => ["Prurit", "Éruption cutanée"],
            "Dermatite séborrhéique" => ["Prurit", "Éruption cutanée"],
            "Urticaire" => ["Prurit", "Éruption cutanée"],
            "Pellagre" => ["Éruption cutanée", "Diarrhée", "Amaigrissement"],

            "Xérophtalmie (carence en vitamine A)" => ["Photophobie"],
            "Conjonctivite du nouveau-né" => ["Écoulement nasal"],
            "Kérato-conjonctivite virale épidémique" => ["Photophobie"],
            "Trachome" => ["Photophobie"],
            "Cellulite périorbitaire et orbitaire" => ["Fièvre", "Douleur", "Œdèmes"],
            "Onchocercose (cécité des rivières)" => ["Prurit", "Éruption cutanée"],
            "Loase" => ["Œdèmes", "Prurit"],

            "Paludisme" => ["Fièvre", "Frissons", "Céphalées", "Anémie", "Splénomégalie"],
            "Trypanosomiase humaine africaine (maladie du sommeil)" => ["Fièvre", "Adénopathies", "Céphalées"],
            "Trypanosomiase américaine (maladie de Chagas)" => ["Fièvre", "Œdèmes"],
            "Leishmanioses" => ["Fièvre", "Splénomégalie", "Éruption cutanée"],
            "Protozooses intestinales (diarrhées parasitaires)" => ["Diarrhée", "Douleur abdominale"],
            "Distomatoses (douves)" => ["Douleur abdominale", "Fièvre"],
            "Schistosomiases" => ["Hématurie", "Douleur abdominale"],
            "Cestodoses" => ["Douleur abdominale"],
            "Nématodoses" => ["Douleur abdominale", "Amaigrissement"],
            "Filarioses lymphatiques (FL)" => ["Œdèmes", "Fièvre"],

            "Méningite bactérienne" => ["Fièvre", "Céphalées", "Raideur de la nuque", "Photophobie", "Altération de la conscience"],
            "Tétanos" => ["Douleur", "Fièvre"],
            "Fièvres entériques (typhoïde et paratyphoïde)" => ["Fièvre", "Douleur abdominale", "Diarrhée"],
            "Brucellose" => ["Fièvre", "Sueurs nocturnes", "Douleur"],
            "Peste" => ["Fièvre", "Adénopathies"],
            "Leptospirose" => ["Fièvre", "Ictère", "Douleur"],
            "Fièvre récurrente à poux (FRP)" => ["Fièvre", "Frissons"],
            "Fièvres récurrentes à tiques (FRT)" => ["Fièvre", "Frissons"],
            "Rickettsioses éruptives" => ["Fièvre", "Éruption cutanée"],

            "Rougeole" => ["Fièvre", "Toux", "Écoulement nasal", "Éruption cutanée"],
            "Poliomyélite" => ["Fièvre"],
            "Rage" => ["Fièvre", "Agitation"],
            "Hépatites virales" => ["Ictère", "Amaigrissement", "Fièvre"],
            "Dengue" => ["Fièvre", "Céphalées", "Douleur"],
            "Fièvres hémorragiques virales" => ["Fièvre"],
            "Infection par le HIV et sida" => ["Amaigrissement", "Fièvre", "Sueurs nocturnes", "Adénopathies"],

            "Syndrome néphrotique chez l'enfant" => ["Œdèmes"],
            "Lithiase urinaire" => ["Douleur abdominale", "Hématurie"],
            "Cystite aiguë" => ["Brûlures mictionnelles"],
            "Pyélonéphrite aiguë" => ["Fièvre", "Douleur abdominale", "Brûlures mictionnelles"],
            "Prostatite aiguë" => ["Fièvre", "Douleur abdominale", "Brûlures mictionnelles"],
            "Écoulement urétral" => ["Écoulement urétral", "Brûlures mictionnelles"],
            "Écoulement vaginal anormal" => ["Écoulement vaginal anormal", "Prurit"],
            "Ulcérations génitales" => ["Ulcération génitale"],
            "Douleur abdominale basse chez la femme" => ["Douleur abdominale", "Fièvre"],
            "Infections génitales hautes (IGH)" => ["Douleur abdominale", "Fièvre", "Écoulement vaginal anormal"],
            "Condylomes" => ["Éruption cutanée"],
            "Saignements utérins anormaux (en dehors de la grossesse)" => ["Douleur abdominale"],

            "Plaie simple" => ["Douleur"],
            "Brûlures" => ["Douleur"],
            "Abcès cutané" => ["Douleur", "Fièvre", "Éruption cutanée"],
            "Pyomyosite" => ["Douleur", "Fièvre"],
            "Ulcère de jambe" => ["Douleur"],
            "Infections nécrosantes de la peau et des tissus mous" => ["Douleur", "Fièvre", "Altération de la conscience"],
            "Morsures et piqûres venimeuses" => ["Douleur", "Œdèmes"],
            "Infections dentaires" => ["Douleur", "Fièvre", "Œdèmes"],

            "Anxiété" => ["Anxiété", "Insomnie", "Tremblements"],
            "Insomnie" => ["Insomnie"],
            "Agitation" => ["Agitation"],
            "État confusionnel" => ["Altération de la conscience", "Agitation"],
            "Syndromes post-traumatiques" => ["Anxiété", "Insomnie"],
            "Dépression" => ["Insomnie", "Amaigrissement"],
            "Épisode psychotique aigu" => ["Agitation", "Altération de la conscience"],
            "Psychoses chroniques" => ["Agitation"],
            "Troubles bipolaires" => ["Agitation", "Insomnie"],

            "Drépanocytose" => ["Douleur", "Anémie", "Splénomégalie", "Fièvre", "Ictère"],
            "Épilepsie" => ["Convulsions / état de mal convulsif"],
            "Diabète de type 2 chez l'adulte" => ["Polyurie / polydipsie", "Amaigrissement"],
            "Hypertension artérielle essentielle de l'adulte (HTA)" => ["Céphalées"],
            "Insuffisance cardiaque chronique" => ["Dyspnée", "Œdèmes"],
            "Insuffisance cardiaque aiguë (OAP)" => ["Dyspnée", "Œdèmes"],
            "Goitre endémique et carence en iode" => ["Œdèmes"],
        ];

        foreach ($liens as $maladieNom => $symptomeNoms) {
            $maladie = Maladie::where('nom', $maladieNom)->first();
            if (!$maladie) {
                continue;
            }
            foreach ($symptomeNoms as $symptomeNom) {
                $symptome = Symptome::where('nom', $symptomeNom)->first();
                if (!$symptome) {
                    continue;
                }
                DB::table('maladie_symptome')->updateOrInsert(
                    ['maladie_id' => $maladie->id, 'symptome_id' => $symptome->id],
                    ['uuid' => (string) Str::uuid(), 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
