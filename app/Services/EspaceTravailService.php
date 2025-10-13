<?php

namespace App\Services;

use App\Http\Controllers\AlerteStockController;
use App\Models\CaisseMouvement;
use App\Models\CaisseSession;
use App\Models\Commande;
use App\Models\Consultation;
use App\Models\ConsultationPrenatale;
use App\Models\DemandeAchat;
use App\Models\DemandeEnLigne;
use App\Models\Depense;
use App\Models\Etablissement;
use App\Models\Grossesse;
use App\Models\Hospitalisation;
use App\Models\Lit;
use App\Models\Ordonnance;
use App\Models\Passage;
use App\Models\Patient;
use App\Models\PrescriptionExamen;
use App\Models\Reference;
use App\Models\RendezVous;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

/**
 * Tableau de bord de chaque compte (EF-13.01) : ses chiffres du jour, ses listes
 * « à traiter » et ses raccourcis. Les sections dépendent des permissions et des
 * modules actifs, pas du nom du rôle : un rôle personnalisé obtient les bonnes
 * sections, et un module désactivé n'apparaît jamais.
 */
class EspaceTravailService
{
    private User $user;
    private Etablissement $etablissement;
    private array $tuiles = [];
    private array $listes = [];
    private array $raccourcis = [];
    /** Propriétaire / responsable : vue de synthèse, sans les compteurs de chaque poste. */
    private bool $direction = false;
    /** Médecin : ses patients et ses consultations, pas le travail de l'accueil ni du magasin. */
    private bool $clinicien = false;

    public function pour(User $user): array
    {
        $this->user = $user;
        $this->etablissement = Etablissement::courant();
        $this->tuiles = $this->listes = $this->raccourcis = [];
        $this->direction = $user->can('parametres.etablissement');
        $this->clinicien = $user->can('consultations.create');

        $this->direction();
        $this->accueil();
        $this->consultations();
        $this->urgences();
        $this->rendezVous();
        $this->hospitalisation();
        $this->maternite();
        $this->laboratoire();
        $this->pharmacie();
        $this->stock();
        $this->caisse();
        $this->finances();
        $this->references();
        $this->liensGeneraux();

        $role = $user->roles->first();

        return [
            'profil'     => $role->libelle ?? ($role ? ucfirst(str_replace('_', ' ', $role->name)) : 'Utilisateur'),
            'salutation' => (now()->hour < 13 ? 'Bonjour' : (now()->hour < 18 ? 'Bon après-midi' : 'Bonsoir')) . ', ' . ($user->prenom ?: $user->name),
            'tuiles'     => $this->tuiles,
            'listes'     => $this->listes,
            'raccourcis' => collect($this->raccourcis)->unique('lien')->values()->all(),
            'activite'   => $this->peut('parametres.etablissement') ? $this->activiteSeptJours() : null,
        ];
    }

    // ── Sections ───────────────────────────────────────────────────────

