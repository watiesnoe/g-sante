<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use App\Models\ServiceMedical;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SalleController extends Controller
{
    /**
     * Affiche la liste des salles
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $salles = Salle::with('serviceMedical');

            return DataTables::of($salles)
                ->addIndexColumn()
                ->addColumn('service', function ($row) {
                    return $row->serviceMedical->nom ?? 'N/A';
                })
                ->addColumn('actions', function($row){
                    $viewBtn = '<span class="  btn-sm view " data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye text-primary"></i></span> ';
                    $editBtn = '<span class="  btn-sm   edit" data-id="'.$row->id.'" title="Modifier"><i class="fa fa-pencil-alt text-info"></i></span> ';
                    $deleteBtn = '<span class="  btn-sm delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash text-danger"></i></span>';
                    return $viewBtn.$editBtn.$deleteBtn;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        $services = ServiceMedical::all();
        return view('application.salle.index', compact('services'));
    }


    /**
     * Formulaire de création
     */
    public function create()
    {
        $services = ServiceMedical::all();
        return view('application.salle.create', compact('services'));
    }
    public function litsLibres($salleId)
    {
        try {
            $salle = Salle::findOrFail($salleId);

            // Récupérer uniquement les lits libres avec plus d'informations
            $litsLibres = $salle->lits()
                ->where('statut', 'libre')
                ->get(['id', 'numero', 'statut', 'created_at']);

            return response()->json($litsLibres);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du chargement des lits'
            ], 500);
        }
    }
    /**
     * Enregistrer une nouvelle salle
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string',
            'service_medical_id' => 'required|exists:service_medicals,id',
            'capacite' => 'required|integer|min:1',
        ]);

        $salle = Salle::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Salle enregistrée avec succès ✅',
            'data' => $salle
        ]);
    }

    /**
     * Formulaire d’édition
     */
    public function edit(Salle $salle)
    {
        $services = ServiceMedical::all();
        return view('application.salle.create', compact('salle', 'services'));
    }

    /**
     * Mettre à jour une salle
     */
    public function update(Request $request, Salle $salle)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string',
            'service_medical_id' => 'required|exists:service_medicals,id',
            'capacite' => 'required|integer|min:1',
        ]);

        $salle->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Salle mise à jour avec succès ✨',
            'data'    => $salle
        ], 200);
    }

    /**
     * Supprimer une salle
     */
    public function destroy(Salle $salle)
    {
        $salle->delete();

        return response()->json(['success' => true, 'message' => 'Salle supprimée avec succès !']);
    }

    public function show($id) {
        $salle = Salle::with('serviceMedical')->findOrFail($id);
        return response()->json($salle);
    }
}
