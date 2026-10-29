<?php

use App\Http\Controllers\Api\SynchroController;
use App\Http\Middleware\VerifierSignatureSynchro;
use Illuminate\Support\Facades\Route;

/*
| Serveur en ligne (GSANTE_MODE=en_ligne), ou espace patient intégré
| (GSANTE_ESPACE_PATIENT=true) pour la partie /patient. Échanges avec les serveurs locaux :
| requêtes signées par établissement, débit limité (SEC-13, SEC-14).
*/
Route::prefix('api/synchro')->middleware(['throttle:120,1', VerifierSignatureSynchro::class])->group(function () {
    Route::post('/catalogue', [SynchroController::class, 'catalogue'])->name('synchro.catalogue');
    Route::post('/evenements', [SynchroController::class, 'recevoir'])->name('synchro.recevoir');
    Route::get('/evenements', [SynchroController::class, 'envoyer'])->name('synchro.envoyer');
});

/*
| Espace patient (lot 8). Pile de session propre : aucun des middlewares de
| l'application interne (établissement courant, comptes du personnel).
*/
Route::prefix('patient')->name('patient.')->middleware([
    \App\Http\Middleware\SessionEspacePatient::class,
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \App\Http\Middleware\CsrfEspacePatient::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
])->group(function () {
    Route::get('/manifest.webmanifest', [\App\Http\Controllers\EspacePatientController::class, 'manifeste'])->name('manifeste');
    Route::get('/sw.js', [\App\Http\Controllers\EspacePatientController::class, 'serviceWorker'])->name('sw');
    Route::get('/hors-ligne', [\App\Http\Controllers\EspacePatientController::class, 'horsLigne'])->name('hors_ligne');

    Route::get('/connexion', [\App\Http\Controllers\EspacePatientController::class, 'connexion'])->name('connexion');
    Route::post('/connexion', [\App\Http\Controllers\EspacePatientController::class, 'envoyerCode'])->middleware('throttle:20,1')->name('code');
    Route::get('/verification', [\App\Http\Controllers\EspacePatientController::class, 'verification'])->name('verification');
    Route::post('/verification', [\App\Http\Controllers\EspacePatientController::class, 'verifier'])->middleware('throttle:20,1')->name('verifier');

    Route::middleware(\App\Http\Middleware\PatientConnecte::class)->group(function () {
        Route::get('/', [\App\Http\Controllers\EspacePatientController::class, 'accueil'])->name('accueil');
        Route::get('/profil', [\App\Http\Controllers\EspacePatientController::class, 'profil'])->name('profil');
        Route::post('/profil', [\App\Http\Controllers\EspacePatientController::class, 'enregistrerProfil'])->name('profil.enregistrer');
        Route::post('/deconnexion', [\App\Http\Controllers\EspacePatientController::class, 'deconnexion'])->name('deconnexion');
        Route::get('/rendez-vous/nouveau/{site?}', [\App\Http\Controllers\EspacePatientController::class, 'nouveau'])->name('nouveau');
        Route::post('/rendez-vous/{site}', [\App\Http\Controllers\EspacePatientController::class, 'demander'])->middleware('throttle:10,1')->name('demander');
        Route::post('/rendez-vous/{demande}/annuler', [\App\Http\Controllers\EspacePatientController::class, 'annuler'])->name('annuler');
        Route::post('/rendez-vous/{demande}/accepter', [\App\Http\Controllers\EspacePatientController::class, 'accepter'])->name('accepter');
    });

    // Une adresse d'action ouverte directement (page rechargée, lien copié) ramène à l'accueil
    Route::get('/rendez-vous/{demande}/{action}', fn() => redirect()->route('patient.accueil'))->whereIn('action', ['annuler', 'accepter']);
    Route::get('/deconnexion', fn() => redirect()->route('patient.accueil'));
});

// Application Android de l'espace patient (Trusted Web Activity) : 404 tant qu'elle n'est pas déclarée
Route::get('/.well-known/assetlinks.json', [\App\Http\Controllers\EspacePatientController::class, 'assetLinks'])->name('patient.assetlinks');

// Sur le serveur en ligne, l'adresse racine mène à l'espace patient
if (config('gsante.mode') === 'en_ligne') {
    Route::redirect('/', '/patient');
}
