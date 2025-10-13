<?php
namespace App\Http\Controllers;
use App\Models\Ordonnance;
use App\Models\Consultation;
use App\Models\Medicament;
use App\Models\OrdonnanceMedicament;
use App\Models\OrdonnancePaiement;
use App\Models\Patient;
use App\Services\StockService;
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
                // La pharmacie/comptabilité (ordonnances.payer) encaisse les ordonnances de tous les médecins
                ->when(!$user->hasRole(['super_admin', 'superadmin', 'admin']) && !$user->can('ordonnances.payer'), function ($query) use ($user) {
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
                        $html .= '<li>'.e($med->nom).' - '.e($med->pivot->posologie).' ('.e($med->pivot->duree_jours).' jrs)</li>';
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
            'posologies.*'    => 'nullable|string|max:255',
            'duree_jours'     => 'nullable|array',
            'duree_jours.*'   => 'nullable|integer|min:1',
            'quantites'       => 'required|array',
            'quantites.*'     => 'nullable|integer|min:1',
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
            \Illuminate\Support\Facades\Log::error('Erreur création ordonnance : ' . $e->getMessage(), ['exception' => $e]);
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Une erreur est survenue lors de la création de l\'ordonnance.'
                ], 500);
            }
            return back()->with('error', 'Une erreur est survenue lors de la création de l\'ordonnance.');
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
            'mode_paiement' => ['nullable', \Illuminate\Validation\Rule::in(\App\Models\Etablissement::courant()->parametre('caisse.modes'))],
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
            $stock = app(StockService::class);
            $tiersPayant = app(\App\Services\TiersPayantService::class);
            $patient = $ordonnance->consultation?->patient;

            foreach ($request->medicaments as $medId => $qteDemandee) {
                // 🔒 Verrou pessimiste pour éviter une survente en cas de paiements concurrents
                $med = Medicament::where('id', $medId)->lockForUpdate()->firstOrFail();
                // Seuls les lots non périmés sont vendables (RG-10)
                $qteFinale = min((int) $qteDemandee, $stock->disponible($med));

                if ($qteFinale > 0) {
                    // Mettre à jour la ligne pivot de manière robuste
                    $ordonnance->medicaments()->updateExistingPivot($med->id, [
                        'qte_vendu' => $qteFinale,
                        'statut_vente' => 'disponible',
                    ]);

                    // Créer le paiement, avec la part prise en charge par l'organisme du patient (EF-07.03)
                    $prixTotal = round($med->prix_vente * $qteFinale, 2);
                    // EF-05.03 : médicament gratuit pour ce patient ? sinon part de l'organisme (EF-07.03)
                    $regle = app(\App\Services\GratuiteService::class)->pourMedicament($patient, $med->id);
                    $parts = $regle
                        ? ['assurance_id' => null, 'convention_id' => null, 'part_assurance' => 0.0, 'part_gratuite' => $prixTotal, 'regle_gratuite_id' => $regle->id, 'part_patient' => 0.0]
                        : $tiersPayant->partsMedicament($patient, $prixTotal);
                    $paiement = OrdonnancePaiement::create([
                        'ordonnance_id' => $ordonnance->id,
                        'medicament_id' => $med->id,
                        'quantite' => $qteFinale,
                        'prix_total' => $prixTotal,
                        'statut' => 'payé',
                    ] + $parts);

                    // Sortie de stock FEFO, rattachée au paiement
                    $stock->sortie($med, $qteFinale, $paiement, 'Dispensation ordonnance #' . $ordonnance->id);

                    // Le patient ne paie que sa part
                    $montantTotal += $parts['part_patient'];
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
                    $ordonnance,
                    $request->input('mode_paiement', 'especes')
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Paiement effectué avec succès.',
            'montant' => $montantTotal
        ]);

    }
    /**
     * Annuler la vente d'une ordonnance (RG-01) : les paiements restent visibles,
     * marqués annulés ; les médicaments reviennent dans leurs lots et
     * l'encaissement est neutralisé en caisse.
     */
    public function annulerPaiement(Request $request, Ordonnance $ordonnance)
    {
        abort_unless(Auth::user()->can('ordonnances.payer'), 403, 'Accès non autorisé.');
        $request->validate(['motif' => 'required|string|max:255'], ['motif.required' => 'Le motif de l\'annulation est obligatoire.']);

        $paiements = OrdonnancePaiement::where('ordonnance_id', $ordonnance->id)->where('statut', 'payé')->get();
        if ($paiements->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Aucune vente à annuler pour cette ordonnance.'], 422);
        }

        try {
            DB::transaction(function () use ($ordonnance, $paiements, $request) {
                app(\App\Services\CaisseService::class)->annulerReference($ordonnance, $request->motif);

                $stock = app(StockService::class);
                foreach ($paiements as $paiement) {
                    $stock->annulerSource($paiement, $request->motif);
                    $paiement->update([
                        'statut'           => 'annule',
                        'motif_annulation' => $request->motif,
                        'annule_par'       => Auth::id(),
                        'annule_at'        => now(),
                    ]);
                    $ordonnance->medicaments()->updateExistingPivot($paiement->medicament_id, ['qte_vendu' => 0]);
                }

                $ordonnance->update(['statutordo' => 'impaye', 'date_paiement' => null]);
            });
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\App\Exceptions\StockInsuffisantException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Vente annulée : médicaments remis en stock et encaissement neutralisé.']);
    }

    public function lespayer(Request $request)
    {
        // Permission dédiée (distincte de ordonnances.view) : les médecins voient et
        // prescrivent des ordonnances, mais ne doivent pas avoir accès aux ordonnances
        // déjà payées — information réservée à la pharmacie/comptabilité.
        abort_unless(Auth::user()->can('ordonnances.view_payees'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les ordonnances payées.');

        if ($request->ajax()) {
            $ordonnances = \App\Models\Etablissement::table('ordonnances')
                ->join('consultations', 'ordonnances.consultation_id', '=', 'consultations.id')
                ->join('patients', 'consultations.patient_id', '=', 'patients.id')
                ->whereIn('ordonnances.statutordo', ['paye', 'partiellement'])
                ->select(
                    'ordonnances.id',
                    'ordonnances.uuid',
                    'ordonnances.created_at',
                    'ordonnances.date',
                    'ordonnances.statutordo',
                    'patients.nom',
                    'patients.prenom',
                    DB::raw("(SELECT SUM(prix_total) FROM ordonnance_paiements WHERE ordonnance_paiements.ordonnance_id = ordonnances.id AND ordonnance_paiements.statut = 'payé') as montant_paye")
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
                    if ($user->can('ordonnances.payer') && $user->can('caisse.annuler')) {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-dark btn-annuler-vente" data-url="'.route('ordonnances.annulerPaiement', $ord->uuid).'" title="Annuler la vente"><i class="fa fa-ban"></i></button>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.ordonnance.listepayes');
    }

}
