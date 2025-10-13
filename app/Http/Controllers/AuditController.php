<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class AuditController extends Controller
{
    /**
     * Journal d'audit en lecture seule (SEC-06, SEC-07)
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('audit.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de consulter le journal d\'audit.');

        if ($request->ajax()) {
            $activites = Activity::with('causer')->select('activity_log.*');

            return DataTables::of($activites)
                ->editColumn('created_at', fn ($a) => $a->created_at->format('d/m/Y H:i:s'))
                ->addColumn('auteur', fn ($a) => $a->causer->name ?? 'Système')
                ->addColumn('action', fn ($a) => ['created' => 'Création', 'updated' => 'Modification', 'deleted' => 'Suppression'][$a->event] ?? ($a->event ?: $a->description))
                ->addColumn('objet', fn ($a) => class_basename((string) $a->subject_type).' #'.$a->subject_id)
                ->addColumn('details', function ($a) {
                    $nouveau = $a->properties['attributes'] ?? [];
                    $ancien = $a->properties['old'] ?? [];
                    $lignes = [];
                    foreach ($nouveau as $champ => $valeur) {
                        $avant = array_key_exists($champ, $ancien) ? $this->texte($ancien[$champ]).' → ' : '';
                        $lignes[] = '<b>'.e($champ).'</b> : '.e($avant.$this->texte($valeur));
                    }

                    return '<div class="small">'.implode('<br>', array_slice($lignes, 0, 12)).(count($lignes) > 12 ? '<br>…' : '').'</div>';
                })
                ->filterColumn('auteur', fn ($q, $mot) => $q->whereHas('causer', fn ($c) => $c->where('name', 'like', "%{$mot}%")))
                ->filterColumn('objet', fn ($q, $mot) => $q->where('subject_type', 'like', "%{$mot}%"))
                ->rawColumns(['details'])
                ->make(true);
        }

        return view('application.parametre.audit');
    }

    private function texte($valeur): string
    {
        if (is_array($valeur)) {
            return json_encode($valeur, JSON_UNESCAPED_UNICODE);
        }

        return $valeur === null ? '∅' : Str::limit((string) $valeur, 80);
    }
}
