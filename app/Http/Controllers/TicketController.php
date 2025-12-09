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

            $tickets = Ticket::with(['patient', 'items'])
                ->where('statut', 'en_attente')
                ->where('date_validite', '>=', $today)
                ->orderBy('created_at', 'asc');

            return DataTables::of($tickets)
                ->addIndexColumn()
                ->addColumn('patient', function($ticket){
                    return $ticket->patient->nom ?? '-' . ' ' . ($ticket->patient->prenom ?? '');
                })
                ->addColumn('nombre_prestations', function($ticket){
                    return $ticket->items->count();
                })
                ->addColumn('total', function($ticket){
                    return number_format($ticket->items->sum('sous_total'), 0, ',', ' ');
                })
                ->addColumn('date', function($ticket){
                    return $ticket->created_at->format('d/m/Y H:i');
                })
                ->addColumn('actions', function($ticket){
                    $btn = '
                        <div class="dropdown">
                          <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton'.$ticket->id.'" data-bs-toggle="dropdown" aria-expanded="false">
                            ⚙️ Actions
                          </button>
                          <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton'.$ticket->id.'">
                            <li><a class="dropdown-item" href="'.route('tickets.show', $ticket->id).'">👁️ Voir</a></li>
                            <li><a class="dropdown-item" href="'.route('tickets.edit', $ticket->id).'">✏️ Modifier</a></li>
                            <li><a class="dropdown-item" href="'.route('tickets.print', $ticket->id).'" target="_blank">🖨️ Imprimer</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                              <form action="'.route('tickets.destroy', $ticket->id).'" method="POST" onsubmit="return confirm(\'Supprimer ce ticket ?\');" style="display:inline;">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="dropdown-item text-danger">🗑️ Supprimer</button>
                              </form>
                            </li>
                          </ul>
                        </div>
                        ';
                    return $btn;
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
                'user_id' => auth()->id()
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
