<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Prestation;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
{
    if ($request->ajax()) {
        $today = \Carbon\Carbon::today();

        // On utilise Eloquent avec Eager Loading
        // Utilise select('tickets.*') pour éviter les conflits de colonnes avec DataTables
        $query = Ticket::with(['patient']) 
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
                return $ticket->created_at->format('d/m/Y H:i');
            })

            // Actions (Dropdown Bootstrap 5)
            ->addColumn('actions', function($ticket) {
                return '
                   <a href="'.route('tickets.show', $ticket->id).'" class="btn-sm" title="Voir"><i class="fa fa-eye text-primary"></i></a>
                   <a href="'.route('tickets.edit', $ticket->id).'" class="btn-sm" title="Modifier"><i class="fa fa-edit text-warning"></i></a>
                   <a href="'.route('tickets.print', $ticket->id).'" class="btn-sm" title="Imprimer"><i class="fa fa-print text-info"></i></a>
                   <span type="button" class="btn-sm delete" onclick="deleteTicket('.$ticket->id.')" title="Supprimer" style="cursor:pointer;"><i class="fa fa-trash text-danger"></i></span>
                ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('application.ticket.index');
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $prestations = Prestation::with('serviceMedical')->get();

        // Plus de chargement de Patient::all() -> Utilisera Select2 AJAX
        $assurances = \App\Models\Assurance::all(); // Ajout des assurances
        $medecins = \App\Models\User::whereIn('role', ['medecin', 'docteur'])->get();

        // Passer les données à la vue
        return view('application.ticket.create', compact('prestations', 'assurances', 'medecins'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'items' => 'required|array|min:1',
            'items.*.prestation_id' => 'required|exists:prestations,id',
            'items.*.prix_unitaire' => 'required|numeric',
            'items.*.quantite' => 'required|integer|min:1',
            'items.*.remise' => 'required|numeric|min:0|max:100',
            'items.*.sous_total' => 'required|numeric',
            'items.*.service' => 'nullable|string', // Changé en nullable
            'description' => 'nullable|string',
            'assurance_id' => 'nullable|exists:assurances,id',
            'medecin_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            // Calcul du total côté serveur (plus fiable)
            $totalTicket = collect($request->items)->sum('sous_total');

            $assuranceId = $request->assurance_id;
            $tauxCouverture = 0;
            if ($assuranceId) {
                $assurance = \App\Models\Assurance::find($assuranceId);
                $tauxCouverture = $assurance ? $assurance->taux : 0;
            }

            $partAssurance = ($totalTicket * $tauxCouverture) / 100;
            $partPatient = $totalTicket - $partAssurance;

            $ticket = Ticket::create([
                    'patient_id'   => $request->patient_id,
                    'description'  => $request->description,
                    'total'        => $totalTicket,
                    'assurance_id' => $assuranceId,
                    'taux_couverture' => $tauxCouverture,
                    'part_assurance'  => $partAssurance,
                    'part_patient'    => $partPatient,
                    'date_validite'=> now()->addWeek(),   // validité = 7 jours
                    'statut'       => 'en_attente',           // statut initial
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'medecin_id' => $request->medecin_id
                ]);

            foreach ($request->items as $item) {
                $ticket->items()->create([
                    'prestation_id' => $item['prestation_id'],
                    'service'       => $item['service'] ?? 'N/A', // Valeur par défaut si vide
                    'prix_unitaire' => $item['prix_unitaire'],
                    'quantite'      => $item['quantite'],
                    'remise'        => $item['remise'],
                    'sous_total'    => $item['sous_total'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket enregistré avec succès !',
                'ticket_id' => $ticket->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                // On retourne le message d'erreur réel pour débugger
                'message' => 'Erreur SQL: ' . $e->getMessage() 
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
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
    public function edit($id)
    {
        // Récupérer le ticket avec patient et items
        $ticket = Ticket::with(['patient', 'items.prestation.serviceMedical'])->findOrFail($id);
        
        $prestations =Prestation::with('serviceMedical')->orderBy('nom')->get();
        $assurances = \App\Models\Assurance::all();
        $medecins = \App\Models\User::whereIn('role', ['medecin', 'docteur'])->get();

        // Retourner la même vue que la création, mais avec les données du ticket
        return view('application.ticket.create', compact('ticket', 'prestations', 'assurances', 'medecins'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        // Si expiré, on bloque
        if ($ticket->date_validite < now()) {
            $ticket->update(['statut' => 'expire']);
            return response()->json([
                'success' => false,
                'message' => 'Impossible de modifier un ticket expiré.'
            ], 403);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.prestation_id' => 'required|exists:prestations,id',
            'items.*.prix_unitaire' => 'required|integer',
            'items.*.quantite' => 'required|integer',
            'items.*.remise' => 'required|numeric',
            'items.*.sous_total' => 'required|integer',
            'items.*.service' => 'required|string',
            'description' => 'nullable|string',
            'assurance_id' => 'nullable|exists:assurances,id',
            'medecin_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $totalTicket = collect($request->items)->sum(fn($i) => $i['sous_total']);
            $assuranceId = $request->assurance_id;
            $tauxCouverture = 0;
            if ($assuranceId) {
                $assurance = \App\Models\Assurance::find($assuranceId);
                $tauxCouverture = $assurance ? $assurance->taux : 0;
            }

            $partAssurance = ($totalTicket * $tauxCouverture) / 100;
            $partPatient = $totalTicket - $partAssurance;

            // Mise à jour du ticket
            $ticket->update([
                'description'   => $request->description,
                'total'         => $totalTicket,
                'assurance_id'  => $assuranceId,
                'taux_couverture' => $tauxCouverture,
                'part_assurance'  => $partAssurance,
                'part_patient'    => $partPatient,
                'date_validite' => now()->addWeek(),  // on prolonge la validité à chaque update
                'statut'        => 'en_attente', // on remet en attente à chaque update
                'medecin_id'    => $request->medecin_id
            ]);

            // On supprime les anciens items et on recrée
            $ticket->items()->delete();
            foreach($request->items as $item){
                $ticket->items()->create($item);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Ticket mis à jour avec succès !',
                'ticket'  => $ticket->load('items')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function print($id)
    {
        $ticket = Ticket::with(['patient', 'consultation', 'user'])->findOrFail($id);

        // 🔹 Vue PDF personnalisée
        $pdf = Pdf::loadView('application.ticket.pdf', compact('ticket'))
            ->setPaper('a4', 'portrait');

        // 🔹 Soit on télécharge, soit on affiche dans un nouvel onglet
        return $pdf->stream('ticket_'.$ticket->id.'.pdf');
        // 👉 pour télécharger automatiquement : return $pdf->download('ticket_'.$ticket->id.'.pdf');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}
