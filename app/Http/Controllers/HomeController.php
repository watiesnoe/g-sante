<?php
namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Hospitalisation;
use App\Models\Patient;
use App\Models\Ticket;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Compteurs rapides
        $patientsCount = Patient::count();
        $consultationsToday = Consultation::whereDate('created_at', today())->count();
        $hospitalisationsActives = Hospitalisation::where('statut', 'en_cours')->count();
//        $totalPaiements = Paiement::sum('montant');

        // Graphique : consultations par mois
        $consultationsParMois = Consultation::selectRaw('MONTH(created_at) as mois, COUNT(*) as total')
            ->groupBy('mois')
            ->pluck('total', 'mois');

        // Graphique : paiements par service
//        $paiementsParService = Paiement::selectRaw('service, SUM(montant) as total')
//            ->groupBy('service')
//            ->pluck('total', 'service');

        // Derniers patients & tickets
        $derniersPatients = Patient::latest()->take(5)->get();
        $ticketsEnAttente = Ticket::where('statut', 'en_attente')->take(5)->get();

        return view('dashboard', compact(
            'patientsCount',
            'consultationsToday',
            'hospitalisationsActives',

            'consultationsParMois',
            'derniersPatients',
            'ticketsEnAttente'
        ));
    }
}
