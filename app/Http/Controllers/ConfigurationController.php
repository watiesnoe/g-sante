<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\ServiceMedical;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $services = ServiceMedical::orderBy('created_at', 'desc');

            return DataTables::of($services)
                ->addIndexColumn() // Numéro de ligne
                ->addColumn('actions', function ($row) {
                    $editUrl = route('service_medicals.edit', $row->id);
                    $deleteUrl = route('service_medicals.destroy', $row->id);

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
        return view('application.parametre.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Configuration $configuration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Configuration $configuration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Configuration $configuration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Configuration $configuration)
    {
        //
    }
}
