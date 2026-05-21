<?php
namespace App\Http\Controllers;

use App\Models\PrescriptionExamen;
use App\Models\ResultatExamen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultatExamenController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('examens.results'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les résultats.');

        if ($request->ajax()) {
            $resultats = ResultatExamen::with(['prescriptionExamen.consultation.patient'])
                ->whereHas('prescriptionExamen', function($q) {
                    $q->where('statut', 'realise');
                })->latest()->get(); // ⚠️ n'oublie pas get() !

            return datatables()->of($resultats)
                ->addColumn('patient', function($row){
                    return $row->prescriptionExamen?->consultation?->patient?->nom
                         .' '. $row->prescriptionExamen?->consultation?->patient?->prenom;
                })
                ->addColumn('examen', function($row){
                    return $row->prescriptionExamen?->examen ?? '-';
                })
                ->addColumn('resultat', function($row){
                    return $row->resultat ?? '-';
                })
                ->addColumn('fichier', function($row){
                    if ($row->fichier) {
                        return '<a href="'.asset(''.$row->fichier).'" target="_blank" class="btn btn-sm btn-info">
                                    📂 Voir fichier
                                </a>';
                    }
                    return 'Aucun';
                })
                ->addColumn('actions', function($row){
                    $user = Auth::user();
                    $html = '';
                    if ($user->can('examens.delete')) {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    }
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['fichier','actions'])
                ->make(true);
        }
        return view('application.examenreponse.index'); // Vue DataTable
    }
    public function create()
    {
        abort_unless(Auth::user()->can('examens.results'), 403, 'Accès non autorisé : vous n\'avez pas la permission de saisir des résultats.');

        $prescriptions = PrescriptionExamen::with('consultation.patient')->get();
        return view('application.examenreponse.create', compact('prescriptions'));
    }
    public function reponse($id)
    {
        abort_unless(Auth::user()->can('examens.results'), 403, 'Accès non autorisé : vous n\'avez pas la permission de saisir des résultats.');

        // On charge la prescription correspondant à l'id
        $prescription = PrescriptionExamen::with('consultation.patient')
            ->where('id', $id)
            ->firstOrFail();

        return view('application.examenreponse.create', compact('prescription'));
    }


    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('examens.results'), 403, 'Accès non autorisé : vous n\'avez pas la permission de saisir des résultats.');

        $data=$request->validate([
            'prescription_examen_id' => 'required|exists:prescriptions_examens,id',
            'resultat' => 'nullable|string',
            'fichier' => 'nullable|file|mimes:pdf,jpg,jpeg,png'
        ]);
        if ($request->hasFile('fichier')) {
            $file = $request->file('fichier');

            // Dossier public/resultats
            $folder = public_path('resultats');
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }

            // Nom unique du fichier
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Déplacement du fichier dans le dossier public/resultats
            $file->move($folder, $filename);

            // Chemin relatif à stocker dans la DB
            $data['fichier'] = 'resultats/' . $filename;
        }


        $reponse=ResultatExamen::create($data);

        $prescription =PrescriptionExamen::find($data['prescription_examen_id']);
        if ($prescription) {
            $prescription->update([ 'statut' => 'realise' ]);
        }
        return response()->json(['success' => true, 'message' =>'Réponse enregistrée avec succès', 'data' => $reponse]);
    }


    public function edit(ResultatExamen $resultatExamen)
    {
        abort_unless(Auth::user()->can('examens.results'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier des résultats.');

        $prescriptions = PrescriptionExamen::with('consultation.patient')->get();
        return view('application.examenreponse.edit', compact('resultatExamen', 'prescriptions'));
    }

    public function update(Request $request, ResultatExamen $resultatExamen)
    {
        abort_unless(Auth::user()->can('examens.results'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier des résultats.');

        $request->validate([
            'prescription_examen_id' => 'required|exists:prescriptions_examens,id',
            'resultat' => 'nullable|string',
            'fichier' => 'nullable|file|mimes:pdf,jpg,jpeg,png'
        ]);

        $data = $request->only('prescription_examen_id', 'resultat');

        if ($request->hasFile('fichier')) {
            $file = $request->file('fichier');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->storeAs('public/resultats', $filename);
            $data['fichier'] = $filename;
        }

        $resultatExamen->update($data);

        return redirect()->route('reponses.index')->with('success', 'Résultat mis à jour avec succès.');
    }

    public function destroy(ResultatExamen $resultatExamen)
    {
        abort_unless(Auth::user()->can('examens.delete'), 403, 'Accès non autorisé : vous n\'avez pas la permission de supprimer des résultats.');

        $resultatExamen->delete();
        return redirect()->route('reponses.index')->with('success', 'Résultat supprimé avec succès.');
    }
}
