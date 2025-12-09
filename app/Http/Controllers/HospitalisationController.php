<?php

namespace App\Http\Controllers;

use App\Models\Hospitalisation;
use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class HospitalisationController extends Controller

{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $hospitalisations = Hospitalisation::with([
                'consultation.patient',
                'salle',
                'lit',
                'service'
            ])
                ->where('etat', 'en cours') // 🔹 Filtre ajouté ici
                ->latest();

            return DataTables::of($hospitalisations)
                ->addColumn('patient', function ($row) {
                    return ($row->consultation->patient->nom ?? $row->patient->nom ?? '-') . ' ' .
                        ($row->consultation->patient->prenom ?? $row->patient->prenom ?? '-');
                })
                ->addColumn('salle_lit', function ($row) {
                    return ($row->salle->nom ?? '-') . '/' . ($row->lit->numero ?? '-');
                })
                ->addColumn('date_entree', function ($row) {
                    return \Carbon\Carbon::parse($row->date_entree)->format('d/m/Y');
                })
                ->addColumn('etat', function ($row) {
                    $class = match ($row->etat) {
                        'en_cours' => 'bg-warning',
                        'terminee' => 'bg-success',
                        default => 'bg-secondary',
                    };
                    return '<span class="badge ' . $class . '">' . ucfirst($row->etat) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="' . route('hospitalisations.show', $row->id) . '">
                                <i class="bx bx-show"></i> Voir
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="' . route('hospitalisations.pdf', $row->id) . '" target="_blank">
                                <i class="bx bx-printer"></i> Imprimer
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="' . route('hospitalisations.edit', $row->id) . '">
                                <i class="bx bx-edit"></i> Modifier
                            </a>
                        </li>
                        <li>
                            <button type="button"
                                    class="dropdown-item btn-paiement"
                                    data-id="' . $row->id . '"
                                    data-date="' . $row->date_entree . '"
                                    data-montant="' . ($row->prix_jour ?? 0) . '">
                                <i class="bx bx-credit-card"></i> Paiement
                            </button>
                        </li>
                        <li>
                            <form action="' . route('hospitalisations.destroy', $row->id) . '" method="POST" onsubmit="return confirm(\'Supprimer cette hospitalisation ?\')">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bx bx-trash"></i> Supprimer
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>';
                })
                ->rawColumns(['etat', 'action'])
                ->make(true);
        }


        return view('application.hospitalisation.index');
    }

    public function hopialisationrealise(Request $request)
    {


        if ($request->ajax()) {
            $hospitalisations = Hospitalisation::with([
                'consultation.patient',
                'salle',
                'lit',
                'service'
            ])->where('etat', 'terminé') // 🔹 Filtre ajouté ici
                ->get();

            return DataTables::of($hospitalisations)
                ->addColumn('patient', function ($row) {
                    return ($row->consultation->patient->nom ?? $row->patient->nom ?? '-') . ' ' .
                        ($row->consultation->patient->prenom ?? $row->patient->prenom ?? '-');
                })
                ->addColumn('salle_lit', function ($row) {
                    return ($row->salle->nom ?? '-') . '/' . ($row->lit->numero ?? '-');
                })
                ->addColumn('date_entree', function ($row) {
                    return \Carbon\Carbon::parse($row->date_entree)->format('d/m/Y');
                })
                ->addColumn('etat', function ($row) {
                    $class = match ($row->etat) {
                        'en_cours' => 'bg-warning',
                        'terminee' => 'bg-success',
                        default => 'bg-secondary',
                    };
                    return '<span class="badge ' . $class . '">' . ucfirst($row->etat) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="' . route('hospitalisations.show', $row->id) . '">
                                <i class="bx bx-show"></i> Voir
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="' . route('hospitalisations.pdf', $row->id) . '" target="_blank">
                                <i class="bx bx-printer"></i> Imprimer
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="' . route('hospitalisations.edit', $row->id) . '">
                                <i class="bx bx-edit"></i> Modifier
                            </a>
                        </li>

                        <li>
                            <form action="' . route('hospitalisations.destroy', $row->id) . '" method="POST" onsubmit="return confirm(\'Supprimer cette hospitalisation ?\')">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bx bx-trash"></i> Supprimer
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>';
                })
                ->rawColumns(['etat', 'action'])
                ->make(true);
        }


        return view('application.hospitalisation.realise');
    }

    // Paiement AJAX
