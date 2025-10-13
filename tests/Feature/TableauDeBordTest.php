<?php

use App\Models\Consultation;
use App\Models\Grossesse;
use App\Models\Ordonnance;
use App\Models\Passage;
use App\Models\PrescriptionExamen;
use App\Models\RendezVous;
use App\Models\Requisition;
use App\Models\ServiceMedical;
use App\Models\User;
use Database\Seeders\PermissionRoleSeeder;
use Tests\Support\Fabrique;

beforeEach(function () {
    Fabrique::etablissement('csref');
    $this->seed(PermissionRoleSeeder::class);

    $this->comptes = collect(['admin', 'medecin', 'secretaire', 'infirmier', 'sage_femme', 'laborantin', 'pharmacien', 'comptable', 'gestionnaire_stock'])
        ->mapWithKeys(function ($role) {
            $user = User::factory()->create(['prenom' => ucfirst($role), 'statut' => 'actif']);
            $user->syncRoles([$role]);

            return [$role => $user];
        });

    // Une journée : une patiente arrive, un rendez-vous, une ordonnance, un examen, une réquisition, un terme proche
    $patient = Fabrique::patient();
    Passage::create(['numero' => 'V-1', 'patient_id' => $patient->id, 'statut' => 'enregistre', 'motif' => 'Fièvre', 'arrive_at' => now()->subMinutes(20)]);
    RendezVous::create(['patient_id' => $patient->id, 'medecin_id' => $this->comptes['medecin']->id, 'date_heure' => now()->addHour(), 'statut' => 'prevu', 'motif' => 'Contrôle']);
    $consultation = Consultation::create(['patient_id' => $patient->id, 'medecin_id' => $this->comptes['medecin']->id, 'diagnostic' => 'Paludisme', 'date_consultation' => now()]);
    Ordonnance::create(['consultation_id' => $consultation->id, 'date' => today(), 'statutordo' => 'impaye']);
    PrescriptionExamen::create(['consultation_id' => $consultation->id, 'examen' => 'Goutte épaisse', 'type' => 'laboratoire', 'statut' => 'demande']);
    Requisition::create(['numero' => 'RQ-1', 'service_medical_id' => ServiceMedical::firstOrCreate(['nom' => 'Maternité'])->id, 'statut' => 'soumise', 'demandeur_id' => $this->comptes['infirmier']->id]);
    Grossesse::create(['patient_id' => $patient->id, 'ddr' => today()->subMonths(9)->subDays(2), 'dpa' => today()->addDays(5), 'statut' => 'En cours']);
});

function tableauDe($test, string $role): string
{
    $test->actingAs($test->comptes[$role]);

    $html = $test->get(route('dashboard'))->assertOk()->getContent();

    // Seul le tableau de bord compte, pas le menu latéral ; apostrophes et accents décodés
    $debut = strpos($html, 'class="tdb-entete');

    return html_entity_decode(substr($html, $debut, strpos($html, '</main>', $debut) - $debut), ENT_QUOTES);
}

it('donne à chaque profil ses propres chiffres et listes « à traiter »', function (string $role, array $voit, array $nevoitpas) {
    $html = tableauDe($this, $role);
    foreach ($voit as $texte) {
        expect($html)->toContain($texte);
    }
    foreach ($nevoitpas as $texte) {
        expect($html)->not->toContain($texte);
    }
})->with([
    'propriétaire' => ['admin', ['Encaissé ce mois', 'Personnel actif', 'Arrivées du jour', 'Grossesses suivies', 'Arrivées — 7 jours'], ['À prélever', 'Ma caisse', 'Ordonnances à servir', 'Termes proches']],
    'médecin'      => ['medecin', ['Mes consultations du jour', 'À signer', 'Patients à voir', 'Mes rendez-vous', 'TRAORÉ Awa', 'Contrôle'], ['Alertes de stock', 'Arrivées du jour', 'À prélever', 'Grossesses suivies']],
    'secrétaire'   => ['secretaire', ['Arrivées du jour', 'File d\'attente', 'Rendez-vous du jour', 'Enregistrer une arrivée'], ['Ordonnances à servir', 'À prélever', 'Encaissé ce mois']],
    'infirmier'    => ['infirmier', ['Arrivées du jour', 'File d\'attente', 'Urgences en cours'], ['Examens à traiter', 'Grossesses suivies', 'Ordonnances à servir']],
    'sage-femme'   => ['sage_femme', ['Grossesses suivies', 'CPN prévues aujourd', 'Termes proches', 'J-5'], ['Ordonnances à servir', 'À prélever']],
    'laborantin'   => ['laborantin', ['À prélever', 'À valider', 'Examens à traiter', 'Goutte épaisse'], ['File d\'attente', 'Ordonnances à servir']],
    'pharmacien'   => ['pharmacien', ['Ordonnances à servir', 'Ordonnances en attente', 'Réquisitions à servir', 'Alertes de stock'], ['File d\'attente', 'À prélever']],
    'caissier'     => ['comptable', ['Ma caisse', 'Fermée', 'Ordonnances à servir'], ['Alertes de stock', 'À prélever', 'File d\'attente']],
    'magasinier'   => ['gestionnaire_stock', ['Alertes de stock', 'Réquisitions à servir', 'Commandes à réceptionner', 'État des besoins'], ['Ordonnances à servir', 'File d\'attente']],
]);

it('n\'affiche pas les sections d\'un module désactivé', function () {
    $etablissement = \App\Models\Etablissement::courant();
    $etablissement->update(['modules' => array_values(array_diff($etablissement->modules, ['maternity']))]);

    expect(tableauDe($this, 'sage_femme'))->not->toContain('Grossesses suivies')->not->toContain('Termes proches');
});
