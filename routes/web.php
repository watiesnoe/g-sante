<?php

use App\Http\Controllers\CommandeController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\FamilleController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HospitalisationController;
use App\Http\Controllers\LitController;
use App\Http\Controllers\MaladieController;
use App\Http\Controllers\MedicamentController;
use App\Http\Controllers\OrdonnanceController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionExamenController;
use App\Http\Controllers\PrestationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\RendezvousController;
use App\Http\Controllers\ResultatExamenController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\ServiceMedicalController;
use App\Http\Controllers\SuiviController;
use App\Http\Controllers\SymptomeController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UniteController;
use Illuminate\Support\Facades\Route;

// Quand on arrive sur la racine "/", on redirige vers le login
Route::get('/', function () {
    return redirect()->route('login');
});

// Page d'accueil après connexion
Route::get('/home', function() {
    return view('dashboard'); // ou 'home'
})->name('home')->middleware(['auth', 'verified']);

// Routes protégées par authentification
Route::middleware('auth')->group(function () {
    Route::get('/parametre', [ConfigurationController::class, 'index'])->name('configuration');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('/services', ServiceMedicalController::class);
    Route::resource('prestations', PrestationController::class);
    Route::resource('lits', LitController::class);
    Route::resource('salles', SalleController::class);
    Route::resource('examens', ExamenController::class);
    Route::resource('prescriptions', PrescriptionExamenController::class);
    Route::resource('reponses', ResultatExamenController::class);
    Route::get('reponses/{id}/create', [ResultatExamenController::class,'reponse'])->name('reponse.create');
    Route::resource('unites', UniteController::class);
    Route::resource('familles', FamilleController::class);
    Route::resource('maladies', MaladieController::class);
    Route::resource('symptomes', SymptomeController::class);
    Route::resource('tickets', TicketController::class);
    Route::resource('patients', PatientController::class);
    Route::resource('consultations', ConsultationController::class);
    Route::resource('suivis', SuiviController::class);

//    Route::get('suivis/create', [SuiviController::class, 'create'])->name('suivis.create');
// Route pour créer un suivi depuis une consultation
    Route::get('consultations/{consultation}/suivis/create', [SuiviController::class, 'create'])
        ->name('consultations.suivi.create');

// Stocker le suivi depuis la consultation
//    Route::post('suivis/{consultation}/suivis', [SuiviController::class, 'store'])
//        ->name('suivi.store');

    Route::get('/rendezvous/data', [RendezvousController::class, 'getData'])->name('rendezvous.data');
    Route::get('/rendezvous/disponible', [RendezvousController::class, 'disponible'])->name('rendezvous.disponible');
    Route::get('/rendezvous/annuler', [RendezvousController::class, 'annuler'])->name('rendezvous.annuler');
    Route::resource('rendezvous', RendezvousController::class);
    Route::post('rendezvous/{rendezvous}/marquer-realise', [RendezvousController::class, 'marquerRealise'])
        ->name('rendezvous.marquerRealise');
    Route::resource('ordonnances', OrdonnanceController::class);
    Route::get('ordonnances/{ordonnance}/pdf', [OrdonnanceController::class, 'pdf'])->name('ordonnances.pdf');
    Route::get('paiement/{ordonnance}/ordonnance', [OrdonnanceController::class, 'paiementForm'])
        ->name('ordonnances.paiement');
    Route::post('/payer/{ordonnance}/ordonnance', [OrdonnanceController::class, 'payer']) ->name('ordonnances.payer');
    Route::get('/ordonnance/lespayer', [OrdonnanceController::class, 'lespayer'])->name('ordonnances.lespayer');      ;

    Route::get('/patients/search', [PatientController::class, 'search'])
        ->name('patients.search');
    Route::resource('hospitalisations', HospitalisationController::class);
    Route::post('/paiements/hospitalisation', [PaiementController::class, 'store'])
        ->name('paiements.hospitalisation');

    Route::get('/hospitalisations/{id}/pdf', [HospitalisationController::class, 'generatePDF'])->name('hospitalisations.pdf');
    Route::get('/paiement/{id}/hospitalisations', [HospitalisationController::class, 'getPaiementData'])
        ->name('hospitalisations.paiement.data');
    Route::get('/hospitalisation/realise', [HospitalisationController::class, 'hopialisationrealise'])
        ->name('hospitalisations.realise');

    Route::resource('fournisseurs', FournisseurController::class);
    Route::resource('medicaments', MedicamentController::class);
    Route::resource('commandes', CommandeController::class);
    Route::post('/commandes/panier/ajouter', [CommandeController::class, 'ajouterAuPanier'])->name('commandes.panier.ajouter');
    Route::get('/commandes/{id}/pdf', [CommandeController::class, 'pdf'])->name('commandes.pdf');

// Supprimer un médicament du panier (AJAX)
    Route::post('/commandes/panier/supprimer', [CommandeController::class, 'supprimerDuPanier'])->name('commandes.panier.supprimer');

// Vider le panier
    Route::post('/commandes/panier/vider', [CommandeController::class, 'viderPanier'])->name('commandes.panier.vider');


//    Route::resource('receptions', ReceptionController::class);

    Route::get('/receptions', [ReceptionController::class, 'index'])->name('receptions.index');

    Route::get('/commandes/{id}/produits', [ReceptionController::class, 'getProduits']); // pour AJAX

    Route::post('/receptions', [ReceptionController::class, 'store'])->name('receptions.store');

    Route::get('/salle/{salle}/lits-libres', [SalleController::class, 'litsLibres']);
// Route pour récupérer les médicaments d'une commande (Ajax)
    Route::get('commandes/{commande}/medicaments', [CommandeController::class,'medicaments'])->name('commandes.medicaments');

});

// Auth routes générées par Breeze ou Jetstream
require __DIR__.'/auth.php';
