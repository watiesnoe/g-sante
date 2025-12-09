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
                ->addColumn('actions', function ($row) {
                    $editUrl = route('salles.edit', $row->id);
                    $deleteUrl = route('salles.destroy', $row->id);
                    return '
                    <a href="'.$editUrl.'" class="btn btn-sm btn-warning">✏️</a>
                    <button data-url="'.$deleteUrl.'" class="btn btn-sm btn-danger delete-btn">🗑</button>
                ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('application.salle.index');
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

        return redirect()->route('salles.index')
            ->with('success', 'Salle supprimée avec succès 🗑');
    }
}
