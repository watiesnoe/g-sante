<?php

use App\Models\DemandeEnLigne;
use App\Models\EmploiDuTemps;
use App\Models\EnLigne\Site;
use App\Models\Etablissement;
use App\Models\RendezVous;
use App\Services\EnLigne\PortailService;
use App\Services\EnLigne\SynchroService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\Fabrique;

/*
| Espace patient intégré (GSANTE_ESPACE_PATIENT=true) : la même installation sert
| l'application interne et /patient ; les échanges se font en base, sans réseau.
*/

beforeEach(function () {
    config(['gsante.mode' => 'local', 'gsante.espace_patient' => true]);
    Http::preventStrayRequests();
    $this->travelTo(Carbon::parse('next monday 07:00'));
    $this->etablissement = Fabrique::etablissement('clinique', ['en_ligne.rendez_vous' => true, 'en_ligne.jours_publies' => 6]);

    $this->medecin = Fabrique::utilisateur();
    $this->medecin->assignRole(\App\Models\Role::firstOrCreate(['name' => 'medecin', 'guard_name' => 'web']));
    EmploiDuTemps::create(['medecin_id' => $this->medecin->id, 'jour_semaine' => 2, 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'statut' => 'actif']);
    $this->accueil = Fabrique::utilisateur(['rendezvous.en_ligne']);
    $this->mardi8h = Carbon::parse('next tuesday 08:00');
});

it('publie les créneaux et relaie les demandes sans serveur séparé ni réseau', function () {
    expect($this->etablissement->espacePatientIntegre())->toBeTrue()
        ->and($this->etablissement->en_ligne_url)->toBeNull();

    expect(app(SynchroService::class)->synchroniser($this->etablissement)->statut)->toBe('ok');
    $site = Site::where('uuid', $this->etablissement->uuid)->sole();
    expect($site->actif)->toBeTrue()->and($site->creneaux()->count())->toBe(4);

    // Le patient demande un créneau dans /patient
    $demande = app(PortailService::class)->demander($site, [
        'medecin_uuid' => $this->medecin->uuid, 'creneau' => $this->mardi8h->format('Y-m-d H:i:s'),
        'patient_nom' => 'Coulibaly', 'patient_prenom' => 'Fanta', 'patient_telephone' => '76112233',
    ]);

    // L'accueil la voit dès l'ouverture de l'écran, sans attendre la tâche planifiée
    $this->actingAs($this->accueil)->get(route('en_ligne.demandes'))->assertOk()->assertSee('COULIBALY');
    $locale = DemandeEnLigne::sole();
    $this->post(route('en_ligne.confirmer', $locale), ['genre' => 'F', 'age' => 34])->assertSessionHas('success');

    app(SynchroService::class)->synchroniser($this->etablissement);
    expect($demande->fresh()->statut)->toBe('confirmee')
        ->and($demande->fresh()->rdv_uuid)->toBe(RendezVous::sole()->uuid);
});

it('retire l\'établissement de l\'espace patient quand le module est désactivé', function () {
    app(SynchroService::class)->synchroniser($this->etablissement);
    expect(Site::where('uuid', $this->etablissement->uuid)->value('actif'))->toBeTrue();

    $this->etablissement->update(['modules' => array_values(array_diff($this->etablissement->modules, ['espace_patient']))]);

    expect(Site::where('uuid', $this->etablissement->uuid)->value('actif'))->toBeFalse()
        ->and(Etablissement::courant()->connecteEnLigne())->toBeFalse();
});

it('n\'impute pas les codes de connexion des patients à un établissement', function () {
    $this->post(route('patient.code'), ['telephone' => '65432109'])->assertRedirect(route('patient.verification'));

    expect(DB::table('sms_envois')->where('telephone', '65432109')->value('etablissement_id'))->toBeNull();
});

it('propose l\'espace patient depuis la page de connexion', function () {
    $this->get(route('login'))->assertOk()->assertSee(route('patient.connexion'));
});

it('fournit le nom, les icônes et la liaison Android de l\'application installable', function () {
    $this->get(route('patient.manifeste'))->assertOk()
        ->assertJsonPath('short_name', 'Kènèya Hub')
        ->assertJsonPath('icons.2.purpose', 'maskable');

    // Tant que l'application Android n'est pas déclarée, rien n'est publié
    $this->get('/.well-known/assetlinks.json')->assertNotFound();

    config(['gsante.android' => ['paquet' => 'io.digitafrika.keneyahub', 'empreintes' => ['AA:BB']]]);
    $this->get('/.well-known/assetlinks.json')->assertOk()
        ->assertJsonPath('0.target.package_name', 'io.digitafrika.keneyahub')
        ->assertJsonPath('0.target.sha256_cert_fingerprints', ['AA:BB']);
});
