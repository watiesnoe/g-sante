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
//                    return Carbon::parse($row->created_at)->format('d-m-Y H:i');
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
                    return '
                        <button class="btn btn-sm btn-primary view" data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye"></i></button>
                        <button class="btn btn-sm btn-info edit" data-id="'.$row->id.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>
                    ';
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y H:i');
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

    public function show($id)
    {
        $service = ServiceMedical::findOrFail($id);
        return response()->json($service);
    }

    public function update(Request $request, $id)
    {
        $service = ServiceMedical::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $service->update($request->all());
        return response()->json(['success' => true, 'message' => 'Service modifié avec succès !']);
    }

    public function destroy($id)
    {
        $service = ServiceMedical::findOrFail($id);
        $service->delete();
        return response()->json(['success' => true, 'message' => 'Service supprimé avec succès !']);
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
