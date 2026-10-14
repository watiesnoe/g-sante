<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\RapportPeriodique;
use App\Services\RapportMensuelService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * EF-13.03, EF-13.04, RG-15 : rapport mensuel brouillon → validé → transmis ;
 * transmis, il est figé ; une correction crée une nouvelle version.
 */
class RapportMensuelController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('rapports.mensuel'), 403, 'Accès non autorisé.');

        return view('application.rapport.mensuels', [
            'rapports' => RapportPeriodique::with(['auteur', 'validateur'])->orderByDesc('periode_du')->orderByDesc('version')->get(),
        ]);
    }

    public function generer(Request $request, RapportMensuelService $service)
    {
        abort_unless(Auth::user()->can('rapports.mensuel'), 403, 'Accès non autorisé.');
        $request->validate(['mois' => 'required|date_format:Y-m']);

        $du = Carbon::createFromFormat('Y-m', $request->mois)->startOfMonth();
        $au = $du->copy()->endOfMonth();
        $existant = RapportPeriodique::where('periode_du', $du->toDateString())->orderByDesc('version')->first();

        if ($existant && $existant->statut !== 'brouillon') {
            return back()->with('error', 'Le rapport de ce mois est déjà ' . strtolower(RapportPeriodique::STATUTS[$existant->statut]) . ' : créez une nouvelle version pour le corriger.');
        }

        $donnees = $service->calculer($du, $au);
        $rapport = $existant
            ? tap($existant)->update(['donnees' => $donnees])
            : RapportPeriodique::create(['periode_du' => $du, 'periode_au' => $au, 'version' => 1, 'statut' => 'brouillon', 'donnees' => $donnees, 'cree_par' => Auth::id()]);

        return redirect()->route('rapports-mensuels.show', $rapport)->with('success', 'Rapport calculé.');
    }

    public function show(RapportPeriodique $rapport)
    {
        abort_unless(Auth::user()->can('rapports.mensuel'), 403, 'Accès non autorisé.');

        return view('application.rapport.mensuel', [
            'rapport'     => $rapport->load(['auteur', 'validateur']),
            'libelles'    => RapportMensuelService::INDICATEURS,
            'correspondances' => DB::table('correspondances_dhis2')->where('etablissement_id', Etablissement::courantId())->pluck('data_element', 'indicateur'),
        ]);
    }

    public function etape(Request $request, RapportPeriodique $rapport, RapportMensuelService $service)
    {
        abort_unless(Auth::user()->can('rapports.mensuel'), 403, 'Accès non autorisé.');
        $user = Auth::user();

        switch ($request->etape) {
            case 'valider':
                abort_unless($user->can('rapports.valider'), 403, 'Vous n\'avez pas la permission de valider un rapport.');
                abort_unless($rapport->statut === 'brouillon', 422, 'Seul un brouillon peut être validé.');
                if ($rapport->cree_par === $user->id && Etablissement::courant()->parametre('securite.separation_taches')) {
                    return back()->with('error', 'Séparation des tâches : un autre responsable doit valider ce rapport.');
                }
                $rapport->update(['statut' => 'valide', 'valide_par' => $user->id, 'valide_at' => now()]);
                break;
            case 'transmettre':
                abort_unless($user->can('rapports.valider'), 403, 'Accès non autorisé.');
                abort_unless($rapport->statut === 'valide', 422, 'Seul un rapport validé peut être transmis.');
                $rapport->update(['statut' => 'transmis', 'transmis_at' => now()]);
                break;
            case 'nouvelle_version':
                abort_unless($rapport->statut === 'transmis', 422, 'Seul un rapport transmis appelle une nouvelle version.');
                $request->validate(['motif' => 'required|string|max:255']);
                $rapport = RapportPeriodique::create([
                    'periode_du' => $rapport->periode_du, 'periode_au' => $rapport->periode_au,
                    'version' => RapportPeriodique::where('periode_du', $rapport->periode_du)->max('version') + 1,
                    'statut' => 'brouillon', 'donnees' => $service->calculer($rapport->periode_du, $rapport->periode_au),
                    'cree_par' => $user->id, 'motif_nouvelle_version' => $request->motif,
                ]);

                return redirect()->route('rapports-mensuels.show', $rapport)->with('success', 'Version ' . $rapport->version . ' créée en brouillon.');
            default:
                abort(404);
        }

        return back()->with('success', 'Rapport ' . strtolower(RapportPeriodique::STATUTS[$rapport->statut]) . '.');
    }

    /** Export tableur (CSV) ou fichier d'échange DHIS2 (dataValueSet JSON) selon la correspondance saisie. */
    public function export(RapportPeriodique $rapport, string $format)
    {
        abort_unless(Auth::user()->can('rapports.mensuel'), 403, 'Accès non autorisé.');
        $nom = 'rapport_mensuel_' . $rapport->periode_du->format('Y_m') . '_v' . $rapport->version;
        $valeurs = $rapport->donnees['indicateurs'] ?? [];

        if ($format === 'dhis2') {
            $etablissement = Etablissement::courant();
            $correspondances = DB::table('correspondances_dhis2')->where('etablissement_id', $etablissement->id)->get()->keyBy('indicateur');

            return response()->json([
                'period'     => $rapport->periode_du->format('Ym'),
                'orgUnit'    => $etablissement->parametre('dhis2.org_unit') ?: null,
                'completeDate' => optional($rapport->valide_at)->toDateString(),
                'dataValues' => collect($valeurs)->filter(fn($v, $k) => isset($correspondances[$k]))->map(fn($v, $k) => array_filter([
                    'dataElement' => $correspondances[$k]->data_element,
                    'categoryOptionCombo' => $correspondances[$k]->category_option_combo,
                    'value' => (string) $v,
                ]))->values(),
            ], 200, ['Content-Disposition' => "attachment; filename={$nom}_dhis2.json"]);
        }

        return response()->streamDownload(function () use ($valeurs, $rapport) {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");
            fputcsv($f, ['Indicateur', 'Code', 'Valeur'], ';');
            foreach (RapportMensuelService::INDICATEURS as $cle => $libelle) {
                fputcsv($f, [$libelle, $cle, $valeurs[$cle] ?? 0], ';');
            }
            foreach ($rapport->donnees['diagnostics'] ?? [] as $d) {
                fputcsv($f, ['Diagnostic ' . $d['code'] . ' — ' . $d['libelle'], 'diagnostic.' . $d['code'], $d['total']], ';');
            }
            fclose($f);
        }, $nom . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Correspondance indicateur → élément de données DHIS2, fournie par le district. */
    public function correspondance(Request $request)
    {
        abort_unless(Auth::user()->can('rapports.valider'), 403, 'Accès non autorisé.');
        $request->validate([
            'indicateur'    => 'required|in:' . implode(',', array_keys(RapportMensuelService::INDICATEURS)),
            'data_element'  => 'nullable|string|max:50',
            'category_option_combo' => 'nullable|string|max:50',
        ]);

        if (!$request->data_element) {
            DB::table('correspondances_dhis2')->where('etablissement_id', Etablissement::courantId())->where('indicateur', $request->indicateur)->delete();
        } else {
            DB::table('correspondances_dhis2')->updateOrInsert(
                ['etablissement_id' => Etablissement::courantId(), 'indicateur' => $request->indicateur],
                ['data_element' => $request->data_element, 'category_option_combo' => $request->category_option_combo, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return back()->with('success', 'Correspondance enregistrée.');
    }
}
