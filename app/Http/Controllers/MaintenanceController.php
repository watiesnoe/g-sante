<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Models\Maintenance;
use App\Models\ServiceMedical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** EF-12.06 : inventaire des équipements, pannes, maintenance préventive planifiée. */
class MaintenanceController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('maintenance.view'), 403, 'Accès non autorisé.');

        return view('application.maintenance.index', [
            'equipements' => Equipement::with(['service', 'maintenances' => fn($q) => $q->where('statut', 'ouverte')])->orderBy('nom')->get(),
            'services'    => ServiceMedical::orderBy('nom')->get(['id', 'nom']),
            'aPrevoir'    => Equipement::whereNotNull('prochaine_maintenance')->whereDate('prochaine_maintenance', '<=', today()->addDays(15))->count(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('maintenance.gerer'), 403, 'Accès non autorisé.');
        $validated = $request->validate([
            'nom' => 'required|string|max:255', 'categorie' => 'nullable|string|max:100', 'numero_serie' => 'nullable|string|max:100',
            'service_medical_id' => 'nullable|exists:service_medicals,id', 'date_acquisition' => 'nullable|date',
            'periodicite_jours' => 'nullable|integer|min:1|max:3650',
        ]);
        $validated['prochaine_maintenance'] = !empty($validated['periodicite_jours']) ? today()->addDays((int) $validated['periodicite_jours']) : null;
        Equipement::create($validated + ['statut' => 'en_service']);

        return back()->with('success', 'Équipement ajouté.');
    }

    /** Signaler une panne (tout utilisateur autorisé) ou ouvrir une maintenance préventive. */
    public function signaler(Request $request, Equipement $equipement)
    {
        abort_unless(Auth::user()->can('maintenance.view'), 403, 'Accès non autorisé.');
        $request->validate(['type' => 'required|in:panne,preventive', 'description' => 'required|string|max:2000']);

        DB::transaction(function () use ($request, $equipement) {
            $equipement->maintenances()->create(['type' => $request->type, 'description' => $request->description, 'statut' => 'ouverte', 'signale_par' => Auth::id()]);
            if ($request->type === 'panne') {
                $equipement->update(['statut' => 'en_panne']);
            }
        });

        return back()->with('success', $request->type === 'panne' ? 'Panne signalée.' : 'Maintenance préventive ouverte.');
    }

    public function resoudre(Request $request, Maintenance $maintenance)
    {
        abort_unless(Auth::user()->can('maintenance.gerer'), 403, 'Accès non autorisé.');
        $request->validate(['intervention_realisee' => 'required|string|max:2000', 'cout' => 'nullable|numeric|min:0', 'hors_service' => 'nullable|boolean']);

        DB::transaction(function () use ($request, $maintenance) {
            $maintenance->update([
                'statut' => 'resolue', 'intervention_realisee' => $request->intervention_realisee, 'cout' => $request->cout,
                'resolue_par' => Auth::id(), 'resolue_at' => now(),
            ]);
            $equipement = $maintenance->equipement;
            $equipement->update([
                'statut' => $request->boolean('hors_service') ? 'hors_service' : 'en_service',
                'prochaine_maintenance' => $equipement->periodicite_jours ? today()->addDays($equipement->periodicite_jours) : $equipement->prochaine_maintenance,
            ]);
        });

        return back()->with('success', 'Intervention de maintenance clôturée.');
    }
}
