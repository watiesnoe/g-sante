<?php
namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Consultation;
use App\Models\Examen;
use App\Models\Fournisseur;
use App\Models\Hospitalisation;
use App\Models\Lit;
use App\Models\Medicament;
use App\Models\Paiement;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Salle;
use App\Models\User;
use App\Models\Ordonnance;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin') || $user->hasRole('superadmin')) {
            return $this->superadminDashboard();
        } elseif ($user->hasRole('admin')) {
            return $this->adminDashboard();
        } elseif ($user->hasRole('secretaire')) {
            return $this->secretaireDashboard();
        } elseif ($user->hasRole('medecin')) {
            return $this->medecinDashboard();
        } elseif ($user->hasRole('client')) {
            return $this->clientDashboard();
        } else {
            return $this->defaultDashboard();
        }
    }

    private function superadminDashboard()
    {
        $year = session('exercice_year', date('Y'));
        $stats = [
            'total_users' => User::count(),
            'total_medecins' => User::role('medecin')->count(),
            'total_secretaires' => User::role('secretaire')->count(),
            'total_admins' => User::role('admin')->count(),
            'total_patients' => Patient::whereYear('created_at', $year)->count(),
            'total_ordonnance'=> Ordonnance::whereYear('created_at', $year)->count(),
            'total_fournisseur'=> Fournisseur::count(),
            'total_rendezvou'=> RendezVous::whereYear('created_at', $year)->count(),
            'total_ticket'=> \App\Models\Ticket::whereYear('created_at', $year)->count(),
            'total_medicament'=> Medicament::count(),
            'total_examens'=> Examen::count(),
            'total_lits'=> Lit::count(),
            'new_patients_today' => Patient::whereYear('created_at', $year)->whereDate('created_at', today())->count(),
            'total_consultations' => Consultation::whereYear('created_at', $year)->count(),
        ];

        // Préparer les données pour le graphique
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $last7Days->push([
                'date' => $date,
                'patients' => Patient::whereDate('created_at', $date)->count(),
                'consultations' => Consultation::whereDate('created_at', $date)->count(),
                'rendezvous' => RendezVous::whereDate('created_at', $date)->count(),
            ]);
        }

        return view('dashboard', compact('stats', 'last7Days'));
    }


    private function adminDashboard()
    {
        $year = session('exercice_year', date('Y'));
        $stats = [
            'total_personnel' => User::role(['medecin', 'secretaire'])->count(),
            'consultations_mois' => Consultation::whereYear('created_at', $year)->whereMonth('created_at', now()->month)->count(),
            'revenus_mois' => Paiement::whereYear('created_at', $year)->whereMonth('created_at', now()->month)->sum('montant_total'),
            'alertes_stock' => Medicament::whereColumn('stock', '<=', 'stock_min')->count(),
            'total_patients' => Patient::whereYear('created_at', $year)->count(),
            'new_patients_today' => Patient::whereYear('created_at', $year)->whereDate('created_at', today())->count(),
        ];

        $lowStockMedicaments = Medicament::whereColumn('stock', '<=', 'stock_min')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'lowStockMedicaments'));
    }

    private function secretaireDashboard()
    {
        $todayAppointments = RendezVous::with(['patient', 'medecin'])
            ->whereDate('date_heure', today())
            ->orderBy('date_heure')
            ->get();

        $stats = [
            'new_patients_today' => Patient::whereDate('created_at', today())->count(),
            'rdv_realises' => RendezVous::whereDate('date_heure', today())->where('statut', 'realise')->count(),
            'rdv_attente' => RendezVous::whereDate('date_heure', today())->where('statut', 'prevu')->count(),
//            'factures_pending' => Facture::where('statut', 'en_attente')->count(),
            'total_patients' => Patient::count(),
        ];

        return view('dashboard', compact('todayAppointments', 'stats'));
    }

    private function medecinDashboard()
    {
        $medecinId = Auth::id();

        $stats = [
            'consultations_today' => Consultation::where('medecin_id', $medecinId)
                ->whereDate('created_at', today())
                ->count(),
            'total_patients' => Patient::count(),
            'total_consultations' => Consultation::where('medecin_id', $medecinId)->count(),
            'total_hospitalisations' => Hospitalisation::count(),
            'new_patients_today' => Patient::whereDate('created_at', today())->count(),
            'active_hospitalisations' => Hospitalisation::where('etat', 'en cours')->count(),
        ];

        $todayAppointments = RendezVous::with('patient')
            ->where('medecin_id', $medecinId)
            ->whereDate('date_heure', today())
            ->orderBy('date_heure')
            ->get();

        $activeHospitalisations = Hospitalisation::with(['consultation.patient', 'salle', 'service'])
            ->where('etat', 'en cours')
            ->get();

        $consultationStats = $this->getConsultationStats();

        return view('dashboard', compact(
            'stats',
            'todayAppointments',
            'activeHospitalisations',
            'consultationStats'
        ));
    }

    private function clientDashboard()
    {
        $user = Auth::user();
        $patientId = $user->patient->id ?? null;

        $data = [
            'mesRendezVous' => collect(),
            'derniereConsultation' => null,
            'ordonnancesActives' => collect(),
        ];

        if ($patientId) {
            $data['mesRendezVous'] = RendezVous::with('medecin')
                ->where('patient_id', $patientId)
                ->where('date_heure', '>=', now())
                ->orderBy('date_heure')
                ->get();

            $data['derniereConsultation'] = Consultation::where('patient_id', $patientId)
                ->latest()
                ->first();

            $data['ordonnancesActives'] = Ordonnance::where('patient_id', $patientId)
                ->where('est_active', true)
                ->get();
        }

        return view('dashboard', $data);
    }

    private function defaultDashboard()
    {
        $user = Auth::user();

        // Tableau de bord spécifique pour le pharmacien
        if ($user->hasRole('pharmacien')) {
            $stats = [
                // Statistiques principales pour le pharmacien
                'total_ordonnances' => Ordonnance::count(),
                'ordonnances_today' => Ordonnance::whereDate('created_at', today())->count(),
                'ordonnances_pending' => Ordonnance::where('statutordo', 'impaye')->count(),
                'ordonnances_processed' => Ordonnance::where('statutordo', 'paye')->count(),

                // Gestion du stock
                'total_medicaments' => Medicament::count(),
                'medicaments_low_stock' => Medicament::where('stock', '<=', 10)->where('stock', '>', 0)->count(),
                'medicaments_out_of_stock' => Medicament::where('stock', '<=', 0)->count(),
//                'medicaments_expiring_soon' => Medicament::whereDate('date_expiration', '<=', now()->addDays(30))->count(),

                // Fournisseurs et approvisionnement
                'total_fournisseurs' => Fournisseur::count(),
                'commandes_pending' => Commande::where('statut', 'en_attente')->count(),


                // Patients et consultations liées
                'total_patients' => Patient::count(),
                'patients_today' => Patient::whereDate('created_at', today())->count(),

                // Hospitalisation et lits
                'total_lits' => Lit::count(),
                'lits_occupes' => Lit::where('statut', 'occupé')->count(),
                'patients_hospitalises' => Hospitalisation::where('etat', 'en cours')->count(),

                // Tickets et file d'attente
                'total_tickets' => \App\Models\Ticket::count(),
                'tickets_today' => \App\Models\Ticket::whereDate('created_at', today())->count(),

                // Paiements et finances
                'paiements_today' => Paiement::whereDate('created_at', today())->sum('montant_total'),
                'paiements_pending' => Paiement::where('statut', 'en_attente')->count(),
            ];

            // Données pour les graphiques
            $charts = [
                'ordonnances_monthly' => $this->getOrdonnancesMonthly(),
                'medicaments_stock' => $this->getMedicamentsStockStats(),
                'top_medicaments' => $this->getTopMedicaments(),
            ];

            return view('dashboard', compact('stats', 'charts'));
        }

        // Tableau de bord par défaut pour les autres rôles
        $stats = [
            'total_patients'          => Patient::count(),
            'total_consultations'     => Consultation::count(),
            'total_ordonnances'       => Ordonnance::count(),
            'ordonnances_today'       => Ordonnance::whereDate('created_at', today())->count(),
            'ordonnances_pending'     => Ordonnance::where('statutordo', 'impaye')->count(),
            'ordonnances_processed'   => Ordonnance::where('statutordo', 'paye')->count(),
            'total_medecins'          => User::role('medecin')->count(),
            'total_rendezvous'        => RendezVous::count(),
            'total_medicaments'       => Medicament::count(),
            'medicaments_low_stock'   => Medicament::where('stock', '<=', 10)->where('stock', '>', 0)->count(),
            'medicaments_out_of_stock'=> Medicament::where('stock', '<=', 0)->count(),
            'total_fournisseurs'      => Fournisseur::count(),
            'patients_today'          => Patient::whereDate('created_at', today())->count(),
            'total_lits'              => Lit::count(),
            'lits_occupes'            => Lit::where('statut', 'occupé')->count(),
            'total_tickets'           => \App\Models\Ticket::count(),
        ];

        return view('dashboard', compact('stats'));
    }

// Méthodes helper pour les données des graphiques
    private function getOrdonnancesMonthly()
    {
        return Ordonnance::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month');
    }

    private function getMedicamentsStockStats()
    {
        return [
            'normal' => Medicament::where('stock', '>', 10)->count(),
            'low' => Medicament::whereBetween('stock', [1, 10])->count(),
            'out' => Medicament::where('stock', 0)->count(),
        ];
    }

    private function getTopMedicaments()
    {
        return Medicament::orderBy('stock', 'asc')
            ->take(10)
            ->get(['nom', 'stock', 'stock_min']);
    }

    private function getConsultationStats()
    {
        $months = [];
        $counts = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');
            $counts[] = Consultation::whereYear('date_consultation', $month->year)
                ->whereMonth('date_consultation', $month->month)
                ->count();
        }

        return [
            'months' => $months,
            'counts' => $counts
        ];
    }
}
