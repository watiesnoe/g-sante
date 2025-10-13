<?php

use App\Http\Middleware\PatientConnecte;
use App\Models\DemandeEnLigne;
use App\Models\EnLigne\Compte;
use App\Models\EnLigne\Creneau;
use App\Models\EnLigne\Demande;
use App\Models\EnLigne\Evenement;
use App\Models\EnLigne\Medecin;
use App\Models\EnLigne\Site;
use App\Models\RendezVous;
use App\Services\EnLigne\DemandesService;
use App\Services\EnLigne\PortailService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\Fabrique;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-02 07:00'));
    $this->site = Site::create(['uuid' => (string) Str::uuid(), 'nom' => 'CSRéf de Ségou', 'district' => 'Ségou', 'cle' => 'GS-P', 'secret' => str_repeat('s', 40), 'actif' => true, 'delai_annulation_heures' => 24]);
    $this->medecinUuid = (string) Str::uuid();
    Medecin::create(['site_id' => $this->site->id, 'uuid' => $this->medecinUuid, 'nom' => 'Dr Traoré', 'service' => 'Médecine générale']);
    foreach (['2026-11-03 08:00', '2026-11-03 08:30', '2026-11-05 10:00'] as $debut) {
        Creneau::create(['site_id' => $this->site->id, 'medecin_uuid' => $this->medecinUuid, 'debut' => $debut]);
    }
    $this->awa = Compte::create(['telephone' => '76112233', 'nom' => 'Traoré', 'prenom' => 'Awa', 'verifie_at' => now()]);
    $this->moussa = Compte::create(['telephone' => '70998877', 'nom' => 'Keita', 'prenom' => 'Moussa', 'verifie_at' => now()]);
});

function demandePatient($test, Compte $compte, string $creneau): Demande
{
    return app(PortailService::class)->demander($test->site, [
        'medecin_uuid' => $test->medecinUuid, 'creneau' => $creneau, 'patient_nom' => $compte->nom,
        'patient_prenom' => $compte->prenom, 'patient_telephone' => $compte->telephone, 'patient_compte_id' => $compte->id,
    ]);
}

it('connecte le patient par un code SMS à usage unique, avec un nombre d\'essais limité (EF-14.02, SEC-11)', function () {
    $this->get(route('patient.accueil'))->assertRedirect(route('patient.connexion'));

    $this->post(route('patient.code'), ['telephone' => '+223 65 43 21 09'])->assertRedirect(route('patient.verification'));
    $code = session('demo_code');
    expect($code)->toMatch('/^\d{6}$/')
        ->and(DB::table('en_ligne_codes')->where('telephone', '65432109')->value('empreinte'))->not->toBe($code)
        ->and(DB::table('sms_envois')->where('telephone', '65432109')->where('message', 'like', "%{$code}%")->exists())->toBeTrue();

    // 5 mauvais codes : le code est grillé, même le bon est ensuite refusé
    $faux = $code === '000000' ? '111111' : '000000';
    foreach (range(1, 5) as $i) {
        $this->post(route('patient.verifier'), ['code' => $faux])->assertSessionHasErrors('code');
    }
    $this->post(route('patient.verifier'), ['code' => $code])->assertSessionHasErrors('code');

    // Nouveau code : connexion, puis identité demandée une fois
    $this->post(route('patient.code'), ['telephone' => '65432109']);
    $this->post(route('patient.verifier'), ['code' => session('demo_code')])->assertRedirect(route('patient.accueil'));
    $this->get(route('patient.accueil'))->assertRedirect(route('patient.profil'));
    $this->post(route('patient.profil.enregistrer'), ['nom' => 'Diallo', 'prenom' => 'Kadia'])->assertRedirect(route('patient.accueil'));
    $this->get(route('patient.accueil'))->assertOk()->assertSee('Bonjour Kadia');

    // Un code déjà utilisé ne resservira pas
    $this->post(route('patient.deconnexion'));
    $this->withSession(['espace_patient.telephone' => '65432109'])->post(route('patient.verifier'), ['code' => '123456'])->assertSessionHasErrors('code');
    expect(Compte::where('telephone', '65432109')->count())->toBe(1);
});

it('limite les demandes de code par numéro (SEC-11)', function () {
    foreach (range(1, 3) as $i) {
        $this->post(route('patient.code'), ['telephone' => '76000001'])->assertSessionHasNoErrors();
    }
    $this->post(route('patient.code'), ['telephone' => '76000001'])->assertSessionHasErrors('telephone');
    expect(DB::table('en_ligne_codes')->where('telephone', '76000001')->count())->toBe(3);
});