    /** Propriétaire / responsable : synthèse du mois et état de l'établissement. */
    private function direction(): void
    {
        if (!$this->peut('parametres.etablissement')) {
            return;
        }
        $mois = now()->startOfMonth();
        $this->tuile('Patients ce mois', Patient::where('created_at', '>=', $mois)->count(), 'Nouveaux dossiers', 'fa-user-plus', 'primary', $this->lien('patients.index'));
        $this->tuile('Encaissé ce mois', $this->montant($this->encaisse($mois)), 'Net des annulations', 'fa-coins', 'success', $this->lien('caisse.index'));
        $this->tuile('Personnel actif', User::deLEtablissement()->where('statut', 'actif')->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['super_admin', 'superadmin']))->count(), 'Comptes de l\'établissement', 'fa-user-md', 'info', $this->lien('users.index'));

        $this->raccourci('Utilisateurs', 'fa-users', 'users.index');
        $this->raccourci('Paramètres', 'fa-cog', 'etablissement.edit');
    }

    /** Accueil : arrivées du jour et file d'attente. */
    private function accueil(): void
    {
        if (!$this->peut('accueil.view') || $this->clinicien) {
            return;
        }
        $jour = Passage::whereDate('arrive_at', today());
        $this->tuile('Arrivées du jour', (clone $jour)->count(), (clone $jour)->ouverts()->count() . ' encore en cours', 'fa-door-open', 'primary', $this->lien('passages.index'));
        if ($this->direction) {
            return;
        }

        $file = Passage::with(['patient', 'service'])->ouverts()->whereDate('arrive_at', today())
            ->orderByDesc('urgence')->orderByRaw('priorite IS NULL, priorite')->orderBy('arrive_at')->limit(8)->get();
        $this->liste('File d\'attente', 'fa-users', $this->lien('passages.index'), 'Personne n\'attend pour le moment.', $file->map(fn($p) => [
            'principal'  => $this->nomPatient($p->patient),
            'secondaire' => ($p->service->nom ?? 'Sans service') . ' · arrivé à ' . $p->arrive_at?->format('H\hi') . ' (' . $p->arrive_at?->diffForHumans(null, true) . ')',
            'badge'      => $p->urgence ? 'Urgence' : (Passage::STATUTS[$p->statut] ?? $p->statut),
            'couleur'    => $p->urgence ? 'danger' : (Passage::COULEURS[$p->statut] ?? 'secondary'),
        ]));

        $this->raccourci('Enregistrer une arrivée', 'fa-door-open', 'passages.index');
        $this->raccourci('Nouveau patient', 'fa-user-plus', 'patients.create', 'patients.create');
        $this->raccourci('Nouveau ticket', 'fa-ticket-alt', 'tickets.create', 'tickets.create');
    }

    /** Médecin : ses consultations, les patients qui l'attendent, ce qu'il reste à signer. */
    private function consultations(): void
    {
        if (!$this->peut('consultations.create')) {
            return;
        }
        $miennes = Consultation::where('medecin_id', $this->user->id);
        $this->tuile('Mes consultations du jour', (clone $miennes)->whereDate('date_consultation', today())->count(), 'Patients vus aujourd\'hui', 'fa-stethoscope', 'primary', $this->lien('consultations.index'));
        $nonSignees = (clone $miennes)->whereNull('signee_at')->where('date_consultation', '>=', now()->subDays(30))->count();
        $this->tuile('À signer', $nonSignees, 'Consultations des 30 derniers jours', 'fa-signature', $nonSignees ? 'warning' : 'success', $this->lien('consultations.index'));

        // Patients du jour en attente de consultation, dans son service s'il en a un
        $enAttente = Passage::with(['patient', 'service'])->whereDate('arrive_at', today())->whereIn('statut', ['enregistre', 'trie'])
            ->when($this->user->service_medical_id, fn($q, $s) => $q->where(fn($w) => $w->where('service_medical_id', $s)->orWhereNull('service_medical_id')))
            ->orderByDesc('urgence')->orderByRaw('priorite IS NULL, priorite')->orderBy('arrive_at')->limit(8)->get();
        $this->liste('Patients à voir', 'fa-user-clock', $this->lien('liste.attente'), 'Aucun patient en attente.', $enAttente->map(fn($p) => [
            'principal'  => $this->nomPatient($p->patient),
            'secondaire' => ($p->motif ?: 'Motif non précisé') . ' · attend depuis ' . $p->arrive_at?->diffForHumans(null, true),
            'badge'      => $p->urgence ? 'Urgence' : ($p->priorite ? 'P' . $p->priorite : null),
            'couleur'    => $p->urgence || $p->priorite === 1 ? 'danger' : 'warning',
        ]));

        $this->raccourci('Liste d\'attente', 'fa-user-clock', 'liste.attente');
        $this->raccourci('Nouvelle consultation', 'fa-notes-medical', 'consultations.create');
    }

    private function urgences(): void
    {
        if (!$this->module('urgences') || !$this->peut('urgences.view')) {
            return;
        }
        $enCours = Passage::with('patient')->where('urgence', true)->ouverts()->orderByRaw('priorite IS NULL, priorite')->orderBy('arrive_at')->limit(6)->get();
        $this->tuile('Urgences en cours', Passage::where('urgence', true)->ouverts()->count(), 'Prises en charge ouvertes', 'fa-ambulance', 'danger', $this->lien('urgences.index'));
        if ($enCours->isNotEmpty() && !$this->direction) {
            $this->liste('Urgences', 'fa-ambulance', $this->lien('urgences.index'), '', $enCours->map(fn($p) => [
                'principal'  => $this->nomPatient($p->patient),
                'secondaire' => ($p->motif ?: 'Urgence') . ' · depuis ' . $p->arrive_at?->diffForHumans(null, true),
                'badge'      => $p->priorite ? 'P' . $p->priorite : (Passage::STATUTS[$p->statut] ?? $p->statut),
                'couleur'    => 'danger',
            ]));
        }
        $this->raccourci('Urgences', 'fa-ambulance', 'urgences.index');
    }

    private function rendezVous(): void
    {
        if (!$this->module('rendezvous') || !$this->peut('rendezvous.view')) {
            return;
        }
        $medecin = $this->peut('consultations.create');
        $jour = RendezVous::with(['patient', 'medecin'])->whereDate('date_heure', today())->whereIn('statut', ['prevu', 'en_attente'])
            ->when($medecin, fn($q) => $q->where('medecin_id', $this->user->id))->orderBy('date_heure')->limit(8)->get();
        $this->tuile($medecin ? 'Mes rendez-vous du jour' : 'Rendez-vous du jour', $jour->count(), 'Prévus aujourd\'hui', 'fa-calendar-check', 'info', $this->lien('agenda.index'));
        if (!$this->direction) $this->liste($medecin ? 'Mes rendez-vous' : 'Rendez-vous du jour', 'fa-calendar-alt', $this->lien('agenda.index'), 'Aucun rendez-vous prévu aujourd\'hui.', $jour->map(fn($r) => [
            'principal'  => Carbon::parse($r->date_heure)->format('H\hi') . ' — ' . $this->nomPatient($r->patient),
            'secondaire' => trim(($r->motif ?: '') . ($medecin ? '' : ' · ' . ($r->medecin->name ?? ''))) ?: null,
            'badge'      => RendezVous::STATUTS[$r->statut] ?? $r->statut,
            'couleur'    => RendezVous::COULEURS[$r->statut] ?? 'secondary',
        ]));

        if ($this->peut('rendezvous.en_ligne')) {
            $enLigne = DemandeEnLigne::whereIn('statut', ['recue', 'conflit'])->count();
            if ($enLigne || $this->etablissement->connecteEnLigne()) {
                $this->tuile('Demandes en ligne', $enLigne, 'À confirmer', 'fa-globe', $enLigne ? 'warning' : 'secondary', $this->lien('en_ligne.demandes'));
            }
        }
        $this->raccourci('Agenda', 'fa-calendar-alt', 'agenda.index');
    }

    /** Infirmier et médecin : patients hospitalisés et lits. */
    private function hospitalisation(): void
    {
        if (!$this->module('hospitalisation') || !$this->peut('hospitalisations.view') || $this->peutSeulementPayer()) {
            return;
        }
        $lits = Lit::count();
        $occupes = Lit::where('statut', 'Occupé')->count();
        $this->tuile('Hospitalisés', Hospitalisation::where('etat', 'en cours')->count(), $lits ? "{$occupes} lit(s) occupé(s) sur {$lits}" : 'Aucun lit déclaré', 'fa-procedures', 'dark', $this->lien('lits.tableau'));

        if ($this->peut('consultations.suivi') || $this->peut('hospitalisations.edit')) {
            $sejours = Hospitalisation::with(['patient', 'lit'])->where('etat', 'en cours')->orderBy('date_entree')->limit(8)->get();
            $this->liste('Patients hospitalisés', 'fa-procedures', $this->lien('hospitalisations.realise'), 'Aucun patient hospitalisé.', $sejours->map(fn($h) => [
                'principal'  => $this->nomPatient($h->patient),
                'secondaire' => ($h->lit ? 'Lit ' . $h->lit->numero . ' · ' : '') . 'entré le ' . Carbon::parse($h->date_entree)->format('d/m'),
                'badge'      => 'J' . max(1, Carbon::parse($h->date_entree)->startOfDay()->diffInDays(today()) + 1),
                'couleur'    => 'secondary',
            ]));
        }
        $this->raccourci('État des lits', 'fa-bed', 'lits.tableau');
    }

    /** Sage-femme : grossesses, CPN du jour, termes proches, accouchements. */
    private function maternite(): void
    {
        // Le poste de la sage-femme (et la synthèse du responsable) ; le médecin passe par ses consultations
        if (!$this->module('maternity') || !$this->peut('maternity.accouchement') || $this->clinicien) {
            return;
        }
        $this->tuile('Grossesses suivies', Grossesse::where('statut', 'En cours')->count(), 'Suivis en cours', 'fa-baby', 'danger', $this->lien('maternity.index'));
        if ($this->direction) {
            return;
        }
        $cpnJour = ConsultationPrenatale::with('grossesse.patient')->whereDate('prochain_rdv', today())->get();
        $this->tuile('CPN prévues aujourd\'hui', $cpnJour->count(), 'Consultations prénatales', 'fa-heartbeat', 'warning', $this->lien('maternity.index'));
        if ($this->peut('maternity.accouchement')) {
            $this->tuile('Accouchements du mois', \App\Models\Accouchement::where('date_heure', '>=', now()->startOfMonth())->count(), 'Depuis le 1er', 'fa-baby-carriage', 'success', $this->lien('accouchements.index'));
        }

        $termes = Grossesse::with('patient')->where('statut', 'En cours')->whereNotNull('dpa')->whereBetween('dpa', [today()->subDays(7), today()->addDays(14)])->orderBy('dpa')->limit(8)->get();
        $this->liste('Termes proches (14 jours)', 'fa-calendar-day', $this->lien('maternity.index'), 'Aucun terme dans les 14 prochains jours.', $termes->map(fn($g) => [
            'principal'  => $this->nomPatient($g->patient),
            'secondaire' => 'Terme prévu le ' . Carbon::parse($g->dpa)->format('d/m/Y'),
            'badge'      => Carbon::parse($g->dpa)->isPast() ? 'Dépassé' : 'J-' . today()->diffInDays(Carbon::parse($g->dpa)),
            'couleur'    => Carbon::parse($g->dpa)->isPast() ? 'danger' : 'warning',
        ]));
        if ($cpnJour->isNotEmpty()) {
            $this->liste('CPN du jour', 'fa-heartbeat', $this->lien('maternity.index'), '', $cpnJour->map(fn($c) => [
                'principal'  => $this->nomPatient($c->grossesse?->patient),
                'secondaire' => 'CPN ' . ($c->numero_cpn + 1) . ' prévue',
                'badge'      => null, 'couleur' => 'warning',
            ]));
        }
        $this->raccourci('Nouveau suivi de grossesse', 'fa-baby', 'maternity.create', 'maternity.create');
        $this->raccourci('Accouchements', 'fa-baby-carriage', 'accouchements.index', 'maternity.accouchement');
    }

    /** Laborantin : examens à prélever, en cours, à valider. */
    private function laboratoire(): void
    {
        // Le poste du laborantin (saisie et validation), pas celui de l'infirmier ni du médecin
        if (!$this->module('examens') || !$this->peut('examens.edit') || $this->clinicien) {
            return;
        }
        $this->tuile('À prélever', PrescriptionExamen::where('statut', 'demande')->count(), 'Examens demandés', 'fa-vial', 'warning', $this->lien('laboratoire.index'));
        $this->tuile('En cours', PrescriptionExamen::whereIn('statut', ['preleve', 'planifie', 'realise'])->count(), 'Résultat à saisir', 'fa-microscope', 'info', $this->lien('laboratoire.index'));
        $aValider = PrescriptionExamen::where('statut', 'saisi')->count();
        $this->tuile('À valider', $aValider, 'Résultats saisis', 'fa-check-double', $aValider ? 'danger' : 'success', $this->lien('laboratoire.index', ['statut' => 'saisi']));

        $examens = PrescriptionExamen::with('consultation.patient')->whereIn('statut', ['demande', 'preleve', 'planifie', 'realise', 'saisi'])
            ->orderByRaw("FIELD(statut, 'saisi', 'realise', 'preleve', 'planifie', 'demande')")->orderBy('created_at')->limit(8)->get();
        $this->liste('Examens à traiter', 'fa-flask', $this->lien('laboratoire.index'), 'Aucun examen en attente.', $examens->map(fn($e) => [
            'principal'  => $e->examen . ' — ' . $this->nomPatient($e->consultation?->patient),
            'secondaire' => 'Demandé ' . $e->created_at->diffForHumans(),
            'badge'      => PrescriptionExamen::STATUTS[$e->statut] ?? $e->statut,
            'couleur'    => PrescriptionExamen::COULEURS[$e->statut] ?? 'secondary',
        ]));
        $this->raccourci('Poste laboratoire', 'fa-flask', 'laboratoire.index');
    }

    /** Pharmacien : ordonnances à servir, réquisitions des services. */
    private function pharmacie(): void
    {
        if (!$this->module('ordonnance') || !$this->peut('ordonnances.payer')) {
            return;
        }
        $aServir = Ordonnance::with('consultation.patient')->where(fn($q) => $q->whereNull('statutordo')->orWhereNotIn('statutordo', ['paye', 'partiellement']))
            ->where('created_at', '>=', now()->subDays(30))->latest()->limit(8)->get();
        $this->tuile('Ordonnances à servir', Ordonnance::where(fn($q) => $q->whereNull('statutordo')->orWhereNotIn('statutordo', ['paye', 'partiellement']))->where('created_at', '>=', now()->subDays(30))->count(), '30 derniers jours', 'fa-prescription-bottle-alt', 'warning', $this->lien('ordonnances.index'));
        $this->tuile('Servies aujourd\'hui', Ordonnance::whereIn('statutordo', ['paye', 'partiellement'])->whereDate('date_paiement', today())->count(), 'Ordonnances délivrées', 'fa-check-circle', 'success', $this->lien('ordonnances.lespayer'));
        $this->liste('Ordonnances en attente', 'fa-prescription', $this->lien('ordonnances.index'), 'Aucune ordonnance en attente.', $aServir->map(fn($o) => [
            'principal'  => $this->nomPatient($o->consultation?->patient),
            'secondaire' => 'Prescrite ' . $o->created_at->diffForHumans(),
            'badge'      => null, 'couleur' => 'warning',
        ]));
        $this->raccourci('Ordonnances à servir', 'fa-prescription-bottle-alt', 'ordonnances.index');
    }

    /** Pharmacie et magasin : alertes, réquisitions, propositions, réceptions. */
    private function stock(): void
    {
        if (!$this->module('stock') || !$this->peut('stock.view')) {
            return;
        }
        // Les postes qui gèrent le stock, et la synthèse du responsable ; pas le médecin qui consulte le catalogue
        if (!$this->direction && !$this->peut('stock.servir') && !$this->peut('stock.receptions') && !$this->peut('stock.inventaire')) {
            return;
        }
        $alertes = AlerteStockController::alertes();
        $nbAlertes = $alertes['ruptures']->count() + $alertes['sousSeuil']->count();
        $this->tuile('Alertes de stock', $nbAlertes, $alertes['perimes']->count() . ' lot(s) périmé(s), ' . $alertes['bientot']->count() . ' bientôt', 'fa-exclamation-triangle', $nbAlertes ? 'danger' : 'success', $this->lien('stock.alertes'));

        if ($this->direction) {
            if ($this->peut('achats.approuver') && ($propositions = DemandeAchat::where('statut', 'soumise')->count())) {
                $this->tuile('Propositions à approuver', $propositions, 'Commandes de la pharmacie', 'fa-file-signature', 'warning', $this->lien('achats.index'));
            }

            return;
        }
        if ($this->peut('stock.servir')) {
            $requisitions = Requisition::with(['service', 'lignes'])->where('statut', 'soumise')->oldest()->limit(6)->get();
            $this->tuile('Réquisitions à servir', Requisition::where('statut', 'soumise')->count(), 'Demandes des services', 'fa-dolly', 'info', $this->lien('requisitions.index'));
            $this->liste('Réquisitions des services', 'fa-dolly', $this->lien('requisitions.index'), 'Aucune réquisition à servir.', $requisitions->map(fn($r) => [
                'principal'  => ($r->service->nom ?? 'Service') . ' — ' . $r->lignes->count() . ' produit(s)',
                'secondaire' => $r->numero . ' · ' . $r->created_at->diffForHumans(),
                'badge'      => 'À servir', 'couleur' => 'warning',
            ]));
        }
        $ruptures = $alertes['ruptures']->merge($alertes['sousSeuil'])->take(6);
        if ($ruptures->isNotEmpty()) {
            $this->liste('Produits à réapprovisionner', 'fa-boxes', $this->lien('stock.alertes'), '', $ruptures->map(fn($m) => [
                'principal'  => $m->nom,
                'secondaire' => 'Stock ' . $m->stock . ' · seuil ' . $m->stock_min,
                'badge'      => $m->stock <= 0 ? 'Rupture' : 'Sous le seuil',
                'couleur'    => $m->stock <= 0 ? 'danger' : 'warning',
            ]));
        }
        if ($this->peut('achats.approuver')) {
            $propositions = DemandeAchat::where('statut', 'soumise')->count();
            if ($propositions) {
                $this->tuile('Propositions à approuver', $propositions, 'Commandes de la pharmacie', 'fa-file-signature', 'warning', $this->lien('achats.index'));
            }
        }
        if ($this->peut('stock.receptions')) {
            $this->tuile('Commandes à réceptionner', Commande::where('statut', 'en_cours')->count(), 'Livraisons attendues', 'fa-truck', 'primary', $this->lien('receptions.index'));
        }
        $this->raccourci('Réquisitions', 'fa-dolly', 'requisitions.index', 'stock.servir');
        $this->raccourci('État des besoins', 'fa-calculator', 'besoins.index', 'stock.besoins');
        $this->raccourci('Alertes de stock', 'fa-exclamation-triangle', 'stock.alertes');
    }

    /** Caissier : sa caisse, ses encaissements du jour, les clôtures à valider. */
    private function caisse(): void
    {
        if (!$this->peut('caisse.view') || !$this->peut('paiements.create')) {
            return;
        }
        $session = $this->direction ? null : CaisseSession::where('user_id', $this->user->id)->where('statut', 'ouverte')->first();
        if ($this->direction) {
            // Le responsable valide les clôtures, il ne tient pas de caisse
        } elseif ($session) {
            $this->tuile('Ma caisse', $this->montant(app(CaisseService::class)->soldeTheorique($session)), 'Ouverte depuis ' . $session->opened_at?->format('H\hi'), 'fa-cash-register', 'success', $this->lien('caisse.my_session'));
            $encaisse = (float) CaisseMouvement::where('caisse_session_id', $session->id)->where('type', 'entree')->sum('montant');
            $this->tuile('Encaissé (session)', $this->montant($encaisse), $session->mouvements()->where('type', 'entree')->count() . ' encaissement(s)', 'fa-hand-holding-usd', 'primary', $this->lien('caisse.my_session'));
        } else {
            $this->tuile('Ma caisse', 'Fermée', 'Ouvrez-la pour encaisser', 'fa-lock', 'secondary', $this->lien('caisse.my_session'));
        }
        if ($this->peut('caisse.valider_cloture')) {
            $aValider = CaisseSession::where('statut', 'fermee')->count();
            if ($aValider) {
                $this->tuile('Clôtures à valider', $aValider, 'Sessions de caisse', 'fa-check-double', 'warning', $this->lien('caisse.index'));
            }
        }
        if (!$this->direction) {
            $this->raccourci('Ma caisse', 'fa-cash-register', 'caisse.my_session');
            $this->raccourci('Nouveau ticket', 'fa-ticket-alt', 'tickets.create', 'tickets.create');
        } else {
            $this->raccourci('Supervision des caisses', 'fa-cash-register', 'caisse.index');
        }
    }

    private function finances(): void
    {
        if (!$this->module('finances')) {
            return;
        }
        if ($this->peut('depenses.valider')) {
            $this->tuile('Dépenses à valider', Depense::where('statut', 'soumise')->count(), 'Soumises', 'fa-file-invoice-dollar', 'info', $this->lien('depenses.index', ['statut' => 'soumise']));
        }
        if ($this->peut('depenses.payer')) {
            $aPayer = Depense::where('statut', 'validee');
            if ((clone $aPayer)->exists()) {
                $this->tuile('Dépenses à payer', (clone $aPayer)->count(), $this->montant((float) (clone $aPayer)->sum('montant')), 'fa-money-bill-wave', 'warning', $this->lien('depenses.index', ['statut' => 'validee']));
            }
        }
    }

    private function references(): void
    {
        if (!$this->module('references') || !$this->peut('references.gerer')) {
            return;
        }
        $attente = Reference::where('sens', 'sortante')->whereIn('statut', ['emise', 'recue', 'prise_en_charge'])->count();
        if ($attente) {
            $this->tuile('Références en attente', $attente, 'Sans contre-référence', 'fa-share-square', 'secondary', $this->lien('references.index'));
        }
        $this->raccourci('Références', 'fa-share-square', 'references.index');
    }

    private function liensGeneraux(): void
    {
        $this->raccourci('Patients', 'fa-folder-open', 'patients.index', 'patients.view');
        $this->raccourci('Rapports', 'fa-chart-bar', 'rapports.index', 'rapports.view');
    }

    /** Arrivées et encaissements des 7 derniers jours (propriétaire). */
    private function activiteSeptJours(): array
    {
        $debut = today()->subDays(6);
        $arrivees = Passage::where('arrive_at', '>=', $debut)->selectRaw('DATE(arrive_at) as jour, COUNT(*) as n')->groupByRaw('DATE(arrive_at)')->pluck('n', 'jour');
        $entrees = CaisseMouvement::where('created_at', '>=', $debut)->where('type', 'entree')->selectRaw('DATE(created_at) as jour, SUM(montant) as total')->groupByRaw('DATE(created_at)')->pluck('total', 'jour');
        $annulations = CaisseMouvement::where('created_at', '>=', $debut)->where('type', 'sortie')->whereNotNull('mouvement_annule_id')->selectRaw('DATE(created_at) as jour, SUM(montant) as total')->groupByRaw('DATE(created_at)')->pluck('total', 'jour');

        return collect(range(6, 0))->map(function ($i) use ($arrivees, $entrees, $annulations) {
            $jour = today()->subDays($i);
            $cle = $jour->toDateString();

            return ['jour' => $jour->translatedFormat('D d'), 'arrivees' => (int) ($arrivees[$cle] ?? 0), 'encaisse' => (float) ($entrees[$cle] ?? 0) - (float) ($annulations[$cle] ?? 0)];
        })->all();
    }

    // ── Outils ─────────────────────────────────────────────────────────

    private function encaisse(Carbon $depuis): float
    {
        $entrees = (float) CaisseMouvement::where('created_at', '>=', $depuis)->where('type', 'entree')->sum('montant');
        $annulations = (float) CaisseMouvement::where('created_at', '>=', $depuis)->where('type', 'sortie')->whereNotNull('mouvement_annule_id')->sum('montant');

        return $entrees - $annulations;
    }

    /** Le caissier consulte les hospitalisations pour encaisser, pas pour soigner. */
    private function peutSeulementPayer(): bool
    {
        return $this->peut('hospitalisations.paiement') && !$this->peut('hospitalisations.edit') && !$this->peut('consultations.suivi');
    }

    private function peut(string $permission): bool
    {
        return $this->user->can($permission);
    }

    private function module(string $module): bool
    {
        return $this->etablissement->moduleActif($module);
    }

    private function lien(string $route, array $parametres = []): ?string
    {
        return Route::has($route) ? route($route, $parametres) : null;
    }

    private function tuile(string $titre, $valeur, string $detail, string $icone, string $couleur, ?string $lien): void
    {
        $this->tuiles[] = compact('titre', 'detail', 'icone', 'couleur', 'lien') + ['valeur' => (string) $valeur];
    }

    private function liste(string $titre, string $icone, ?string $lien, string $vide, $lignes): void
    {
        $this->listes[] = ['titre' => $titre, 'icone' => $icone, 'lien' => $lien, 'vide' => $vide, 'lignes' => collect($lignes)->values()->all()];
    }

    private function raccourci(string $titre, string $icone, string $route, ?string $permission = null): void
    {
        if (($permission === null || $this->peut($permission)) && ($lien = $this->lien($route))) {
            $this->raccourcis[] = compact('titre', 'icone', 'lien');
        }
    }

    private function montant(float $valeur): string
    {
        return number_format($valeur, 0, ',', ' ') . ' F';
    }

    private function nomPatient(?Patient $patient): string
    {
        return $patient ? mb_strtoupper((string) $patient->nom) . ' ' . $patient->prenom : 'Patient';
    }
}
