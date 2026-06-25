<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use App\Models\ServiceMedical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SalleController extends Controller
{
    /**
     * Affiche la liste des salles
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $salles = DB::table('salles')
                ->join('service_medicals', 'salles.service_medical_id', '=', 'service_medicals.id')
                ->leftJoin('lits', 'salles.id', '=', 'lits.salle_id')
                ->select([
                    'salles.id',
                    'salles.nom',
                    'salles.type',
                    'salles.capacite',
                    'service_medicals.nom as service_medical_nom',
                    DB::raw('COUNT(lits.id) as lits_total'),
                    DB::raw('SUM(CASE WHEN lits.statut = "Libre" THEN 1 ELSE 0 END) as lits_libres')
                ])
                ->groupBy('salles.id', 'salles.nom', 'salles.type', 'salles.capacite', 'service_medicals.nom');

            return DataTables::of($salles)
                ->addIndexColumn()
                ->addColumn('service', function ($row) {
                    return $row->service_medical_nom ?? 'N/A';
                })
                ->addColumn('disponibilite', function ($row) {
                    $total = $row->lits_total ?? 0;
                    $libres = $row->lits_libres ?? 0;
                    $color = $libres > 0 ? 'success' : 'danger';
                    return '<span class="badge bg-'.$color.'">'.$libres.' / '.$total.' Libres</span>';
                })
                ->addColumn('actions', function($row){
                    $viewBtn   = '<button type="button" class="btn btn-sm btn-outline-primary view" data-id="'.$row->id.'" title="Détails"><i class="fa fa-eye"></i></button>';
                    $editBtn   = '<button type="button" class="btn btn-sm btn-outline-info edit" data-id="'.$row->id.'" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                    $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="'.$row->id.'" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    
                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $viewBtn . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['actions', 'disponibilite'])
                ->make(true);
        }

        $services = DB::table('service_medicals')->select('id', 'nom')->get();
        return view('application.salle.index', compact('services'));
    }


    /**
     * Formulaire de création
     */
    public function create()
    {
        $services = DB::table('service_medicals')->select('id', 'nom')->get();
        return view('application.salle.create', compact('services'));
    }
    public function litsLibres($salleId)
    {
        try {
            $salle = Salle::findOrFail($salleId);

            // Récupérer uniquement les lits libres avec plus d'informations
            $litsLibres = $salle->lits()
                ->where('statut', 'Libre')
                ->get(['id', 'uuid', 'numero', 'statut', 'created_at']);

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
        $services = DB::table('service_medicals')->select('id', 'nom')->get();
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
