<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
class RendezvousController extends Controller
{


    public function index(Request $request)
    {
        if ($request->ajax()) {
            $year = session('exercice_year', date('Y'));
            $rdvs = RendezVous::with(['patient', 'medecin', 'consultation'])
                ->whereYear('date_heure', $year)
                ->whereNotIn('statut', ['annule', 'realise']) // 🔹 Exclure annulé et réalisé
                ->select('rendezvous.*');

            return DataTables::of($rdvs)
                ->addIndexColumn()

                // Patient
                ->addColumn('patient', fn($rdv) => optional($rdv->patient)->nom . ' ' . optional($rdv->patient)->prenom ?? '-')

                // Médecin
                ->addColumn('medecin', fn($rdv) => optional($rdv->medecin)->name ?? '-')

                // Date formatée
                ->addColumn('date_heure', fn($rdv) => $rdv->date_heure ? Carbon::parse($rdv->date_heure)->format('d-m-Y H:i') : '-')

                // Motif
                ->addColumn('motif', fn($rdv) => $rdv->motif ?? '-')

                // Consultation
                ->addColumn('consultation', fn($rdv) => $rdv->consultation ? "Consultation #{$rdv->consultation->id}" : '-')

                // Actions
                ->addColumn('actions', function($rdv) {
                    $view   = '<a href="'.route('rendezvous.show', $rdv->id).'" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    $edit   = '<a href="'.route('rendezvous.edit', $rdv->id).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    $delete = '<button type="button" data-url="'.route('rendezvous.destroy', $rdv->id).'" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    $realiseBtn = '';
                    if ($rdv->statut !== 'realise') {
                        $realiseBtn = '<button type="button" class="btn btn-sm btn-outline-success btn-realise" data-url="'.route('rendezvous.marquerRealise', $rdv->id).'" title="Marquer comme réalisé"><i class="fa fa-check"></i></button>';
                    }

                    $suiviBtn = '';
                    if ($rdv->consultation) {
                        $suiviBtn = '<a href="'.route('consultations.suivi.create', $rdv->consultation->id).'" class="btn btn-sm btn-outline-success" title="Créer un suivi"><i class="fa fa-file-medical"></i></a>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $realiseBtn . $suiviBtn . $delete . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.rendezvous.index');
    }
    public function disponible(Request $request)
    {
        if ($request->ajax()) {
            $rdvs = RendezVous::with(['patient', 'medecin', 'consultation'])
                ->where('statut', ['realise']) // 🔹 Exclure annulé et réalisé
                ->select('rendezvous.*');

            return DataTables::of($rdvs)
                ->addIndexColumn()

                // Patient
                ->addColumn('patient', fn($rdv) => optional($rdv->patient)->nom . ' ' . optional($rdv->patient)->prenom ?? '-')

                // Médecin
                ->addColumn('medecin', fn($rdv) => optional($rdv->medecin)->name ?? '-')

                // Date formatée
                ->addColumn('date_heure', fn($rdv) => $rdv->date_heure ? Carbon::parse($rdv->date_heure)->format('d-m-Y H:i') : '-')

                // Motif
                ->addColumn('motif', fn($rdv) => $rdv->motif ?? '-')

                // Consultation
                ->addColumn('consultation', fn($rdv) => $rdv->consultation ? "Consultation #{$rdv->consultation->id}" : '-')

                // Actions
                ->addColumn('actions', function($rdv) {
                    $view   = '<a href="'.route('rendezvous.show', $rdv->id).'" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    $edit   = '<a href="'.route('rendezvous.edit', $rdv->id).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    $delete = '<button type="button" data-url="'.route('rendezvous.destroy', $rdv->id).'" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    $realiseBtn = '';
                    if ($rdv->statut !== 'realise') {
                        $realiseBtn = '<button type="button" class="btn btn-sm btn-outline-success btn-realise" data-url="'.route('rendezvous.marquerRealise', $rdv->id).'" title="Marquer comme réalisé"><i class="fa fa-check"></i></button>';
                    }

                    $suiviBtn = '';
                    if ($rdv->consultation) {
                        $suiviBtn = '<a href="'.route('consultations.suivi.create', $rdv->consultation->id).'" class="btn btn-sm btn-outline-success" title="Créer un suivi"><i class="fa fa-file-medical"></i></a>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $realiseBtn . $suiviBtn . $delete . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.rendezvous.rdvdisponible');
    }
    public function annuler(Request $request)
    {
        if ($request->ajax()) {
            $rdvs = RendezVous::with(['patient', 'medecin', 'consultation'])
                ->where('statut', ['annule']) // 🔹 Exclure annulé et réalisé
                ->select('rendezvous.*');

            return DataTables::of($rdvs)
                ->addIndexColumn()

                // Patient
                ->addColumn('patient', fn($rdv) => optional($rdv->patient)->nom . ' ' . optional($rdv->patient)->prenom ?? '-')

                // Médecin
                ->addColumn('medecin', fn($rdv) => optional($rdv->medecin)->name ?? '-')

                // Date formatée
                ->addColumn('date_heure', fn($rdv) => $rdv->date_heure ? Carbon::parse($rdv->date_heure)->format('d-m-Y H:i') : '-')

                // Motif
                ->addColumn('motif', fn($rdv) => $rdv->motif ?? '-')

                // Consultation
                ->addColumn('consultation', fn($rdv) => $rdv->consultation ? "Consultation #{$rdv->consultation->id}" : '-')

                // Actions
                ->addColumn('actions', function($rdv) {
                    $view   = '<a href="'.route('rendezvous.show', $rdv->id).'" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    $edit   = '<a href="'.route('rendezvous.edit', $rdv->id).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    $delete = '<button type="button" data-url="'.route('rendezvous.destroy', $rdv->id).'" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    $realiseBtn = '';
                    if ($rdv->statut !== 'realise') {
                        $realiseBtn = '<button type="button" class="btn btn-sm btn-outline-success btn-realise" data-url="'.route('rendezvous.marquerRealise', $rdv->id).'" title="Marquer comme réalisé"><i class="fa fa-check"></i></button>';
                    }

                    $suiviBtn = '';
                    if ($rdv->consultation) {
                        $suiviBtn = '<a href="'.route('consultations.suivi.create', $rdv->consultation->id).'" class="btn btn-sm btn-outline-success" title="Créer un suivi"><i class="fa fa-file-medical"></i></a>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $realiseBtn . $suiviBtn . $delete . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.rendezvous.annule');
    }
    public function marquerRealise(RendezVous $rendezvous)
    {
        // Vérifie que le rendez-vous n'est pas déjà réalisé
        if ($rendezvous->statut !== 'realise') {
            $rendezvous->statut = 'realise';
            $rendezvous->save();
            return response()->json(['success' => true, 'message' => 'Rendez-vous marqué comme réalisé.']);
        }

        return response()->json(['success' => false, 'message' => 'Rendez-vous déjà réalisé.']);
    }


    // Optionnel : afficher un rendez-vous
    public function show(RendezVous $rendezvous)
    {
        abort_unless(auth()->user()->can('rendezvous.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les rendez-vous.');

        $rendezvous->load(['patient', 'medecin', 'consultation']);
        return view('application.rendezvous.index');
    }
}
