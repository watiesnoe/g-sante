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
use App\Http\Controllers\EmploiDuTempsController;
use App\Http\Controllers\ExportImportController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/accueil', [\App\Http\Controllers\PassageController::class, 'index'])->name('passages.index');
    Route::post('/accueil/arrivee', [\App\Http\Controllers\PassageController::class, 'store'])->name('passages.store');
    Route::post('/accueil/{passage}/statut', [\App\Http\Controllers\PassageController::class, 'changerStatut'])->name('passages.statut');
    Route::get('/depenses', [\App\Http\Controllers\DepenseController::class, 'index'])->name('depenses.index');
    Route::get('/depenses/nouvelle', [\App\Http\Controllers\DepenseController::class, 'create'])->name('depenses.create');
    Route::post('/depenses', [\App\Http\Controllers\DepenseController::class, 'store'])->name('depenses.store');
    Route::get('/depenses/{depense}/piece', [\App\Http\Controllers\DepenseController::class, 'piece'])->name('depenses.piece');
    Route::post('/depenses/{depense}/{action}', [\App\Http\Controllers\DepenseController::class, 'action'])->name('depenses.action')
        ->whereIn('action', ['soumettre', 'valider', 'rejeter', 'payer', 'annuler']);
    Route::get('/stock/alertes', [\App\Http\Controllers\AlerteStockController::class, 'index'])->name('stock.alertes');
    Route::post('/consultations/{consultation}/signer', [\App\Http\Controllers\ConsultationSignatureController::class, 'signer'])->name('consultations.signer');
    Route::post('/consultations/{consultation}/addendum', [\App\Http\Controllers\ConsultationSignatureController::class, 'addendum'])->name('consultations.addendum');
    Route::get('/diagnostics/recherche', [\App\Http\Controllers\ConsultationSignatureController::class, 'diagnostics'])->name('diagnostics.recherche');
    Route::get('/documents/nouveau', [\App\Http\Controllers\DocumentMedicalController::class, 'create'])->name('documents.create');
    Route::post('/documents', [\App\Http\Controllers\DocumentMedicalController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/pdf', [\App\Http\Controllers\DocumentMedicalController::class, 'pdf'])->name('documents.pdf');
    Route::get('/parametre/modeles-documents', [\App\Http\Controllers\DocumentMedicalController::class, 'modelesIndex'])->name('modeles.index');
    Route::post('/parametre/modeles-documents', [\App\Http\Controllers\DocumentMedicalController::class, 'modeleEnregistrer'])->name('modeles.enregistrer');
    Route::post('/patients/{patient}/fusion', [\App\Http\Controllers\FusionPatientController::class, 'store'])->name('patients.fusion');
    Route::post('/fusions/{fusion}/annuler', [\App\Http\Controllers\FusionPatientController::class, 'annuler'])->name('fusions.annuler');
    Route::get('/agenda', [\App\Http\Controllers\AgendaController::class, 'index'])->name('agenda.index');
    Route::post('/agenda/{rendezvous}/statut', [\App\Http\Controllers\AgendaController::class, 'statut'])->name('agenda.statut');
    Route::get('/rapports', [\App\Http\Controllers\RapportController::class, 'index'])->name('rapports.index');
    Route::get('/parametre/diagnostics', [\App\Http\Controllers\DiagnosticController::class, 'index'])->name('diagnostics.index');
    Route::get('/tiers-payants/conventions', [\App\Http\Controllers\TiersPayantController::class, 'conventions'])->name('conventions.index');
    Route::post('/tiers-payants/conventions', [\App\Http\Controllers\TiersPayantController::class, 'enregistrerConvention'])->name('conventions.enregistrer');
    Route::get('/tiers-payants/bordereaux', [\App\Http\Controllers\TiersPayantController::class, 'bordereaux'])->name('bordereaux.index');
    Route::post('/tiers-payants/bordereaux', [\App\Http\Controllers\TiersPayantController::class, 'genererBordereau'])->name('bordereaux.generer');
    Route::get('/tiers-payants/bordereaux/{bordereau}', [\App\Http\Controllers\TiersPayantController::class, 'show'])->name('bordereaux.show');
    Route::get('/tiers-payants/bordereaux/{bordereau}/pdf', [\App\Http\Controllers\TiersPayantController::class, 'pdf'])->name('bordereaux.pdf');
    Route::post('/tiers-payants/bordereaux/{bordereau}/recouvrement', [\App\Http\Controllers\TiersPayantController::class, 'recouvrer'])->name('bordereaux.recouvrer');
    Route::get('/laboratoire', [\App\Http\Controllers\LaboratoireController::class, 'index'])->name('laboratoire.index');
    Route::post('/laboratoire/{examen}/etape', [\App\Http\Controllers\LaboratoireController::class, 'etape'])->name('laboratoire.etape');
    Route::post('/laboratoire/{examen}/valider', [\App\Http\Controllers\LaboratoireController::class, 'valider'])->name('laboratoire.valider');
    Route::get('/hospitalisation/lits', [\App\Http\Controllers\TableauLitsController::class, 'index'])->name('lits.tableau');
    Route::post('/hospitalisation/lits/{lit}/statut', [\App\Http\Controllers\TableauLitsController::class, 'statut'])->name('lits.statut');
    Route::post('/hospitalisation/{hospitalisation}/transfert-lit', [\App\Http\Controllers\TableauLitsController::class, 'transferer'])->name('lits.transferer');
    Route::get('/parametre/guichets', [\App\Http\Controllers\GuichetController::class, 'index'])->name('guichets.index');
    Route::post('/parametre/guichets', [\App\Http\Controllers\GuichetController::class, 'store'])->name('guichets.store');
    Route::post('/parametre/guichets/{guichet}/basculer', [\App\Http\Controllers\GuichetController::class, 'basculer'])->name('guichets.basculer');
    Route::get('/achats/demandes', [\App\Http\Controllers\DemandeAchatController::class, 'index'])->name('achats.index');
    Route::post('/achats/demandes', [\App\Http\Controllers\DemandeAchatController::class, 'store'])->name('achats.store');
    Route::post('/achats/demandes/{demande}/{action}', [\App\Http\Controllers\DemandeAchatController::class, 'action'])->name('achats.action')
        ->whereIn('action', ['soumettre', 'approuver', 'rejeter', 'commander']);
    Route::get('/personnel/gardes', [\App\Http\Controllers\GardeController::class, 'index'])->name('gardes.index');
    Route::post('/personnel/gardes', [\App\Http\Controllers\GardeController::class, 'store'])->name('gardes.store');
    Route::delete('/personnel/gardes/{garde}', [\App\Http\Controllers\GardeController::class, 'destroy'])->name('gardes.destroy');
    Route::get('/urgences', [\App\Http\Controllers\UrgenceController::class, 'index'])->name('urgences.index');
    Route::post('/urgences', [\App\Http\Controllers\UrgenceController::class, 'store'])->name('urgences.store');
    Route::post('/urgences/{passage}/prise-en-charge', [\App\Http\Controllers\UrgenceController::class, 'priseEnCharge'])->name('urgences.prise_en_charge');
    Route::post('/urgences/{passage}/decision', [\App\Http\Controllers\UrgenceController::class, 'decision'])->name('urgences.decision');
    Route::get('/maternite/accouchements', [\App\Http\Controllers\AccouchementController::class, 'index'])->name('accouchements.index');
    Route::get('/maternite/accouchements/nouveau', [\App\Http\Controllers\AccouchementController::class, 'create'])->name('accouchements.create');
    Route::post('/maternite/accouchements', [\App\Http\Controllers\AccouchementController::class, 'store'])->name('accouchements.store');
    Route::get('/references', [\App\Http\Controllers\ReferenceController::class, 'index'])->name('references.index');
    Route::post('/references', [\App\Http\Controllers\ReferenceController::class, 'store'])->name('references.store');
    Route::post('/references/{reference}/etape', [\App\Http\Controllers\ReferenceController::class, 'etape'])->name('references.etape');
    Route::post('/references/{reference}/devenir', [\App\Http\Controllers\ReferenceController::class, 'devenir'])->name('references.devenir');
    Route::get('/references/{reference}/fiche/{type}', [\App\Http\Controllers\ReferenceController::class, 'fiche'])->name('references.fiche');
    Route::get('/gratuites', [\App\Http\Controllers\GratuiteController::class, 'index'])->name('gratuites.index');
    Route::post('/gratuites', [\App\Http\Controllers\GratuiteController::class, 'store'])->name('gratuites.store');
    Route::post('/gratuites/{regle}/basculer', [\App\Http\Controllers\GratuiteController::class, 'basculer'])->name('gratuites.basculer');
    Route::get('/stock/magasins', [\App\Http\Controllers\MagasinController::class, 'index'])->name('magasins.index');
    Route::post('/stock/magasins', [\App\Http\Controllers\MagasinController::class, 'store'])->name('magasins.store');
    Route::post('/stock/magasins/transfert', [\App\Http\Controllers\MagasinController::class, 'transferer'])->name('magasins.transferer');
    Route::get('/budget', [\App\Http\Controllers\BudgetController::class, 'index'])->name('budget.index');
    Route::post('/budget', [\App\Http\Controllers\BudgetController::class, 'store'])->name('budget.store');
    Route::get('/rapports-mensuels', [\App\Http\Controllers\RapportMensuelController::class, 'index'])->name('rapports-mensuels.index');
    Route::post('/rapports-mensuels', [\App\Http\Controllers\RapportMensuelController::class, 'generer'])->name('rapports-mensuels.generer');
    Route::post('/rapports-mensuels/correspondance', [\App\Http\Controllers\RapportMensuelController::class, 'correspondance'])->name('rapports-mensuels.correspondance');
    Route::get('/rapports-mensuels/{rapport}', [\App\Http\Controllers\RapportMensuelController::class, 'show'])->name('rapports-mensuels.show');
    Route::post('/rapports-mensuels/{rapport}/etape', [\App\Http\Controllers\RapportMensuelController::class, 'etape'])->name('rapports-mensuels.etape');
    Route::get('/rapports-mensuels/{rapport}/export/{format}', [\App\Http\Controllers\RapportMensuelController::class, 'export'])->name('rapports-mensuels.export')->whereIn('format', ['csv', 'dhis2']);
    Route::get('/bloc', [\App\Http\Controllers\BlocController::class, 'index'])->name('bloc.index');
    Route::post('/bloc', [\App\Http\Controllers\BlocController::class, 'store'])->name('bloc.store');
    Route::get('/bloc/{intervention}', [\App\Http\Controllers\BlocController::class, 'show'])->name('bloc.show');
    Route::post('/bloc/{intervention}/{action}', [\App\Http\Controllers\BlocController::class, 'action'])->name('bloc.action')->whereIn('action', ['demarrer', 'terminer', 'annuler', 'consommer']);
    Route::get('/maintenance', [\App\Http\Controllers\MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [\App\Http\Controllers\MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::post('/maintenance/{equipement}/signaler', [\App\Http\Controllers\MaintenanceController::class, 'signaler'])->name('maintenance.signaler');
    Route::post('/maintenance/intervention/{maintenance}/resoudre', [\App\Http\Controllers\MaintenanceController::class, 'resoudre'])->name('maintenance.resoudre');
    Route::get('/hospitalisation/indicateurs', [\App\Http\Controllers\IndicateursHospitaliersController::class, 'index'])->name('hospitalisation.indicateurs');
    Route::post('/hospitalisation/unites', [\App\Http\Controllers\TableauLitsController::class, 'creerUnite'])->name('lits.unite');
    Route::post('/hospitalisation/salles/{salle}/unite', [\App\Http\Controllers\TableauLitsController::class, 'affecterUnite'])->name('lits.salle_unite');
    Route::post('/etablissement/changer', [\App\Http\Controllers\EtablissementController::class, 'changer'])->name('etablissement.changer');
    Route::get('/etablissements', [\App\Http\Controllers\EtablissementController::class, 'index'])->name('etablissements.index');
    Route::get('/etablissements/creer', [\App\Http\Controllers\EtablissementController::class, 'create'])->name('etablissements.create');
    Route::post('/etablissements', [\App\Http\Controllers\EtablissementController::class, 'store'])->name('etablissements.store');
    Route::get('/parametre/audit', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
    Route::get('/parametre/etablissement', [\App\Http\Controllers\EtablissementController::class, 'edit'])->name('etablissement.edit');
    Route::put('/parametre/etablissement', [\App\Http\Controllers\EtablissementController::class, 'update'])->name('etablissement.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('/services', ServiceMedicalController::class);
    Route::resource('prestations', PrestationController::class);
    Route::resource('lits', LitController::class);
    Route::resource('salles', SalleController::class);
    Route::resource('examens', ExamenController::class)->except(['show']);
    Route::resource('prescriptions', PrescriptionExamenController::class);
    Route::get('prescriptions/{consultation}/pdf', [PrescriptionExamenController::class, 'pdf'])->name('prescriptions.pdf');
    Route::resource('reponses', ResultatExamenController::class);
    Route::get('reponses/{id}/create', [ResultatExamenController::class, 'reponse'])->name('reponse.create');
    Route::get('reponses/{resultatExamen}/fichier', [ResultatExamenController::class, 'download'])->name('reponses.fichier');
    Route::post('unites/prix-forfaitaire', [UniteController::class, 'prixForfaitaire'])->name('unites.prix_forfaitaire');
    Route::resource('unites', UniteController::class);
    Route::resource('familles', FamilleController::class)->except(['create']);
    Route::resource('maladies', MaladieController::class)->except(['create']);
    Route::delete('maladies-bulk', [MaladieController::class, 'destroyBulk'])->name('maladies.destroyBulk');
    Route::delete('symptomes-bulk', [SymptomeController::class, 'destroyBulk'])->name('symptomes.destroyBulk');
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
    // "Suivi" est un rendez-vous rattaché à une consultation (voir RendezVous) :
    // seules index/store/create sont exposées, le détail/l'édition/la suppression
    // se font depuis le module Rendez-vous (rendezvous.show/edit/destroy).
    Route::get('suivis', [SuiviController::class, 'index'])->name('suivis.index');
    Route::post('suivis', [SuiviController::class, 'store'])->name('suivis.store');
    Route::resource('assurances', AssuranceController::class);

    Route::get('consultations/{consultation}/suivis/create', [SuiviController::class, 'create'])
        ->name('consultations.suivi.create');

    Route::get('/rendezvous/data', [RendezvousController::class, 'getData'])->name('rendezvous.data');
    Route::get('/rendezvous/realiser', [RendezvousController::class, 'realiser'])->name('rendezvous.realiser');

    // Emploi du temps des médecins
    Route::get('/emploi-du-temps', [EmploiDuTempsController::class, 'index'])->name('emploi-du-temps.index');
    Route::post('/emploi-du-temps', [EmploiDuTempsController::class, 'store'])->name('emploi-du-temps.store');
    Route::delete('/emploi-du-temps/{emploiDuTemp}', [EmploiDuTempsController::class, 'destroy'])->name('emploi-du-temps.destroy');
    Route::get('/emploi-du-temps/disponibles', [EmploiDuTempsController::class, 'disponiblesParJour'])->name('emploi-du-temps.disponibles');
    Route::get('/emploi-du-temps/medecin-creneaux', [EmploiDuTempsController::class, 'getMedecinCreneaux'])->name('emploi-du-temps.creneaux');
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
    Route::post('ordonnances/{ordonnance}/annuler-paiement', [OrdonnanceController::class, 'annulerPaiement'])->name('ordonnances.annulerPaiement');
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

    Route::get('/receptions/{reception}/pdf', [ReceptionController::class, 'pdf'])->name('receptions.pdf');
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
    Route::get('/caisse/consolidation', [\App\Http\Controllers\CaisseController::class, 'consolidation'])->name('caisse.consolidation');
    Route::post('/caisse/{session}/valider', [\App\Http\Controllers\CaisseController::class, 'valider'])->name('caisse.valider');
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
        Route::get('/pathologies/export/{format}', [\App\Http\Controllers\InfectiologieController::class, 'exportPathologies'])->name('infectiologie.pathologies.export');
        Route::get('/pathogenes', [\App\Http\Controllers\InfectiologieController::class, 'pathogenes'])->name('infectiologie.pathogenes');
        Route::get('/pathologies-api', [\App\Http\Controllers\InfectiologieController::class, 'pathologiesApi'])
            ->name('infectiologie.pathologies.api');
        Route::get('/statistiques', [\App\Http\Controllers\InfectiologieController::class, 'statistiques'])->name('infectiologie.statistiques');
        Route::get('/protocoles', [\App\Http\Controllers\InfectiologieController::class, 'protocoles'])->name('infectiologie.protocoles');
        Route::get('/protocoles/{protocole}', [\App\Http\Controllers\InfectiologieController::class, 'showProtocole'])->name('infectiologie.protocoles.show');
        Route::post('/protocoles', [\App\Http\Controllers\InfectiologieController::class, 'storeProtocole'])->name('infectiologie.protocoles.store');
        Route::delete('/protocoles/{protocole}', [\App\Http\Controllers\InfectiologieController::class, 'destroyProtocole'])->name('infectiologie.protocoles.destroy');
        Route::get('/aide-prescription', [\App\Http\Controllers\InfectiologieController::class, 'aidePrescription'])->name('infectiologie.aide_prescription');
        Route::get('/suivi-traitements', [\App\Http\Controllers\InfectiologieController::class, 'suivi'])->name('infectiologie.suivi');
        Route::get('/suivi-traitements/pdf', [\App\Http\Controllers\InfectiologieController::class, 'suiviPdf'])->name('infectiologie.suivi.pdf');
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

// ── Export / Import generique ──────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/export/{module}',      [ExportImportController::class, 'export'])->name('export.model');
    Route::get('/export-xlsx/{module}', [ExportImportController::class, 'exportXlsx'])->name('export.model.xlsx');
    Route::post('/import/{module}',     [ExportImportController::class, 'import'])->name('import.model');
});
