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

                    return '
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="'.route('rendezvous.show', $rdv->id).'">👁️ Voir</a></li>
                            <li><a class="dropdown-item" href="'.route('rendezvous.edit', $rdv->id).'">✏️ Modifier</a></li>
                            '.($realiseBtn ? "<li>$realiseBtn</li>" : "").'
                            '.($suiviBtn ? "<li>$suiviBtn</li>" : "").'
                            <li>
                                <button data-url="'.route('rendezvous.destroy', $rdv->id).'" class="dropdown-item text-danger btn-delete">🗑️ Supprimer</button>
                            </li>
                        </ul>
                    </div>';
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

                    return '
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="'.route('rendezvous.show', $rdv->id).'">👁️ Voir</a></li>
                            <li><a class="dropdown-item" href="'.route('rendezvous.edit', $rdv->id).'">✏️ Modifier</a></li>
                            '.($realiseBtn ? "<li>$realiseBtn</li>" : "").'
                            '.($suiviBtn ? "<li>$suiviBtn</li>" : "").'
                            <li>
                                <button data-url="'.route('rendezvous.destroy', $rdv->id).'" class="dropdown-item text-danger btn-delete">🗑️ Supprimer</button>
                            </li>
                        </ul>
                    </div>';
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

                    return '
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="'.route('rendezvous.show', $rdv->id).'">👁️ Voir</a></li>
                            <li><a class="dropdown-item" href="'.route('rendezvous.edit', $rdv->id).'">✏️ Modifier</a></li>
                            '.($realiseBtn ? "<li>$realiseBtn</li>" : "").'
                            '.($suiviBtn ? "<li>$suiviBtn</li>" : "").'
                            <li>
                                <button data-url="'.route('rendezvous.destroy', $rdv->id).'" class="dropdown-item text-danger btn-delete">🗑️ Supprimer</button>
                            </li>
                        </ul>
                    </div>';
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
        return view('rendezvous.show', compact('rendezvous'));
    }
}
