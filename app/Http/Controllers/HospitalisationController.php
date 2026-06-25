<?php

namespace App\Http\Controllers;

use App\Models\Hospitalisation;
use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class HospitalisationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('hospitalisations.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les hospitalisations.');

        if ($request->ajax()) {
            $year = session('exercice_year', date('Y'));
            $user = Auth::user();
            $hospitalisations = DB::table('hospitalisations')
                ->join('salles', 'hospitalisations.salles_id', '=', 'salles.id')
                ->join('lits', 'hospitalisations.lit_id', '=', 'lits.id')
                ->join('service_medicals', 'hospitalisations.service_id', '=', 'service_medicals.id')
                ->leftJoin('consultations', 'hospitalisations.consultation_id', '=', 'consultations.id')
                ->leftJoin('patients', 'consultations.patient_id', '=', 'patients.id')
                ->select([
                    'hospitalisations.id',
                    'hospitalisations.uuid',
                    'hospitalisations.date_entree',
                    'salles.prix as prix_jour',
                    'hospitalisations.etat',
                    'hospitalisations.motif',
                    'patients.nom as patient_nom',
                    'patients.prenom as patient_prenom',
                    'patients.uuid as patient_uuid',
                    'consultations.uuid as consultation_uuid',
                    'salles.nom as salle_nom',
                    'lits.numero as lit_numero',
                    'hospitalisations.created_at'
                ])
                ->whereYear('hospitalisations.created_at', $year)
                ->where('hospitalisations.etat', 'en cours')
                ->when(!$user->hasRole(['super_admin', 'superadmin', 'admin']), function ($query) use ($user) {
                    $query->where('consultations.medecin_id', $user->id);
                })
                ->orderBy('hospitalisations.created_at', 'desc');

            return DataTables::of($hospitalisations)
                ->addColumn('patient', function ($row) {
                    return ($row->patient_nom ?? '-') . ' ' . ($row->patient_prenom ?? '-');
                })
                ->addColumn('salle_lit', function ($row) {
                    return ($row->salle_nom ?? '-') . '/' . ($row->lit_numero ?? '-');
                })
                ->addColumn('date_entree', function ($row) {
                    return \Carbon\Carbon::parse($row->date_entree)->format('d-m-Y');
                })
                ->addColumn('etat', function ($row) {
                    $class = match ($row->etat) {
                        'en_cours', 'en cours' => 'bg-warning',
                        'terminee', 'terminé', 'termine' => 'bg-success',
                        default => 'bg-secondary',
                    };
                    return '<span class="badge ' . $class . '">' . ucfirst($row->etat) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $user = Auth::user();
                    $html = '';

                    if ($user->can('hospitalisations.view')) {
                        $html .= '<a href="' . route('hospitalisations.show', $row->uuid) . '" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    }
                    if ($user->can('hospitalisations.pdf')) {
                        $html .= '<a href="' . route('hospitalisations.pdf', $row->uuid) . '" target="_blank" class="btn btn-sm btn-outline-warning" title="Imprimer"><i class="fa fa-print"></i></a>';
                    }
                    if ($user->can('hospitalisations.edit')) {
                        $html .= '<a href="' . route('hospitalisations.edit', $row->uuid) . '" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    }
                    if ($user->can('hospitalisations.paiement')) {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-success btn-paiement" data-id="' . $row->uuid . '" data-date="' . $row->date_entree . '" data-montant="' . ($row->prix_jour ?? 0) . '" title="Paiement"><i class="fa fa-credit-card"></i></button>';
                    }
                    if ($user->can('transferts.create')) {
                        $patientUuid = $row->patient_uuid ?? '';
                        $consultationUuid = $row->consultation_uuid ?? '';
                        $hospitalisationUuid = $row->uuid ?? '';
                        $html .= '<button type="button" class="btn btn-sm btn-outline-success" onclick="openTransfertModal(\'' . $patientUuid . '\', \'' . $consultationUuid . '\', \'' . $hospitalisationUuid . '\')" title="Transférer"><i class="fa fa-exchange-alt"></i></button>';
                    }
                    if ($user->can('hospitalisations.delete')) {
                        $html .= '<form action="' . route('hospitalisations.destroy', $row->uuid) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Supprimer cette hospitalisation ?\')">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fa fa-trash"></i></button></form>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['etat', 'action'])
                ->make(true);
        }

        return view('application.hospitalisation.index');
    }

    public function hopialisationrealise(Request $request)
    {
        abort_unless(Auth::user()->can('hospitalisations.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les hospitalisations.');

        if ($request->ajax()) {
            $year = session('exercice_year', date('Y'));
            $user = Auth::user();
            $hospitalisations = DB::table('hospitalisations')
                ->join('salles', 'hospitalisations.salles_id', '=', 'salles.id')
                ->join('lits', 'hospitalisations.lit_id', '=', 'lits.id')
                ->join('service_medicals', 'hospitalisations.service_id', '=', 'service_medicals.id')
                ->leftJoin('consultations', 'hospitalisations.consultation_id', '=', 'consultations.id')
                ->leftJoin('patients', 'consultations.patient_id', '=', 'patients.id')
                ->select([
                    'hospitalisations.id',
                    'hospitalisations.uuid',
                    'hospitalisations.date_entree',
                    'salles.prix as prix_jour',
                    'hospitalisations.etat',
                    'patients.id as patient_id',
                    "hospitalisations.motif",
                    'hospitalisations.consultation_id',
                    'patients.nom as patient_nom',
                    'patients.prenom as patient_prenom',
                    'patients.uuid as patient_uuid',
                    'consultations.uuid as consultation_uuid',
                    'salles.nom as salle_nom',
                    'lits.numero as lit_numero',
                    'hospitalisations.created_at'
                ])
                ->whereYear('hospitalisations.created_at', $year)
                ->where('hospitalisations.etat', 'terminé')
                ->when(!$user->hasRole(['super_admin', 'superadmin', 'admin']), function ($query) use ($user) {
                    $query->where('consultations.medecin_id', $user->id);
                })
                ->orderBy('hospitalisations.created_at', 'desc');

            return DataTables::of($hospitalisations)
                ->addColumn('patient', function ($row) {
                    return ($row->patient_nom ?? '-') . ' ' . ($row->patient_prenom ?? '-');
                })
                ->addColumn('salle_lit', function ($row) {
                    return ($row->salle_nom ?? '-') . '/' . ($row->lit_numero ?? '-');
                })
                ->addColumn('date_entree', function ($row) {
                    return \Carbon\Carbon::parse($row->date_entree)->format('d-m-Y');
                })
                ->addColumn('etat', function ($row) {
                    $class = match ($row->etat) {
                        'en_cours', 'en cours' => 'bg-warning',
                        'terminee', 'terminé', 'termine' => 'bg-success',
                        default => 'bg-secondary',
                    };
                    return '<span class="badge ' . $class . '">' . ucfirst($row->etat) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $user = Auth::user();
                    $html = '';

                    if ($user->can('hospitalisations.view')) {
                        $html .= '<a href="' . route('hospitalisations.show', $row->uuid) . '" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    }
                    if ($user->can('hospitalisations.pdf')) {
                        $html .= '<a href="' . route('hospitalisations.pdf', $row->uuid) . '" target="_blank" class="btn btn-sm btn-outline-warning" title="Imprimer"><i class="fa fa-print"></i></a>';
                    }
                    if ($user->can('hospitalisations.edit')) {
                        $html .= '<a href="' . route('hospitalisations.edit', $row->uuid) . '" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    }
                    if ($user->can('transferts.create')) {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-success" onclick="openTransfertModal(' . ($row->patient_id ?? "''") . ', ' . ($row->consultation_id ?? "''") . ', ' . $row->id . ')" title="Transférer"><i class="fa fa-exchange-alt"></i></button>';
                    }
                    if ($user->can('hospitalisations.delete')) {
                        $html .= '<form action="' . route('hospitalisations.destroy', $row->uuid) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Supprimer cette hospitalisation ?\')">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fa fa-trash"></i></button></form>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['etat', 'action'])
                ->make(true);
        }

        return view('application.hospitalisation.realise');
    }

    public function pdf(Hospitalisation $hospitalisation)
    {
        abort_unless(Auth::user()->can('hospitalisations.pdf'), 403, 'Accès non autorisé : vous n\'avez pas la permission d\'imprimer.');

        $hospitalisation->load(['patient', 'salle', 'lit']);

        $pdf = Pdf::loadView('application.hospitalisation.pdf', compact('hospitalisation'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream("hospitalisation-{$hospitalisation->id}.pdf");
    }

    public function getPaiementData(Hospitalisation $hospitalisation)
    {
        $hospitalisation->load('salle');

        return response()->json([
            'date_entree' => $hospitalisation->date_entree,
            'prix_jour'   => $hospitalisation->salle->prix ?? 0,
        ]);
    }

    public function create()
    {
        abort_unless(Auth::user()->can('hospitalisations.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer une hospitalisation.');

        $services = \App\Models\ServiceMedical::orderBy('nom')->get();
        $salles   = \App\Models\Salle::orderBy('nom')->get();
        $lits     = \App\Models\Lit::where('statut', 'Libre')->orderBy('numero')->get();

        // Consultations sans hospitalisation active
        $consultations = \App\Models\Consultation::with('patient')
            ->whereDoesntHave('hospitalisation', function ($q) {
                $q->where('etat', 'en cours');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('application.hospitalisation.create', compact('services', 'salles', 'lits', 'consultations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('hospitalisations.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer une hospitalisation.');

        $validated = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'service_id'      => 'required|exists:service_medicals,id',
            'salles_id'       => 'required|exists:salles,id',
            'lit_id'          => 'required|exists:lits,id',
            'date_entree'     => 'required|date',
            'motif'           => 'nullable|string|max:500',
            'observations'    => 'nullable|string',
        ]);

        // Créer l'hospitalisation
        $hospitalisation = Hospitalisation::create(array_merge($validated, [
            'etat' => 'en cours',
        ]));

        // Marquer le lit comme occupé
        \App\Models\Lit::where('id', $validated['lit_id'])->update(['statut' => 'Occupé']);

        return redirect()
            ->route('hospitalisations.show', $hospitalisation->uuid)
            ->with('success', 'Hospitalisation créée avec succès.');
    }

    /**
     * Enregistrer un paiement de sortie (appelé en AJAX depuis la liste).
     */
    public function storePaiement(Request $request)
    {
        abort_unless(Auth::user()->can('hospitalisations.paiement'), 403, 'Accès non autorisé : vous n\'avez pas la permission d\'enregistrer un paiement d\'hospitalisation.');

        try {
            // 🔹 Vérifier si la caisse est ouverte
            if (!\App\Models\CaisseSession::hasOpenSession()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre caisse est fermée. Veuillez l\'ouvrir pour encaisser le paiement.'
                ], 403);
            }

            // 🔹 Validation des données (Ajout de statut_sortie et renommage pour coller aux 'name' du formulaire)
            $validated = $request->validate([
                'hospitalisation_id' => 'required|exists:hospitalisations,id', // Modifié de uuid à id car le formulaire envoie l'ID numérique
                'date_sortie'        => 'required|date', // Doit correspondre exactement au name="date_sortie" du HTML
                'statut_sortie'      => 'required|in:Guérison,Amélioration,Décès,Transfert,Évasion,Contre avis médical', // Ajout de l'enum médical
                'montant_total'      => 'required|numeric|min:0',
                'montant_recu'       => 'required|numeric|min:0',
            ]);

            // 🔹 Récupération de l'hospitalisation par son ID numérique
            $hospitalisation = Hospitalisation::findOrFail($validated['hospitalisation_id']);

            // 🔹 Vérifications métier
            if ($validated['date_sortie'] <= $hospitalisation->date_entree) {
                return response()->json([
                    'success' => false,
                    'message' => 'La date de sortie doit être après la date d\'entrée.'
                ], 422);
            }

            if ($validated['montant_recu'] > $validated['montant_total']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant reçu ne peut pas être supérieur au montant total.'
                ], 422);
            }

            // 🔹 Calcul du montant restant
            $montantRestant = $validated['montant_total'] - $validated['montant_recu'];

            // 🔹 Enregistrement du paiement (on passe bien l'id numérique)
            $paiement = Paiement::create([
                'hospitalisation_id' => $hospitalisation->id,
                'date_sortie'        => $validated['date_sortie'],
                'montant_total'      => $validated['montant_total'],
                'montant_recu'       => $validated['montant_recu'],
                'montant_restant'    => $montantRestant,
                'statut'             => $montantRestant > 0 ? 'partiel' : 'payé',
                'user_id'            => Auth::id(),
            ]);

            // 🔹 Mise à jour de l'état médical de l'hospitalisation et libération du lit
            $hospitalisation->update([
                'etat'          => 'terminé',
                'date_sortie'   => $validated['date_sortie'],
                'statut_sortie' => $validated['statut_sortie'], // Sauvegarde de l'état médical corrigé !
            ]);

            if ($hospitalisation->lit_id) {
                \App\Models\Lit::where('id', $hospitalisation->lit_id)->update(['statut' => 'Libre']);
            }

            // 🔹 Enregistrement dans la caisse
            if ($validated['montant_recu'] > 0) {
                \App\Models\CaisseSession::enregistrerMouvement(
                    $validated['montant_recu'],
                    'Paiement Hospitalisation #' . $hospitalisation->id . ' (Patient: ' . ($hospitalisation->consultation->patient->nom_complet ?? $hospitalisation->patient->nom_complet ?? 'Inconnu') . ')',
                    'entree',
                    $paiement
                );
            }

            // 🔹 Retour JSON pour AJAX
            return response()->json([
                'success' => true,
                'message' => 'Paiement et mode de sortie enregistrés avec succès.',
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
    public function show(Hospitalisation $hospitalisation)
    {
        abort_unless(Auth::user()->can('hospitalisations.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les hospitalisations.');

        $hospitalisation->load([
            'consultation.patient',
            'service',
            'salle',
            'lit',
            'paiements'
        ]);

        $montant_total   = $hospitalisation->paiements->sum('montant_total');
        $montant_recu    = $hospitalisation->paiements->sum('montant_recu');
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
    public function generatePDF(Hospitalisation $hospitalisation)
    {
        $hospitalisation->load([
            'consultation.patient',
            'service',
            'salle',
            'lit',
            'paiements'
        ]);

        $montant_total   = $hospitalisation->paiements->sum('montant_total');
        $montant_recu    = $hospitalisation->paiements->sum('montant_recu');
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
        abort_unless(Auth::user()->can('hospitalisations.delete'), 403, 'Accès non autorisé : vous n\'avez pas la permission de supprimer une hospitalisation.');
        //
    }
}