it('demande un rendez-vous sur un créneau libre et le retrouve dans « Mes rendez-vous » (EF-14.03)', function () {
    $this->withSession([PatientConnecte::SESSION => $this->awa->id]);

    $this->get(route('patient.nouveau'))->assertOk()->assertSee('CSRéf de Ségou');
    $this->get(route('patient.nouveau', ['site' => $this->site, 'medecin' => $this->medecinUuid]))->assertOk()
        ->assertSee('Dr Traoré')->assertSee('08h00')->assertSee('08h30');

    $this->post(route('patient.demander', $this->site), ['medecin_uuid' => $this->medecinUuid, 'creneau' => '2026-11-03 08:30:00', 'motif' => 'Fièvre'])
        ->assertRedirect(route('patient.accueil'));

    $demande = Demande::sole();
    expect($demande->patient_compte_id)->toBe($this->awa->id)
        ->and($demande->patient_telephone)->toBe('76112233')
        ->and(Creneau::where('debut', '2026-11-03 08:30:00')->exists())->toBeFalse()
        ->and(Evenement::where('type', 'demande.creee')->count())->toBe(1);
    $this->get(route('patient.accueil'))->assertOk()->assertSee('Dr Traoré')->assertSee('En attente de confirmation');

    // Le créneau pris n'est plus proposé ; le redemander est refusé
    $this->post(route('patient.demander', $this->site), ['medecin_uuid' => $this->medecinUuid, 'creneau' => '2026-11-03 08:30:00'])
        ->assertSessionHas('erreur');
});

it('refuse à un patient l\'accès aux rendez-vous d\'un autre, quelle que soit l\'adresse (REC-21, SEC-12)', function () {
    $sienne = demandePatient($this, $this->moussa, '2026-11-05 10:00:00');
    $sienne->update(['statut' => 'proposee', 'creneau_propose' => '2026-11-06 09:00:00']);

    $this->withSession([PatientConnecte::SESSION => $this->awa->id]);
    $this->get(route('patient.accueil'))->assertOk()->assertDontSee('2026')->assertDontSee('Keita');
    $this->post(route('patient.annuler', $sienne->uuid))->assertNotFound();
    $this->post(route('patient.accepter', $sienne->uuid))->assertNotFound();
    $this->get(route('patient.nouveau', ['remplace' => $sienne->uuid]))->assertNotFound();
    $this->post(route('patient.demander', $this->site), ['medecin_uuid' => $this->medecinUuid, 'creneau' => '2026-11-03 08:00:00', 'remplace' => $sienne->uuid])
        ->assertNotFound();

    expect($sienne->fresh()->statut)->toBe('proposee')->and(Demande::count())->toBe(1);
});

it('annule ou déplace un rendez-vous confirmé seulement avant le délai fixé par l\'établissement (EF-14.04)', function () {
    $proche = demandePatient($this, $this->awa, '2026-11-03 08:00:00');
    $loin = demandePatient($this, $this->awa, '2026-11-05 10:00:00');
    $proche->update(['statut' => 'confirmee']);
    $loin->update(['statut' => 'confirmee']);
    Creneau::create(['site_id' => $this->site->id, 'medecin_uuid' => $this->medecinUuid, 'debut' => '2026-11-06 09:00']);
    $this->withSession([PatientConnecte::SESSION => $this->awa->id]);

    // Le 3 novembre à 8 h est dans moins de 24 h... à partir du 2 novembre 8 h 01
    $this->travelTo(Carbon::parse('2026-11-02 09:00'));
    $this->post(route('patient.annuler', $proche->uuid))->assertSessionHas('erreur');
    $this->get(route('patient.accueil'))->assertSee('appelez l\'établissement', false);

    // Déplacement du rendez-vous lointain : nouvelle demande, l'ancienne est annulée
    $this->post(route('patient.demander', $this->site), ['medecin_uuid' => $this->medecinUuid, 'creneau' => '2026-11-06 09:00:00', 'remplace' => $loin->uuid])
        ->assertRedirect(route('patient.accueil'));
    $nouvelle = Demande::where('remplace_uuid', $loin->uuid)->sole();
    expect($loin->fresh()->statut)->toBe('annulee')->and($nouvelle->statut)->toBe('en_attente')
        ->and(Evenement::where('type', 'demande.annulee')->count())->toBe(1);
});

