<?php

namespace App\Http\Controllers;

use App\Models\AbreviationClinique;
use App\Models\DciReference;
use App\Models\FicheBerrebi;
use App\Models\Maladie;
use App\Models\Symptome;
use App\Services\Referentiel\ImportGuideClinique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Guide clinique : maladies par chapitre, recherche par symptômes, fiche maladie avec traitements. */
class GuideCliniqueController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('referentiel.view'), 403, 'Accès non autorisé.');

        $coches = array_map('intval', (array) $request->input('symptomes', []));
        $texte = trim((string) $request->q);

        // Fiches Berrebi : recherche dans le titre, la démarche et les médicaments cités
        $berrebi = FicheBerrebi::query()
            ->when($texte !== '', fn($q) => $q->where(fn($w) => $w->where('titre', 'like', "%$texte%")
                ->orWhere('demarche', 'like', "%$texte%")->orWhere('medicaments_cites', 'like', "%$texte%")))
            ->when($request->specialite, fn($q, $sp) => $q->where('specialite', $sp))
            ->orderBy('specialite')->orderBy('titre')->get(['id', 'uuid', 'specialite', 'titre', 'demarche', 'traitement']);

        if ($request->vue === 'berrebi') {
            return view('application.guide_clinique.berrebi_index', [
                'parSpecialite' => $berrebi->groupBy('specialite'),
                'total'         => $berrebi->count(),
                'specialites'   => FicheBerrebi::distinct()->orderBy('specialite')->pluck('specialite'),
            ]);
        }

        $maladies = Maladie::actives()->ordreGuide()
            ->withCount(['symptomes' => fn($q) => $q->where('symptomes.actif', true)])
            ->withCount(['symptomes as symptomes_retrouves' => fn($q) => $q->whereIn('symptomes.id', $coches ?: [0])])
            ->when($request->chapitre, fn($q, $c) => $q->where('chapitre', $c))
            ->when($texte !== '', fn($q) => $q->where(fn($w) => $w->where('nom', 'like', "%$texte%")
                ->orWhere('description', 'like', "%$texte%")
                ->orWhereHas('symptomes', fn($s) => $s->where('nom', 'like', "%$texte%"))))
            ->when($coches, fn($q) => $q->whereHas('symptomes', fn($s) => $s->whereIn('symptomes.id', $coches)))
            ->get();

        // Recherche par symptômes : les maladies qui en regroupent le plus, puis les mieux couvertes
        if ($coches) {
            $maladies = $maladies->sortBy([
                ['symptomes_retrouves', 'desc'],
                fn($a, $b) => $b->symptomes_retrouves / max($b->symptomes_count, 1) <=> $a->symptomes_retrouves / max($a->symptomes_count, 1),
            ])->values();
        }

        return view('application.guide_clinique.index', [
            'maladies'  => $maladies,
            'parChapitre' => $coches ? null : $maladies->groupBy('chapitre'),
            'chapitres' => Maladie::actives()->ordreGuide()->distinct()->pluck('chapitre')->filter()->unique()->values(),
            'symptomes' => Symptome::where('actif', true)->orderBy('nom')->get(['id', 'nom']),
            'coches'    => $coches,
            'nbBerrebi' => $texte !== '' ? $berrebi->count() : FicheBerrebi::count(),
            'source'    => Maladie::actives()->whereNotNull('source')->value('source'),
        ]);
    }

    public function show(Maladie $maladie)
    {
        abort_unless(Auth::user()->can('referentiel.view'), 403, 'Accès non autorisé.');

        $maladie->load(['symptomes' => fn($q) => $q->orderBy('nom'), 'protocole.lignes.fiche', 'fichesBerrebi']);
        $lignes = $maladie->protocole && $maladie->protocole->actif ? $maladie->protocole->lignes : collect();
        $references = DciReference::parDci($lignes->map(fn($l) => ImportGuideClinique::dciDeLigne((string) $l->nom_medicament))->all());

        $medicaments = $lignes->map(fn($ligne) => [
            'ligne'     => $ligne,
            'reference' => $references[mb_strtolower(ImportGuideClinique::dciDeLigne((string) $ligne->nom_medicament))] ?? null,
            'produit'   => $ligne->produitEnStock(),
        ]);

        return view('application.guide_clinique.show', [
            'maladie'      => $maladie,
            'medicaments'  => $medicaments,
            'abreviations' => AbreviationClinique::orderBy('abreviation')->get(),
        ]);
    }

    /** Fiche du livre Berrebi : démarche, traitement et ordonnances (posologies OCR à vérifier). */
    public function berrebi(FicheBerrebi $fiche)
    {
        abort_unless(Auth::user()->can('referentiel.view'), 403, 'Accès non autorisé.');

        $fiche->load(['ordonnances', 'maladies' => fn($q) => $q->where('actif', true)->orderBy('nom')]);

        return view('application.guide_clinique.berrebi_show', ['fiche' => $fiche]);
    }
}
