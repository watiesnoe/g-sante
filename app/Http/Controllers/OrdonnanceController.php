<?php
namespace App\Http\Controllers;
use App\Models\Ordonnance;
use App\Models\Consultation;
use App\Models\Medicament;
use App\Models\OrdonnanceMedicament;
use App\Models\OrdonnancePaiement;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class OrdonnanceController extends Controller
{
    // Liste des ordonnances

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $ordonnances = Ordonnance::with(['consultation.patient','medicaments'])
                ->select('ordonnances.*')
                ->whereNotIn('statutordo', ['paye', 'partiellement']) // 🔥 exclure ces statuts
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
                    $pdfBtn = '<a href="'.route('ordonnances.pdf',$ord->id).'" class="btn btn-sm btn-danger">📄 PDF</a>';

                    $paiementBtn = '<a href="'.route('ordonnances.paiement', $ord->id).'"
                            class="btn btn-sm btn-success ml-1">💳 Paiement</a>';

                    $deleteBtn = '<button data-url="'.route('ordonnances.destroy',$ord->id).'"
                            class="btn btn-sm btn-warning btn-delete ml-1">🗑️ Supprimer</button>';

                    return $pdfBtn.' '.$paiementBtn.' '.$deleteBtn;
                })
                ->rawColumns(['medicaments','actions'])
                ->make(true);
        }

        return view('application.ordonnance.index');
    }

    // Formulaire de création
    public function create()
    {
        $consultations = Consultation::with('patient')->get();
        $medicaments   = Medicament::all();
        return view('ordonnances.create', compact('consultations', 'medicaments'));
    }

    // Enregistrer une ordonnance
    public function store(Request $request)
    {
        $request->validate([
        'consultation_id' => 'required|exists:consultations,id',
        'medicaments'     => 'required|array',
        'medicaments.*.id' => 'required|exists:medicaments,id',
        'medicaments.*.posologie' => 'required|string',
        'medicaments.*.duree_jours' => 'nullable|integer',
        'medicaments.*.quantite' => 'required|integer|min:1', // ✅ nouveau
        ]);


        $ordonnance = Ordonnance::create([
            'consultation_id' => $request->consultation_id,
            'date' => now(),
        ]);

        foreach ($request->medicaments as $med) {
            $ordonnance->medicaments()->attach($med['id'], [
            'posologie' => $med['posologie'],
            'duree_jours' => $med['duree_jours'] ?? null,
            'quantite' => $med['quantite'], // ✅
            ]);
        }

        return redirect()->route('ordonnances.index')->with('success', 'Ordonnance créée avec succès');
    }

    // Exporter en PDF
    public function pdf($id)
    {
        $ordonnance = Ordonnance::with(['consultation.patient','consultation.medecin','medicaments'])->findOrFail($id);
        $patient = (object)[
            'nom_patient'=>$ordonnance->consultation->patient->nom,
            'prenom_patient'=>$ordonnance->consultation->patient->prenom,
            'age_patient'=>$ordonnance->consultation->patient->age,
            'genre'=>$ordonnance->consultation->patient->genre,
            'nom_medecin'=>$ordonnance->consultation->medecin->name ?? '',
            'prenom_medecin'=>$ordonnance->consultation->medecin->prenom ?? '',
        ];
        $medicaments = $ordonnance->medicaments;

        $totale = $medicaments->sum(function($m){
            return ($m->pivot->quantite ?? 1) * ($m->prix_vente ?? 0);
        });

        $pdf = Pdf::loadView('application.ordonnance.pdf', compact('patient','medicaments','totale'));
        return $pdf->download('ordonnance_'.$ordonnance->id.'.pdf');
    }
    public function paiementForm(Ordonnance $ordonnance)
    {
        // Médicaments disponibles en stock
        $medicaments = $ordonnance->medicaments()
            ->where('stock', '>', 0)
            ->get();

        return view('application.ordonnance.paiement', compact('ordonnance', 'medicaments'));
    }

    public function payer(Request $request, Ordonnance $ordonnance)
    {
        $request->validate([
            'medicaments' => 'required|array',
            'medicaments.*' => 'required|integer|min:1|exists:medicaments,id',
        ]);

        $montantTotal = 0;

        DB::transaction(function() use ($request, $ordonnance, &$montantTotal) {
            foreach ($request->medicaments as $medId => $qteDemandee) {
                $med = Medicament::findOrFail($medId);
                $qteFinale = min($qteDemandee, $med->stock);

                if ($qteFinale > 0) {
                    // Mettre à jour la ligne pivot
                    $ligne = $ordonnance->medicaments()->where('medicament_id', $med->id)->first();
                    if($ligne) {
                        $ligne->pivot->update([
                            'qte_vendu' => $qteFinale,
                            'statut_vente' => 'disponible',
                        ]);
                    }

                    // Décrémenter le stock
                    $med->decrement('stock', $qteFinale);

                    // Créer le paiement
//                    OrdonnancePaiement::create([
//                        'ordonnance_id' => $ordonnance->id,
//                        'medicament_id' => $med->id,
//                        'quantite' => $qteFinale,
//                        'prix_total' => $med->prix_vente * $qteFinale,
//                        'statutordo' => 'paye',
//                    ]);

                    // Ajouter au montant total
                    $montantTotal += $med->prix_vente * $qteFinale;
                }
            }

            // Mise à jour de l’ordonnance
            $ordonnance->update([
                'date_paiement' => now(),
                'montant' => $montantTotal,
                'statutordo' => 'paye',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Paiement effectué avec succès.',
            'montant' => $montantTotal
        ]);

    }
    public function lespayer(Request $request)
    {
        if ($request->ajax()) {
            $ordonnances = Ordonnance::with(['consultation.patient'])
                ->whereIn('statutordo', ['paye', 'partiellement'])
                ->orderBy('ordonnances.created_at','desc')
                ->get(); // 👈 ici

            return datatables()->of($ordonnances)
                ->addColumn('patient', function($ord){
                    return $ord->consultation->patient->nom.' '.$ord->consultation->patient->prenom;
                })
                ->addColumn('actions', function($ord){
                    $pdfBtn = '<a href="'.route('ordonnances.pdf',$ord->id).'" class="btn btn-sm btn-danger">📄 PDF</a>';
                    return $pdfBtn;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.ordonnance.listepayes');
    }

}
