<?php
// database/seeders/PermissionRoleSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nettoyer le cache des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================================
        // 1. PERMISSIONS — basées sur les modules RÉELS de l'application
        // ============================================================

        $permissions = [

            // ── Dashboard ────────────────────────────────────────────
            'dashboard.view'                     => 'Voir le tableau de bord',

            // ── Dossiers Patients ─────────────────────────────────────
            'patients.view'                      => 'Voir les dossiers patients',
            'patients.create'                    => 'Créer un patient',
            'patients.edit'                      => 'Modifier un patient',
            'patients.delete'                    => 'Supprimer un patient',
            'patients.dossier'                   => 'Voir le dossier médical complet',
            'patients.search'                    => 'Rechercher des patients',

            // ── Tickets / Réception ───────────────────────────────────
            'tickets.view'                       => 'Voir les tickets d\'accueil',
            'tickets.create'                     => 'Créer un ticket d\'accueil',
            'tickets.edit'                       => 'Modifier un ticket',
            'tickets.delete'                     => 'Supprimer un ticket',
            'tickets.print'                      => 'Imprimer un ticket',

            // ── Consultations ─────────────────────────────────────────
            'consultations.view'                 => 'Voir la liste des consultations',
            'consultations.create'               => 'Créer une consultation',
            'consultations.edit'                 => 'Modifier une consultation',
            'consultations.delete'               => 'Supprimer une consultation',
            'consultations.liste_attente'        => 'Gérer la liste d\'attente',
            'consultations.print'                => 'Imprimer une consultation',
            'consultations.prescribe'            => 'Prescrire des médicaments',
            'consultations.suivi'                => 'Créer/voir les suivis de consultation',

            // ── Ordonnances ───────────────────────────────────────────
            'ordonnances.view'                   => 'Voir les ordonnances',
            'ordonnances.create'                 => 'Créer une ordonnance',
            'ordonnances.edit'                   => 'Modifier une ordonnance',
            'ordonnances.delete'                 => 'Supprimer une ordonnance',
            'ordonnances.pdf'                    => 'Générer le PDF d\'ordonnance',
            'ordonnances.payer'                  => 'Enregistrer le paiement d\'ordonnance',

            // ── Rendez-vous ───────────────────────────────────────────
            'rendezvous.view'                    => 'Voir les rendez-vous',
            'rendezvous.create'                  => 'Créer un rendez-vous',
            'rendezvous.edit'                    => 'Modifier un rendez-vous',
            'rendezvous.delete'                  => 'Annuler un rendez-vous',
            'rendezvous.confirm'                 => 'Confirmer/marquer réalisé un rendez-vous',

            // ── Examens / Analyses ────────────────────────────────────
            'examens.view'                       => 'Voir les examens et analyses',
            'examens.create'                     => 'Prescrire un examen',
            'examens.edit'                       => 'Modifier un examen',
            'examens.delete'                     => 'Supprimer un examen',
            'examens.results'                    => 'Saisir les résultats d\'analyses',

            // ── Hospitalisations ──────────────────────────────────────
            'hospitalisations.view'              => 'Voir les hospitalisations',
            'hospitalisations.create'            => 'Créer une hospitalisation',
            'hospitalisations.edit'              => 'Modifier une hospitalisation',
            'hospitalisations.delete'            => 'Supprimer une hospitalisation',
            'hospitalisations.pdf'               => 'Générer le PDF d\'hospitalisation',
            'hospitalisations.paiement'          => 'Gérer le paiement d\'hospitalisation',

            // ── Transferts ────────────────────────────────────────────
            'transferts.view'                    => 'Voir les transferts de patients',
            'transferts.create'                  => 'Effectuer un transfert',
            'transferts.delete'                  => 'Annuler un transfert',

            // ── Maternité ─────────────────────────────────────────────
            'maternity.view'                     => 'Voir les suivis de grossesse',
            'maternity.create'                   => 'Créer un suivi de grossesse',
            'maternity.edit'                     => 'Modifier un suivi de grossesse',
            'maternity.cpn'                      => 'Enregistrer une CPN',
            'maternity.close'                    => 'Clôturer un suivi de grossesse',

            // ── Infectiologie & Traitements ───────────────────────────
            'infectiologie.view'                 => 'Voir le module infectiologie',
            'infectiologie.pathologies'          => 'Voir les pathologies infectieuses',
            'infectiologie.pathogenes'           => 'Voir les pathogènes',
            'infectiologie.protocoles'           => 'Gérer les protocoles de traitement',
            'infectiologie.antibiotiques'        => 'Gérer les antibiotiques',
            'infectiologie.aide_prescription'    => 'Utiliser l\'aide à la prescription',
            'infectiologie.suivi'                => 'Suivre les traitements infectieux',

            // ── Stock / Pharmacie ─────────────────────────────────────
            'stock.view'                         => 'Voir le module stock',
            'stock.medicaments'                  => 'Gérer les médicaments',
            'stock.fournisseurs'                 => 'Gérer les fournisseurs',
            'stock.commandes'                    => 'Gérer les commandes',
            'stock.receptions'                   => 'Gérer les réceptions de stock',
            'stock.familles'                     => 'Gérer les familles de médicaments',
            'stock.unites'                       => 'Gérer les unités de mesure',

            // ── Paiements / Caisse ────────────────────────────────────
            'paiements.view'                     => 'Voir les paiements',
            'paiements.create'                   => 'Enregistrer un paiement',
            'paiements.edit'                     => 'Modifier un paiement',
            'paiements.delete'                   => 'Supprimer un paiement',
            'caisse.view'                        => 'Voir la caisse globale',

            // ── Paramètres / Configuration ────────────────────────────
            'parametres.view'                    => 'Voir les paramètres',
            'parametres.services'                => 'Gérer les structures / services',
            'parametres.prestations'             => 'Gérer les prestations',
            'parametres.salles'                  => 'Gérer les salles',
            'parametres.lits'                    => 'Gérer les lits',
            'parametres.examens_config'          => 'Configurer les types d\'examens',
            'parametres.assurances'              => 'Gérer les assurances / sécurité sociale',
            'parametres.symptomes'               => 'Gérer les symptômes',
            'parametres.maladies'                => 'Gérer les maladies',
            'parametres.backup'                  => 'Gérer les sauvegardes système',

            // ── Utilisateurs & Rôles ──────────────────────────────────
            'users.view'                         => 'Voir les utilisateurs',
            'users.create'                       => 'Créer un utilisateur',
            'users.edit'                         => 'Modifier un utilisateur',
            'users.delete'                       => 'Supprimer un utilisateur',
            'users.status'                       => 'Changer le statut d\'un utilisateur',
            'roles.view'                         => 'Voir les rôles',
            'roles.create'                       => 'Créer des rôles',
            'roles.edit'                         => 'Modifier des rôles',
            'roles.delete'                       => 'Supprimer des rôles',
            'roles.assign_permissions'           => 'Attribuer des permissions aux rôles',
        ];

        // Créer chaque permission (idempotent)
        foreach ($permissions as $permission => $description) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['module' => explode('.', $permission)[0]]
            );
        }

        $this->command->info('✅ ' . count($permissions) . ' permissions configurées.');

        // ============================================================
        // 2. RÔLES avec leurs permissions adaptées aux modules réels
        // ============================================================

        // ── Rôle 1 : Super Administrateur ──────────────────────────
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['libelle' => 'Super Administrateur']
        );
        $superAdmin->syncPermissions(Permission::all());
        $this->command->info('✅ super_admin → toutes les permissions (' . Permission::count() . ')');

        // ── Rôle 2 : Administrateur ────────────────────────────────
        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['libelle' => 'Administrateur']
        );
        $admin->syncPermissions([
            'dashboard.view',
            'patients.view', 'patients.create', 'patients.edit', 'patients.search', 'patients.dossier',
            'tickets.view', 'tickets.create', 'tickets.edit',
            'consultations.view', 'consultations.liste_attente',
            'rendezvous.view', 'rendezvous.create', 'rendezvous.edit', 'rendezvous.confirm',
            'examens.view',
            'hospitalisations.view',
            'transferts.view',
            'maternity.view',
            'infectiologie.view',
            'stock.view', 'stock.medicaments', 'stock.fournisseurs', 'stock.commandes', 'stock.receptions',
            'paiements.view', 'paiements.create', 'caisse.view',
            'parametres.view', 'parametres.services', 'parametres.prestations', 'parametres.salles',
            'parametres.lits', 'parametres.examens_config', 'parametres.assurances',
            'parametres.symptomes', 'parametres.maladies',
            'users.view', 'users.create', 'users.edit', 'users.status',
            'roles.view', 'roles.create', 'roles.edit',
        ]);
        $this->command->info('✅ admin → permissions configurées');

        // ── Rôle 3 : Médecin ───────────────────────────────────────
        $medecin = Role::firstOrCreate(
            ['name' => 'medecin', 'guard_name' => 'web'],
            ['libelle' => 'Médecin']
        );
        $medecin->syncPermissions([
            'dashboard.view',
            'patients.view', 'patients.create', 'patients.edit', 'patients.search', 'patients.dossier',
            'tickets.view',
            'consultations.view', 'consultations.create', 'consultations.edit',
            'consultations.liste_attente', 'consultations.print', 'consultations.prescribe', 'consultations.suivi',
            'ordonnances.view', 'ordonnances.create', 'ordonnances.edit', 'ordonnances.pdf',
            'rendezvous.view', 'rendezvous.create', 'rendezvous.edit', 'rendezvous.confirm',
            'examens.view', 'examens.create', 'examens.results',
            'hospitalisations.view', 'hospitalisations.create', 'hospitalisations.edit',
            'transferts.view', 'transferts.create',
            'maternity.view', 'maternity.create', 'maternity.edit', 'maternity.cpn', 'maternity.close',
            'infectiologie.view', 'infectiologie.pathologies', 'infectiologie.pathogenes',
            'infectiologie.protocoles', 'infectiologie.antibiotiques',
            'infectiologie.aide_prescription', 'infectiologie.suivi',
            'stock.view', 'stock.medicaments',
        ]);
        $this->command->info('✅ medecin → permissions configurées');

        // ── Rôle 4 : Secrétaire médicale ──────────────────────────
        $secretaire = Role::firstOrCreate(
            ['name' => 'secretaire', 'guard_name' => 'web'],
            ['libelle' => 'Secrétaire médicale']
        );
        $secretaire->syncPermissions([
            'dashboard.view',
            'patients.view', 'patients.create', 'patients.edit', 'patients.search',
            'tickets.view', 'tickets.create', 'tickets.edit', 'tickets.print',
            'consultations.view', 'consultations.liste_attente',
            'rendezvous.view', 'rendezvous.create', 'rendezvous.edit',
            'rendezvous.delete', 'rendezvous.confirm',
            'hospitalisations.view',
            'transferts.view',
            'paiements.view', 'paiements.create', 'caisse.view',
        ]);
        $this->command->info('✅ secretaire → permissions configurées');

        // ── Rôle 5 : Infirmier(e) ─────────────────────────────────
        $infirmier = Role::firstOrCreate(
            ['name' => 'infirmier', 'guard_name' => 'web'],
            ['libelle' => 'Infirmier(e)']
        );
        $infirmier->syncPermissions([
            'dashboard.view',
            'patients.view', 'patients.search', 'patients.dossier',
            'tickets.view',
            'consultations.view', 'consultations.suivi',
            'examens.view', 'examens.results',
            'hospitalisations.view',
            'maternity.view', 'maternity.cpn',
            'infectiologie.view', 'infectiologie.suivi',
        ]);
        $this->command->info('✅ infirmier → permissions configurées');

        // ── Rôle 6 : Pharmacien ───────────────────────────────────
        $pharmacien = Role::firstOrCreate(
            ['name' => 'pharmacien', 'guard_name' => 'web'],
            ['libelle' => 'Pharmacien']
        );
        $pharmacien->syncPermissions([
            'dashboard.view',
            'stock.view', 'stock.medicaments', 'stock.fournisseurs',
            'stock.commandes', 'stock.receptions', 'stock.familles', 'stock.unites',
            'ordonnances.view', 'ordonnances.payer',
            'paiements.view', 'paiements.create',
        ]);
        $this->command->info('✅ pharmacien → permissions configurées');

        // ── Rôle 7 : Laborantin ───────────────────────────────────
        $laborantin = Role::firstOrCreate(
            ['name' => 'laborantin', 'guard_name' => 'web'],
            ['libelle' => 'Laborantin']
        );
        $laborantin->syncPermissions([
            'dashboard.view',
            'patients.view', 'patients.search',
            'examens.view', 'examens.edit', 'examens.results',
        ]);
        $this->command->info('✅ laborantin → permissions configurées');

        // ── Rôle 8 : Comptable / Caissier ────────────────────────
        $comptable = Role::firstOrCreate(
            ['name' => 'comptable', 'guard_name' => 'web'],
            ['libelle' => 'Comptable / Caissier']
        );
        $comptable->syncPermissions([
            'dashboard.view',
            'paiements.view', 'paiements.create', 'paiements.edit',
            'caisse.view',
            'ordonnances.view', 'ordonnances.payer',
            'hospitalisations.view', 'hospitalisations.paiement',
        ]);
        $this->command->info('✅ comptable → permissions configurées');

        // ── Rôle 9 : Gestionnaire de stock ───────────────────────
        $gestionnaire = Role::firstOrCreate(
            ['name' => 'gestionnaire_stock', 'guard_name' => 'web'],
            ['libelle' => 'Gestionnaire de stock']
        );
        $gestionnaire->syncPermissions([
            'dashboard.view',
            'stock.view', 'stock.medicaments', 'stock.fournisseurs',
            'stock.commandes', 'stock.receptions', 'stock.familles', 'stock.unites',
        ]);
        $this->command->info('✅ gestionnaire_stock → permissions configurées');

        // ── Rôle 10 : Sage-femme ──────────────────────────────────
        $sageFemme = Role::firstOrCreate(
            ['name' => 'sage_femme', 'guard_name' => 'web'],
            ['libelle' => 'Sage-femme']
        );
        $sageFemme->syncPermissions([
            'dashboard.view',
            'patients.view', 'patients.search', 'patients.dossier',
            'maternity.view', 'maternity.create', 'maternity.edit', 'maternity.cpn', 'maternity.close',
            'consultations.view',
            'infectiologie.view',
        ]);
        $this->command->info('✅ sage_femme → permissions configurées');

        // ── Rôle 11 : Visiteur (lecture seule) ───────────────────
        $visiteur = Role::firstOrCreate(
            ['name' => 'visiteur', 'guard_name' => 'web'],
            ['libelle' => 'Visiteur (Lecture seule)']
        );
        $visiteur->syncPermissions([
            'dashboard.view',
            'patients.view',
            'consultations.view',
            'rendezvous.view',
        ]);
        $this->command->info('✅ visiteur → permissions configurées');

        // ============================================================
        // 3. UTILISATEURS DE TEST
        // ============================================================

        if (Schema::hasTable('users')) {

            $testUsers = [
                [
                    'email'  => 'superadmin@exemple.com',
                    'name'   => 'superadmin',
                    'nom'    => 'Super', 'prenom' => 'Admin',
                    'role'   => 'super_admin',
                ],
                [
                    'email'  => 'admin@exemple.com',
                    'name'   => 'admin',
                    'nom'    => 'Admin', 'prenom' => 'Principal',
                    'role'   => 'admin',
                ],
                [
                    'email'  => 'medecin@exemple.com',
                    'name'   => 'medecin',
                    'nom'    => 'Jean', 'prenom' => 'Dupont',
                    'telephone' => '771234567',
                    'role'   => 'medecin',
                ],
                [
                    'email'  => 'secretaire@exemple.com',
                    'name'   => 'secretaire',
                    'nom'    => 'Sophie', 'prenom' => 'Martin',
                    'telephone' => '772345678',
                    'role'   => 'secretaire',
                ],
                [
                    'email'  => 'infirmier@exemple.com',
                    'name'   => 'infirmier',
                    'nom'    => 'Paul', 'prenom' => 'Bernard',
                    'telephone' => '773456789',
                    'role'   => 'infirmier',
                ],
                [
                    'email'  => 'pharmacien@exemple.com',
                    'name'   => 'pharmacien',
                    'nom'    => 'Marie', 'prenom' => 'Curie',
                    'telephone' => '774567890',
                    'role'   => 'pharmacien',
                ],
                [
                    'email'  => 'laborantin@exemple.com',
                    'name'   => 'laborantin',
                    'nom'    => 'Julie', 'prenom' => 'Dubois',
                    'telephone' => '775678901',
                    'role'   => 'laborantin',
                ],
                [
                    'email'  => 'comptable@exemple.com',
                    'name'   => 'comptable',
                    'nom'    => 'Robert', 'prenom' => 'Moreau',
                    'telephone' => '776789012',
                    'role'   => 'comptable',
                ],
                [
                    'email'  => 'gestionnaire@exemple.com',
                    'name'   => 'gestionnaire',
                    'nom'    => 'Thomas', 'prenom' => 'Petit',
                    'telephone' => '777890123',
                    'role'   => 'gestionnaire_stock',
                ],
                [
                    'email'  => 'sagefemme@exemple.com',
                    'name'   => 'sagefemme',
                    'nom'    => 'Aminata', 'prenom' => 'Diallo',
                    'telephone' => '778901234',
                    'role'   => 'sage_femme',
                ],
            ];

            foreach ($testUsers as $data) {
                $user = User::updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'uuid'      => Str::uuid(),
                        'name'      => $data['name'],
                        'nom'       => $data['nom'],
                        'prenom'    => $data['prenom'],
                        'telephone' => $data['telephone'] ?? null,
                        'email'     => $data['email'],
                        'password'  => Hash::make('password'),
                        'statut'    => 'actif',
                    ]
                );
                $user->syncRoles([$data['role']]);
                $this->command->info('👤 ' . $data['nom'] . ' ' . $data['prenom'] . ' → rôle : ' . $data['role']);
            }
        }

        $this->command->line('');
        $this->command->line('==========================================');
        $this->command->line('🎉 SEED TERMINÉ AVEC SUCCÈS !');
        $this->command->line('==========================================');
        $this->command->line('📊 Résumé :');
        $this->command->line('  - ' . Permission::count() . ' permissions (basées sur les modules réels)');
        $this->command->line('  - ' . Role::count() . ' rôles métier');
        $this->command->line('  - ' . User::count() . ' utilisateurs en base');
        $this->command->line('==========================================');
        $this->command->line('📋 Modules couverts :');
        $this->command->line('  dashboard | patients | tickets | consultations');
        $this->command->line('  ordonnances | rendezvous | examens | hospitalisations');
        $this->command->line('  transferts | maternity | infectiologie | stock');
        $this->command->line('  paiements | caisse | parametres | users | roles');
        $this->command->line('==========================================');
    }
}