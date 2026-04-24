<?php

namespace Database\Seeders;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicamentSeeder extends Seeder
{
    public function run(): void
    {
        $uniteIds = DB::table('unites')->pluck('id', 'nom')->toArray();
        $familleIds = DB::table('familles')->pluck('id', 'nom')->toArray();
        
        $medicaments = [];
        
        // ==================== ANTIBIOTIQUES ====================
        
        $medicaments[] = [
            'nom' => 'Amoxicilline 500mg',
            'description' => 'Antibiotique de la famille des pénicillines. Indiqué dans les infections respiratoires, ORL, urinaires.',
            'stock' => 2000,
            'stock_min' => 200,
            'prix_achat' => 250,
            'prix_vente' => 500,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Amoxicilline 250mg dispersible',
            'description' => 'Antibiotique pour enfants, comprimé dispersible',
            'stock' => 1500,
            'stock_min' => 150,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Amoxicilline/Acide clavulanique 500/62.5mg',
            'description' => 'Antibiotique à large spectre avec inhibiteur de bêta-lactamase',
            'stock' => 800,
            'stock_min' => 80,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Amoxicilline/Acide clavulanique 875/125mg',
            'description' => 'Antibiotique à large spectre, dosage renforcé',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 600,
            'prix_vente' => 1200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Amoxicilline/Acide clavulanique suspension 125/31.25mg/5ml',
            'description' => 'Antibiotique pour enfants, suspension buvable',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['suspension'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Céfalexine 500mg',
            'description' => 'Céphalosporine de première génération, infections ORL et cutanées',
            'stock' => 600,
            'stock_min' => 60,
            'prix_achat' => 350,
            'prix_vente' => 700,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Céfalexine suspension 250mg/5ml',
            'description' => 'Céphalosporine pour enfants',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['suspension'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Céfixime 200mg',
            'description' => 'Céphalosporine de troisième génération',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Céfixime suspension 100mg/5ml',
            'description' => 'Céphalosporine de troisième génération pour enfants',
            'stock' => 250,
            'stock_min' => 25,
            'prix_achat' => 600,
            'prix_vente' => 1200,
            'unite_id' => $uniteIds['suspension'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Azithromycine 500mg',
            'description' => 'Macrolide, traitement des infections respiratoires et IST',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 600,
            'prix_vente' => 1200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Azithromycine 250mg',
            'description' => 'Macrolide, dosage pour traitement court',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 350,
            'prix_vente' => 700,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Azithromycine suspension 200mg/5ml',
            'description' => 'Macrolide pour enfants',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['suspension'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Clarithromycine 500mg',
            'description' => 'Macrolide, éradication H. pylori',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 700,
            'prix_vente' => 1400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Érythromycine 500mg',
            'description' => 'Macrolide, alternative aux pénicillines',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Érythromycine suspension 125mg/5ml',
            'description' => 'Macrolide pour enfants',
            'stock' => 250,
            'stock_min' => 25,
            'prix_achat' => 450,
            'prix_vente' => 900,
            'unite_id' => $uniteIds['suspension'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Ciprofloxacine 500mg',
            'description' => 'Fluoroquinolone, infections urinaires et digestives',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 450,
            'prix_vente' => 900,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Ciprofloxacine 250mg',
            'description' => 'Fluoroquinolone, dosage pour infections simples',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Doxycycline 100mg',
            'description' => 'Cycline, traitement du choléra, peste, brucellose',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Clindamycine 300mg',
            'description' => 'Lincosamide, infections à staphylocoques et anaérobies',
            'stock' => 200,
            'stock_min' => 20,
            'prix_achat' => 800,
            'prix_vente' => 1600,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Cloxacilline 500mg',
            'description' => 'Pénicilline anti-staphylococcique',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 350,
            'prix_vente' => 700,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Co-trimoxazole 800/160mg',
            'description' => 'Association antibactérienne, traitement de la pneumocystose',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 250,
            'prix_vente' => 500,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Co-trimoxazole 400/80mg',
            'description' => 'Association antibactérienne, dosage pédiatrique',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Métronidazole 500mg',
            'description' => 'Antiprotozoaire et antibactérien (anaérobies)',
            'stock' => 600,
            'stock_min' => 60,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Métronidazole 250mg',
            'description' => 'Antiprotozoaire, dosage standard',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 120,
            'prix_vente' => 240,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Métronidazole suspension 200mg/5ml',
            'description' => 'Antiprotozoaire pour enfants',
            'stock' => 200,
            'stock_min' => 20,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['suspension'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Fosfomycine trométamol 3g',
            'description' => 'Antibiotique pour cystite aiguë, dose unique',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 800,
            'prix_vente' => 1600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Nitrofurantoïne 100mg',
            'description' => 'Antibiotique pour cystite, alternative',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 250,
            'prix_vente' => 500,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Phénoxyméthylpénicilline 250mg',
            'description' => 'Pénicilline V, angine streptococcique',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== ANTIDOULEURS ET AINS ====================
        
        $medicaments[] = [
            'nom' => 'Paracétamol 500mg',
            'description' => 'Analgésique et antipyrétique de première intention',
            'stock' => 2000,
            'stock_min' => 200,
            'prix_achat' => 100,
            'prix_vente' => 200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Paracétamol 100mg',
            'description' => 'Analgésique pédiatrique',
            'stock' => 1500,
            'stock_min' => 150,
            'prix_achat' => 50,
            'prix_vente' => 100,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Paracétamol suspension 120mg/5ml',
            'description' => 'Analgésique pour enfants',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['suspension'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Ibuprofène 400mg',
            'description' => 'Anti-inflammatoire non stéroïdien, antalgique, antipyrétique',
            'stock' => 800,
            'stock_min' => 80,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['AINS'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Ibuprofène 200mg',
            'description' => 'AINS, dosage pour douleurs légères',
            'stock' => 800,
            'stock_min' => 80,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['AINS'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Ibuprofène suspension 100mg/5ml',
            'description' => 'AINS pour enfants',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['suspension'],
            'famille_id' => $familleIds['AINS'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Aspirine 300mg',
            'description' => 'AINS, antiagrégant plaquettaire à faible dose',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['AINS'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Aspirine 75mg gastro-résistant',
            'description' => 'Antiagrégant plaquettaire, prévention cardiovasculaire',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 100,
            'prix_vente' => 200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['AINS'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Codéine 30mg',
            'description' => 'Analgésique opioïde pour douleurs modérées',
            'stock' => 200,
            'stock_min' => 20,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Tramadol 50mg',
            'description' => 'Analgésique opioïde pour douleurs modérées à sévères',
            'stock' => 150,
            'stock_min' => 15,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Morphine LI 10mg',
            'description' => 'Analgésique opioïde puissant pour douleurs intenses',
            'stock' => 100,
            'stock_min' => 10,
            'prix_achat' => 800,
            'prix_vente' => 1600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Morphine LP 30mg',
            'description' => 'Analgésique opioïde à libération prolongée',
            'stock' => 80,
            'stock_min' => 8,
            'prix_achat' => 1000,
            'prix_vente' => 2000,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== ANTIPALUDÉENS ====================
        
        $medicaments[] = [
            'nom' => 'Artéméther/Luméfantrine 20/120mg',
            'description' => 'Combinaison thérapeutique à base d\'artémisinine (ACT)',
            'stock' => 1000,
            'stock_min' => 100,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antipaludéens'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Artéméther/Luméfantrine 80/480mg',
            'description' => 'ACT, dosage adulte',
            'stock' => 800,
            'stock_min' => 80,
            'prix_achat' => 800,
            'prix_vente' => 1600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antipaludéens'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Artésunate/Amodiaquine 50/135mg',
            'description' => 'ACT, alternative à l\'artéméther/luméfantrine',
            'stock' => 800,
            'stock_min' => 80,
            'prix_achat' => 600,
            'prix_vente' => 1200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antipaludéens'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Chloroquine 155mg base',
            'description' => 'Antipaludique pour P. vivax sensible',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antipaludéens'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Quinine 300mg',
            'description' => 'Antipaludique de deuxième intention',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antipaludéens'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Sulfadoxine/Pyriméthamine 500/25mg',
            'description' => 'Traitement préventif intermittent du paludisme chez la femme enceinte',
            'stock' => 600,
            'stock_min' => 60,
            'prix_achat' => 250,
            'prix_vente' => 500,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antipaludéens'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== ANTIHYPERTENSEURS ====================
        
        $medicaments[] = [
            'nom' => 'Amlodipine 5mg',
            'description' => 'Inhibiteur calcique, antihypertenseur',
            'stock' => 600,
            'stock_min' => 60,
            'prix_achat' => 250,
            'prix_vente' => 500,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antihypertenseurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Amlodipine 10mg',
            'description' => 'Inhibiteur calcique, dosage renforcé',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 350,
            'prix_vente' => 700,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antihypertenseurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Énalapril 5mg',
            'description' => 'Inhibiteur de l\'enzyme de conversion',
            'stock' => 600,
            'stock_min' => 60,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antihypertenseurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Énalapril 20mg',
            'description' => 'IEC, dosage renforcé',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antihypertenseurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Hydrochlorothiazide 25mg',
            'description' => 'Diurétique thiazidique, antihypertenseur',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antihypertenseurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Bisoprolol 5mg',
            'description' => 'Bêta-bloquant cardioselectif',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antihypertenseurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Labétalol 100mg',
            'description' => 'Bêta-bloquant, hypertension gravidique',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antihypertenseurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Méthyldopa 250mg',
            'description' => 'Antihypertenseur d\'action centrale, grossesse',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 350,
            'prix_vente' => 700,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antihypertenseurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== ANTIDIABÉTIQUES ====================
        
        $medicaments[] = [
            'nom' => 'Metformine 500mg',
            'description' => 'Antidiabétique oral, première intention',
            'stock' => 800,
            'stock_min' => 80,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antidiabétiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Metformine 1g',
            'description' => 'Antidiabétique, dosage fort',
            'stock' => 600,
            'stock_min' => 60,
            'prix_achat' => 350,
            'prix_vente' => 700,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antidiabétiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Glibenclamide 5mg',
            'description' => 'Sulfamide hypoglycémiant, deuxième intention',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antidiabétiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Gliclazide 80mg',
            'description' => 'Sulfamide hypoglycémiant pour patients âgés',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antidiabétiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== CORTICOÏDES ====================
        
        $medicaments[] = [
            'nom' => 'Dexaméthasone 2mg',
            'description' => 'Corticoïde à longue durée d\'action',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Corticoïdes'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Dexaméthasone 4mg',
            'description' => 'Corticoïde, dosage fort',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Corticoïdes'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Prednisolone 5mg',
            'description' => 'Corticoïde à durée intermédiaire',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Corticoïdes'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Prednisone 20mg',
            'description' => 'Corticoïde, anti-inflammatoire',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Corticoïdes'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Hydrocortisone crème 1%',
            'description' => 'Dermocorticoïde, eczéma, dermatite',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['crème'],
            'famille_id' => $familleIds['Corticoïdes'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== ANTIHISTAMINIQUES ====================
        
        $medicaments[] = [
            'nom' => 'Loratadine 10mg',
            'description' => 'Antihistaminique non sédatif',
            'stock' => 600,
            'stock_min' => 60,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antihistaminiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Loratadine solution 5mg/5ml',
            'description' => 'Antihistaminique pour enfants',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['solution buvable'],
            'famille_id' => $familleIds['Antihistaminiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Prométhazine 25mg',
            'description' => 'Antihistaminique sédatif, antiémétique',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antihistaminiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Hydroxyzine 25mg',
            'description' => 'Antihistaminique, anxiolytique',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 250,
            'prix_vente' => 500,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antihistaminiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== GASTRO-ENTÉROLOGIQUES ====================
        
        $medicaments[] = [
            'nom' => 'Oméprazole 20mg',
            'description' => 'Inhibiteur de la pompe à protons, antiulcéreux',
            'stock' => 800,
            'stock_min' => 80,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Antiacides'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Oméprazole 10mg',
            'description' => 'IPP, dosage pédiatrique',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiacides'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Aluminium/Magnésium comprimé',
            'description' => 'Antiacide, soulagement des brûlures d\'estomac',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 100,
            'prix_vente' => 200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiacides'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Métoclopramide 10mg',
            'description' => 'Antiémétique, adulte uniquement',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antispasmodiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Butylscopolamine 10mg',
            'description' => 'Antispasmodique, douleurs abdominales',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antispasmodiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Lactulose solution',
            'description' => 'Laxatif osmotique, constipation',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['solution buvable'],
            'famille_id' => $familleIds['Antispasmodiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Bisacodyl 5mg',
            'description' => 'Laxatif stimulant',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 100,
            'prix_vente' => 200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antispasmodiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== BRONCHODILATATEURS ====================
        
        $medicaments[] = [
            'nom' => 'Salbutamol aérosol-doseur 100µg/dose',
            'description' => 'Bronchodilatateur, crise d\'asthme',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 800,
            'prix_vente' => 1600,
            'unite_id' => $uniteIds['aérosol'],
            'famille_id' => $familleIds['Bronchodilatateurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Salbutamol solution nébulisation 5mg/2.5ml',
            'description' => 'Bronchodilatateur pour nébuliseur',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 600,
            'prix_vente' => 1200,
            'unite_id' => $uniteIds['solution buvable'],
            'famille_id' => $familleIds['Bronchodilatateurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Béclométasone aérosol-doseur 250µg/dose',
            'description' => 'Corticoïde inhalé, asthme persistant',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 1000,
            'prix_vente' => 2000,
            'unite_id' => $uniteIds['aérosol'],
            'famille_id' => $familleIds['Bronchodilatateurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Ipratropium aérosol-doseur 20µg/dose',
            'description' => 'Bronchodilatateur anticholinergique',
            'stock' => 200,
            'stock_min' => 20,
            'prix_achat' => 800,
            'prix_vente' => 1600,
            'unite_id' => $uniteIds['aérosol'],
            'famille_id' => $familleIds['Bronchodilatateurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== ANTICONVULSIVANTS ====================
        
        $medicaments[] = [
            'nom' => 'Diazépam 5mg',
            'description' => 'Anxiolytique, anticonvulsivant',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Anticonvulsivants'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Carbamazépine 200mg',
            'description' => 'Antiépileptique, douleurs neuropathiques',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Anticonvulsivants'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Phénobarbital 60mg',
            'description' => 'Antiépileptique, sédatif',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Anticonvulsivants'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Phénytoïne 100mg',
            'description' => 'Antiépileptique, alternative',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 250,
            'prix_vente' => 500,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Anticonvulsivants'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Valproate de sodium 500mg',
            'description' => 'Antiépileptique, thymorégulateur',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Anticonvulsivants'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== VITAMINES ET SUPPLÉMENTS ====================
        
        $medicaments[] = [
            'nom' => 'Fer (sulfate ferreux) 200mg',
            'description' => 'Antianémique, carence en fer',
            'stock' => 800,
            'stock_min' => 80,
            'prix_achat' => 100,
            'prix_vente' => 200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Fer/Acide folique',
            'description' => 'Prévention carences pendant grossesse',
            'stock' => 600,
            'stock_min' => 60,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Acide folique 5mg',
            'description' => 'Traitement anémie mégaloblastique',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 100,
            'prix_vente' => 200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Vitamine A 200000 UI',
            'description' => 'Prévention et traitement carence vitamine A',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Vitamine C 500mg',
            'description' => 'Traitement scorbut',
            'stock' => 600,
            'stock_min' => 60,
            'prix_achat' => 100,
            'prix_vente' => 200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Zinc sulfate 20mg',
            'description' => 'Complément diarrhée enfant',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Multivitamines',
            'description' => 'Complexe vitaminique',
            'stock' => 600,
            'stock_min' => 60,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Vitamine B1 50mg',
            'description' => 'Traitement béribéri, neuropathies alcooliques',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 100,
            'prix_vente' => 200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Vitamine B6 25mg',
            'description' => 'Prévention neuropathies induites par isoniazide',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 100,
            'prix_vente' => 200,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Vitamine D3 100000 UI',
            'description' => 'Prévention et traitement carence vitamine D',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['solution buvable'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== ANTIPARASITAIRES ====================
        
        $medicaments[] = [
            'nom' => 'Albendazole 400mg',
            'description' => 'Anthelminthique large spectre',
            'stock' => 800,
            'stock_min' => 80,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiparasitaires'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Mébendazole 100mg',
            'description' => 'Anthelminthique',
            'stock' => 600,
            'stock_min' => 60,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiparasitaires'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Ivermectine 3mg',
            'description' => 'Anthelminthique, onchocercose, gale',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiparasitaires'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Praziquantel 600mg',
            'description' => 'Anthelminthique, schistosomiase',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiparasitaires'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Fluconazole 200mg',
            'description' => 'Antifongique, candidoses',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Antifongiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Fluconazole 50mg',
            'description' => 'Antifongique, dosage pédiatrique',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Antifongiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Nystatine suspension 100000 UI/ml',
            'description' => 'Antifongique, candidose oropharyngée',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['suspension'],
            'famille_id' => $familleIds['Antifongiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Clotrimazole crème 2%',
            'description' => 'Antifongique, mycoses cutanées',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['crème'],
            'famille_id' => $familleIds['Antifongiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== ANTIRÉTROVIRAUX ====================
        
        $medicaments[] = [
            'nom' => 'Ténofovir/Lamivudine/Dolutégravir 300/300/50mg',
            'description' => 'Antirétroviral, association complète',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 2000,
            'prix_vente' => 4000,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiviraux'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Ténofovir 300mg',
            'description' => 'Antirétroviral, inhibiteur nucléotidique',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 800,
            'prix_vente' => 1600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiviraux'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Lamivudine 150mg',
            'description' => 'Antirétroviral, inhibiteur nucléosidique',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiviraux'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Dolutégravir 50mg',
            'description' => 'Antirétroviral, inhibiteur de l\'intégrase',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 1200,
            'prix_vente' => 2400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiviraux'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Éfavirenz 600mg',
            'description' => 'Antirétroviral, inhibiteur non nucléosidique',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 800,
            'prix_vente' => 1600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiviraux'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Aciclovir 200mg',
            'description' => 'Antiviral, herpès',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiviraux'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Aciclovir 800mg',
            'description' => 'Antiviral, zona',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antiviraux'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== AUTRES MÉDICAMENTS ESSENTIELS ====================
        
        $medicaments[] = [
            'nom' => 'Furosémide 40mg',
            'description' => 'Diurétique de l\'anse',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Diurétiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Spironolactone 25mg',
            'description' => 'Diurétique d\'épargne potassique',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Diurétiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Amitriptyline 25mg',
            'description' => 'Antidépresseur tricyclique, douleurs neuropathiques',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 150,
            'prix_vente' => 300,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antidépresseurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Fluoxétine 20mg',
            'description' => 'Antidépresseur, inhibiteur sélectif recapture sérotonine',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['gélule'],
            'famille_id' => $familleIds['Antidépresseurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Sertraline 50mg',
            'description' => 'Antidépresseur, IRS',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antidépresseurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Halopéridol 5mg',
            'description' => 'Antipsychotique',
            'stock' => 200,
            'stock_min' => 20,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antipsychotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Risperidone 2mg',
            'description' => 'Antipsychotique atypique',
            'stock' => 200,
            'stock_min' => 20,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['comprimé'],
            'famille_id' => $familleIds['Antipsychotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Charbon activé 50g',
            'description' => 'Adsorbant, intoxication médicamenteuse',
            'stock' => 100,
            'stock_min' => 10,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['g'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'SRO (sels réhydratation orale)',
            'description' => 'Prévention et traitement déshydratation par diarrhée',
            'stock' => 1000,
            'stock_min' => 100,
            'prix_achat' => 50,
            'prix_vente' => 100,
            'unite_id' => $uniteIds['g'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== MÉDICAMENTS INJECTABLES ====================
        
        $medicaments[] = [
            'nom' => 'Ceftriaxone 1g injectable',
            'description' => 'Céphalosporine 3e génération, infections sévères',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 800,
            'prix_vente' => 1600,
            'unite_id' => $uniteIds['injection'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Gentamicine 80mg injectable',
            'description' => 'Aminoside, infections sévères',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['injection'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Artésunate 60mg injectable',
            'description' => 'Antipaludique, paludisme sévère',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 1000,
            'prix_vente' => 2000,
            'unite_id' => $uniteIds['injection'],
            'famille_id' => $familleIds['Antipaludéens'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Dexaméthasone 4mg injectable',
            'description' => 'Corticoïde, réactions allergiques sévères',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['injection'],
            'famille_id' => $familleIds['Corticoïdes'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Diazépam 10mg injectable',
            'description' => 'Anticonvulsivant, état de mal convulsif',
            'stock' => 200,
            'stock_min' => 20,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['injection'],
            'famille_id' => $familleIds['Anticonvulsivants'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Adrénaline 1mg injectable',
            'description' => 'Sympathomimétique, choc anaphylactique',
            'stock' => 200,
            'stock_min' => 20,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['injection'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Sulfate de magnésium 5g injectable',
            'description' => 'Anticonvulsivant, pré-éclampsie',
            'stock' => 200,
            'stock_min' => 20,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['injection'],
            'famille_id' => $familleIds['Anticonvulsivants'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Oxytocine 10UI injectable',
            'description' => 'Ocytocique, hémorragie post-partum',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['injection'],
            'famille_id' => $familleIds['Antispasmodiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Naloxone 0.4mg injectable',
            'description' => 'Antagoniste morphinique, surdosage opioïdes',
            'stock' => 100,
            'stock_min' => 10,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['injection'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Kétamine 50mg/ml injectable',
            'description' => 'Anesthésique général',
            'stock' => 150,
            'stock_min' => 15,
            'prix_achat' => 600,
            'prix_vente' => 1200,
            'unite_id' => $uniteIds['injection'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Midazolam 5mg injectable',
            'description' => 'Anxiolytique, anticonvulsivant',
            'stock' => 150,
            'stock_min' => 15,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['injection'],
            'famille_id' => $familleIds['Anticonvulsivants'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== MÉDICAMENTS USAGE EXTERNE ====================
        
        $medicaments[] = [
            'nom' => 'Povidone iodée 10% solution',
            'description' => 'Antiseptique, antisepsie cutanée',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['solution buvable'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Chlorhexidine 2% solution alcoolique',
            'description' => 'Antiseptique, antisepsie avant injection',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['solution buvable'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Perméthrine 5% crème',
            'description' => 'Scabicide, traitement de la gale',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'unite_id' => $uniteIds['crème'],
            'famille_id' => $familleIds['Antiparasitaires'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Mupirocine pommade 2%',
            'description' => 'Antibactérien, impétigo',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 600,
            'prix_vente' => 1200,
            'unite_id' => $uniteIds['pommade'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Tétracycline pommade ophtalmique 1%',
            'description' => 'Antibactérien, conjonctivite, prévention nouveau-né',
            'stock' => 300,
            'stock_min' => 30,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['pommade'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Oxyde de zinc pommade 10%',
            'description' => 'Protecteur cutané, érythème fessier',
            'stock' => 400,
            'stock_min' => 40,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'unite_id' => $uniteIds['pommade'],
            'famille_id' => $familleIds['Antidouleurs'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Fluorescéine collyre 0.5%',
            'description' => 'Colorant, détection érosion cornéenne',
            'stock' => 200,
            'stock_min' => 20,
            'prix_achat' => 400,
            'prix_vente' => 800,
            'unite_id' => $uniteIds['collyre'],
            'famille_id' => $familleIds['Antibiotiques'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // ==================== SOLUTIONS DE PERFUSION ====================
        
        $medicaments[] = [
            'nom' => 'Glucose 5% 500ml',
            'description' => 'Solution isotonique, véhicule pour médicaments',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'unite_id' => $uniteIds['ml'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'Ringer lactate 500ml',
            'description' => 'Solution isotonique, remplissage vasculaire',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 350,
            'prix_vente' => 700,
            'unite_id' => $uniteIds['ml'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        $medicaments[] = [
            'nom' => 'NaCl 0.9% 500ml',
            'description' => 'Solution isotonique, véhicule pour médicaments',
            'stock' => 500,
            'stock_min' => 50,
            'prix_achat' => 250,
            'prix_vente' => 500,
            'unite_id' => $uniteIds['ml'],
            'famille_id' => $familleIds['Vitamines'],
            'uuid' => (string) Str::uuid(), 'created_at' => now(),
            'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        
        // Insertion de tous les médicaments
        DB::table('medicaments')->insert($medicaments);
    }
}