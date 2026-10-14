<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Guichet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/** Guichets de caisse de l'établissement (EF-06.06). */
class GuichetController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('parametres.etablissement'), 403, 'Accès non autorisé.');

        return view('application.parametre.guichets', ['guichets' => Guichet::orderBy('nom')->get()]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.etablissement'), 403, 'Accès non autorisé.');
        $request->validate(['nom' => ['required', 'string', 'max:100', Rule::unique('guichets')->where('etablissement_id', Etablissement::courantId())]]);

        Guichet::create(['nom' => $request->nom, 'actif' => true]);

        return back()->with('success', 'Guichet ajouté.');
    }

    public function basculer(Guichet $guichet)
    {
        abort_unless(Auth::user()->can('parametres.etablissement'), 403, 'Accès non autorisé.');
        $guichet->update(['actif' => !$guichet->actif]);

        return back()->with('success', 'Guichet ' . ($guichet->actif ? 'activé' : 'désactivé') . '.');
    }
}
