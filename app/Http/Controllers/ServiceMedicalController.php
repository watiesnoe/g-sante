<?php
namespace App\Http\Controllers;

use App\Models\ServiceMedical;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

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
        abort_unless(Auth::user()->can('parametres.services'), 403, 'Accès non autorisé.');

        if ($request->ajax()) {
            $services = ServiceMedical::orderBy('created_at', 'desc');

            return DataTables::of($services)
                ->addIndexColumn() // Numéro de ligne
                ->addColumn('actions', function ($row) {
                    $view   = '<button type="button" class="btn btn-sm btn-outline-primary view" data-id="'.$row->uuid.'" title="Détails"><i class="fa fa-eye"></i></button>';
                    $edit   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$row->uuid.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->uuid.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $view . $edit . $delete . '</div>';
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
        abort_unless(Auth::user()->can('parametres.services'), 403, 'Accès non autorisé.');

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
        abort_unless(Auth::user()->can('parametres.services'), 403, 'Accès non autorisé.');

        $service = ServiceMedical::where('uuid', $id)->firstOrFail();
        return response()->json($service);
    }

    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->can('parametres.services'), 403, 'Accès non autorisé.');

        $service = ServiceMedical::where('uuid', $id)->firstOrFail();
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $service->update($request->all());
        return response()->json(['success' => true, 'message' => 'Service modifié avec succès !']);
    }

    public function destroy($id)
    {
        abort_unless(Auth::user()->can('parametres.services'), 403, 'Accès non autorisé.');

        $service = ServiceMedical::where('uuid', $id)->firstOrFail();
        $service->delete();
        return response()->json(['success' => true, 'message' => 'Service supprimé avec succès !']);
    }
    public function create()
    {
        abort_unless(Auth::user()->can('parametres.services'), 403, 'Accès non autorisé.');

        return view('application.service.create');
    }
    public function edit()
    {
        abort_unless(Auth::user()->can('parametres.services'), 403, 'Accès non autorisé.');

        return view('application.service.create');
    }

}
