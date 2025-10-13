<?php

namespace App\Http\Controllers;

use App\Models\Prestation;
use App\Models\ServiceMedical;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PrestationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $prestations = Prestation::with('serviceMedical'); // pas ->get()

            return DataTables::of($prestations)
                ->addIndexColumn()
                ->addColumn('service_medical', function($row){
                    return $row->serviceMedical->nom ?? '';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('prestations.edit', $row->id);
                    $deleteUrl = route('prestations.destroy', $row->id);

                    return '
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    ⚙️ Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="'.$editUrl.'">✏️ Éditer</a></li>
                    <li><button class="dropdown-item text-danger delete-btn" data-url="'.$deleteUrl.'">🗑 Supprimer</button></li>
                </ul>
            </div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }


        return view('application.prestation.index');
    }

    public function create()
    {
        $services = ServiceMedical::all();
        return view('application.prestation.create', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_medical_id' => 'required|exists:service_medicals,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Prestation::create($request->all());

        return redirect()->route('application.prestation.create')
            ->with('success', 'Prestation créée avec succès.');
    }

    public function show(Prestation $prestation)
    {
        return response()->json($prestation->load('service', 'users'));
    }

    public function update(Request $request, Prestation $prestation)
    {
        $prestation->update($request->all());
        return response()->json($prestation);
    }

    public function destroy(Prestation $prestation)
    {
        $prestation->delete();
        return response()->json(null, 204);
    }
}
