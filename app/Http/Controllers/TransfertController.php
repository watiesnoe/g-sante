<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Hospitalisation;
use App\Models\Transfert;
use App\Models\User;
use App\Models\ServiceMedical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Yajra\DataTables\DataTables;

class TransfertController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('transferts.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les transferts.');

        if ($request->ajax()) {
            $transferts = Transfert::with(['patient', 'sourceMedecin', 'destMedecin', 'sourceService', 'destService', 'user'])->latest();

            return DataTables::of($transferts)
                ->addColumn('patient', function($row) {
                    return $row->patient ? $row->patient->prenom.' '.$row->patient->nom : '-';
                })
                ->addColumn('type_label', function($row) {
                    return match($row->type) {
                        'medecin' => '<span class="badge bg-primary">Médecin</span>',
                        'service' => '<span class="badge bg-info">Service</span>',
                        'hopital_externe' => '<span class="badge bg-warning">Hôpital Externe</span>',
                        default => $row->type
                    };
                })
                ->addColumn('source', function($row) {
                    if ($row->type === 'medecin') return $row->sourceMedecin ? $row->sourceMedecin->name : '-';
                    if ($row->type === 'service') return $row->sourceService ? $row->sourceService->nom : '-';
                    return 'Interne';
                })
                ->addColumn('destination', function($row) {
                    if ($row->type === 'medecin') return $row->destMedecin ? $row->destMedecin->name : '-';
                    if ($row->type === 'service') return $row->destService ? $row->destService->nom : '-';
                    return $row->hopital_destination ?? '-';
                })
                ->addColumn('date', function($row) {
                    return \Carbon\Carbon::parse($row->date_transfert)->format('d/m/Y H:i');
                })
                ->addColumn('actions', function($row){
                    $user = Auth::user();
                    $html = '';

                    if ($user->can('transferts.delete')) {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTransfert('.$row->id.')" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    }
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['type_label', 'actions'])
                ->make(true);
        }

        return view('application.transfert.index');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('transferts.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission d\'effectuer un transfert.');

        $request->validate([
            'patient_id'          => 'required|exists:patients,uuid',
            'type'                => 'required|in:medecin,service,hopital_externe',
            'motif'               => 'required|string',
            'consultation_id'     => 'nullable|exists:consultations,uuid',
            'hospitalisation_id'  => 'nullable|exists:hospitalisations,uuid',
            'dest_medecin_id'     => 'required_if:type,medecin|nullable|exists:users,id',
            'dest_service_id'     => 'required_if:type,service|nullable|exists:service_medicals,id',
            'hopital_destination' => 'required_if:type,hopital_externe|nullable|string',
        ]);

        // 🔒 Le dropdown ne propose que des médecins actifs, mais on revérifie côté
        // serveur pour empêcher qu'un transfert ne cible un compte sans le rôle médecin.
        if ($request->type === 'medecin' && !User::deLEtablissement()->role('medecin')->where('id', $request->dest_medecin_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Le compte sélectionné n\'est pas un médecin.',
            ], 422);
        }

        // Resolve models by UUID
        $patient        = \App\Models\Patient::where('uuid', $request->patient_id)->firstOrFail();
        $consultation   = $request->consultation_id
            ? \App\Models\Consultation::where('uuid', $request->consultation_id)->first()
            : null;
        $hospitalisation = $request->hospitalisation_id
            ? \App\Models\Hospitalisation::where('uuid', $request->hospitalisation_id)->first()
            : null;

        DB::beginTransaction();

        try {
            $transfert = new Transfert();
            $transfert->patient_id = $patient->id;
            $transfert->type = $request->type;
            $transfert->motif = $request->motif;
            $transfert->date_transfert = now();
            $transfert->user_id = Auth::id();
            $transfert->consultation_id = $consultation?->id;
            $transfert->hospitalisation_id = $hospitalisation?->id;

            if ($request->type === 'medecin') {
                if (!$transfert->consultation_id) {
                    throw new \Exception("Une consultation est requise pour transférer à un autre médecin.");
                }
                $transfert->source_medecin_id = $consultation->medecin_id;
                $transfert->dest_medecin_id = $request->dest_medecin_id;

                // 🔒 On ne réassigne PAS consultation->medecin_id : ce champ doit rester
                // celui du médecin qui a réellement examiné/documenté cette consultation
                // (traçabilité médicale). Le transfert donne au médecin destinataire un
                // accès au dossier du patient via Transfert::dest_medecin_id (voir
                // ConsultationController), sans effacer l'auteur d'origine — qui, sinon,
                // perdrait tout accès à sa propre consultation.
            } else {
                // Pour service ou hopital_externe, on a besoin d'une hospitalisation
                if (!$hospitalisation && $consultation) {
                    $hospitalisation = Hospitalisation::where('consultation_id', $consultation->id)
                        ->where('etat', 'en cours')
                        ->first();
                }

                if (!$hospitalisation) {
                    throw new \Exception("Le transfert vers un service ou un autre hôpital nécessite une hospitalisation en cours.");
                }

                $transfert->hospitalisation_id = $hospitalisation->id;

                if ($request->type === 'service') {
                    $transfert->source_service_id = $hospitalisation->service_id;
                    $transfert->dest_service_id = $request->dest_service_id;

                    // Update hospitalisation service
                    $hospitalisation->service_id = $request->dest_service_id;
                    $hospitalisation->save();
                } elseif ($request->type === 'hopital_externe') {
                    $transfert->hopital_destination = $request->hopital_destination;
                    
                    $hospitalisation->observations = ($hospitalisation->observations ? $hospitalisation->observations . "\n" : "") . "Transféré vers: " . $request->hopital_destination . ". Motif: " . $request->motif;
                    $hospitalisation->save();

                    // Sortie par transfert : séjour clos, lit libéré (RG-12)
                    app(\App\Services\HospitalisationService::class)->sortir($hospitalisation, 'Transfert');

                    // Un départ vers un autre établissement est une référence sortante :
                    // fiche imprimable, contre-référence attendue, compté au rapport mensuel (EF-11.01)
                    $reference = \App\Models\Reference::create([
                        'numero'                => app(\App\Services\NumerotationService::class)->suivant('reference'),
                        'sens'                  => 'sortante',
                        'evacuation'            => false,
                        'patient_id'            => $patient->id,
                        'hospitalisation_id'    => $hospitalisation->id,
                        'structure_origine'     => \App\Models\Etablissement::courant()->nom,
                        'structure_destination' => $request->hopital_destination,
                        'motif'                 => $request->motif,
                        'statut'                => 'emise',
                        'date_reference'        => now(),
                        'user_id'               => Auth::id(),
                    ]);
                }
            }

            $transfert->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => isset($reference)
                    ? 'Transfert enregistré ✅ Référence ' . $reference->numero . ' créée : imprimez sa fiche depuis « Références / évacuations ».'
                    : 'Transfert enregistré avec succès ✅',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Erreur transfert patient : ' . $e->getMessage(), ['exception' => $e]);

            $messagesMetier = [
                "Une consultation est requise pour transférer à un autre médecin.",
                "Le transfert vers un service ou un autre hôpital nécessite une hospitalisation en cours.",
            ];
            $message = in_array($e->getMessage(), $messagesMetier)
                ? $e->getMessage()
                : 'Une erreur est survenue lors de l\'enregistrement du transfert.';

            return response()->json([
                'success' => false,
                'message' => $message,
            ], in_array($e->getMessage(), $messagesMetier) ? 422 : 500);
        }
    }

    public function destroy(Transfert $transfert)
    {
        abort_unless(Auth::user()->can('transferts.delete'), 403, 'Accès non autorisé : vous n\'avez pas la permission d\'annuler un transfert.');

        try {
            $transfert->delete();
            return response()->json([
                'success' => true,
                'message' => 'Transfert supprimé avec succès ✅',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur suppression transfert : ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression du transfert.',
            ], 500);
        }
    }
}