it('relaie au serveur local le déplacement et l\'acceptation d\'un créneau proposé', function () {
    $etablissement = Fabrique::etablissement('clinique');
    $medecin = Fabrique::utilisateur();
    \Illuminate\Support\Facades\DB::table('users')->where('id', $medecin->id)->update(['uuid' => $this->medecinUuid]);
    $this->actingAs(Fabrique::utilisateur(['rendezvous.en_ligne']));
    $local = app(DemandesService::class);

    // Demande confirmée sur place, avec son rendez-vous et son dossier
    $initiale = demandePatient($this, $this->awa, '2026-11-05 10:00:00');
    $local->appliquer($etablissement, 'demande.creee', app(PortailService::class)->exporter($initiale));
    $rdv = $local->confirmer(DemandeEnLigne::sole(), null, ['genre' => 'F', 'age' => 31]);

    // Le patient déplace : le serveur local reçoit la nouvelle demande et l'annulation
    Creneau::create(['site_id' => $this->site->id, 'medecin_uuid' => $this->medecinUuid, 'debut' => '2026-11-06 09:00']);
    $initiale->update(['statut' => 'confirmee']);
    app(PortailService::class)->deplacer($initiale->fresh(), [
        'medecin_uuid' => $this->medecinUuid, 'creneau' => '2026-11-06 09:00:00', 'patient_nom' => 'Traoré', 'patient_prenom' => 'Awa',
        'patient_telephone' => '76112233', 'patient_compte_id' => $this->awa->id,
    ]);
    foreach (Evenement::where('id', '>', 1)->orderBy('id')->get() as $e) {
        $local->appliquer($etablissement, $e->type, $e->donnees);
    }
    $nouvelle = DemandeEnLigne::where('remplace_uuid', $initiale->uuid)->sole();
    expect($rdv->fresh()->statut)->toBe('annule')
        ->and($nouvelle->statut)->toBe('recue')
        ->and($nouvelle->patient_id)->toBe($rdv->patient_id); // même dossier, pas de doublon

    // L'accueil propose un autre horaire ; le patient l'accepte en ligne
    $local->proposer($nouvelle, Carbon::parse('2026-11-06 11:00'), null);
    Demande::where('uuid', $nouvelle->uuid)->update(['statut' => 'proposee', 'creneau_propose' => '2026-11-06 11:00:00']);
    $this->withSession([PatientConnecte::SESSION => $this->awa->id])
        ->post(route('patient.accepter', $nouvelle->uuid))->assertSessionHas('info');
    $acceptee = Evenement::where('type', 'demande.acceptee')->sole();
    $local->appliquer($etablissement, $acceptee->type, $acceptee->donnees);

    expect($nouvelle->fresh()->statut)->toBe('recue')
        ->and($nouvelle->fresh()->creneau->format('Y-m-d H:i'))->toBe('2026-11-06 11:00');
});

it('fournit une application installable et des pages légères (EF-14.07, ENF-11)', function () {
    $manifeste = $this->get(route('patient.manifeste'))->assertOk()->assertHeader('Content-Type', 'application/manifest+json')->json();
    expect($manifeste['display'])->toBe('standalone')->and($manifeste['start_url'])->toBe('/patient')
        ->and(collect($manifeste['icons'])->pluck('sizes')->all())->toContain('192x192', '512x512');
    expect(file_exists(public_path('patient-icone-512.png')))->toBeTrue();
    $this->get(route('patient.sw'))->assertOk()->assertHeader('Content-Type', 'application/javascript');

    $this->withSession([PatientConnecte::SESSION => $this->awa->id]);
    demandePatient($this, $this->awa, '2026-11-03 08:00:00');
    $page = $this->get(route('patient.accueil'))->assertOk()->getContent();
    expect(strlen($page))->toBeLessThan(30_000)->and($page)->not->toContain('<script src=');
});

it('ramène à l\'accueil une action ouverte directement par son adresse (page rechargée)', function () {
    $demande = demandePatient($this, $this->awa, '2026-11-05 10:00:00');
    $this->withSession([PatientConnecte::SESSION => $this->awa->id]);

    $this->get('/patient/rendez-vous/' . $demande->uuid . '/annuler')->assertRedirect(route('patient.accueil'));
    $this->get('/patient/rendez-vous/' . $demande->uuid . '/accepter')->assertRedirect(route('patient.accueil'));
    expect($demande->fresh()->statut)->toBe('en_attente');
});
