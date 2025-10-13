<?php

namespace App\Http\Controllers;

use App\Models\CategorieDepense;
use App\Models\Depense;
use App\Models\Etablissement;
use App\Models\ServiceMedical;
use App\Services\DepenseService;
use App\Services\NumerotationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DepenseController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('depenses.view'), 403, 'Accès non autorisé.');

        $depenses = Depense::with(['categorie', 'service', 'auteur', 'validateur'])
            ->when($request->statut, fn($q) => $q->where('statut', $request->statut))
            ->whereYear('date_depense', session('exercice_year', date('Y')))
            ->latest('date_depense')->latest('id')->get();

        $totaux = Depense::whereYear('date_depense', session('exercice_year', date('Y')))
            ->selectRaw('statut, SUM(montant) as total, COUNT(*) as nombre')->groupBy('statut')->get()->keyBy('statut');

        return view('application.depense.index', compact('depenses', 'totaux'));
    }

    public function create()
    {
        abort_unless(Auth::user()->can('depenses.create'), 403, 'Accès non autorisé.');

        return view('application.depense.create', [
            'categories' => CategorieDepense::orderBy('nom')->get(),
            'services'   => ServiceMedical::orderBy('nom')->get(['id', 'nom']),
            'lignes'     => \App\Models\LigneBudgetaire::where('exercice', session('exercice_year', date('Y')))->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request, NumerotationService $numerotation, DepenseService $service)
    {
        abort_unless(Auth::user()->can('depenses.create'), 403, 'Accès non autorisé.');

        $validated = $request->validate([
            'categorie_depense_id' => ['nullable', Rule::exists('categories_depense', 'id')->where('etablissement_id', Etablissement::courantId())],
            'nouvelle_categorie'   => 'nullable|required_without:categorie_depense_id|string|max:100',
            'service_medical_id'   => 'nullable|exists:service_medicals,id',
            'ligne_budgetaire_id'  => ['nullable', Rule::exists('lignes_budgetaires', 'id')->where('etablissement_id', Etablissement::courantId())],
            'objet'                => 'required|string|max:255',
            'beneficiaire'         => 'nullable|string|max:255',
            'montant'              => 'required|numeric|min:1',
            'date_depense'         => 'required|date|before_or_equal:today',
            'piece_justificative'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], ['nouvelle_categorie.required_without' => 'Choisissez une catégorie ou saisissez-en une nouvelle.']);

        if (empty($validated['categorie_depense_id'])) {
            $validated['categorie_depense_id'] = CategorieDepense::firstOrCreate(['nom' => trim($validated['nouvelle_categorie'])])->id;
        }
        unset($validated['nouvelle_categorie']);

        if ($request->hasFile('piece_justificative')) {
            $validated['piece_justificative'] = $request->file('piece_justificative')->store('depenses');
        }

        $depense = Depense::create(array_merge($validated, [
            'numero'     => $numerotation->suivant('depense'),
            'statut'     => 'brouillon',
            'created_by' => Auth::id(),
        ]));

        if ($request->boolean('soumettre')) {
            $service->soumettre($depense);
        }

        return redirect()->route('depenses.index')->with('success', 'Dépense ' . $depense->numero . ' enregistrée' . ($depense->statut === 'soumise' ? ' et soumise à validation.' : ' (brouillon).'));
    }

    /** Étapes du circuit : soumettre, valider, rejeter, payer, annuler. */
    public function action(Request $request, Depense $depense, string $action, DepenseService $service)
    {
        abort_unless(Auth::user()->can('depenses.view'), 403, 'Accès non autorisé.');

        try {
            match ($action) {
                'soumettre' => $service->soumettre($depense),
                'valider'   => $service->valider($depense),
                'rejeter'   => $service->rejeter($depense, (string) $request->motif),
                'payer'     => $service->payer($depense, $request->input('mode_paiement', 'especes')),
                'annuler'   => $service->annuler($depense),
                default     => abort(404),
            };
        } catch (AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Dépense ' . $depense->numero . ' : ' . Depense::STATUTS[$depense->statut] . '.');
    }

    public function piece(Depense $depense)
    {
        abort_unless(Auth::user()->can('depenses.view') && $depense->piece_justificative, 404);

        return response()->file(storage_path('app/private/' . $depense->piece_justificative));
    }
}
