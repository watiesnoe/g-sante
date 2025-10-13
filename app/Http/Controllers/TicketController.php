<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Prestation;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = Carbon::today();

        // Récupérer tous les tickets en attente et non expirés
        $tickets = Ticket::with(['patient', 'items'])
            ->where('statut', 'en_attente')        // seulement en attente
            ->where('date_validite', '>=', $today) // pas encore expiré
            ->orderBy('created_at', 'asc')         // tri par date de création
            ->get();

        return view('application.ticket.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $prestations = Prestation::with('serviceMedical')->get();

        // Récupérer tous les patients
        $patients = Patient::all();

        // Passer les données à la vue
        return view('application.ticket.create', compact('prestations', 'patients'));

        //
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
            'items.*.prix_unitaire' => 'required|integer',
            'items.*.quantite' => 'required|integer',
            'items.*.remise' => 'required|numeric',
            'items.*.sous_total' => 'required|integer',
            'items.*.service' => 'required|string',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // --- Création du ticket avec statut et validité
            $ticket = Ticket::create([
                'patient_id'   => $request->patient_id,
                'description'  => $request->description,
                'total'        => collect($request->items)->sum(fn($i) => $i['sous_total']),
                'date_validite'=> now()->addWeek(),   // validité = 7 jours
                'statut'       => 'en_attente',           // statut initial
            ]);

            // --- Ajout des items
            foreach($request->items as $item){
                $ticket->items()->create([
                    'prestation_id' => $item['prestation_id'],
                    'service'       => $item['service'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'quantite'      => $item['quantite'],
                    'remise'        => $item['remise'],
                    'sous_total'    => $item['sous_total'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => 'Ticket enregistré avec succès !',
                'ticket_id' => $ticket->id,
                'total'     => $ticket->total,
                'statut'    => $ticket->statut,
                'date_validite' => $ticket->date_validite,
                'items'     => $ticket->items()->get(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }





    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Récupérer le ticket avec patient et items
        $ticket = Ticket::with(['patient', 'items.prestation.serviceMedical'])->findOrFail($id);
        // Récupérer tous les patients et prestations pour le formulaire
        $patients =Patient::orderBy('nom')->get();
        $prestations =Prestation::with('serviceMedical')->orderBy('nom')->get();

        // Retourner la même vue que la création, mais avec les données du ticket
        return view('application.ticket.create', compact('ticket', 'patients', 'prestations'));
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
        ]);

        DB::beginTransaction();
        try {
            // Mise à jour du ticket
            $ticket->update([
                'description'   => $request->description,
                'total'         => collect($request->items)->sum(fn($i) => $i['sous_total']),
                'date_validite' => now()->addWeek(),  // on prolonge la validité à chaque update
                'statut'        => 'en_attente', // on remet en attente à chaque update
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



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}
