<?php

namespace App\Http\Controllers;

use App\Models\Famille;
use App\Models\Medicament;
use App\Models\Unite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class MedicamentController extends Controller
{
    public function index(Request $request, $famille = null)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas accès à la gestion des médicaments.');

        $selectedFamilleId = null;
        if ($famille) {
            $familleModel = Famille::where('uuid', $famille)
                ->orWhere('id', $famille)
                ->first();
            $selectedFamilleId = $familleModel?->id;
        } else {
            $selectedFamilleId = $request->get('famille_id');
        }

        // 🎯 Détection stricte pour différencier l'état actif (1) de l'état inactif (0)
        $filterStockCritique = $request->input('stock_critique') == 1 || $request->input('stock_critique') === '1';

        if ($request->ajax()) {
            $query = Medicament::with(['unites', 'famille']);

            if ($selectedFamilleId) {
                $query->where('famille_id', $selectedFamilleId);
            }

            // 🎯 Utilisation du scope centralisé du modèle
            if ($filterStockCritique) {
                $query->critique();
            }

            return DataTables::of($query)
                ->addColumn('checkbox', function ($m) {
                    return '<input type="checkbox" class="form-check-input medicament-checkbox" value="' . $m->uuid . '">';
                })
                ->addColumn('famille', function ($m) {
                    return $m->famille?->nom ?? '-';
                })
                ->addColumn('unite_select', function ($m) {
                    if ($m->unites->isEmpty()) return '<span class="text-muted">-</span>';

                    $options = '';
                    foreach ($m->unites as $u) {
                        $label   = e($u->nom) . ' (' . e($u->symbole) . ')';
                        $selected = $u->is_default ? 'selected' : '';
                        $achat   = number_format($u->prix_achat, 0, ',', '\u00a0');
                        $vente   = number_format($u->prix_vente, 0, ',', '\u00a0');
                        $options .= '<option value="' . $u->id . '" '
                            . 'data-achat="' . $achat . '" '
                            . 'data-vente="' . $vente . '" '
                            . $selected . '>'
                            . $label . '</option>';
                    }

                    return '<select class="form-select form-select-sm unite-select">' . $options . '</select>';
                })
                ->addColumn('prix_achat_display', function ($m) {
                    $default = $m->unites->firstWhere('is_default', true) ?? $m->unites->first();
                    $val = $default ? number_format($default->prix_achat, 0, ',', '\u00a0') : '-';
                    return '<span class="price-achat">' . $val . '</span>';
                })
                ->addColumn('prix_vente_display', function ($m) {
                    $default = $m->unites->firstWhere('is_default', true) ?? $m->unites->first();
                    $val = $default ? number_format($default->prix_vente, 0, ',', '\u00a0') : '-';
                    return '<span class="price-vente fw-semibold text-success">' . $val . '</span>';
                })
                ->addColumn('actions', function ($m) {
                    $user = Auth::user();
                    $html = '';

                    if ($user->can('stock.medicaments')) {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-primary view"   data-id="' . $m->uuid . '" title="Détails"><i class="fa fa-eye"></i></button>';
                        $html .= '<button type="button" class="btn btn-sm btn-outline-info edit"    data-id="' . $m->uuid . '" title="Modifier"><i class="fa fa-pencil-alt"></i></button>';
                        $html .= '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="' . $m->uuid . '" title="Supprimer"><i class="fa fa-trash"></i></button>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions', 'checkbox', 'unite_select', 'prix_achat_display', 'prix_vente_display'])
                ->make(true);
        }

        $familles       = Famille::orderBy('nom')->get();
        $unites         = Unite::all();
        $totalMolecules = Medicament::count();

        // 🎯 Utilisation du scope pour le compteur global
        $stockCritique  = Medicament::critique()->count();

        $pageTitle = '🩺 Gestion des Médicaments';
        if ($selectedFamilleId) {
            $familleObj = $familles->find($selectedFamilleId);
            if ($familleObj) {
                $pageTitle = '💊 Gestion des ' . $familleObj->nom;
            }
        }

        return view('application.medicament.index', compact('familles', 'unites', 'totalMolecules', 'stockCritique', 'pageTitle', 'selectedFamilleId', 'filterStockCritique'));
    }

    public function show($id)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les médicaments.');

        $medicament = Medicament::where('uuid', $id)->orWhere('id', $id)->firstOrFail();

        if (request()->ajax()) {
            return response()->json($medicament->load(['unite', 'famille']));
        }

        $medicament->load(['unite', 'famille', 'protocoles.maladie']);
        return view('application.medicament.show', compact('medicament'));
    }

    public function create()
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un médicament.');

        $unites  = Unite::all();
        $familles = Famille::all();

        return view('application.medicament.create', compact('unites', 'familles'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un médicament.');

        $request->validate([
            'nom'                      => 'required|string|max:255',
            'description'              => 'nullable|string',
            'stock'                    => 'required|integer|min:0',
            'stock_min'                => 'required|integer|min:0',
            'famille_id'               => 'required|exists:familles,id',
            'unites'                   => 'required|array|min:1',
            'unites.*.nom'             => 'required|string|max:100',
            'unites.*.symbole'         => 'required|string|max:20',
            'unites.*.facteur'         => 'required|numeric|min:0.01',
            'unites.*.prix_achat'      => 'required|numeric|min:0',
            'unites.*.prix_vente'      => 'required|numeric|min:0',
        ]);

        $medicament = Medicament::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'nom'         => $request->nom,
            'code_barre'  => $request->code_barre,
            'description' => $request->description,
            'stock'       => $request->stock,
            'stock_min'   => $request->stock_min,
            'famille_id'  => $request->famille_id,
        ]);

        $defaultIdx = (int) $request->input('default_unit_idx', 0);
        $unitesData = array_values($request->input('unites', []));

        foreach ($unitesData as $i => $u) {
            $medicament->unites()->create([
                'nom'        => $u['nom'],
                'symbole'    => $u['symbole'],
                'facteur'    => $u['facteur'],
                'prix_achat' => $this->parsePrice($u['prix_achat']),
                'prix_vente' => $this->parsePrice($u['prix_vente']),
                'is_default' => ($i === $defaultIdx),
            ]);
        }

        return redirect()->route('medicaments.index')
            ->with('success', 'Médicament ajouté avec succès ✅');
    }

    public function edit($id)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier un médicament.');

        $medicament = Medicament::where('uuid', $id)->orWhere('id', $id)->firstOrFail();
        $unites  = Unite::all();
        $familles = Famille::all();

        return view('application.medicament.create', compact('medicament', 'unites', 'familles'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier un médicament.');

        $medicament = Medicament::where('uuid', $id)->orWhere('id', $id)->firstOrFail();

        $request->validate([
            'nom'                      => 'required|string|max:255',
            'description'              => 'nullable|string',
            'stock'                    => 'required|integer|min:0',
            'stock_min'                => 'required|integer|min:0',
            'famille_id'               => 'required|exists:familles,id',
            'unites'                   => 'required|array|min:1',
            'unites.*.nom'             => 'required|string|max:100',
            'unites.*.symbole'         => 'required|string|max:20',
            'unites.*.facteur'         => 'required|numeric|min:0.01',
            'unites.*.prix_achat'      => 'required|numeric|min:0',
            'unites.*.prix_vente'      => 'required|numeric|min:0',
        ]);

        $medicament->update([
            'nom'         => $request->nom,
            'code_barre'  => $request->code_barre,
            'description' => $request->description,
            'stock'       => $request->stock,
            'stock_min'   => $request->stock_min,
            'famille_id'  => $request->famille_id,
        ]);

        $defaultIdx      = (int) $request->input('default_unit_idx', 0);
        $unitesData      = array_values($request->input('unites', []));
        $existingIds     = $medicament->unites()->pluck('id')->toArray();
        $submittedIds    = [];

        foreach ($unitesData as $i => $u) {
            $unitId = isset($u['id']) && $u['id'] ? (int) $u['id'] : null;

            $data = [
                'nom'        => $u['nom'],
                'symbole'    => $u['symbole'],
                'facteur'    => $u['facteur'],
                'prix_achat' => $this->parsePrice($u['prix_achat']),
                'prix_vente' => $this->parsePrice($u['prix_vente']),
                'is_default' => ($i === $defaultIdx),
            ];

            if ($unitId && in_array($unitId, $existingIds)) {
                Unite::where('id', $unitId)->update($data);
                $submittedIds[] = $unitId;
            } else {
                $newUnite = $medicament->unites()->create($data);
                $submittedIds[] = $newUnite->id;
            }
        }

        // Supprimer les unités retirées
        $toDelete = array_diff($existingIds, $submittedIds);
        if ($toDelete) {
            Unite::whereIn('id', $toDelete)->delete();
        }

        return redirect()->route('medicaments.index')
            ->with('success', 'Médicament mis à jour avec succès ✏️');
    }

    public function destroy($id)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé : vous n\'avez pas la permission de supprimer un médicament.');

        $medicament = Medicament::where('uuid', $id)->orWhere('id', $id)->firstOrFail();
        $medicament->delete();

        return response()->json([
            'success' => true,
            'message' => 'Médicament supprimé avec succès 🗑️',
        ]);
    }

    /**
     * Parse un prix formaté (ex: "20 000" ou "20,000") en float.
     */
    private function parsePrice($val): float
    {
        if (!$val && $val !== '0') return 0;
        $str = str_replace([' ', "\xc2\xa0"], '', (string) $val); // retire les espaces et NBSP
        $str = str_replace(',', '.', $str);
        return (float) $str ?: 0;
    }

    public function search(Request $request)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403);

        $query = $request->get('q', '');
        $medicaments = Medicament::with('unite')
            ->where('nom', 'like', "%{$query}%")
            ->orWhere('code_barre', 'like', "%{$query}%")
            ->limit(20)
            ->get();

        return response()->json($medicaments);
    }

    public function getUnitesApi($id)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403);

        $medicament = Medicament::where('uuid', $id)->orWhere('id', $id)->firstOrFail();
        return response()->json($medicament->unites);
    }
}