<?php

namespace App\Http\Controllers;

use App\Models\FicheMedicament;
use App\Models\Medicament;
use App\Models\ValidationFiche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Guide des médicaments : référentiel commun, validé par chaque établissement. */
class FicheMedicamentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('referentiel.view'), 403, 'Accès non autorisé.');

        $fiches = FicheMedicament::query()->recherche($request->q)
            ->when($request->voie, fn($q, $v) => $q->where('voie', $v))
            ->when($request->etat === 'validees', fn($q) => $q->valideesIci())
            ->when($request->etat === 'a_valider', fn($q) => $q->valideesIci(false)->where('deconseille', false))
            ->when($request->etat === 'deconseilles', fn($q) => $q->where('deconseille', true))
            ->orderBy('nom')->paginate(30)->withQueryString();

        $validees = FicheMedicament::valideesIci()->pluck('id');

        return view('application.referentiel.index', [
            'fiches'   => $fiches,
            'validees' => $validees,
            'total'    => FicheMedicament::count(),
            'nbValidees' => $validees->count(),
            'sources'  => FicheMedicament::distinct()->pluck('source'),
        ]);
    }

    public function show(FicheMedicament $fiche)
    {
        abort_unless(Auth::user()->can('referentiel.view'), 403, 'Accès non autorisé.');

        // Produits du stock de l'établissement qui pourraient correspondre (premier mot du nom)
        $motCle = preg_split('/[\s=\/,]+/u', $fiche->nom)[0] ?? $fiche->nom;
        $lies = Medicament::where('fiche_medicament_id', $fiche->id)->orderBy('nom')->get(['id', 'uuid', 'nom', 'stock']);
        $suggestions = mb_strlen($motCle) >= 4
            ? Medicament::where('nom', 'like', '%' . $motCle . '%')->where(fn($q) => $q->whereNull('fiche_medicament_id')->orWhere('fiche_medicament_id', '!=', $fiche->id))->orderBy('nom')->limit(10)->get(['id', 'uuid', 'nom', 'stock', 'fiche_medicament_id'])
            : collect();

        return view('application.referentiel.show', [
            'fiche'       => $fiche,
            'validation'  => $fiche->validationCourante(),
            'lies'        => $lies,
            'suggestions' => $suggestions,
            'syntheses'   => \App\Models\FicheSynthese::whereHas('medicaments', fn($q) => $q->whereKey($fiche->id))
                ->where(fn($w) => $w->where('statut', '!=', 'brouillon')->orWhere('auteur_id', Auth::id()))->orderBy('titre')->get(['id', 'uuid', 'titre', 'statut']),
        ]);
    }

    /** Relecture par le médecin ou le pharmacien de l'établissement. */
    public function valider(Request $request, FicheMedicament $fiche)
    {
        abort_unless(Auth::user()->can('referentiel.valider'), 403, 'Seuls un médecin ou un pharmacien peuvent valider une fiche.');
        $request->validate(['commentaire' => 'nullable|string|max:500']);

        ValidationFiche::updateOrCreate(
            ['fiche_medicament_id' => $fiche->id, 'etablissement_id' => \App\Models\Etablissement::courantId()],
            ['user_id' => Auth::id(), 'empreinte' => $fiche->empreinte(), 'commentaire' => $request->commentaire]
        );

        return back()->with('success', 'Fiche « ' . $fiche->nom . ' » validée pour votre établissement.');
    }

    public function retirerValidation(FicheMedicament $fiche)
    {
        abort_unless(Auth::user()->can('referentiel.valider'), 403, 'Accès non autorisé.');
        ValidationFiche::where('fiche_medicament_id', $fiche->id)->delete(); // limité à l'établissement courant (cloisonnement)

        return back()->with('success', 'Validation retirée : la fiche est de nouveau « à valider ».');
    }

    /** Relier (ou délier) un produit du stock de l'établissement à cette fiche. */
    public function lier(Request $request, FicheMedicament $fiche)
    {
        abort_unless(Auth::user()->can('stock.medicaments'), 403, 'Accès non autorisé.');
        $request->validate(['medicament_id' => 'required|integer', 'action' => 'required|in:lier,delier']);
        $medicament = Medicament::findOrFail($request->medicament_id); // introuvable s'il appartient à un autre établissement

        $medicament->forceFill(['fiche_medicament_id' => $request->action === 'lier' ? $fiche->id : null])->save();

        return back()->with('success', $medicament->nom . ($request->action === 'lier' ? ' est relié à cette fiche.' : ' n\'est plus relié à cette fiche.'));
    }
}
