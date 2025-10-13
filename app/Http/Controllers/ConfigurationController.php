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
                    $edit   = '<a href="'.route('service_medicals.edit', $row->id).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-url="'.route('service_medicals.destroy', $row->id).'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $edit . $delete . '</div>';
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y H:i');
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
