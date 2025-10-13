<?php

use App\Models\DemandeEnLigne;
use App\Models\EmploiDuTemps;
use App\Models\EnLigne\Creneau;
use App\Models\EnLigne\Demande;
use App\Models\EnLigne\Site;
use App\Models\Etablissement;
use App\Models\RendezVous;
use App\Models\SynchroSortante;
use App\Services\EnLigne\PortailService;
use App\Services\EnLigne\SynchroService;
use App\Support\SignatureSynchro;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as RequeteHttp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\Fabrique;

/*
| Les deux serveurs tournent ici dans le même processus : les appels HTTP du
| serveur local sont acheminés vers les routes du serveur en ligne.
*/

const SECRET_TEST = 'secret-de-test-tres-long-pour-la-synchronisation-0123456789';

beforeEach(function () {
    $this->travelTo(Carbon::parse('next monday 07:00'));
    $this->etablissement = Fabrique::etablissement('clinique', ['en_ligne.rendez_vous' => true, 'en_ligne.jours_publies' => 6]);
    $this->etablissement->forceFill(['en_ligne_url' => 'http://127.0.0.1', 'en_ligne_cle' => 'GS-TEST', 'en_ligne_secret' => SECRET_TEST])->save();
    $this->etablissement = Etablissement::courant();
    $this->site = Site::create(['uuid' => $this->etablissement->uuid, 'nom' => 'Clinique', 'cle' => 'GS-TEST', 'secret' => SECRET_TEST, 'actif' => true]);

    // Un médecin consulte le mardi de 8 h à 10 h : 4 créneaux de 30 min
    $this->medecin = Fabrique::utilisateur();
    $this->medecin->assignRole(\App\Models\Role::firstOrCreate(['name' => 'medecin', 'guard_name' => 'web']));
    EmploiDuTemps::create(['medecin_id' => $this->medecin->id, 'jour_semaine' => 2, 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'statut' => 'actif']);
    $this->accueil = Fabrique::utilisateur(['rendezvous.en_ligne']);
    $this->actingAs($this->accueil);
    $this->mardi8h = Carbon::parse('next tuesday 08:00');
});

function brancherServeurEnLigne($test): void
{
    $test->horsLigne = false;
    if ($test->reseauInstalle ?? false) {
        return;
    }
    $test->reseauInstalle = true;
    Http::fake(function (RequeteHttp $requete) use ($test) {
        if ($test->horsLigne) {
            throw new ConnectionException('Connexion impossible : réseau injoignable');
        }
        $serveur = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        foreach ($requete->headers() as $nom => $valeurs) {
            $serveur['HTTP_' . strtoupper(str_replace('-', '_', $nom))] = $valeurs[0];
        }
        $reponse = $test->call($requete->method(), SignatureSynchro::uri($requete->url()), [], [], [], $serveur, $requete->body());

        return Http::response($reponse->getContent(), $reponse->getStatusCode());
    });
}

function couperInternet($test): void
{
    brancherServeurEnLigne($test);
    $test->horsLigne = true;
}

function demanderEnLigne($test, Carbon $creneau): Demande
{
    return app(PortailService::class)->demander($test->site, [
        'medecin_uuid' => $test->medecin->uuid, 'creneau' => $creneau->format('Y-m-d H:i:s'), 'motif' => 'Toux persistante',
        'patient_nom' => 'Coulibaly', 'patient_prenom' => 'Fanta', 'patient_telephone' => '76112233',
    ]);
}

it('publie les créneaux libres, relaie une demande et renvoie la confirmation de l\'accueil (EF-14.01, RG-17)', function () {
    brancherServeurEnLigne($this);
    expect(app(SynchroService::class)->synchroniser($this->etablissement)->statut)->toBe('ok');

    // Le serveur en ligne ne connaît que le minimum : médecin publié et créneaux libres
    expect($this->site->medecins()->pluck('uuid')->all())->toBe([$this->medecin->uuid])
        ->and($this->site->creneaux()->count())->toBe(4);

    $demande = demanderEnLigne($this, $this->mardi8h);
    expect($this->site->creneaux()->count())->toBe(3);

    app(SynchroService::class)->synchroniser($this->etablissement);
    $locale = DemandeEnLigne::sole();
    expect($locale->statut)->toBe('recue')->and($locale->medecin_id)->toBe($this->medecin->id)
        ->and(RendezVous::count())->toBe(0); // pas de rendez-vous avant confirmation

    $this->get(route('en_ligne.demandes'))->assertOk()->assertSee('COULIBALY')->assertSee('76112233');
    $this->post(route('en_ligne.confirmer', $locale), ['genre' => 'F', 'age' => 34])->assertSessionHas('success');

    $rdv = RendezVous::sole();
    expect(Carbon::parse($rdv->date_heure)->equalTo($this->mardi8h))->toBeTrue()
        ->and($rdv->patient->telephone)->toBe('76112233')
        ->and($locale->fresh()->statut)->toBe('confirmee')
        ->and(DB::table('sms_envois')->where('telephone', '76112233')->where('message', 'like', 'Rendez-vous confirmé%')->exists())->toBeTrue();

    $journal = app(SynchroService::class)->synchroniser($this->etablissement);
    expect($journal->envoyes)->toBe(1)
        ->and($demande->fresh()->statut)->toBe('confirmee')
        ->and($demande->fresh()->rdv_uuid)->toBe($rdv->uuid)
        // Le créneau confirmé n'est plus publié
        ->and($this->site->creneaux()->where('debut', $this->mardi8h)->exists())->toBeFalse();
});

