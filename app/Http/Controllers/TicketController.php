<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Prestation;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
{
        abort_unless(Auth::user()->can('tickets.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les tickets.');

        if ($request->ajax()) {
            $year = session('exercice_year', date('Y'));
            $today = \Carbon\Carbon::today();

            // On utilise Eloquent avec Eager Loading
            // Utilise select('tickets.*') pour éviter les conflits de colonnes avec DataTables
            $query = Ticket::with(['patient']) 
                ->whereYear('created_at', $year)
                ->where('statut', 'en_attente')
            ->whereDate('date_validite', '>=', $today) // Utilise whereDate pour plus de précision
            ->orderBy('created_at', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            
            // Patient : Nom et Prénom proprement concaténés
            ->addColumn('patient', function($ticket){
                if (!$ticket->patient) return "Inconnu";
                return strtoupper($ticket->patient->nom) . ' ' . $ticket->patient->prenom;
            })

            // On utilise l'attribut calculé dans ton modèle Ticket
            ->addColumn('nombre_prestations', function($ticket){
                return $ticket->nombre_prestations; 
            })

            // Medecin assigné
            ->addColumn('medecin', function($ticket){
                return $ticket->medecin_id && $ticket->medecin ? $ticket->medecin->name : 'Non assigné'; 
            })

            // Total formaté (utilise le champ total déjà en base ou l'accesseur)
            ->addColumn('total', function($ticket){
                return number_format($ticket->total, 0, ',', ' ') . ' XOF';
            })

            // Date de création
            ->addColumn('date', function($ticket){
                $date = $ticket->created_at->format('d/m/Y H:i');
                return $ticket->statut === 'annule'
                    ? $date . ' <span class="badge bg-danger" title="' . e($ticket->motif_annulation) . '">Annulé</span>'
                    : $date;
            })

            // Actions (Dropdown Bootstrap 5)
            ->addColumn('actions', function($ticket) {
                $user = Auth::user();
                $html = '';

                if ($user->can('tickets.view')) {
                    $html .= '<a href="'.route('tickets.show', $ticket).'" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                }
                if ($user->can('tickets.edit')) {
                    $html .= '<a href="'.route('tickets.edit', $ticket).'" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                }
                if ($user->can('tickets.print')) {
                    $html .= '<a href="'.route('tickets.print', $ticket).'" class="btn btn-sm btn-outline-warning" title="Imprimer"><i class="fa fa-print"></i></a>';
                }
                if ($user->can('tickets.delete') && $ticket->statut !== 'annule') {
                    $html .= '<button type="button" class="btn btn-sm btn-outline-danger btn-annuler-ticket" data-url="'.route('tickets.destroy', $ticket).'" title="Annuler le ticket"><i class="fa fa-ban"></i></button>';
                }
                
                return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
            })
            ->rawColumns(['date', 'actions'])
            ->make(true);
    }

    return view('application.ticket.index');
}

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        abort_unless(Auth::user()->can('tickets.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un ticket.');

        $prestations = Prestation::with('serviceMedical')->get();

        // Plus de chargement de Patient::all() -> Utilisera Select2 AJAX
        $assurances = DB::table('assurances')->select('id', 'nom', 'taux')->orderBy('nom')->get();
        $medecins   = \App\Models\Etablissement::tableUtilisateurs()
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'medecin')
            ->select('users.id', 'users.name', 'users.prenom', 'users.nom', 'users.service_medical_id')
            ->orderBy('users.name')
            ->get();
        $serviceIdsByMedecin = DB::table('service_user')
            ->whereIn('user_id', $medecins->pluck('id'))
            ->get(['user_id', 'service_medical_id'])
            ->groupBy('user_id');
        $medecins->each(function ($medecin) use ($serviceIdsByMedecin) {
            $medecin->service_ids = collect([$medecin->service_medical_id])
                ->merge($serviceIdsByMedecin->get($medecin->id, collect())->pluck('service_medical_id'))
                ->filter()
                ->unique()
                ->values();
        });
        $services = \App\Models\Etablissement::table('service_medicals')->select('id', 'nom')->orderBy('nom')->get();

        // Passer les données à la vue
        // Patient déjà choisi (encaissement après consultation, depuis l'accueil)
        $patientPreselectionne = $request->patient ? \App\Models\Patient::where('uuid', $request->patient)->first() : null;

        $passageId = $request->integer('passage') ?: null;

        return view('application.ticket.create', compact('prestations', 'assurances', 'medecins', 'services', 'patientPreselectionne', 'passageId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('tickets.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un ticket.');

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'service_medical_id' => 'required|exists:service_medicals,id',
            'medecin_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.prestation_id' => 'required|exists:prestations,id',
            'items.*.prix_unitaire' => 'required|numeric',
            'items.*.quantite' => 'required|integer|min:1',
            'items.*.remise' => 'required|numeric|min:0|max:100',
            'items.*.sous_total' => 'required|numeric',
            'items.*.service' => 'nullable|string', // Changé en nullable
            'description' => 'nullable|string',
            'assurance_id' => 'nullable|exists:assurances,id',
            'mode_paiement' => ['nullable', \Illuminate\Validation\Rule::in(\App\Models\Etablissement::courant()->parametre('caisse.modes'))],
            'passage_id' => 'nullable|exists:passages,id',
            // RG-04 : toute remise est motivée
            'motif_remise' => [\Illuminate\Validation\Rule::requiredIf(fn() => collect($request->items)->contains(fn($i) => (float) ($i['remise'] ?? 0) > 0)), 'nullable', 'string', 'max:255'],
        ], ['motif_remise.required' => 'Une remise a été accordée : indiquez son motif.']);

        DB::beginTransaction();
        try {
            // Vérifier si la caisse est ouverte
            if (!\App\Models\CaisseSession::hasOpenSession()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre caisse est fermée. Veuillez l\'ouvrir pour encaisser des paiements.'
                ], 403);
            }

            // 🔒 Montants recalculés côté serveur au tarif en vigueur (jamais ceux envoyés par le navigateur)
            // EF-07.02 : la prise en charge du patient doit être valide à la date du ticket
            $patientTicket = \App\Models\Patient::findOrFail($request->patient_id);
            if ($request->assurance_id && $patientTicket->fin_validite_assurance && \Illuminate\Support\Carbon::parse($patientTicket->fin_validite_assurance)->lt(today())) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'La prise en charge de ce patient a expiré le ' . \Illuminate\Support\Carbon::parse($patientTicket->fin_validite_assurance)->format('d/m/Y') . '.'], 422);
            }

            $calcul = app(\App\Services\FacturationService::class)->calculer($request->items, $request->assurance_id, null, $patientTicket);
            $itemsData = $calcul['lignes'];
            $totalTicket = $calcul['total'];
            $assuranceId = $request->assurance_id;
            $tauxCouverture = $calcul['taux_couverture'];
            $partAssurance = $calcul['part_assurance'];
            $partPatient = $calcul['part_patient'];

            $ticket = Ticket::create([
                    'numero'       => app(\App\Services\NumerotationService::class)->suivant('ticket'),
                    'patient_id'   => $request->patient_id,
                    'description'  => $request->description,
                    'total'        => $totalTicket,
                    'assurance_id' => $assuranceId,
                    'taux_couverture' => $tauxCouverture,
                    'part_assurance'  => $partAssurance,
                    'part_patient'    => $partPatient,
                    'motif_remise'    => $request->motif_remise,
                    'convention_id'   => $calcul['convention_id'],
                    'part_gratuite'   => $calcul['part_gratuite'],
                    'passage_id'      => $request->passage_id,
                    'numero_assure'   => $request->assurance_id ? $patientTicket->numero_assurance : null,
                    'date_validite'=> now()->addWeek(),   // validité = 7 jours
                    'statut'       => 'en_attente',           // statut initial
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'medecin_id' => $request->medecin_id
                ]);

            foreach ($itemsData as $data) {
                $ticket->items()->create($data);
            }

            // Enregistrement dans la caisse, avec le mode de paiement choisi (EF-06.02, EF-06.03)
            if ($partPatient > 0) {
                \App\Models\CaisseSession::enregistrerMouvement(
                    $partPatient,
                    'Ticket ' . $ticket->numero . ' (Patient : ' . ($ticket->patient ? $ticket->patient->nom : $request->patient_id) . ')',
                    'entree',
                    $ticket,
                    $request->input('mode_paiement', 'especes')
                );
            }

            // EF-03.01 : l'arrivée du patient ouvre (ou complète) son passage du jour
            if ($request->passage_id) {
                \App\Models\Passage::whereKey($request->passage_id)->whereNull('ticket_id')->update(['ticket_id' => $ticket->id]);
            } else app(\App\Services\PassageService::class)->rattacherTicket($ticket->patient, $ticket->id, [
                'service_medical_id' => $request->service_medical_id,
                'motif'              => $request->description,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket enregistré avec succès !',
                'ticket_id' => $ticket->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Erreur création ticket : ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement du ticket.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        abort_unless(Auth::user()->can('tickets.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les tickets.');

        // Charger les relations nécessaires
        $ticket->load([
            'patient',
            'items.prestation.serviceMedical',
            'consultation',
            'user'
        ]);

        // Calculer des statistiques supplémentaires si besoin
        $stats = [
            'nombre_prestations' => $ticket->items->count(),
            'total_prestations' => $ticket->items->sum('sous_total'),
            'moyenne_prix' => $ticket->items->avg('prix_unitaire'),
        ];

        return view('application.ticket.show', compact('ticket', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        abort_unless(Auth::user()->can('tickets.edit'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier ce ticket.');

        $ticket->load(['patient', 'items.prestation.serviceMedical']);
        $prestations = Prestation::with('serviceMedical')->orderBy('nom')->get();
        $assurances  = DB::table('assurances')->select('id', 'nom', 'taux')->orderBy('nom')->get();
        $medecins    = \App\Models\Etablissement::tableUtilisateurs()
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'medecin')
            ->select('users.id', 'users.name', 'users.prenom', 'users.nom', 'users.service_medical_id')
            ->orderBy('users.name')
            ->get();
        $services    = \App\Models\Etablissement::table('service_medicals')->select('id', 'nom')->orderBy('nom')->get();
        return view('application.ticket.create', compact('ticket', 'prestations', 'assurances', 'medecins', 'services'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        abort_unless(Auth::user()->can('tickets.edit'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier ce ticket.');

        if ($ticket->statut === 'annule') {
            return response()->json(['success' => false, 'message' => 'Ce ticket est annulé : il ne peut plus être modifié.'], 422);
        }

        // Si expiré, on bloque
        if ($ticket->date_validite < now()) {
            $ticket->update(['statut' => 'expire']);
            return response()->json([
                'success' => false,
                'message' => 'Impossible de modifier un ticket expiré.'
            ], 403);
        }

        $request->validate([
            'service_medical_id' => 'required|exists:service_medicals,id',
            'medecin_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.prestation_id' => 'required|exists:prestations,id',
            'items.*.quantite' => 'required|integer|min:1',
            'items.*.remise' => 'required|numeric|min:0|max:100',
            'items.*.service' => 'required|string',
            'description' => 'nullable|string',
            'assurance_id' => 'nullable|exists:assurances,id',
        ]);

        DB::beginTransaction();
        try {
            // 🔒 Montants recalculés côté serveur, au tarif en vigueur à la date du ticket (RG-07)
            $calcul = app(\App\Services\FacturationService::class)->calculer($request->items, $request->assurance_id, $ticket->created_at, $ticket->patient);
            $itemsData = $calcul['lignes'];
            $totalTicket = $calcul['total'];
            $assuranceId = $request->assurance_id;
            $tauxCouverture = $calcul['taux_couverture'];
            $partAssurance = $calcul['part_assurance'];
            $partPatient = $calcul['part_patient'];

            // RG-01 : un ticket encaissé ne change pas de montant ni de contenu facturé.
            // La correction passe par l'annulation du ticket puis un nouveau ticket.
            $signature = fn($lignes) => collect($lignes)->map(fn($l) => $l['prestation_id'] . 'x' . (int) $l['quantite'] . '-' . (float) $l['remise'])->sort()->values()->all();
            $contenuChange = $signature($itemsData) !== $signature($ticket->items->map->only(['prestation_id', 'quantite', 'remise']))
                || (int) $assuranceId !== (int) $ticket->assurance_id
                || round($partPatient, 2) != round((float) $ticket->part_patient, 2);
            if ($contenuChange && $ticket->aDesEncaissements()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Ce ticket est déjà encaissé : pour changer les prestations, les remises ou l\'assurance, annulez-le puis créez un nouveau ticket.',
                ], 422);
            }

            // Mise à jour du ticket
            $ticket->update([
                'description'   => $request->description,
                'total'         => $totalTicket,
                'assurance_id'  => $assuranceId,
                'taux_couverture' => $tauxCouverture,
                'part_assurance'  => $partAssurance,
                'part_gratuite'   => $calcul['part_gratuite'],
                'part_patient'    => $partPatient,
                'date_validite' => now()->addWeek(),  // on prolonge la validité à chaque update
                'statut'        => 'en_attente', // on remet en attente à chaque update
                'medecin_id'    => $request->medecin_id
            ]);

            // On supprime les anciens items et on recrée
            $ticket->items()->delete();
            foreach ($itemsData as $data) {
                $ticket->items()->create($data);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Ticket mis à jour avec succès !',
                'ticket'  => $ticket->load('items')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Erreur mise à jour ticket : ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour du ticket.'
            ], 500);
        }
    }
    public function print(Ticket $ticket)
    {
        abort_unless(Auth::user()->can('tickets.print'), 403, 'Accès non autorisé : vous n\'avez pas la permission d\'imprimer un ticket.');

        $ticket->load([
            'patient',
            'user',
            'medecin',
            'assurance',
            'items.prestation.serviceMedical',
        ]);
        $pdf = Pdf::loadView('application.ticket.pdf', compact('ticket'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('ticket_'.$ticket->uuid.'.pdf');
    }
    /**
     * Remove the specified resource from storage.
     */
    /**
     * Annuler un ticket (RG-01) : le ticket est conservé avec son motif et
     * l'encaissement est neutralisé par une écriture inverse en caisse.
     */
    public function destroy(Request $request, Ticket $ticket)
    {
        abort_unless(Auth::user()->can('tickets.delete'), 403, 'Accès non autorisé : vous n\'avez pas la permission d\'annuler un ticket.');

        $request->validate(['motif' => 'required|string|max:255'], ['motif.required' => 'Le motif de l\'annulation est obligatoire.']);

        if ($ticket->statut === 'annule') {
            return response()->json(['success' => false, 'message' => 'Ce ticket est déjà annulé.'], 422);
        }
        if (\App\Models\Consultation::where('ticket_id', $ticket->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Une consultation a déjà été faite sur ce ticket : il ne peut plus être annulé.'], 422);
        }

        try {
            DB::transaction(function () use ($ticket, $request) {
                app(\App\Services\CaisseService::class)->annulerReference($ticket, $request->motif);
                $ticket->update([
                    'statut'           => 'annule',
                    'motif_annulation' => $request->motif,
                    'annule_par'       => Auth::id(),
                    'annule_at'        => now(),
                ]);
            });
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Ticket annulé ; l\'encaissement a été neutralisé en caisse.']);
    }
}
