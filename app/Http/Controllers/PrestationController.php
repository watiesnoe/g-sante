<?php

namespace App\Http\Controllers;

use App\Models\Prestation;
use App\Models\ServiceMedical;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrestationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.prestations'), 403, 'Accès non autorisé.');

        if ($request->ajax()) {
            $prestations = DB::table('prestations')
                ->join('service_medicals', 'prestations.service_medical_id', '=', 'service_medicals.id')
                ->select([
                    'prestations.id',
                    'prestations.nom',
                    'prestations.description',
                    'prestations.prix',
                    'service_medicals.nom as service_medical_nom'
                ]);

            return DataTables::of($prestations)
                ->addIndexColumn()
                ->addColumn('service_medical', function ($row) {
                    return $row->service_medical_nom ?? '';
                })
                ->addColumn('actions', function ($row) {
                    $edit   = '<a href="'.route('prestations.edit', $row->id).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    $delete = '<button type="button" data-url="'.route('prestations.destroy', $row->id).'" class="btn btn-sm btn-outline-danger delete-btn" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $edit . $delete . '</div>';
                }) 
                ->rawColumns(['actions'])
                ->make(true);
        }


        return view('application.prestation.index');
    }

    public function create()
    {
        abort_unless(Auth::user()->can('parametres.prestations'), 403, 'Accès non autorisé.');

        $services = DB::table('service_medicals')->select('id', 'nom')->get();
        return view('application.prestation.create', compact('services'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.prestations'), 403, 'Accès non autorisé.');

        $request->validate([
            'service_medical_id' => 'required|exists:service_medicals,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix' => 'required|numeric|min:0',
        ]);

        Prestation::create($request->all());

        return redirect()->route('prestations.index')
            ->with('success', 'Prestation créée avec succès.');
    }

    public function edit(Prestation $prestation)
    {
        abort_unless(Auth::user()->can('parametres.prestations'), 403, 'Accès non autorisé.');

        $services = DB::table('service_medicals')->select('id', 'nom')->get();
        return view('application.prestation.create', compact('prestation', 'services'));
    }

    public function show(Prestation $prestation)
    {
        abort_unless(Auth::user()->can('parametres.prestations'), 403, 'Accès non autorisé.');

        return response()->json($prestation->load('service', 'users'));
    }

    public function update(Request $request, Prestation $prestation)
    {
        abort_unless(Auth::user()->can('parametres.prestations'), 403, 'Accès non autorisé.');

        $request->validate([
            'service_medical_id' => 'required|exists:service_medicals,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix' => 'required|numeric|min:0',
        ]);

        $prestation->update($request->all());

        return redirect()->route('prestations.index')
            ->with('success', 'Prestation modifiée avec succès.');
    }

    public function destroy(Prestation $prestation)
    {
        abort_unless(Auth::user()->can('parametres.prestations'), 403, 'Accès non autorisé.');

        $prestation->delete();
        return response()->json(null, 204);
    }
}
