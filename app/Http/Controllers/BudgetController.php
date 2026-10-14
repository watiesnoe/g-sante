<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\LigneBudgetaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/** EF-12.03 : lignes budgétaires — prévu, engagé, réalisé, écart. */
class BudgetController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('budget.view'), 403, 'Accès non autorisé.');
        $exercice = (int) ($request->exercice ?: session('exercice_year', date('Y')));

        $lignes = LigneBudgetaire::where('exercice', $exercice)->orderBy('code')->get()->map(fn($l) => [
            'ligne'   => $l,
            'engage'  => $l->engage(),
            'realise' => $l->realise(),
            'ecart'   => $l->montant_prevu - $l->engage(),
        ]);

        return view('application.budget.index', compact('lignes', 'exercice'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('budget.gerer'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'exercice'      => 'required|integer|min:2000|max:2100',
            'code'          => ['required', 'string', 'max:30', Rule::unique('lignes_budgetaires')->where('etablissement_id', Etablissement::courantId())->where('exercice', $request->exercice)],
            'libelle'       => 'required|string|max:255',
            'montant_prevu' => 'required|numeric|min:0',
        ]);
        LigneBudgetaire::create($validated);

        return back()->with('success', 'Ligne budgétaire ajoutée.');
    }
}
