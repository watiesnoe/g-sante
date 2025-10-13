<?php

namespace App\Http\Controllers;

use App\Models\FicheMedicament;
use App\Models\FicheSynthese;
use App\Models\Maladie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Fiches de synthèse « du symptôme à la prescription » de l'établissement. */
class FicheSyntheseController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('syntheses.view'), 403, 'Accès non autorisé.');
        $q = $request->q;

        $fiches = FicheSynthese::with(['auteur', 'medicaments:id,nom'])
            ->when($q, fn($r) => $r->where(fn($w) => $w->where('titre', 'like', "%{$q}%")->orWhere('motif', 'like', "%{$q}%")->orWhere('diagnostics', 'like', "%{$q}%")))
            ->when($request->statut, fn($r, $s) => $r->where('statut', $s))
            // Un brouillon n'est visible que de son auteur
            ->where(fn($w) => $w->where('statut', '!=', 'brouillon')->orWhere('auteur_id', Auth::id()))
            ->orderByRaw("FIELD(statut, 'validee', 'a_valider', 'brouillon')")->orderBy('titre')
            ->paginate(25)->withQueryString();

        return view('application.syntheses.index', compact('fiches'));
    }

    public function create()
    {
        abort_unless(Auth::user()->can('syntheses.rediger'), 403, 'Accès non autorisé.');

        return view('application.syntheses.form', $this->donneesFormulaire(new FicheSynthese()));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('syntheses.rediger'), 403, 'Accès non autorisé.');
        $fiche = DB::transaction(fn() => $this->enregistrer(new FicheSynthese(['auteur_id' => Auth::id()]), $request));

        return redirect()->route('syntheses.show', $fiche)->with('success', 'Fiche « ' . $fiche->titre . ' » enregistrée (' . FicheSynthese::STATUTS[$fiche->statut] . ').');
    }

    public function show(FicheSynthese $synthese)
    {
        abort_unless(Auth::user()->can('syntheses.view'), 403, 'Accès non autorisé.');
        abort_if($synthese->statut === 'brouillon' && $synthese->auteur_id !== Auth::id(), 404);

        return view('application.syntheses.show', [
            'fiche' => $synthese->load(['medicaments', 'references', 'maladie', 'auteur', 'validateur']),
        ]);
    }

    public function edit(FicheSynthese $synthese)
    {
        abort_unless(Auth::user()->can('syntheses.rediger'), 403, 'Accès non autorisé.');
        abort_if($synthese->statut === 'brouillon' && $synthese->auteur_id !== Auth::id(), 404);

        return view('application.syntheses.form', $this->donneesFormulaire($synthese->load(['medicaments', 'references'])));
    }

    public function update(Request $request, FicheSynthese $synthese)
    {
        abort_unless(Auth::user()->can('syntheses.rediger'), 403, 'Accès non autorisé.');
        abort_if($synthese->statut === 'brouillon' && $synthese->auteur_id !== Auth::id(), 404);
        DB::transaction(fn() => $this->enregistrer($synthese, $request));

        return redirect()->route('syntheses.show', $synthese)->with('success', 'Fiche enregistrée (' . FicheSynthese::STATUTS[$synthese->fresh()->statut] . ').');
    }

    /** Validation par un médecin autre que l'auteur. */
    public function valider(FicheSynthese $synthese)
    {
        abort_unless(Auth::user()->can('syntheses.valider'), 403, 'Seul un médecin peut valider une fiche de synthèse.');
        if ($synthese->statut !== 'a_valider') {
            return back()->with('error', 'Seule une fiche soumise peut être validée.');
        }
        if ($synthese->auteur_id === Auth::id()) {
            return back()->with('error', 'Un autre médecin que l\'auteur doit valider cette fiche.');
        }
        $synthese->update(['statut' => 'validee', 'valide_par' => Auth::id(), 'valide_at' => now()]);

        return back()->with('success', 'Fiche validée : elle est maintenant une référence de l\'établissement.');
    }

    private function enregistrer(FicheSynthese $fiche, Request $request): FicheSynthese
    {
        $validated = $request->validate([
            'titre'        => 'required|string|max:255',
            'specialite'   => 'nullable|string|max:100',
            'motif'        => 'nullable|string|max:255',
            'maladie_id'   => 'nullable|exists:maladies,id',
            'demarche'     => 'nullable|string|max:20000',
            'diagnostics'  => 'nullable|string|max:20000',
            'examens'      => 'nullable|string|max:20000',
            'gravite'      => 'nullable|string|max:20000',
            'traitement'   => 'nullable|string|max:20000',
            'suivi'        => 'nullable|string|max:20000',
            'medicaments'          => 'nullable|array|max:30',
            'medicaments.*.id'     => 'nullable|integer|exists:fiches_medicaments,id',
            'medicaments.*.usage'  => 'nullable|string|max:255',
            'references'                 => 'nullable|array|max:20',
            'references.*.ouvrage'       => 'nullable|string|max:255',
            'references.*.localisation'  => 'nullable|string|max:150',
            'references.*.note'          => 'nullable|string|max:255',
            'action'       => 'required|in:brouillon,soumettre',
        ]);

        $fiche->fill(collect($validated)->only(['titre', 'specialite', 'motif', 'maladie_id', ...array_keys(FicheSynthese::SECTIONS)])->all());

        // Le contenu change ou l'auteur soumet : la fiche (re)passe en validation
        $statut = $validated['action'] === 'soumettre' ? 'a_valider' : 'brouillon';
        if ($fiche->statut === 'validee' && $validated['action'] === 'brouillon' && !$fiche->isDirty() && $this->liensInchanges($fiche, $validated)) {
            $statut = 'validee';
        }
        $fiche->fill(['statut' => $statut] + ($statut === 'validee' ? [] : ['valide_par' => null, 'valide_at' => null]))->save();

        $medicaments = [];
        foreach (array_values($validated['medicaments'] ?? []) as $i => $ligne) {
            if (!empty($ligne['id'])) {
                $medicaments[(int) $ligne['id']] = ['usage' => $ligne['usage'] ?? null, 'ordre' => $i];
            }
        }
        $fiche->medicaments()->sync($medicaments);

        $fiche->references()->delete();
        foreach ($validated['references'] ?? [] as $reference) {
            if (filled($reference['ouvrage'] ?? null)) {
                $fiche->references()->create($reference);
            }
        }

        return $fiche;
    }

    private function liensInchanges(FicheSynthese $fiche, array $validated): bool
    {
        $actuels = $fiche->medicaments()->pluck('fiches_medicaments.id')->sort()->values()->all();
        $nouveaux = collect($validated['medicaments'] ?? [])->pluck('id')->filter()->map(fn($id) => (int) $id)->sort()->values()->all();

        return $actuels === $nouveaux;
    }

    private function donneesFormulaire(FicheSynthese $fiche): array
    {
        return [
            'fiche'       => $fiche,
            'catalogue'   => FicheMedicament::orderBy('nom')->get(['id', 'nom', 'voie', 'page', 'deconseille']),
            'maladies'    => Maladie::actives()->orderBy('nom')->get(['id', 'nom']),
        ];
    }
}