it('continue l\'activité interne sans Internet et rattrape les échanges au retour (REC-19)', function () {
    brancherServeurEnLigne($this);
    app(SynchroService::class)->synchroniser($this->etablissement);
    $demande = demanderEnLigne($this, $this->mardi8h->copy()->addMinutes(30));

    couperInternet($this);
    $journal = app(SynchroService::class)->synchroniser($this->etablissement);
    expect($journal->statut)->toBe('erreur')->and($journal->message)->toContain('réseau injoignable')
        ->and($this->etablissement->fresh()->en_ligne_erreur)->not->toBeNull();

    // Activité interne normale pendant la coupure
    $patient = Fabrique::patient();
    RendezVous::create(['patient_id' => $patient->id, 'medecin_id' => $this->medecin->id, 'date_heure' => $this->mardi8h->copy()->addHour(), 'statut' => 'prevu']);
    $this->get(route('en_ligne.demandes'))->assertOk()->assertSee('échec');

    brancherServeurEnLigne($this);
    $journal = app(SynchroService::class)->synchroniser($this->etablissement);
    expect($journal->statut)->toBe('ok')->and($journal->recus)->toBe(1)
        ->and(DemandeEnLigne::where('uuid', $demande->uuid)->value('statut'))->toBe('recue')
        ->and($this->etablissement->fresh()->en_ligne_erreur)->toBeNull()
        // Le rendez-vous pris sur place pendant la coupure n'est plus proposé en ligne
        ->and($this->site->creneaux()->where('debut', $this->mardi8h->copy()->addHour())->exists())->toBeFalse();

    // Un nouveau passage ne renvoie pas les mêmes demandes
    expect(app(SynchroService::class)->synchroniser($this->etablissement)->recus)->toBe(0)
        ->and(DemandeEnLigne::count())->toBe(1);
});

it('détecte le conflit d\'un créneau pris sur place pendant une coupure : le local fait foi, le patient est prévenu (REC-20)', function () {
    brancherServeurEnLigne($this);
    app(SynchroService::class)->synchroniser($this->etablissement);

    couperInternet($this);
    // En ligne, une patiente demande 9 h ; sur place, l'accueil donne 9 h à quelqu'un d'autre
    $demande = demanderEnLigne($this, $this->mardi8h->copy()->addHour());
    RendezVous::create(['patient_id' => Fabrique::patient()->id, 'medecin_id' => $this->medecin->id, 'date_heure' => $this->mardi8h->copy()->addHour(), 'statut' => 'prevu']);
    app(SynchroService::class)->synchroniser($this->etablissement);

    brancherServeurEnLigne($this);
    app(SynchroService::class)->synchroniser($this->etablissement);

    $locale = DemandeEnLigne::where('uuid', $demande->uuid)->sole();
    expect($locale->statut)->toBe('conflit')
        ->and(RendezVous::count())->toBe(1)
        ->and($demande->fresh()->statut)->toBe('conflit')
        ->and($demande->fresh()->message)->toContain('attribué entre-temps')
        ->and(DB::table('sms_envois')->where('telephone', '76112233')->where('message', 'like', '%n\'est plus libre%')->exists())->toBeTrue();

    // L'accueil ne peut pas confirmer un conflit ; il propose un autre créneau
    $this->post(route('en_ligne.confirmer', $locale), ['genre' => 'F', 'age' => 30])->assertSessionHas('error');
    $this->post(route('en_ligne.proposer', $locale), ['creneau_propose' => $this->mardi8h->copy()->addMinutes(90)->format('Y-m-d\TH:i')])->assertSessionHas('success');
    app(SynchroService::class)->synchroniser($this->etablissement);
    expect($demande->fresh()->statut)->toBe('proposee')
        ->and($demande->fresh()->creneau_propose->equalTo($this->mardi8h->copy()->addMinutes(90)))->toBeTrue();
});

