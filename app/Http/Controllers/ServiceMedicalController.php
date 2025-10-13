<?php
namespace App\Http\Controllers;

use App\Models\ServiceMedical;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ServiceMedicalController extends Controller
{
//    public function index(Request $request)
//    {
//        if ($request->ajax()) {
//            $services = ServiceMedical::orderBy('created_at', 'desc');
//
//            return DataTables::of($services)
//                ->addIndexColumn() // Numéro de ligne
//                ->addColumn('actions', function ($row) {
//                    $editUrl = route('service_medicals.edit', $row->id);
//                    $deleteUrl = route('service_medicals.destroy', $row->id);
//
//                    return '
//                    <div class="dropdown">
//                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
//                            ⚙️ Actions
//                        </button>
//                        <ul class="dropdown-menu">
//                            <li><a class="dropdown-item" href="'.$editUrl.'">✏️ Éditer</a></li>
//                            <li><button class="dropdown-item text-danger delete-btn" data-url="'.$deleteUrl.'">🗑 Supprimer</button></li>
//                        </ul>
//                    </div>';
//                })
//                ->editColumn('created_at', function ($row) {
//                    return Carbon::parse($row->created_at)->format('d/m/Y H:i');
//                })
//                ->rawColumns(['actions'])
//                ->make(true);
//        }
//
//        // Si ce n'est pas une requête AJAX, afficher la vue
//        return view('application.parametre.index');
//    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $services = ServiceMedical::orderBy('created_at', 'desc');

            return DataTables::of($services)
                ->addIndexColumn() // Numéro de ligne
                ->addColumn('actions', function ($row) {
                    $editUrl = route('services.edit', $row->id);
                    $deleteUrl = route('services.destroy', $row->id);

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
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d/m/Y H:i');
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        // Si ce n'est pas une requête AJAX, afficher la vue
        return view('application.service.index');
    }
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $service = ServiceMedical::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Service médical enregistré avec succès !',
            'service' => $service
        ]);
    }

    public function show(ServiceMedical $serviceMedical)
    {
        return response()->json($serviceMedical->load('prestations'));
    }

    public function update(Request $request, ServiceMedical $serviceMedical)
    {
        $serviceMedical->update($request->all());
        return response()->json($serviceMedical);
    }

    public function destroy(ServiceMedical $serviceMedical)
    {
        $serviceMedical->delete();
        return response()->json(null, 204);
    }
    public function create()
    {
        return view('application.service.create');
    }
    public function edit()
    {
        return view('application.service.create');
    }

}
