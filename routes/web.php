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
use App\Http\Controllers\PaiementCommandeController;
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
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssuranceController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\MigrationReportController;
use Illuminate\Support\Facades\Route;

Route::get('/run-migrations-temp', function() {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return '<pre>' . \Illuminate\Support\Facades\Artisan::output() . '</pre>';
});

// Quand on arrive sur la racine "/", on redirige vers le login
Route::get('/', function () {
    return redirect()->route('login');
});

// Routes protégées par authentification
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('/exercice/set', [\App\Http\Controllers\ExerciceController::class, 'setYear'])->name('exercice.set');
    Route::get('/parametre', [ConfigurationController::class, 'index'])->name('configuration');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('/services', ServiceMedicalController::class);
    Route::resource('prestations', PrestationController::class);
    Route::resource('lits', LitController::class);
    Route::resource('salles', SalleController::class);
    Route::resource('examens', ExamenController::class)->except(['show']);
    Route::resource('prescriptions', PrescriptionExamenController::class);
    Route::resource('reponses', ResultatExamenController::class);
    Route::get('reponses/{id}/create', [ResultatExamenController::class, 'reponse'])->name('reponse.create');
    Route::resource('unites', UniteController::class);
    Route::resource('familles', FamilleController::class)->except(['create']);
    Route::resource('maladies', MaladieController::class)->except(['create']);
    Route::resource('symptomes', SymptomeController::class)->except(['create']);
    Route::middleware('caisse.ouverte')->group(function () {
        Route::get('tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('tickets', [TicketController::class, 'store'])->name('tickets.store');
    });
    Route::resource('tickets', TicketController::class)->except(['create', 'store']);
    Route::get('/patients/search', [PatientController::class, 'search'])->name('patients.search');
    Route::get('/consultations/create/{ticket_uuid?}', [ConsultationController::class, 'create'])
        ->name('consultations.listeentente');
    Route::resource('consultations', ConsultationController::class);
    Route::get('liste-attente', [ConsultationController::class, 'listeAttente'])->name('liste.attente');
    Route::resource('suivis', SuiviController::class);
    Route::resource('assurances', AssuranceController::class);

    Route::get('consultations/{consultation}/suivis/create', [SuiviController::class, 'create'])
        ->name('consultations.suivi.create');

    Route::get('/rendezvous/data', [RendezvousController::class, 'getData'])->name('rendezvous.data');
    Route::get('/rendezvous/disponible', [RendezvousController::class, 'disponible'])->name('rendezvous.disponible');
    Route::get('/rendezvous/annuler', [RendezvousController::class, 'annuler'])->name('rendezvous.annuler');
    Route::get('/rendezvous', [RendezvousController::class, 'index'])->name('rendezvous.index');
    Route::get('/rendezvous/patient/{patient}', [RendezvousController::class, 'create'])->name('rendezvous.maternite');
    Route::get('/ordonnances/patient/{patient}', [OrdonnanceController::class, 'create'])->name('ordonnances.maternite');
    Route::post('/rendezvous', [RendezvousController::class, 'store'])->name('rendezvous.store');
    Route::get('/rendezvous/{rendezvous}', [RendezvousController::class, 'show'])->name('rendezvous.show');
    Route::get('/rendezvous/{rendezvous}/edit', [RendezvousController::class, 'edit'])->name('rendezvous.edit');
    Route::put('/rendezvous/{rendezvous}', [RendezvousController::class, 'update'])->name('rendezvous.update');
    Route::patch('/rendezvous/{rendezvous}', [RendezvousController::class, 'update']);
    Route::delete('/rendezvous/{rendezvous}', [RendezvousController::class, 'destroy'])->name('rendezvous.destroy');
    Route::post('rendezvous/{rendezvous}/marquer-realise', [RendezvousController::class, 'marquerRealise'])
        ->name('rendezvous.marquerRealise');
    Route::resource('ordonnances', OrdonnanceController::class);
    Route::get('ordonnances/{ordonnance}/pdf', [OrdonnanceController::class, 'pdf'])->name('ordonnances.pdf');
    Route::get('ordonnances/{ordonnance}/paiement', [OrdonnanceController::class, 'paiementForm'])
        ->name('ordonnances.paiement');
    Route::post('ordonnances/{ordonnance}/payer', [OrdonnanceController::class, 'payer'])->name('ordonnances.payer')->middleware('caisse.ouverte');
    Route::get('/ordonnance/lespayer', [OrdonnanceController::class, 'lespayer'])->name('ordonnances.lespayer');

    Route::resource('hospitalisations', HospitalisationController::class);
    Route::post('/paiements/hospitalisation', [PaiementController::class, 'store'])
        ->name('paiements.hospitalisation')->middleware('caisse.ouverte');

    Route::get('/hospitalisations/{hospitalisation}/pdf', [HospitalisationController::class, 'generatePDF'])->name('hospitalisations.pdf');
    Route::get('/hospitalisations/{hospitalisation}/paiement-data', [HospitalisationController::class, 'getPaiementData'])
        ->name('hospitalisations.paiement.data');
    Route::get('/hospitalisation/realise', [HospitalisationController::class, 'hopialisationrealise'])
        ->name('hospitalisations.realise');

    Route::resource('fournisseurs', FournisseurController::class);
    
    // Medicaments with custom AJAX routes
    Route::get('medicaments/search', [MedicamentController::class, 'search'])->name('medicaments.search');
    Route::get('medicaments/{medicament}/unites-api', [MedicamentController::class, 'getUnitesApi'])->name('medicaments.unites.api');
    Route::resource('medicaments', MedicamentController::class);
    Route::get('/familles/{famille}/medicaments', [MedicamentController::class, 'index'])->name('familles.medicaments');
    
    Route::resource('commandes', CommandeController::class);
    Route::get('/commandes/{commande?}/paiements', [PaiementCommandeController::class, 'create'])->name('paiementscommande.create')->middleware('caisse.ouverte');
    Route::post('/commandes/panier/ajouter', [CommandeController::class, 'ajouterAuPanier'])->name('commandes.panier.ajouter');
    Route::post('/commandes/panier/bulk-ajouter', [CommandeController::class, 'bulkAjouterAuPanier'])->name('commandes.panier.bulk-ajouter');
    Route::post('/commandes/panier/modifier', [CommandeController::class, 'modifierPanier'])->name('commandes.panier.modifier');
    Route::post('/commandes/panier/supprimer', [CommandeController::class, 'supprimerDuPanier'])->name('commandes.panier.supprimer');
    Route::get('/commandes/{commande}/pdf', [CommandeController::class, 'pdf'])->name('commandes.pdf');

    Route::get('/consultations/{consultation}/print', [ConsultationController::class, 'print'])->name('consultations.print');
    Route::post('/commandes/panier/vider', [CommandeController::class, 'viderPanier'])->name('commandes.panier.vider');
    Route::get('/tickets/{ticket}/print', [TicketController::class, 'print'])->name('tickets.print');

    Route::resource('receptions', ReceptionController::class);
    Route::resource('inventaires', \App\Http\Controllers\InventaireController::class);
    Route::post('/inventaires/{inventaire}/valider', [\App\Http\Controllers\InventaireController::class, 'valider'])->name('inventaires.valider');
    Route::get('/commandes/{commande}/produits', [ReceptionController::class, 'getProduits']); // pour AJAX
    Route::get('/salle/{salleId}/lits-libres', [SalleController::class, 'litsLibres'])->name('salles.litsLibres');
    Route::get('commandes/{commande}/medicaments', [CommandeController::class, 'medicaments'])->name('commandes.medicaments');

    Route::prefix('paiementscommande')->group(function () {
        Route::get('/dashboard', [PaiementCommandeController::class, 'dashboard'])->name('paiementscommande.dashboard');
        Route::post('/store', [PaiementCommandeController::class, 'store'])->name('paiementscommande.store')->middleware('caisse.ouverte');
        Route::get('/history/{commande}', [PaiementCommandeController::class, 'history'])->name('paiementscommande.history');
        Route::get('/{paiement}', [PaiementCommandeController::class, 'show'])->name('paiementscommande.show');
        Route::delete('/{paiement}', [PaiementCommandeController::class, 'destroy'])->name('paiementscommande.destroy');
    });

    Route::get('/caisse', [\App\Http\Controllers\CaisseController::class, 'index'])->name('caisse.index');
    Route::get('/caisse/ma-session', [\App\Http\Controllers\CaisseController::class, 'mySession'])->name('caisse.my_session');
    Route::get('/caisse/ouvrir', [\App\Http\Controllers\CaisseController::class, 'open'])->name('caisse.open');
    Route::post('/caisse/ouvrir', [\App\Http\Controllers\CaisseController::class, 'storeOpen'])->name('caisse.storeOpen');
    Route::get('/caisse/cloturer', [\App\Http\Controllers\CaisseController::class, 'close'])->name('caisse.close');
    Route::post('/caisse/cloturer', [\App\Http\Controllers\CaisseController::class, 'storeClose'])->name('caisse.storeClose');
    Route::get('/caisse/mouvement/{mouvement}', [\App\Http\Controllers\CaisseController::class, 'showMouvement'])->name('caisse.mouvement.show');
    Route::get('/caisse/{session}', [\App\Http\Controllers\CaisseController::class, 'show'])->name('caisse.show');

    Route::resource('transferts', \App\Http\Controllers\TransfertController::class)->only(['index', 'store', 'destroy']);
    Route::resource('patients', PatientController::class);
    Route::get('/patients/{patient}/dossier', [PatientController::class, 'print'])->name('patients.medicales');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('/users/datatable', [UserController::class, 'datatable'])->name('users.datatable');
    Route::get('/users/data', [UserController::class, 'getData'])->name('users.data');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/medecins', [UserController::class, 'medecins'])->name('medecins.index');

    Route::prefix('infectiologie')->group(function () {
        Route::get('/pathologies', [\App\Http\Controllers\InfectiologieController::class, 'pathologies'])->name('infectiologie.pathologies');
        Route::get('/pathogenes', [\App\Http\Controllers\InfectiologieController::class, 'pathogenes'])->name('infectiologie.pathogenes');
        Route::get('/pathologies-api', function () {
            return response()->json(\App\Models\Maladie::all());
        })->name('infectiologie.pathologies.api');
        Route::get('/statistiques', [\App\Http\Controllers\InfectiologieController::class, 'statistiques'])->name('infectiologie.statistiques');
        Route::get('/protocoles', [\App\Http\Controllers\InfectiologieController::class, 'protocoles'])->name('infectiologie.protocoles');
        Route::get('/protocoles/{protocole}', [\App\Http\Controllers\InfectiologieController::class, 'showProtocole'])->name('infectiologie.protocoles.show');
        Route::post('/protocoles', [\App\Http\Controllers\InfectiologieController::class, 'storeProtocole'])->name('infectiologie.protocoles.store');
        Route::delete('/protocoles/{protocole}', [\App\Http\Controllers\InfectiologieController::class, 'destroyProtocole'])->name('infectiologie.protocoles.destroy');
        Route::get('/aide-prescription', [\App\Http\Controllers\InfectiologieController::class, 'aidePrescription'])->name('infectiologie.aide_prescription');
        Route::get('/suivi-traitements', [\App\Http\Controllers\InfectiologieController::class, 'suivi'])->name('infectiologie.suivi');
        Route::get('/api/protocoles/{maladie}', [\App\Http\Controllers\InfectiologieController::class, 'getProtocole'])->name('infectiologie.get_protocole');
        Route::get('/api/medicaments-list', [\App\Http\Controllers\InfectiologieController::class, 'getMedicaments'])->name('api.medicaments.list');
    });

    Route::get('/api/clinical/interrogation', [\App\Http\Controllers\DiagnosticController::class, 'interrogate'])->name('clinical.interrogation');
    Route::get('/api/clinical/suggest-symptoms', [\App\Http\Controllers\DiagnosticController::class, 'suggestFollowUp'])->name('clinical.suggest_symptoms');
    Route::post('/suivi-traitements-store', [\App\Http\Controllers\SuiviTraitementController::class, 'store'])->name('suivi.store');
    Route::get('/consultation/{id}/suivis', [\App\Http\Controllers\SuiviTraitementController::class, 'getByConsultation'])->name('suivi.get');

    Route::prefix('maternity')->name('maternity.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MaternityController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\MaternityController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\MaternityController::class, 'store'])->name('store');
        Route::get('/{grossesse}', [\App\Http\Controllers\MaternityController::class, 'show'])->name('show');
        Route::post('/cpn', [\App\Http\Controllers\MaternityController::class, 'storeCpn'])->name('cpn.store');
        Route::post('/{grossesse}/close', [\App\Http\Controllers\MaternityController::class, 'close'])->name('close');
    });
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RolePermissionController::class);
    Route::put('roles/{role}/permissions', [RolePermissionController::class, 'updatePermissions'])
        ->name('roles.permissions.update');
    Route::get('permissions', [RolePermissionController::class, 'permissions'])
        ->name('permissions.index');
    Route::get('users/{user}/permissions', [RolePermissionController::class, 'getUserPermissions'])
        ->name('users.permissions');
    Route::post('users/{user}/permissions', [RolePermissionController::class, 'assignUserPermissions'])
        ->name('users.permissions.assign');
    Route::get('migrations/report-pdf', [MigrationReportController::class, 'downloadPdf'])
        ->name('migrations.report-pdf');
});

require __DIR__ . '/auth.php';