it('refuse toute requête de synchronisation non signée, rejouée, périmée ou d\'un établissement désactivé (SEC-14)', function () {
    $corps = json_encode(['evenements' => []]);
    $appel = function (array $entetes) use ($corps) {
        $serveur = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        foreach ($entetes as $nom => $valeur) {
            $serveur['HTTP_' . strtoupper(str_replace('-', '_', $nom))] = $valeur;
        }

        return $this->call('POST', '/api/synchro/evenements', [], [], [], $serveur, $corps);
    };

    $valides = SignatureSynchro::entetes('GS-TEST', SECRET_TEST, 'POST', 'http://x/api/synchro/evenements', $corps);
    expect($appel($valides)->getStatusCode())->toBe(200)
        ->and($appel($valides)->getStatusCode())->toBe(401) // rejeu
        ->and($appel(SignatureSynchro::entetes('GS-TEST', 'mauvais-secret-mauvais-secret-mauvais', 'POST', 'http://x/api/synchro/evenements', $corps))->getStatusCode())->toBe(401)
        ->and($appel(SignatureSynchro::entetes('GS-INCONNU', SECRET_TEST, 'POST', 'http://x/api/synchro/evenements', $corps))->getStatusCode())->toBe(401)
        ->and($appel([])->getStatusCode())->toBe(401);

    // Corps modifié en route : la signature ne correspond plus
    $entetes = SignatureSynchro::entetes('GS-TEST', SECRET_TEST, 'POST', 'http://x/api/synchro/evenements', json_encode(['evenements' => [['uuid' => (string) \Illuminate\Support\Str::uuid(), 'type' => 'x', 'donnees' => []]]]));
    expect($appel($entetes)->getStatusCode())->toBe(401);

    // Horodatage trop ancien
    $this->travel(-10)->minutes();
    $anciens = SignatureSynchro::entetes('GS-TEST', SECRET_TEST, 'POST', 'http://x/api/synchro/evenements', $corps);
    $this->travel(10)->minutes();
    expect($appel($anciens)->getStatusCode())->toBe(401);

    $this->site->update(['actif' => false]);
    expect($appel(SignatureSynchro::entetes('GS-TEST', SECRET_TEST, 'POST', 'http://x/api/synchro/evenements', $corps))->getStatusCode())->toBe(401);
});

it('n\'applique qu\'une fois un événement renvoyé après une coupure', function () {
    brancherServeurEnLigne($this);
    app(SynchroService::class)->synchroniser($this->etablissement);
    $demande = demanderEnLigne($this, $this->mardi8h);
    app(SynchroService::class)->synchroniser($this->etablissement);
    $this->post(route('en_ligne.refuser', DemandeEnLigne::sole()), ['message' => 'Médecin absent ce jour'])->assertSessionHas('success');

    // L'accusé de réception s'est perdu : l'événement repart au passage suivant
    $evenement = SynchroSortante::sole();
    app(SynchroService::class)->synchroniser($this->etablissement);
    expect($demande->fresh()->statut)->toBe('refusee');
    $demande->fresh()->update(['statut' => 'en_attente']);
    $evenement->update(['envoye_at' => null]);
    app(SynchroService::class)->synchroniser($this->etablissement);

    expect($demande->fresh()->statut)->toBe('en_attente')
        ->and(DB::table('en_ligne_recus')->count())->toBe(1);
});

it('ne publie rien si la publication des créneaux est désactivée', function () {
    $this->etablissement->update(['parametres' => array_merge($this->etablissement->parametres ?? [], ['en_ligne.rendez_vous' => false])]);
    brancherServeurEnLigne($this);
    app(SynchroService::class)->synchroniser(Etablissement::courant());

    expect($this->site->medecins()->count())->toBe(0)->and(Creneau::count())->toBe(0);
});

it('calcule les créneaux libres quand une demande attend et qu\'aucun rendez-vous n\'est pris', function () {
    // Cas réel : une demande en ligne reçue, aucun rendez-vous à venir pour le médecin
    DemandeEnLigne::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(), 'etablissement_id' => $this->etablissement->id, 'medecin_id' => $this->medecin->id,
        'creneau' => $this->mardi8h, 'patient_nom' => 'A', 'patient_prenom' => 'B', 'patient_telephone' => '70000000', 'statut' => 'recue',
    ]);
    expect(RendezVous::count())->toBe(0);

    $creneaux = app(\App\Services\EnLigne\CreneauxService::class)->disponibles($this->etablissement);
    expect(collect($creneaux)->pluck('debut')->all())->not->toContain($this->mardi8h->format('Y-m-d H:i:s'))->toHaveCount(3);

    brancherServeurEnLigne($this);
    expect(app(SynchroService::class)->synchroniser($this->etablissement)->statut)->toBe('ok');
});
