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
            $rdvs = RendezVous::with(['patient', 'medecin', 'consultation'])
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
                    $realiseBtn = '';
                    $suiviBtn   = '';

                    // Bouton "Marquer comme réalisé" seulement si pas déjà réalisé
                    if ($rdv->statut !== 'realise') {
                        $realiseBtn = '<span class="  btn-sm btn-realise text-success" data-url="'.route('rendezvous.marquerRealise', $rdv->id).'" title="Marquer comme réalisé"><i class="fa fa-check text-success"></i></span> ';
                    }


                    // Bouton "Créer un suivi" si consultation existe
                    if ($rdv->consultation) {
                        $suiviBtn = '<span class="  btn-sm btn-realise text-success" data-url="'.route('consultations.suivi.create', $rdv->consultation->id).'" title="Marquer comme réalisé"><i class="fa fa-check text-success"></i></span> ';
                    }

                    $viewBtn = '<a href="'.route('rendezvous.show', $rdv->id).'" class="btn-sm" title="Voir"><i class="fa fa-eye text-primary"></i></a> ';
                    $editBtn = '<a href="'.route('rendezvous.edit', $rdv->id).'" class="btn-sm" title="Modifier"><i class="fa fa-pencil-alt text-info"></i></a> ';
                    $realiseBtn = $realiseBtn ? '<span class="'.$realiseBtn.'</span> ' : '';
                    $suiviBtn = $suiviBtn ? '<span class="'.$suiviBtn.'</span> ' : '';
                    $deleteBtn = '<button data-url="'.route('rendezvous.destroy', $rdv->id).'" class="btn-sm border-0 bg-transparent text-danger btn-delete" title="Supprimer"><i class="fa fa-trash text-danger"></i></button>';
                    return $viewBtn.$editBtn.$realiseBtn.$suiviBtn.$deleteBtn;
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
                    $realiseBtn = '';
                    $suiviBtn   = '';

                    // Bouton "Marquer comme réalisé" seulement si pas déjà réalisé
                    if ($rdv->statut !== 'realise') {
                        $realiseBtn = '<button data-url="'.route('rendezvous.marquerRealise', $rdv->id).'" class="dropdown-item btn-realise text-success">
                            ✅ Marquer comme réalisé
                        </button>';
                    }


                    // Bouton "Créer un suivi" si consultation existe
                    if ($rdv->consultation) {
                        $suiviBtn = '<a href="'.route('consultations.suivi.create', $rdv->consultation->id).'" class="dropdown-item text-primary">
                        📄 Ajouter un suivi
                    </a>';
                    }

                    $viewBtn = '<a href="'.route('rendezvous.show', $rdv->id).'" class="btn-sm" title="Voir"><i class="fa fa-eye text-primary"></i></a> ';
                    $editBtn = '<a href="'.route('rendezvous.edit', $rdv->id).'" class="btn-sm" title="Modifier"><i class="fa fa-pencil-alt text-info"></i></a> ';
                    $deleteBtn = '<button data-url="'.route('rendezvous.destroy', $rdv->id).'" class="btn-sm border-0 bg-transparent text-danger btn-delete" title="Supprimer"><i class="fa fa-trash text-danger"></i></button>';
                    
                    $out = $viewBtn.$editBtn;
                    if ($realiseBtn) $out .= '<div class="d-inline">'.$realiseBtn.'</div> ';
                    if ($suiviBtn) $out .= '<div class="d-inline">'.$suiviBtn.'</div> ';
                    return $out.$deleteBtn;
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
                    $realiseBtn = '';
                    $suiviBtn   = '';

                    // Bouton "Marquer comme réalisé" seulement si pas déjà réalisé
                    if ($rdv->statut !== 'realise') {
                        $realiseBtn = '<button data-url="'.route('rendezvous.marquerRealise', $rdv->id).'" class="dropdown-item btn-realise text-success">
                            ✅ Marquer comme réalisé
                        </button>';
                    }


                    // Bouton "Créer un suivi" si consultation existe
                    if ($rdv->consultation) {
                        $suiviBtn = '<a href="'.route('consultations.suivi.create', $rdv->consultation->id).'" class="dropdown-item text-primary">
                        📄 Ajouter un suivi
                    </a>';
                    }

                    $viewBtn = '<a href="'.route('rendezvous.show', $rdv->id).'" class="btn-sm" title="Voir"><i class="fa fa-eye text-primary"></i></a> ';
                    $editBtn = '<a href="'.route('rendezvous.edit', $rdv->id).'" class="btn-sm" title="Modifier"><i class="fa fa-pencil-alt text-info"></i></a> ';
                    $deleteBtn = '<button data-url="'.route('rendezvous.destroy', $rdv->id).'" class="btn-sm border-0 bg-transparent text-danger btn-delete" title="Supprimer"><i class="fa fa-trash text-danger"></i></button>';
                    
                    $out = $viewBtn.$editBtn;
                    if ($realiseBtn) $out .= '<div class="d-inline">'.$realiseBtn.'</div> ';
                    if ($suiviBtn) $out .= '<div class="d-inline">'.$suiviBtn.'</div> ';
                    return $out.$deleteBtn;
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
        $rendezvous->load(['patient', 'medecin', 'consultation']);
        return view('application.rendezvous.index');
    }
}
