<?php
// database/seeders/PermissionRoleSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

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
            'ordonnances.view_payees'             => 'Voir les ordonnances payées (réservé pharmacie/comptabilité)',

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
            'examens.valider'                    => 'Valider les résultats (biologiste, radiologue)',

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
            'stock.inventaire'                   => 'Réaliser et valider les inventaires',
            'achats.demander'                    => 'Préparer une proposition de commande (pharmacie)',
            'achats.approuver'                   => 'Approuver ou rejeter une proposition de commande',
            'stock.requisition'                  => 'Demander des produits à la pharmacie (réquisition d\'un service)',
            'stock.servir'                       => 'Servir les réquisitions des services',
            'stock.besoins'                      => 'Consulter l\'état des besoins (CMM)',
            'rh.gardes.view'                     => 'Voir le planning des gardes',
            'rh.gardes.gerer'                    => 'Programmer les gardes et astreintes',
            'urgences.view'                      => 'Voir le registre des urgences',
            'urgences.gerer'                     => 'Enregistrer et orienter les patients aux urgences',
            'maternity.accouchement'             => 'Enregistrer un accouchement',
            'references.view'                    => 'Voir les références et évacuations',
            'references.gerer'                   => 'Émettre, recevoir et suivre les références',
            'gratuites.gerer'                    => 'Gérer les règles de gratuité',
            'budget.view'                        => 'Voir l\'exécution budgétaire',
            'budget.gerer'                       => 'Gérer les lignes budgétaires',
            'rapports.mensuel'                   => 'Calculer et exporter le rapport mensuel d\'activité',
            'rapports.valider'                   => 'Valider et transmettre le rapport mensuel',
            'bloc.view'                          => 'Voir le programme du bloc opératoire',
            'bloc.programmer'                    => 'Programmer et conduire les interventions',
            'maintenance.view'                   => 'Voir les équipements et signaler une panne',
            'maintenance.gerer'                  => 'Gérer les équipements et clôturer les maintenances',

            // ── Paiements / Caisse ────────────────────────────────────
            'paiements.view'                     => 'Voir les paiements',
            'paiements.create'                   => 'Enregistrer un paiement',
            'paiements.edit'                     => 'Modifier un paiement',
            'paiements.delete'                   => 'Supprimer un paiement',
            'caisse.view'                        => 'Voir la caisse globale',
            'caisse.annuler'                     => 'Annuler un encaissement (ticket, vente, paiement) avec motif',
            'caisse.valider_cloture'             => 'Valider la clôture de caisse d\'un caissier',
            'audit.view'                         => 'Consulter le journal d\'audit',
            'etablissements.gerer'               => 'Créer et lister les établissements (super-administration)',
            'patients.fusionner'                 => 'Fusionner deux dossiers patients (réversible)',
            'rapports.view'                      => 'Consulter et exporter les rapports d\'activité et financiers',
            'tiers_payants.conventions'          => 'Gérer les conventions avec les organismes payeurs',
            'tiers_payants.bordereaux'           => 'Émettre les bordereaux et enregistrer les recouvrements',
            // ── Accueil / passages (lot 1) ───────────────────────────
            'accueil.view'                       => 'Voir l\'accueil et la file d\'attente',
            'accueil.create'                     => 'Enregistrer l\'arrivée d\'un patient',
            'accueil.orienter'                   => 'Orienter un patient (trier, envoyer en consultation, sortir)',
            // ── Dépenses (lot 1) ─────────────────────────────────────
            'depenses.view'                      => 'Voir les dépenses',
            'depenses.create'                    => 'Saisir une dépense',
            'depenses.valider'                   => 'Valider ou rejeter une dépense',
            'depenses.payer'                     => 'Payer une dépense validée (sortie de caisse)',

            // ── Assurances / Sécurité sociale ─────────────────────────
            'assurances.view'                    => 'Voir les assurances',
            'assurances.create'                  => 'Créer une assurance',
            'assurances.edit'                    => 'Modifier une assurance',
            'assurances.delete'                  => 'Supprimer une assurance',

            // ── Fournisseurs ───────────────────────────────────────────
            'fournisseurs.view'                  => 'Voir les fournisseurs',
            'fournisseurs.create'                => 'Créer un fournisseur',
            'fournisseurs.edit'                  => 'Modifier un fournisseur',
            'fournisseurs.delete'                => 'Supprimer un fournisseur',

            // ── Paramètres / Configuration ────────────────────────────
            'parametres.view'                    => 'Voir les paramètres',
            'parametres.etablissement'           => 'Configurer l\'établissement (identité, type, modules)',
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
            'accueil.view', 'accueil.create', 'accueil.orienter', 'depenses.view', 'depenses.create', 'depenses.valider', 'depenses.payer', 'patients.fusionner', 'examens.valider', 'achats.demander', 'achats.approuver', 'stock.requisition', 'stock.servir', 'stock.besoins', 'rh.gardes.view', 'rh.gardes.gerer', 'urgences.view', 'urgences.gerer', 'maternity.accouchement', 'references.view', 'references.gerer', 'gratuites.gerer', 'budget.view', 'budget.gerer', 'rapports.mensuel', 'rapports.valider', 'bloc.view', 'bloc.programmer', 'maintenance.view', 'maintenance.gerer', 'rapports.view', 'tiers_payants.conventions', 'tiers_payants.bordereaux',
            'dashboard.view',
            'patients.view', 'patients.create', 'patients.edit', 'patients.search', 'patients.dossier',
            'tickets.view', 'tickets.create', 'tickets.edit',
            'consultations.view', 'consultations.liste_attente',
            'rendezvous.view', 'rendezvous.create', 'rendezvous.edit', 'rendezvous.confirm',
            'examens.view', 'examens.results',
            'hospitalisations.view',
            'transferts.view',
            'maternity.view',
            'infectiologie.view',
            'stock.view', 'stock.medicaments', 'stock.fournisseurs', 'stock.commandes', 'stock.receptions',
            'stock.familles', 'stock.unites', 'stock.inventaire',
            'paiements.view', 'paiements.create', 'caisse.view',
            'assurances.view', 'assurances.create', 'assurances.edit',
            'fournisseurs.view', 'fournisseurs.create', 'fournisseurs.edit',
            'caisse.annuler', 'caisse.valider_cloture', 'audit.view',
            'parametres.view', 'parametres.etablissement', 'parametres.services', 'parametres.prestations', 'parametres.salles',
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
            'bloc.view', 'bloc.programmer', 'maintenance.view',
            'urgences.view', 'urgences.gerer', 'references.view', 'references.gerer', 'maternity.accouchement',
            'rh.gardes.view',
            'stock.requisition',
            'accueil.view', 'accueil.orienter',
            'dashboard.view',
            'patients.view', 'patients.create', 'patients.edit', 'patients.search', 'patients.dossier',
            'consultations.view', 'consultations.create', 'consultations.edit',
            'consultations.liste_attente', 'consultations.print', 'consultations.prescribe', 'consultations.suivi',
            'ordonnances.view', 'ordonnances.create', 'ordonnances.edit', 'ordonnances.pdf',
            'rendezvous.view', 'rendezvous.create', 'rendezvous.edit', 'rendezvous.confirm',
            'examens.view', 'examens.create', 'examens.edit', 'examens.results',
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
            'accueil.view', 'accueil.create', 'accueil.orienter',
            'dashboard.view',
            'patients.view', 'patients.create', 'patients.edit', 'patients.search',
            'tickets.view', 'tickets.create', 'tickets.edit', 'tickets.print',
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
            'bloc.view', 'maintenance.view',
            'urgences.view', 'urgences.gerer', 'references.view',
            'rh.gardes.view',
            'stock.requisition',
            'accueil.view', 'accueil.orienter',
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
            'achats.demander', 'stock.servir', 'stock.besoins',
            'dashboard.view',
            'stock.view', 'stock.medicaments', 'stock.fournisseurs',
            'stock.commandes', 'stock.receptions', 'stock.familles', 'stock.unites', 'stock.inventaire',
            'fournisseurs.view', 'fournisseurs.create', 'fournisseurs.edit',
            'assurances.view',
            'ordonnances.view', 'ordonnances.payer', 'ordonnances.view_payees',
            'paiements.view', 'paiements.create', 'caisse.view',
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
            'examens.view', 'examens.edit', 'examens.results', 'examens.valider',
        ]);
        $this->command->info('✅ laborantin → permissions configurées');

        // ── Rôle 8 : Comptable / Caissier ────────────────────────
        $comptable = Role::firstOrCreate(
            ['name' => 'comptable', 'guard_name' => 'web'],
            ['libelle' => 'Comptable / Caissier']
        );
        $comptable->syncPermissions([
            'budget.view',
            'depenses.view', 'depenses.create', 'depenses.payer', 'rapports.view', 'tiers_payants.bordereaux',
            'dashboard.view',
            'paiements.view', 'paiements.create', 'paiements.edit',
            'caisse.view',
            'ordonnances.view', 'ordonnances.payer', 'ordonnances.view_payees',
            'hospitalisations.view', 'hospitalisations.paiement',
        ]);
        $this->command->info('✅ comptable → permissions configurées');

        // ── Rôle 9 : Gestionnaire de stock ───────────────────────
        $gestionnaire = Role::firstOrCreate(
            ['name' => 'gestionnaire_stock', 'guard_name' => 'web'],
            ['libelle' => 'Gestionnaire de stock']
        );
        $gestionnaire->syncPermissions([
            'achats.demander', 'stock.servir', 'stock.besoins',
            'dashboard.view',
            'stock.view', 'stock.medicaments', 'stock.fournisseurs',
            'stock.commandes', 'stock.receptions', 'stock.familles', 'stock.unites', 'stock.inventaire',
            'fournisseurs.view', 'fournisseurs.create', 'fournisseurs.edit', 'fournisseurs.delete',
        ]);
        $this->command->info('✅ gestionnaire_stock → permissions configurées');

        // ── Rôle 10 : Sage-femme ──────────────────────────────────
        $sageFemme = Role::firstOrCreate(
            ['name' => 'sage_femme', 'guard_name' => 'web'],
            ['libelle' => 'Sage-femme']
        );
        $sageFemme->syncPermissions([
            'maintenance.view', 'stock.requisition',
            'maternity.accouchement', 'references.view', 'references.gerer', 'urgences.view',
            'rh.gardes.view',
            'accueil.view', 'accueil.orienter',
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