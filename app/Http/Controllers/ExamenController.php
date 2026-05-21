<?php

namespace App\Http\Controllers;

use App\Models\Examen;
use App\Models\ServiceMedical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ExamenController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.examens_config'), 403, 'Accès non autorisé : vous n\'avez pas la permission de configurer les examens.');

        if($request->ajax()) {
            $examens = Examen::with('serviceMedical')->select('examens.*');
            return DataTables::of($examens)
                ->addIndexColumn()
                ->addColumn('service', fn($row) => $row->serviceMedical->nom ?? '-')
                ->addColumn('actions', function($row){
                    $user = Auth::user();
                    $html = '';
                    if ($user->can('parametres.examens_config')) {
                        $html .= '<a href="'.route("examens.edit",$row->id).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                        $html .= '<button type="button" data-url="'.route("examens.destroy",$row->id).'" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    }
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('application.examen.index');
    }

    public function create()
    {
        abort_unless(Auth::user()->can('parametres.examens_config'), 403, 'Accès non autorisé.');

        $services = ServiceMedical::all();
        return view('application.examen.create', compact('services'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.examens_config'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'service_medical_id' => 'required|exists:service_medicals,id',
            'description' => 'nullable|string',
            'prix' => 'nullable|numeric'
        ]);

        $examen = Examen::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Examen ajouté avec succès ✨',
            'data' => $examen
        ]);
    }

    public function edit(Examen $examen)
    {
        abort_unless(Auth::user()->can('parametres.examens_config'), 403, 'Accès non autorisé.');

        $services = ServiceMedical::all();
        return view('application.examen.create', compact('examen','services'));
    }

    public function update(Request $request, Examen $examen)
    {
        abort_unless(Auth::user()->can('parametres.examens_config'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'service_medical_id' => 'required|exists:service_medicals,id',
            'description' => 'nullable|string',
            'prix' => 'nullable|numeric'
        ]);

        $examen->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Examen mis à jour avec succès ✨',
            'data' => $examen
        ]);
    }

    public function destroy(Examen $examen)
    {
        abort_unless(Auth::user()->can('parametres.examens_config'), 403, 'Accès non autorisé.');

        $examen->delete();
        return response()->json([
            'success' => true,
            'message' => 'Examen supprimé ✨'
        ]);
    }
}

