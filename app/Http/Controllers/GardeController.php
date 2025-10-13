<?php

namespace App\Http\Controllers;

use App\Models\Garde;
use App\Models\ServiceMedical;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/** EF-12.05 : planning des gardes et astreintes, par semaine. */
class GardeController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('rh.gardes.view'), 403, 'Accès non autorisé.');

        $debut = ($request->semaine ? Carbon::parse($request->semaine) : today())->startOfWeek();
        $fin = $debut->copy()->endOfWeek();

        return view('application.rh.gardes', [
            'debut'     => $debut,
            'fin'       => $fin,
            'gardes'    => Garde::with(['user', 'service'])->where('debut', '<=', $fin)->where('fin', '>=', $debut)->orderBy('debut')->get(),
            'personnel' => User::deLEtablissement()->where('statut', 'actif')->orderBy('name')->get(['id', 'name']),
            'services'  => ServiceMedical::orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('rh.gardes.gerer'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'user_id'            => 'required|exists:users,id',
            'service_medical_id' => 'nullable|exists:service_medicals,id',
            'type'               => 'required|in:garde,astreinte,permanence',
            'debut'              => 'required|date',
            'fin'                => 'required|date|after:debut',
            'observation'        => 'nullable|string|max:255',
        ]);

        abort_unless(User::deLEtablissement()->whereKey($validated['user_id'])->exists(), 422, 'Cette personne n\'est pas affectée à l\'établissement.');

        // Une même personne ne peut pas être de garde deux fois sur la même période
        $chevauchement = Garde::where('user_id', $validated['user_id'])
            ->where('debut', '<', $validated['fin'])->where('fin', '>', $validated['debut'])->exists();
        if ($chevauchement) {
            return back()->with('error', 'Cette personne a déjà une garde sur cette période.')->withInput();
        }

        Garde::create($validated + ['created_by' => Auth::id()]);

        return back()->with('success', 'Garde enregistrée.');
    }

    public function destroy(Garde $garde)
    {
        abort_unless(Auth::user()->can('rh.gardes.gerer'), 403, 'Accès non autorisé.');
        abort_if($garde->debut->isPast(), 422, 'Une garde commencée ou passée ne se supprime pas.');

        $garde->delete();

        return back()->with('success', 'Garde supprimée.');
    }
}