//    public function paiement(Request $request)
//    {
//        $request->validate([
//            'hospitalisation_id' => 'required|exists:hospitalisations,id',
//            'dateSortie'         => 'required|date',
//            'montantTotal'       => 'required|numeric|min:0',
//            'montantRecu'        => 'required|numeric|min:0',
//        ]);
//
//        $hospitalisation = Hospitalisation::findOrFail($request->hospitalisation_id);
//
//        // Mettre à jour la sortie
//        $hospitalisation->date_sortie = $request->dateSortie;
//        $hospitalisation->etat = 'terminée';
//        $hospitalisation->save();
//
//        // Créer un paiement
//        Paiement::create([
//            'hospitalisation_id' => $hospitalisation->id,
//            'montant_total'      => $request->montantTotal,
//            'montant_recu'       => $request->montantRecu,
//            'montant_restant'    => $request->montantTotal - $request->montantRecu,
//            'date_paiement'      => now(),
//        ]);
//
//        return response()->json([
//            'success' => true,
//            'message' => 'Paiement enregistré avec succès ✅'
//        ]);
//    }

    public function pdf($id)
    {
        $hospitalisation = Hospitalisation::with(['patient','salle','lit'])->findOrFail($id);

        $pdf = Pdf::loadView('application.hospitalisation.pdf', compact('hospitalisation'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream("hospitalisation-{$hospitalisation->id}.pdf");
    }
    /**
     * Show the form for creating a new resource.
     */
    public function getPaiementData($id)
    {
        $hospitalisation = Hospitalisation::with('salle')->findOrFail($id);

        return response()->json([
            'date_entree' => $hospitalisation->date_entree,
            'prix_jour'   => $hospitalisation->salle->prix ?? 0,
        ]);
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // 🔹 Validation des données
            $validated = $request->validate([
                'hospitalisation_id' => 'required|exists:hospitalisations,id',
                'dateSortie'         => 'required|date',
                'montant_total'      => 'required|numeric|min:0',
                'montant_recu'       => 'required|numeric|min:0',
            ]);

            // 🔹 Récupération de l'hospitalisation
            $hospitalisation = Hospitalisation::findOrFail($validated['hospitalisation_id']);

            // 🔹 Vérifications métier
            if ($validated['dateSortie'] <= $hospitalisation->date_entree) {
                return response()->json([
                    'success' => false,
                    'message' => 'La date de sortie doit être après la date d’entrée'
                ], 422);
            }

            if ($validated['montant_recu'] > $validated['montant_total']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant reçu ne peut pas être supérieur au montant total'
                ], 422);
            }

            // 🔹 Calcul du montant restant
            $montantRestant = $validated['montant_total'] - $validated['montant_recu'];

            // 🔹 Enregistrement du paiement
            $paiement = Paiement::create([
                'hospitalisation_id' => $validated['hospitalisation_id'],
                'date_sortie'        => $validated['dateSortie'],
                'montant_total'      => $validated['montant_total'],
                'montant_recu'       => $validated['montant_recu'],
                'montant_restant'    => $montantRestant,
                'statut'             => $montantRestant > 0 ? 'partiel' : 'payé',
                'user_id'            => auth()->id(),
            ]);

            // 🔹 Mise à jour de l'état de l'hospitalisation
            $hospitalisation->update([
                'statut'      => 'terminé',
                'date_sortie' => $validated['dateSortie'],
            ]);

            // 🔹 Retour JSON pour AJAX
            return response()->json([
                'success' => true,
                'message' => 'Paiement enregistré avec succès.',
                'data'    => $paiement,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $hospitalisation = Hospitalisation::with([
            'consultation.patient',
            'service',
            'salle',
            'lit',
            'paiements'
        ])->findOrFail($id);

        $montant_total = $hospitalisation->paiements->sum('montant_total');
        $montant_recu = $hospitalisation->paiements->sum('montant_recu');
        $montant_restant = $montant_total - $montant_recu;

        return view('application.hospitalisation.show', compact(
            'hospitalisation',
            'montant_total',
            'montant_recu',
            'montant_restant'
        ));
    }

    /**
     * Générer la facture PDF
     */
    public function generatePDF($id)
    {
        $hospitalisation = Hospitalisation::with([
            'consultation.patient',
            'service',
            'salle',
            'lit',
            'paiements'
        ])->findOrFail($id);

        $montant_total = $hospitalisation->paiements->sum('montant_total');
        $montant_recu = $hospitalisation->paiements->sum('montant_recu');
        $montant_restant = $montant_total - $montant_recu;
        $pdf = PDF::loadView('application.hospitalisation.pdf', compact(
            'hospitalisation',
            'montant_total',
            'montant_recu',
            'montant_restant'
        ));

        $fileName = 'Facture_Hospitalisation_' . $hospitalisation->id . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hospitalisation $hospitalisation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hospitalisation $hospitalisation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hospitalisation $hospitalisation)
    {
        //
    }
}
