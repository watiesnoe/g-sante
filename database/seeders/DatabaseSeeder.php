<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Nettoyer les tables avant de seed (optionnel mais recommandé pour test)
        // Décommentez si vous voulez repartir de zéro sans faire migrate:fresh
        // $this->cleanTables();

        // 1. Unités
        $unites = [
            ['nom' => 'mg', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'ml', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'UI', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Comprimé', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Gélule', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Sirop', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Injection', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($unites as $unite) { $unite['uuid'] = (string) Str::uuid();
            DB::table('unites')->updateOrInsert(
                ['nom' => $unite['nom']],
                $unite
            );
        }
        $uniteIds = DB::table('unites')->pluck('id', 'nom')->toArray();

        // 2. Familles de médicaments
        $familles = [
            ['nom' => 'Antibiotiques', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Antalgiques', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Anti-inflammatoires', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Antihypertenseurs', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Antidiabétiques', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Vitamines', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Antidépresseurs', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($familles as $famille) { $famille['uuid'] = (string) Str::uuid();
            DB::table('familles')->updateOrInsert(
                ['nom' => $famille['nom']],
                $famille
            );
        }
        $familleIds = DB::table('familles')->pluck('id', 'nom')->toArray();

        // 3. Assurances
        $assurances = [
            ['nom' => 'CNSS', 'telephone' => '0800100100', 'adresse' => 'Dakar', 'taux' => 80, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'IPM', 'telephone' => '0800200200', 'adresse' => 'Dakar', 'taux' => 70, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Aucune', 'telephone' => null, 'adresse' => null, 'taux' => 0, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($assurances as $assurance) { $assurance['uuid'] = (string) Str::uuid();
            DB::table('assurances')->updateOrInsert(
                ['nom' => $assurance['nom']],
                $assurance
            );
        }
        $assuranceIds = DB::table('assurances')->pluck('id', 'nom')->toArray();

        // 4. Services médicaux
        $services = [
            ['nom' => 'Médecine Générale', 'description' => 'Service de médecine générale', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Pédiatrie', 'description' => 'Service pédiatrique', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Gynécologie', 'description' => 'Service gynécologie-obstétrique', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Cardiologie', 'description' => 'Service de cardiologie', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Urgences', 'description' => 'Service des urgences', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Laboratoire', 'description' => 'Service de laboratoire d\'analyses', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Radiologie', 'description' => 'Service de radiologie', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($services as $service) { $service['uuid'] = (string) Str::uuid();
            DB::table('service_medicals')->updateOrInsert(
                ['nom' => $service['nom']],
                $service
            );
        }
        $serviceIds = DB::table('service_medicals')->pluck('id', 'nom')->toArray();

        // 5. Prestations
        $prestations = [
            ['service_medical_id' => $serviceIds['Médecine Générale'], 'nom' => 'Consultation générale', 'description' => 'Consultation médicale standard', 'quantifiable' => false, 'prix' => 5000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['service_medical_id' => $serviceIds['Pédiatrie'], 'nom' => 'Consultation pédiatrique', 'description' => 'Consultation pour enfants', 'quantifiable' => false, 'prix' => 6000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['service_medical_id' => $serviceIds['Gynécologie'], 'nom' => 'Consultation gynécologique', 'description' => 'Consultation spécialisée', 'quantifiable' => false, 'prix' => 7000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['service_medical_id' => $serviceIds['Cardiologie'], 'nom' => 'Consultation cardiologique', 'description' => 'Consultation cœur', 'quantifiable' => false, 'prix' => 8000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['service_medical_id' => $serviceIds['Laboratoire'], 'nom' => 'NFS', 'description' => 'Numération formule sanguine', 'quantifiable' => true, 'prix' => 3000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['service_medical_id' => $serviceIds['Laboratoire'], 'nom' => 'Glycémie', 'description' => 'Taux de sucre dans le sang', 'quantifiable' => true, 'prix' => 1500, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['service_medical_id' => $serviceIds['Radiologie'], 'nom' => 'Radio thorax', 'description' => 'Radiographie pulmonaire', 'quantifiable' => false, 'prix' => 10000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['service_medical_id' => $serviceIds['Radiologie'], 'nom' => 'Échographie', 'description' => 'Échographie abdominale', 'quantifiable' => false, 'prix' => 15000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($prestations as $prestation) { $prestation['uuid'] = (string) Str::uuid();
            DB::table('prestations')->updateOrInsert(
                ['nom' => $prestation['nom'], 'service_medical_id' => $prestation['service_medical_id']],
                $prestation
            );
        }
        $prestationIds = DB::table('prestations')->pluck('id', 'nom')->toArray();

        // 6. Examens
        $examens = [
            ['nom' => 'Bilan sanguin complet', 'description' => 'Analyse complète du sang', 'prix' => 20000, 'service_medical_id' => $serviceIds['Laboratoire'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Électrocardiogramme', 'description' => 'ECG', 'prix' => 12000, 'service_medical_id' => $serviceIds['Cardiologie'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'IRM cérébrale', 'description' => 'Imagerie par résonance magnétique', 'prix' => 50000, 'service_medical_id' => $serviceIds['Radiologie'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($examens as $examen) { $examen['uuid'] = (string) Str::uuid();
            DB::table('examens')->updateOrInsert(
                ['nom' => $examen['nom']],
                $examen
            );
        }

        // 7. Maladies
        $maladies = [
            ['nom' => 'Paludisme', 'description' => 'Infection parasitaire transmise par les moustiques', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Hypertension artérielle', 'description' => 'Pression artérielle élevée chronique', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Diabète de type 2', 'description' => 'Trouble de la régulation du sucre dans le sang', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Gastro-entérite', 'description' => 'Inflammation de l\'estomac et des intestins', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Infection respiratoire', 'description' => 'Infection des voies respiratoires', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Grossesse normale', 'description' => 'Suivi de grossesse normale', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($maladies as $maladie) { $maladie['uuid'] = (string) Str::uuid();
            DB::table('maladies')->updateOrInsert(
                ['nom' => $maladie['nom']],
                $maladie
            );
        }
        $maladieIds = DB::table('maladies')->pluck('id', 'nom')->toArray();

        // 8. Symptômes
        $symptomes = [
            ['nom' => 'Fièvre', 'description' => 'Température corporelle élevée', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Céphalées', 'description' => 'Maux de tête', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Toux', 'description' => 'Toux sèche ou grasse', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Douleur thoracique', 'description' => 'Douleur dans la poitrine', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Nausées', 'description' => 'Envie de vomir', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Fatigue', 'description' => 'Fatigue générale', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Dyspnée', 'description' => 'Essoufflement', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($symptomes as $symptome) { $symptome['uuid'] = (string) Str::uuid();
            DB::table('symptomes')->updateOrInsert(
                ['nom' => $symptome['nom']],
                $symptome
            );
        }
        $symptomeIds = DB::table('symptomes')->pluck('id', 'nom')->toArray();

        // 9. Association Maladie-Symptôme
        $maladieSymptomes = [
            ['maladie_id' => $maladieIds['Paludisme'], 'symptome_id' => $symptomeIds['Fièvre'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['maladie_id' => $maladieIds['Paludisme'], 'symptome_id' => $symptomeIds['Céphalées'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['maladie_id' => $maladieIds['Paludisme'], 'symptome_id' => $symptomeIds['Fatigue'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['maladie_id' => $maladieIds['Hypertension artérielle'], 'symptome_id' => $symptomeIds['Céphalées'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['maladie_id' => $maladieIds['Hypertension artérielle'], 'symptome_id' => $symptomeIds['Douleur thoracique'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['maladie_id' => $maladieIds['Gastro-entérite'], 'symptome_id' => $symptomeIds['Nausées'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['maladie_id' => $maladieIds['Infection respiratoire'], 'symptome_id' => $symptomeIds['Toux'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['maladie_id' => $maladieIds['Infection respiratoire'], 'symptome_id' => $symptomeIds['Fièvre'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($maladieSymptomes as $ms) { $ms['uuid'] = (string) Str::uuid(); $ms['uuid'] = (string) Str::uuid();
            DB::table('maladie_symptome')->updateOrInsert(
                ['maladie_id' => $ms['maladie_id'], 'symptome_id' => $ms['symptome_id']],
                $ms
            );
        }

        // 10. Médicaments
        $medicaments = [
            [
                'nom' => 'Paracétamol 500mg', 'description' => 'Antalgique', 'stock' => 500, 'stock_min' => 50,
                'prix_achat' => 500, 'prix_vente' => 1000, 'unite_id' => $uniteIds['Comprimé'],
                'famille_id' => $familleIds['Antalgiques'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'nom' => 'Ibuprofène 400mg', 'description' => 'Anti-inflammatoire', 'stock' => 300, 'stock_min' => 30,
                'prix_achat' => 800, 'prix_vente' => 1500, 'unite_id' => $uniteIds['Comprimé'],
                'famille_id' => $familleIds['Anti-inflammatoires'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'nom' => 'Amoxicilline 500mg', 'description' => 'Antibiotique', 'stock' => 200, 'stock_min' => 20,
                'prix_achat' => 1200, 'prix_vente' => 2000, 'unite_id' => $uniteIds['Gélule'],
                'famille_id' => $familleIds['Antibiotiques'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'nom' => 'Artésunate injectable', 'description' => 'Traitement paludisme grave', 'stock' => 50, 'stock_min' => 10,
                'prix_achat' => 5000, 'prix_vente' => 7500, 'unite_id' => $uniteIds['Injection'],
                'famille_id' => $familleIds['Antibiotiques'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'nom' => 'Vitamine C', 'description' => 'Complément alimentaire', 'stock' => 1000, 'stock_min' => 100,
                'prix_achat' => 200, 'prix_vente' => 500, 'unite_id' => $uniteIds['Comprimé'],
                'famille_id' => $familleIds['Vitamines'], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ];
        foreach ($medicaments as $medicament) { $medicament['uuid'] = (string) Str::uuid();
            DB::table('medicaments')->updateOrInsert(
                ['nom' => $medicament['nom']],
                $medicament
            );
        }
        $medicamentIds = DB::table('medicaments')->pluck('id', 'nom')->toArray();

        // 11. Utilisateurs (docteurs, pharmaciens, etc.)
        $users = [
            [
                'name' => 'Dr Diallo', 'nom' => 'Diallo', 'prenom' => 'Amadou', 'telephone' => '771234567',
                'adresse' => 'Dakar', 'role' => 'medecin', 'email' => 'doctor@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => $serviceIds['Médecine Générale'],
                'remember_token' => Str::random(10), 'email_verified_at' => now(), 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'name' => 'Dr Sow', 'nom' => 'Sow', 'prenom' => 'Fatou', 'telephone' => '772345678',
                'adresse' => 'Dakar', 'role' => 'medecin', 'email' => 'doctor2@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => $serviceIds['Pédiatrie'],
                'remember_token' => Str::random(10), 'email_verified_at' => now(), 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'name' => 'Pharmacien', 'nom' => 'Ndiaye', 'prenom' => 'Moussa', 'telephone' => '773456789',
                'adresse' => 'Dakar', 'role' => 'pharmacien', 'email' => 'pharmacy@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
                'remember_token' => Str::random(10), 'email_verified_at' => now(), 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'name' => 'Gestionnaire', 'nom' => 'Fall', 'prenom' => 'Aissatou', 'telephone' => '774567890',
                'adresse' => 'Dakar', 'role' => 'gestionnaire', 'email' => 'manager@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
                'remember_token' => Str::random(10), 'email_verified_at' => now(), 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'name' => 'SuperAdmin', 'nom' => 'Admin', 'prenom' => 'System', 'telephone' => '775678901',
                'adresse' => 'Dakar', 'role' => 'superadmin', 'email' => 'admin@example.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
                'remember_token' => Str::random(10), 'email_verified_at' => now(), 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Siabaneo', 'nom' => 'TRAORE', 'prenom' => 'Siabaneo', 'telephone' => '770000000',
                'adresse' => 'Dakar', 'role' => 'superadmin', 'email' => 'siabaneotraore@gmail.com',
                'password' => Hash::make('password'), 'statut' => 'actif', 'service_medical_id' => null,
                'remember_token' => Str::random(10), 'email_verified_at' => now(), 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'updated_at' => now()
            ],
        ];
        foreach ($users as $user) { $user['uuid'] = (string) Str::uuid();
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
        $userIds = DB::table('users')->pluck('id', 'email')->toArray();

        // 12. Salles
        $salles = [
            ['nom' => 'Salle A101', 'type' => 'Hospitalisation', 'service_medical_id' => $serviceIds['Médecine Générale'], 'capacite' => 10, 'prix' => 15000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Salle B202', 'type' => 'Hospitalisation', 'service_medical_id' => $serviceIds['Pédiatrie'], 'capacite' => 8, 'prix' => 12000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'Salle Urgences', 'type' => 'Urgences', 'service_medical_id' => $serviceIds['Urgences'], 'capacite' => 5, 'prix' => 20000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($salles as $salle) { $salle['uuid'] = (string) Str::uuid();
            DB::table('salles')->updateOrInsert(
                ['nom' => $salle['nom']],
                $salle
            );
        }
        $salleIds = DB::table('salles')->pluck('id', 'nom')->toArray();

        // 13. Lits
        $lits = [
            ['numero' => 'LIT-001', 'salle_id' => $salleIds['Salle A101'], 'statut' => 'Libre', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['numero' => 'LIT-002', 'salle_id' => $salleIds['Salle A101'], 'statut' => 'Libre', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['numero' => 'LIT-003', 'salle_id' => $salleIds['Salle A101'], 'statut' => 'Occupé', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['numero' => 'LIT-004', 'salle_id' => $salleIds['Salle B202'], 'statut' => 'Libre', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['numero' => 'LIT-005', 'salle_id' => $salleIds['Salle B202'], 'statut' => 'Libre', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['numero' => 'LIT-006', 'salle_id' => $salleIds['Salle Urgences'], 'statut' => 'Libre', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($lits as $lit) { $lit['uuid'] = (string) Str::uuid();
            DB::table('lits')->updateOrInsert(
                ['numero' => $lit['numero']],
                $lit
            );
        }
        $litIds = DB::table('lits')->pluck('id', 'numero')->toArray();

        // 14. Patients
        $patients = [
            [
                'nom' => 'Mbaye', 'prenom' => 'Mamadou', 'genre' => 'M', 'telephone' => '761234567',
                'ethnie' => 'Wolof', 'age' => 35, 'adresse' => 'Pikine', 'groupe_sanguin' => 'O+',
                'antecedents' => 'Hypertendu', 'assurance_id' => $assuranceIds['CNSS'], 'numero_assurance' => 'CNSS12345',
                'fin_validite_assurance' => '2025-12-31', 'est_decede' => false, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'nom' => 'Diop', 'prenom' => 'Aminata', 'genre' => 'F', 'telephone' => '762345678',
                'ethnie' => 'Sérère', 'age' => 28, 'adresse' => 'Guédiawaye', 'groupe_sanguin' => 'A+',
                'antecedents' => null, 'assurance_id' => $assuranceIds['IPM'], 'numero_assurance' => 'IPM67890',
                'fin_validite_assurance' => '2025-10-31', 'est_decede' => false, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'nom' => 'Sarr', 'prenom' => 'Ibrahima', 'genre' => 'M', 'telephone' => '763456789',
                'ethnie' => 'Peulh', 'age' => 45, 'adresse' => 'Dakar', 'groupe_sanguin' => 'B+',
                'antecedents' => 'Diabétique', 'assurance_id' => null, 'numero_assurance' => null,
                'fin_validite_assurance' => null, 'est_decede' => false, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'nom' => 'Faye', 'prenom' => 'Marième', 'genre' => 'F', 'telephone' => '764567890',
                'ethnie' => 'Wolof', 'age' => 8, 'adresse' => 'Rufisque', 'groupe_sanguin' => 'O-',
                'antecedents' => 'Asthme', 'assurance_id' => $assuranceIds['CNSS'], 'numero_assurance' => 'CNSS67890',
                'fin_validite_assurance' => '2025-11-30', 'est_decede' => false, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ];
        foreach ($patients as $patient) { $patient['uuid'] = (string) Str::uuid();
            DB::table('patients')->updateOrInsert(
                ['telephone' => $patient['telephone']],
                $patient
            );
        }
        $patientIds = DB::table('patients')->pluck('id', 'nom')->toArray();

        // 15. Tickets
        $tickets = [
            [
                'patient_id' => $patientIds['Mbaye'], 'user_id' => $userIds['doctor@example.com'],
                'description' => 'Consultation générale', 'total' => 5000, 'assurance_id' => $assuranceIds['CNSS'],
                'taux_couverture' => 80, 'part_assurance' => 4000, 'part_patient' => 1000,
                'date_validite' => Carbon::now()->addDays(7), 'statut' => 'valide', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'patient_id' => $patientIds['Diop'], 'user_id' => $userIds['doctor2@example.com'],
                'description' => 'Consultation pédiatrique', 'total' => 6000, 'assurance_id' => $assuranceIds['IPM'],
                'taux_couverture' => 70, 'part_assurance' => 4200, 'part_patient' => 1800,
                'date_validite' => Carbon::now()->addDays(7), 'statut' => 'valide', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ];
        foreach ($tickets as $ticket) { $ticket['uuid'] = (string) Str::uuid();
            DB::table('tickets')->insert($ticket);
        }
        $ticketIds = DB::table('tickets')->pluck('id')->toArray();

        // 16. Consultations
        $consultations = [
            [
                'ticket_id' => $ticketIds[0] ?? null, 'patient_id' => $patientIds['Mbaye'], 'medecin_id' => $userIds['doctor@example.com'],
                'date_consultation' => Carbon::now()->subDays(2), 'motif' => 'Fièvre et fatigue', 'diagnostic' => 'Paludisme',
                'notes' => 'Prescription d\'antipaludéens', 'poids' => 70, 'temperature' => 38.5,
                'tension' => '130/85', 'taille' => 175, 'imc' => 22.9, 'maladie_id' => $maladieIds['Paludisme'],
                'groupe_sanguin' => 'O+', 'adresse_patient' => 'Pikine', 'antecedents' => 'Hypertendu',
                'grossesse_id' => null, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'ticket_id' => $ticketIds[1] ?? null, 'patient_id' => $patientIds['Diop'], 'medecin_id' => $userIds['doctor2@example.com'],
                'date_consultation' => Carbon::now()->subDays(5), 'motif' => 'Toux persistante', 'diagnostic' => 'Infection respiratoire',
                'notes' => 'Antibiotiques et repos', 'poids' => 65, 'temperature' => 37.8,
                'tension' => '120/80', 'taille' => 165, 'imc' => 23.9, 'maladie_id' => $maladieIds['Infection respiratoire'],
                'groupe_sanguin' => 'A+', 'adresse_patient' => 'Guédiawaye', 'antecedents' => null,
                'grossesse_id' => null, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ];
        foreach ($consultations as $consultation) { $consultation['uuid'] = (string) Str::uuid();
            DB::table('consultations')->insert($consultation);
        }
        $consultationIds = DB::table('consultations')->pluck('id')->toArray();

        // 17. Consultation-Symptôme
        $consSymptomes = [
            ['consultation_id' => $consultationIds[0], 'symptome_id' => $symptomeIds['Fièvre']],
            ['consultation_id' => $consultationIds[0], 'symptome_id' => $symptomeIds['Céphalées']],
            ['consultation_id' => $consultationIds[0], 'symptome_id' => $symptomeIds['Fatigue']],
            ['consultation_id' => $consultationIds[1], 'symptome_id' => $symptomeIds['Toux']],
            ['consultation_id' => $consultationIds[1], 'symptome_id' => $symptomeIds['Fièvre']],
        ];
        foreach ($consSymptomes as $cs) { $cs['uuid'] = (string) Str::uuid(); $cs['uuid'] = (string) Str::uuid();
            DB::table('consultation_symptome')->updateOrInsert(
                ['consultation_id' => $cs['consultation_id'], 'symptome_id' => $cs['symptome_id']],
                $cs
            );
        }

        // 18. Consultation-Maladie
        $consMaladies = [
            ['consultation_id' => $consultationIds[0], 'maladie_id' => $maladieIds['Paludisme']],
            ['consultation_id' => $consultationIds[1], 'maladie_id' => $maladieIds['Infection respiratoire']],
        ];
        foreach ($consMaladies as $cm) { $cm['uuid'] = (string) Str::uuid(); $cm['uuid'] = (string) Str::uuid();
            DB::table('consultation_maladie')->updateOrInsert(
                ['consultation_id' => $cm['consultation_id'], 'maladie_id' => $cm['maladie_id']],
                $cm
            );
        }

        // 19. Ordonnances
        $ordonnances = [
            ['consultation_id' => $consultationIds[0], 'date' => Carbon::now()->subDays(2), 'date_paiement' => null, 'statutordo' => 'impaye', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['consultation_id' => $consultationIds[1], 'date' => Carbon::now()->subDays(5), 'date_paiement' => Carbon::now()->subDays(4), 'statutordo' => 'paye', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($ordonnances as $ordonnance) { $ordonnance['uuid'] = (string) Str::uuid();
            DB::table('ordonnances')->insert($ordonnance);
        }
        $ordonnanceIds = DB::table('ordonnances')->pluck('id')->toArray();

        // 20. Ordonnance médicaments
        $ordoMedicaments = [
            [
                'ordonnance_id' => $ordonnanceIds[0], 'medicament_id' => $medicamentIds['Paracétamol 500mg'],
                'posologie' => '1 comprimé toutes les 6h', 'duree_jours' => 3, 'quantite' => 12, 'qte_vendu' => 0,
                'statut_vente' => 'disponible', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'ordonnance_id' => $ordonnanceIds[0], 'medicament_id' => $medicamentIds['Artésunate injectable'],
                'posologie' => '1 injection par jour', 'duree_jours' => 3, 'quantite' => 3, 'qte_vendu' => 0,
                'statut_vente' => 'disponible', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'ordonnance_id' => $ordonnanceIds[1], 'medicament_id' => $medicamentIds['Amoxicilline 500mg'],
                'posologie' => '1 gélule matin et soir', 'duree_jours' => 7, 'quantite' => 14, 'qte_vendu' => 14,
                'statut_vente' => 'non_disponible', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ];
        foreach ($ordoMedicaments as $om) { $om['uuid'] = (string) Str::uuid();
            DB::table('ordonnance_medicaments')->insert($om);
        }

        // 21. Prescriptions examens
        $prescriptionsExamens = [
            ['consultation_id' => $consultationIds[0], 'examen' => 'NFS', 'statut' => 'realise', 'notes' => 'Résultats disponibles', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['consultation_id' => $consultationIds[1], 'examen' => 'Radio thorax', 'statut' => 'en_cours', 'notes' => 'À réaliser', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($prescriptionsExamens as $pe) { $pe['uuid'] = (string) Str::uuid();
            DB::table('prescriptions_examens')->insert($pe);
        }
        $prescriptionExamenIds = DB::table('prescriptions_examens')->pluck('id')->toArray();

        // 22. Résultats examens
        DB::table('resultats_examens')->insert([
            ['prescription_examen_id' => $prescriptionExamenIds[0], 'resultat' => 'Hémoglobine: 12g/dL, Globules blancs: 8000/mm³', 'fichier' => null, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ]);

        // 23. Certificats
        DB::table('certificats')->insert([
            ['consultation_id' => $consultationIds[0], 'date' => Carbon::now()->subDays(2), 'contenu' => 'Repos de 3 jours pour paludisme', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ]);

        // 24. Rendez-vous
        $rendezvous = [
            ['consultation_id' => null, 'patient_id' => $patientIds['Sarr'], 'medecin_id' => $userIds['doctor@example.com'],
             'date_heure' => Carbon::now()->addDays(3), 'motif' => 'Contrôle tension', 'statut' => 'prevu', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['consultation_id' => $consultationIds[1], 'patient_id' => $patientIds['Diop'], 'medecin_id' => $userIds['doctor2@example.com'],
             'date_heure' => Carbon::now()->subDays(5), 'motif' => 'Consultation initiale', 'statut' => 'realise', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($rendezvous as $rdv) { $rdv['uuid'] = (string) Str::uuid();
            DB::table('rendezvous')->insert($rdv);
        }

        // 25. Hospitalisations
        DB::table('hospitalisations')->insert([
            [
                'consultation_id' => $consultationIds[0], 'salles_id' => $salleIds['Salle A101'], 'lit_id' => $litIds['LIT-003'],
                'date_entree' => Carbon::now()->subDays(2), 'date_sortie' => null, 'motif' => 'Paludisme sévère',
                'etat' => 'en cours', 'service_id' => $serviceIds['Médecine Générale'], 'observations' => 'Patient sous traitement',
                'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ]);

        // 26. Fournisseurs
        $fournisseurs = [
            ['nom' => 'Pharma-Sénégal', 'contact' => '771112233', 'email' => 'contact@pharmasn.com', 'adresse' => 'Dakar', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['nom' => 'MedicAfrique', 'contact' => '772223344', 'email' => 'info@medicafrique.com', 'adresse' => 'Thiès', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($fournisseurs as $fournisseur) { $fournisseur['uuid'] = (string) Str::uuid();
            DB::table('fournisseurs')->updateOrInsert(
                ['nom' => $fournisseur['nom']],
                $fournisseur
            );
        }
        $fournisseurIds = DB::table('fournisseurs')->pluck('id', 'nom')->toArray();

        // 27. Commandes
        $commandes = [
            [
                'reference' => 'CMD-2025-001', 'fournisseur_id' => $fournisseurIds['Pharma-Sénégal'], 'date_commande' => Carbon::now()->subDays(15),
                'statut' => 'valide', 'StatutPaiement' => 'total', 'total' => 150000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
            [
                'reference' => 'CMD-2025-002', 'fournisseur_id' => $fournisseurIds['MedicAfrique'], 'date_commande' => Carbon::now()->subDays(10),
                'statut' => 'en_cours', 'StatutPaiement' => 'partielle', 'total' => 200000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ];
        foreach ($commandes as $commande) { $commande['uuid'] = (string) Str::uuid();
            DB::table('commandes')->updateOrInsert(
                ['reference' => $commande['reference']],
                $commande
            );
        }
        $commandeIds = DB::table('commandes')->pluck('id')->toArray();

        // 28. Commande médicaments
        $commandeMedicaments = [
            ['commande_id' => $commandeIds[0], 'medicament_id' => $medicamentIds['Paracétamol 500mg'], 'quantite' => 200, 'quantiterecue' => 200, 'prix_unitaire' => 400, 'total' => 80000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['commande_id' => $commandeIds[0], 'medicament_id' => $medicamentIds['Ibuprofène 400mg'], 'quantite' => 100, 'quantiterecue' => 100, 'prix_unitaire' => 700, 'total' => 70000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['commande_id' => $commandeIds[1], 'medicament_id' => $medicamentIds['Amoxicilline 500mg'], 'quantite' => 150, 'quantiterecue' => 100, 'prix_unitaire' => 1000, 'total' => 150000, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($commandeMedicaments as $cm) { $cm['uuid'] = (string) Str::uuid();
            DB::table('commande_medicaments')->insert($cm);
        }

        // 29. Réceptions
        $receptions = [
            [
                'commande_id' => $commandeIds[0], 'fournisseur_id' => $fournisseurIds['Pharma-Sénégal'],
                'reference_reception' => 'REC-2025-001', 'date_reception' => Carbon::now()->subDays(12),
                'observations' => 'Réception complète', 'user_id' => $userIds['pharmacy@example.com'],
                'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ];
        foreach ($receptions as $reception) { $reception['uuid'] = (string) Str::uuid();
            DB::table('receptions')->updateOrInsert(
                ['reference_reception' => $reception['reference_reception']],
                $reception
            );
        }
        $receptionIds = DB::table('receptions')->pluck('id')->toArray();

        // 30. Réception lignes
        $receptionLignes = [
            ['reception_id' => $receptionIds[0], 'medicament_id' => $medicamentIds['Paracétamol 500mg'], 'quantite_commandee' => 200, 'quantite_recue' => 200, 'prix_unitaire' => 400, 'lot' => 'LOT001', 'date_peremption' => '2026-12-31', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['reception_id' => $receptionIds[0], 'medicament_id' => $medicamentIds['Ibuprofène 400mg'], 'quantite_commandee' => 100, 'quantite_recue' => 100, 'prix_unitaire' => 700, 'lot' => 'LOT002', 'date_peremption' => '2026-10-31', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($receptionLignes as $rl) { $rl['uuid'] = (string) Str::uuid();
            DB::table('reception_lignes')->insert($rl);
        }

        // 31. Paiement commandes
        $paiementCommandes = [
            ['commande_id' => $commandeIds[0], 'montant' => 150000, 'mode' => 'virement', 'date_paiement' => Carbon::now()->subDays(14), 'reference' => 'PCMD-2025-001', 'observations' => 'Paiement intégral', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['commande_id' => $commandeIds[1], 'montant' => 100000, 'mode' => 'espèce', 'date_paiement' => Carbon::now()->subDays(9), 'reference' => 'PCMD-2025-002', 'observations' => 'Paiement partiel', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($paiementCommandes as $pc) { $pc['uuid'] = (string) Str::uuid();
            DB::table('paiement_commandes')->updateOrInsert(
                ['reference' => $pc['reference']],
                $pc
            );
        }

        // 32. Grossesse (pour patiente Diop)
        $grossesses = [
            ['patient_id' => $patientIds['Diop'], 'ddr' => '2026-01-15', 'dpa' => '2026-10-22', 'parite' => 0, 'gestite' => 1,
             'antecedents_particuliers' => null, 'statut' => 'En cours', 'date_fin' => null, 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($grossesses as $grossesse) { $grossesse['uuid'] = (string) Str::uuid();
            DB::table('grossesses')->insert($grossesse);
        }
        $grossesseIds = DB::table('grossesses')->pluck('id')->toArray();

        // 33. Consultations prénatales
        DB::table('consultations_prenatales')->insert([
            ['grossesse_id' => $grossesseIds[0], 'numero_cpn' => 1, 'date_cpn' => '2026-02-20', 'poids' => 60, 'tension' => '110/70',
             'hauteur_uterine' => 12, 'bcf' => 'Normal', 'mouvement_foetal' => 'Présent', 'oedemes' => 'Aucun',
             'observations' => 'Grossesse évolutive normale', 'traitement_recu' => 'Acide folique', 'prochain_rdv' => '2026-03-20',
             'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ]);

        // 34. Consultation prénatale (avec maladie_id = Grossesse normale)
        $consultationPrenatale = [
            'ticket_id' => null, 'patient_id' => $patientIds['Diop'], 'medecin_id' => $userIds['doctor2@example.com'],
            'date_consultation' => '2026-02-20', 'motif' => 'CPN 1', 'diagnostic' => 'Grossesse normale',
            'notes' => 'Première consultation prénatale', 'poids' => 60, 'temperature' => 36.8,
            'tension' => '110/70', 'taille' => 165, 'imc' => 22.0, 'maladie_id' => $maladieIds['Grossesse normale'],
            'groupe_sanguin' => 'A+', 'adresse_patient' => 'Guédiawaye', 'antecedents' => null,
            'grossesse_id' => $grossesseIds[0], 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
        ];
        DB::table('consultations')->insert($consultationPrenatale);
        $consultationPrenataleId = DB::getPdo()->lastInsertId();

        // 35. Transferts
        DB::table('transferts')->insert([
            [
                'patient_id' => $patientIds['Mbaye'], 'consultation_id' => $consultationIds[0], 'hospitalisation_id' => 1,
                'type' => 'service', 'source_medecin_id' => null, 'dest_medecin_id' => null,
                'source_service_id' => $serviceIds['Médecine Générale'], 'dest_service_id' => $serviceIds['Cardiologie'],
                'hopital_destination' => null, 'motif' => 'Complications cardiaques suspectées',
                'date_transfert' => Carbon::now()->subDays(1), 'user_id' => $userIds['doctor@example.com'],
                'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ]);

        // 36. Suivis
        DB::table('suivis')->insert([
            [
                'consultation_id' => $consultationIds[0], 'patient_id' => $patientIds['Mbaye'], 'medecin_id' => $userIds['doctor@example.com'],
                'date_heure' => Carbon::now()->addDays(7), 'motif' => 'Suivi post-paludisme', 'resultat' => 'À contrôler',
                'statut' => 'prevu', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ]);

        // 37. Suivi traitements
        DB::table('suivi_traitements')->insert([
            [
                'consultation_id' => $consultationIds[0], 'date_suivi' => Carbon::now()->subDays(1),
                'evolution' => 'Amélioration', 'observations' => 'Fièvre en baisse', 'recommandations' => 'Continuer traitement',
                'temperature' => '37.5', 'tension' => '125/80', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ]);

        // 38. Protocole traitements
        $protocoles = [
            [
                'maladie_id' => $maladieIds['Paludisme'], 'titre' => 'Protocole Paludisme simple',
                'signes' => 'Fièvre, céphalées, frissons', 'diagnostics' => 'Test de goutte épaisse positif',
                'germes_nourrisson' => 'Plasmodium falciparum', 'germes_adulte' => 'Plasmodium falciparum',
                'traitement_principal' => 'Artéméther-Luméfantrine', 'posologie_principale' => '2 comprimés matin et soir pendant 3 jours',
                'traitement_alternatif' => 'Quinine', 'posologie_alternative' => '500mg toutes les 8h pendant 7 jours',
                'remarques' => 'Adapté selon poids', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()
            ],
        ];
        foreach ($protocoles as $protocole) { $protocole['uuid'] = (string) Str::uuid();
            DB::table('protocole_traitements')->updateOrInsert(
                ['maladie_id' => $protocole['maladie_id']],
                $protocole
            );
        }
        $protocoleIds = DB::table('protocole_traitements')->pluck('id')->toArray();

        // 39. Protocole médicaments
        if (!empty($protocoleIds)) {
            DB::table('protocole_medicament')->updateOrInsert(
                ['protocole_id' => $protocoleIds[0], 'medicament_id' => $medicamentIds['Artésunate injectable']],
                ['protocole_id' => $protocoleIds[0], 'medicament_id' => $medicamentIds['Artésunate injectable'], 'type' => 'principal', 'posologie' => '2.4 mg/kg', 'duree' => '3 jours', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()]
            );
        }

        // 40. Consultation suggestions (IA)
        $consultationSuggestions = [
            ['consultation_id' => $consultationIds[0], 'pathologie_id' => $maladieIds['Paludisme'], 'score' => 95, 'niveau_confiance' => 'Très élevé', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
            ['consultation_id' => $consultationIds[0], 'pathologie_id' => $maladieIds['Infection respiratoire'], 'score' => 25, 'niveau_confiance' => 'Faible', 'uuid' => (string) Str::uuid(), 'created_at' => now(), 'uuid' => (string) Str::uuid(), 'updated_at' => now()],
        ];
        foreach ($consultationSuggestions as $cs) { $cs['uuid'] = (string) Str::uuid();
            DB::table('consultation_suggestions')->insert($cs);
        }

        // 41. Données spécialisées Infectiologie
        $this->call(InfectiologieSeeder::class);

        echo "✅ Seeding terminé avec succès !\n";
        echo "📊 Comptes de test:\n";
        echo "   - Admin: admin@example.com / password\n";
        echo "   - Médecin 1: doctor@example.com / password\n";
        echo "   - Médecin 2: doctor2@example.com / password\n";
        echo "   - Pharmacien: pharmacy@example.com / password\n";
        echo "   - Gestionnaire: manager@example.com / password\n";
    }

    /**
     * Nettoyer les tables avant de seed (optionnel)
     */
    private function cleanTables(): void
    {
        $tables = [
            'consultation_suggestions', 'protocole_medicament', 'protocole_traitements',
            'suivi_traitements', 'suivis', 'transferts', 'consultations_prenatales', 'grossesses',
            'paiement_commandes', 'reception_lignes', 'receptions', 'commande_medicaments',
            'commandes', 'fournisseurs', 'hospitalisations', 'rendezvous', 'certificats',
            'resultats_examens', 'prescriptions_examens', 'ordonnance_medicaments',
            'ordonnances', 'consultation_maladie', 'consultation_symptome', 'consultations',
            'tickets', 'patients', 'lits', 'salles', 'users', 'medicaments',
            'maladie_symptome', 'symptomes', 'maladies', 'examens', 'prestations',
            'service_medicals', 'assurances', 'familles', 'unites'
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) { $table['uuid'] = (string) Str::uuid();
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
