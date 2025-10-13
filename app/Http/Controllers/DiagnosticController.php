<?php

namespace App\Http\Controllers;

use App\Models\Diagnostic;
use App\Models\Maladie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

/** EF-04.02 : nomenclature des diagnostics, importée depuis un fichier officiel. */
class DiagnosticController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.maladies'), 403, 'Accès non autorisé.');

        if ($request->ajax()) {
            return DataTables::of(Diagnostic::query())->make(true);
        }

        return view('application.parametre.diagnostics', ['total' => Diagnostic::count()]);
    }

    /**
     * Questions pour affiner l'interrogatoire : les autres symptômes des maladies
     * les plus compatibles avec ceux déjà cochés, du plus discriminant au moins.
     */
    public function suggestFollowUp(Request $request)
    {
        abort_unless(Auth::user()->can('consultations.create') || Auth::user()->can('consultations.edit'), 403, 'Accès non autorisé.');

        $coches = array_map('intval', (array) $request->input('symptomes', []));
        if (!$coches) {
            return response()->json([]);
        }

        // Maladies en vigueur qui partagent au moins un symptôme coché, les plus compatibles d'abord
        $candidates = Maladie::actives()->whereHas('symptomes', fn($q) => $q->whereIn('symptomes.id', $coches))
            ->with(['symptomes' => fn($q) => $q->where('symptomes.actif', true)])->get()
            ->sortByDesc(fn($m) => [$m->symptomes->whereIn('id', $coches)->count(), -$m->symptomes->count()])
            ->take(5);

        // Les symptômes non cochés de ces maladies, les plus partagés d'abord
        $autres = $candidates->flatMap(fn($m) => $m->symptomes->whereNotIn('id', $coches));
        $noms = $autres->pluck('nom', 'id');

        return response()->json($autres->pluck('id')->countBy()->sortDesc()->keys()->take(8)
            ->map(fn($id) => ['id' => $id, 'nom' => $noms[$id]])->values());
    }
}
