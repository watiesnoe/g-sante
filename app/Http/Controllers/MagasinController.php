<?php

namespace App\Http\Controllers;

use App\Exceptions\StockInsuffisantException;
use App\Models\Etablissement;
use App\Models\Lot;
use App\Models\Magasin;
use App\Models\Medicament;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/** EF-08.07 : magasins (pharmacie centrale, dépôts) et transferts entre eux. */
class MagasinController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('stock.view'), 403, 'Accès non autorisé.');
        Magasin::parDefaut();

        $magasins = Magasin::orderByDesc('par_defaut')->orderBy('nom')->get();
        $stocks = Lot::where('quantite', '>', 0)->selectRaw('magasin_id, COUNT(DISTINCT medicament_id) as references_, SUM(quantite) as unites')
            ->groupBy('magasin_id')->get()->keyBy('magasin_id');

        return view('application.stock.magasins', [
            'magasins'    => $magasins,
            'stocks'      => $stocks,
            'medicaments' => Medicament::where('stock', '>', 0)->orderBy('nom')->get(['id', 'nom', 'stock']),
            'transferts'  => \App\Models\MouvementStock::with(['medicament', 'lot.magasin', 'user'])->where('type', 'transfert')->where('quantite', '>', 0)->latest()->limit(30)->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('stock.inventaire'), 403, 'Accès non autorisé.');
        $request->validate([
            'nom'  => ['required', 'string', 'max:100', Rule::unique('magasins')->where('etablissement_id', Etablissement::courantId())],
            'type' => 'required|in:central,dispensation',
        ]);
        Magasin::create($request->only('nom', 'type') + ['actif' => true, 'par_defaut' => false]);

        return back()->with('success', 'Magasin ajouté.');
    }

    public function transferer(Request $request, StockService $stock)
    {
        abort_unless(Auth::user()->can('stock.inventaire'), 403, 'Accès non autorisé.');
        $validated = $request->validate([
            'medicament_id' => 'required|exists:medicaments,id',
            'de'            => 'required|exists:magasins,id|different:vers',
            'vers'          => 'required|exists:magasins,id',
            'quantite'      => 'required|integer|min:1',
            'motif'         => 'nullable|string|max:255',
        ], ['de.different' => 'Choisissez deux magasins différents.']);

        try {
            $stock->transferer(Medicament::findOrFail($validated['medicament_id']), (int) $validated['quantite'],
                Magasin::findOrFail($validated['de']), Magasin::findOrFail($validated['vers']), $validated['motif'] ?? null);
        } catch (StockInsuffisantException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Transfert enregistré.');
    }
}
