<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * EF-03.04 : agenda par professionnel (jour ou semaine), rendez-vous du jour et
 * statuts prévu, honoré, absent, annulé.
 */
class AgendaController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('rendezvous.view'), 403, 'Accès non autorisé.');

        $medecins = User::deLEtablissement()->whereHas('roles', fn($q) => $q->where('name', 'medecin'))->orderBy('name')->get(['id', 'name']);
        $medecinId = $request->integer('medecin_id') ?: (Auth::user()->hasRole('medecin') ? Auth::id() : null);
        $vue = $request->vue === 'semaine' ? 'semaine' : 'jour';
        $date = $request->date ? Carbon::parse($request->date) : today();
        [$debut, $fin] = $vue === 'semaine' ? [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()] : [$date->copy()->startOfDay(), $date->copy()->endOfDay()];

        $rendezvous = RendezVous::with(['patient', 'medecin'])
            ->whereBetween('date_heure', [$debut, $fin])
            ->when($medecinId, fn($q) => $q->where('medecin_id', $medecinId))
            ->orderBy('date_heure')->get()
            ->groupBy(fn($r) => Carbon::parse($r->date_heure)->toDateString());

        return view('application.rendezvous.agenda', compact('medecins', 'medecinId', 'vue', 'date', 'debut', 'fin', 'rendezvous'));
    }

    public function statut(Request $request, RendezVous $rendezvous)
    {
        abort_unless(Auth::user()->can('rendezvous.confirm'), 403, 'Accès non autorisé.');
        $request->validate(['statut' => 'required|in:realise,absent,annule']);
        abort_if(in_array($rendezvous->statut, ['realise', 'absent', 'annule'], true), 422, 'Ce rendez-vous est déjà clos.');

        $rendezvous->update(['statut' => $request->statut]);

        return back()->with('success', 'Rendez-vous : ' . RendezVous::STATUTS[$request->statut] . '.');
    }
}
