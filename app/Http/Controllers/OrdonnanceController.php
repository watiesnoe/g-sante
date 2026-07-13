<?php
namespace App\Http\Controllers;
use App\Models\Ordonnance;
use App\Models\Consultation;
use App\Models\Medicament;
use App\Models\OrdonnanceMedicament;
use App\Models\OrdonnancePaiement;
use App\Models\Patient;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrdonnanceController extends Controller
{
    // Liste des ordonnances

    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->can('ordonnances.view'), 403, 'Accès non autorisé : vous n\'avez pas accès à la gestion des ordonnances.');

        if ($request->ajax()) {
            $year = session('exercice_year', date('Y'));
            $ordonnances = Ordonnance::with(['consultation.patient','medicaments'])
                ->select('ordonnances.*')
                ->whereYear('ordonnances.created_at', $year)
                ->whereNotIn('statutordo', ['paye', 'partiellement']) // 🔥 exclure ces statuts
                ->when(!$user->hasRole(['super_admin', 'superadmin', 'admin']), function ($query) use ($user) {
                    $query->whereHas('consultation', function ($q) use ($user) {
                        $q->where('medecin_id', $user->id);
                    });
                })
                ->distinct()
                ->orderBy('ordonnances.created_at','desc');


            return datatables()->of($ordonnances)
                ->addColumn('patient', function($ord){
                    return $ord->consultation->patient->nom.' '.$ord->consultation->patient->prenom;
                })
                ->addColumn('medicaments', function($ord){
                    $html = '<ul>';
                    foreach($ord->medicaments as $med){
                        $html .= '<li>'.$med->nom.' - '.$med->pivot->posologie.' ('.$med->pivot->duree_jours.' jrs)</li>';
                    }
                    $html .= '</ul>';
                    return $html;
                })
                ->addColumn('actions', function($ord){
                    $user = Auth::user();
                    $html = '';

                    if ($user->can('ordonnances.pdf')) {
                        $html .= '<a href="'.route('ordonnances.pdf', $ord).'" class="btn btn-sm btn-outline-danger" title="PDF"><i class="fa fa-file-pdf"></i></a> ';
                    }
                    if ($user->can('ordonnances.payer')) {
                        $html .= '<a href="'.route('ordonnances.paiement', $ord).'" class="btn btn-sm btn-outline-success" title="Payer"><i class="fa fa-credit-card"></i></a> ';
                    }
                    if ($user->can('ordonnances.delete')) {
                        $html .= '<button type="button" data-url="'.route('ordonnances.destroy', $ord).'" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['medicaments','actions'])
                ->make(true);
        }

        return view('application.ordonnance.index');
    }

    public function create(Request $request, $patient = null)
    {
        abort_unless(Auth::user()->can('ordonnances.create'), 403, 'Accès non autorisé.');

        // Résoudre le patient depuis le segment de route (UUID) ou le query param
        if ($patient) {
            $patient = Patient::where('uuid', $patient)->firstOrFail();
        } elseif ($request->has('patient_id')) {
            $patient = Patient::where('uuid', $request->patient_id)->firstOrFail();
        }

        // Récupérer la grossesse active si existante
        $grossesse_id = null;
        if ($patient) {
            $activeGrossesse = $patient->grossesses->where('statut', 'En cours')->first();
            $grossesse_id = $activeGrossesse ? $activeGrossesse->id : null;
        }

        $medicaments = Medicament::orderBy('nom')->get();
        $patients = $patient ? collect() : Patient::orderBy('nom')->get();

        return view('application.ordonnance.create', compact('patient', 'medicaments', 'patients', 'grossesse_id'));
    }

    // Afficher une ordonnance
    public function show(Ordonnance $ordonnance)
    {
        abort_unless(Auth::user()->can('ordonnances.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les ordonnances.');

        $ordonnance->load(['consultation.patient', 'consultation.medecin', 'medicaments']);
        return view('application.ordonnance.index');
    }

    // Enregistrer une ordonnance
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('ordonnances.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer une ordonnance.');

        $request->validate([
            'consultation_id' => 'nullable|exists:consultations,id',
            'patient_id'      => 'required_without:consultation_id|exists:patients,id',
            'grossesse_id'    => 'nullable|exists:grossesses,id',
            'medicaments'     => 'required|array',
            'medicaments.*'   => 'required|exists:medicaments,id',
            'posologies'      => 'required|array',
            'duree_jours'     => 'nullable|array',
            'quantites'       => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $consultationId = $request->consultation_id;

            if (!$consultationId) {
                // Créer une consultation simplifiée pour l'ordonnance
                $consultation = Consultation::create([
                    'patient_id'        => $request->patient_id,
                    'medecin_id'        => Auth::id(),
                    'date_consultation' => now(),
                    'motif'             => $request->grossesse_id ? 'Prescription Maternité' : 'Prescription simple',
                    'diagnostic'        => 'Prescription d\'ordonnance',
                    'grossesse_id'      => $request->grossesse_id,
                ]);
                $consultationId = $consultation->id;
            }

            $ordonnance = Ordonnance::create([
                'consultation_id' => $consultationId,
                'date'            => now(),
            ]);

            foreach ($request->medicaments as $i => $medId) {
                if (!$medId) continue;

                OrdonnanceMedicament::create([
                    'ordonnance_id' => $ordonnance->id,
                    'medicament_id' => $medId,
                    'posologie'     => $request->posologies[$i] ?? '',
                    'duree_jours'   => $request->duree_jours[$i] ?? null,
                    'quantite'      => $request->quantites[$i] ?? 1,
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ordonnance créée avec succès ✅',
                    'redirect' => route('ordonnances.index')
                ]);
            }

            return redirect()->route('ordonnances.index')->with('success', 'Ordonnance créée avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error'   => $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Erreur lors de la création de l\'ordonnance : ' . $e->getMessage());
        }
    }

    // Exporter en PDF
    public function pdf(Ordonnance $ordonnance)
    {
        abort_unless(Auth::user()->can('ordonnances.pdf'), 403, 'Accès non autorisé : vous n\'avez pas la permission de générer le PDF.');

        $ordonnance->load(['consultation.patient', 'consultation.medecin', 'medicaments']);
        $patient = (object)[
            'id'             => $ordonnance->consultation->patient->id,
            'nom_patient'    => $ordonnance->consultation->patient->nom,
            'prenom_patient' => $ordonnance->consultation->patient->prenom,
            'age_patient'    => $ordonnance->consultation->patient->age,
            'genre'          => $ordonnance->consultation->patient->genre,
            'nom_medecin'    => $ordonnance->consultation->medecin->name ?? '',
            'prenom_medecin' => $ordonnance->consultation->medecin->prenom ?? '',
        ];
        $medicaments = $ordonnance->medicaments;

        $totale = $medicaments->sum(function($m){
            return ($m->pivot->quantite ?? 1) * ($m->prix_vente ?? 0);
        });

        $pdf = Pdf::loadView('application.ordonnance.pdf', compact('patient','medicaments','totale','ordonnance'));
        return $pdf->download('ordonnance_'.$ordonnance->uuid.'.pdf');
    }
    public function paiementForm(Ordonnance $ordonnance)
    {
        abort_unless(Auth::user()->can('ordonnances.payer'), 403, 'Accès non autorisé : vous n\'avez pas la permission de payer une ordonnance.');

        // Médicaments disponibles en stock
        $medicaments = $ordonnance->medicaments()
            ->where('stock', '>', 0)
            ->get();

        return view('application.ordonnance.paiement', compact('ordonnance', 'medicaments'));
    }

    public function payer(Request $request, Ordonnance $ordonnance)
    {
        abort_unless(Auth::user()->can('ordonnances.payer'), 403, 'Accès non autorisé : vous n\'avez pas la permission de payer une ordonnance.');

        $request->validate([
            'medicaments' => 'required|array',
        ]);

        foreach ($request->medicaments as $medId => $qteDemandee) {
            $validator = \Illuminate\Support\Facades\Validator::make(
                ['medicament_id' => $medId, 'quantite' => $qteDemandee],
                [
                    'medicament_id' => 'required|integer|exists:medicaments,id',
                    'quantite' => 'required|integer|min:1',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de paiement invalides : ' . implode(' ', $validator->errors()->all())
                ], 422);
            }
        }

        $montantTotal = 0;
        
        // Vérifier si la caisse est ouverte
        if (!\App\Models\CaisseSession::hasOpenSession()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre caisse est fermée. Veuillez l\'ouvrir pour encaisser le paiement de l\'ordonnance.'
            ], 403);
        }
        
        DB::transaction(function() use ($request, $ordonnance, &$montantTotal) {
            foreach ($request->medicaments as $medId => $qteDemandee) {
                $med = Medicament::findOrFail($medId);
                $qteFinale = min($qteDemandee, $med->stock);

                if ($qteFinale > 0) {
                    // Mettre à jour la ligne pivot de manière robuste
                    $ordonnance->medicaments()->updateExistingPivot($med->id, [
                        'qte_vendu' => $qteFinale,
                        'statut_vente' => 'disponible',
                    ]);

                    // Décrémenter le stock
                    $med->decrement('stock', $qteFinale);

                    // Créer le paiement
                    OrdonnancePaiement::create([
                        'ordonnance_id' => $ordonnance->id,
                        'medicament_id' => $med->id,
                        'quantite' => $qteFinale,
                        'prix_total' => $med->prix_vente * $qteFinale,
                        'statut' => 'payé',
                    ]);

                    // Ajouter au montant total
                    $montantTotal += $med->prix_vente * $qteFinale;
                }
            }

            // Mise à jour de l’ordonnance (retrait de la colonne fictive 'montant')
            $ordonnance->update([
                'date_paiement' => now(),
                'statutordo' => 'paye',
            ]);

            // Enregistrement dans la caisse
            if ($montantTotal > 0) {
                \App\Models\CaisseSession::enregistrerMouvement(
                    $montantTotal,
                    'Paiement Ordonnance #' . $ordonnance->id,
                    'entree',
                    $ordonnance
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Paiement effectué avec succès.',
            'montant' => $montantTotal
        ]);

    }
    public function lespayer(Request $request)
    {
        abort_unless(Auth::user()->can('ordonnances.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les ordonnances.');

        if ($request->ajax()) {
            $user = Auth::user();
            $ordonnances = DB::table('ordonnances')
                ->join('consultations', 'ordonnances.consultation_id', '=', 'consultations.id')
                ->join('patients', 'consultations.patient_id', '=', 'patients.id')
                ->whereIn('ordonnances.statutordo', ['paye', 'partiellement'])
                ->when(!$user->hasRole(['super_admin', 'superadmin', 'admin']), function ($query) use ($user) {
                    $query->where('consultations.medecin_id', $user->id);
                })
                ->select(
                    'ordonnances.id',
                    'ordonnances.uuid',
                    'ordonnances.created_at',
                    'ordonnances.date',
                    'ordonnances.statutordo',
                    'patients.nom',
                    'patients.prenom',
                    DB::raw('(SELECT SUM(prix_total) FROM ordonnance_paiements WHERE ordonnance_paiements.ordonnance_id = ordonnances.id) as montant_paye')
                )
                ->orderByDesc('ordonnances.created_at');

            return datatables()->of($ordonnances)
                ->addColumn('patient', fn($ord) => $ord->nom . ' ' . $ord->prenom)
                ->addColumn('date', fn($ord) => $ord->date ? \Carbon\Carbon::parse($ord->date)->format('d/m/Y') : '-')
                ->addColumn('montant_paye', fn($ord) => number_format($ord->montant_paye ?? 0, 0, ',', ' ') . ' FCFA')
                ->addColumn('actions', function($ord) {
                    $user = Auth::user();
                    $html = '';

                    if ($user->can('ordonnances.pdf')) {
                        $html .= '<a href="'.route('ordonnances.pdf', $ord->uuid).'" class="btn btn-sm btn-outline-danger" title="Imprimer PDF"><i class="fa fa-file-pdf"></i> PDF</a>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.ordonnance.listepayes');
    }

}
