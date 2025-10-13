<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Maladie;
use App\Models\ProtocoleTraitement;

class InfectiologieController extends Controller
{
    public function statistiques()
    {
        abort_unless(Auth::user()->can('infectiologie.view'), 403, 'Accès non autorisé : vous n\'avez pas accès au module infectiologie.');

        // Top 5 maladies
        $topMaladies = \App\Models\Maladie::withCount('consultations')
            ->orderBy('consultations_count', 'desc')
            ->take(5)
            ->get();

        // Consultations par mois (pour les maladies infectieuses)
        $monthlyStats = \App\Models\Consultation::selectRaw('COUNT(*) as count, MONTH(date_consultation) as month')
            ->whereHas('maladies')
            ->groupBy('month')
            ->get();

        // Stock antibiotiques critiques (utilisation de la famille 'Antibiotiques')
        $atbFamille = \App\Models\Famille::where('nom', 'like', 'Antibiotique%')->first();
        $lowStockCount = \App\Models\Medicament::when($atbFamille, function($q) use ($atbFamille) {
                $q->where('famille_id', $atbFamille->id);
            })
            ->whereColumn('stock', '<=', 'stock_min')
            ->count();

        return view('application.infectiologie.statistiques', compact('topMaladies','monthlyStats', 'lowStockCount'));
    }

    public function pathologies(\Illuminate\Http\Request $request)
    {
        abort_unless(Auth::user()->can('infectiologie.view'), 403, 'Accès non autorisé.');

        $search = $request->input('search');

        $query = \App\Models\Maladie::actives()->with(['protocole', 'symptomes'])
            ->withCount('consultations');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('symptomes', function ($q2) use ($search) {
                      $q2->where('nom', 'like', "%{$search}%");
                  });
            });
        }

        $pathologies = $query->orderBy('nom')->paginate(12)->withQueryString();

        return view('application.infectiologie.pathologies', compact('pathologies', 'search'));
    }

    public function exportPathologies(string $format)
    {
        abort_unless(Auth::user()->can('infectiologie.view'), 403, 'Accès non autorisé.');
        abort_unless(in_array($format, ['xlsx', 'pdf']), 404);

        $pathologies = Maladie::actives()->with(['protocole', 'symptomes'])
            ->withCount('consultations')
            ->orderBy('nom')
            ->get();

        $filename = 'pathologies_infectieuses_' . now()->format('Ymd_His');

        if ($format === 'pdf') {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('application.infectiologie.pathologies_pdf', compact('pathologies'))
                ->setPaper('a4', 'landscape')
                ->download($filename . '.pdf');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Pathologies');

        $headers = ['Pathologie', 'Description', 'Symptômes', 'Protocole', 'Traitement principal', 'Posologie principale', 'Cas enregistrés'];
        $sheet->fromArray($headers, null, 'A1');

        $rows = $pathologies->map(fn ($p) => [
            $p->nom,
            $p->description,
            $p->symptomes->pluck('nom')->implode(', '),
            $p->protocole->titre ?? '',
            $p->protocole->traitement_principal ?? '',
            $p->protocole->posologie_principale ?? '',
            $p->consultations_count,
        ])->all();
        $sheet->fromArray($rows, null, 'A2');

        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0665D0']],
        ]);
        foreach (['A' => 28, 'B' => 50, 'C' => 40, 'D' => 30, 'E' => 35, 'F' => 30, 'G' => 16] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->getStyle('A2:G' . (count($rows) + 1))->getAlignment()->setWrapText(true)->setVertical('top');
        $sheet->freezePane('A2');

        return response()->streamDownload(function () use ($spreadsheet) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        }, $filename . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function pathogenes()
    {
        abort_unless(Auth::user()->can('infectiologie.view'), 403, 'Accès non autorisé.');

        $pathologies = \App\Models\Maladie::actives()->with(['protocole', 'symptomes'])->get();
        return view('application.infectiologie.pathogenes', compact('pathologies'));
    }

    /**
     * Liste JSON des maladies, utilisée par l'aide à la prescription.
     */
    public function pathologiesApi()
    {
        abort_unless(
            Auth::user()->can('infectiologie.view') || Auth::user()->can('infectiologie.aide_prescription'),
            403,
            'Accès non autorisé.'
        );

        return response()->json(Maladie::actives()->ordreGuide()->get());
    }

    public function protocoles()
    {
        abort_unless(Auth::user()->can('infectiologie.view'), 403, 'Accès non autorisé.');

        return view('application.infectiologie.protocoles');
    }

    public function showProtocole(ProtocoleTraitement $protocole)
    {
        abort_unless(Auth::user()->can('infectiologie.view'), 403, 'Accès non autorisé.');

        $protocole->load(['maladie', 'lignes.fiche']);
        $produits = $protocole->lignes->mapWithKeys(fn($l) => [$l->id => $l->produitEnStock()]);

        return view('application.infectiologie.show', compact('protocole', 'produits'));
    }

    public function storeProtocole(Request $request)
    {
        abort_unless(Auth::user()->can('infectiologie.aide_prescription'), 403, 'Accès non autorisé : vous n\'avez pas la permission de gérer les protocoles.');

        $request->validate([
            'maladie_id' => 'required|exists:maladies,id',
            'titre' => 'required|string',
            'medicaments_ids' => 'nullable|array',
            'medicaments_ids.*' => 'exists:medicaments,id'
        ]);

        $protocole = ProtocoleTraitement::updateOrCreate(
            ['maladie_id' => $request->maladie_id],
            $request->except(['_token', 'medicaments_ids'])
        );

        // Le protocole est commun à la plateforme : on enregistre le nom et la fiche du
        // guide des produits choisis, pas le produit du stock de cet établissement
        if ($request->has('medicaments_ids')) {
            $protocole->lignes()->where('type', 'principal')->delete();
            foreach (\App\Models\Medicament::whereIn('id', $request->medicaments_ids)->get() as $medicament) {
                $protocole->lignes()->create([
                    'nom_medicament'      => $medicament->nom,
                    'fiche_medicament_id' => $medicament->fiche_medicament_id,
                    'type'                => 'principal',
                    'posologie'           => $request->posologie_principale ?? 'Selon protocole',
                    'duree'               => '7 jours',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Protocole expert enregistré avec succès.');
    }

    public function getMedicaments(Request $request)
    {
        abort_unless(Auth::user()->can('infectiologie.view'), 403, 'Accès non autorisé.');

        $search = $request->q;
        $medicaments = \App\Models\Medicament::select('id', 'nom')
            ->when($search, function($query) use ($search) {
                return $query->where('nom', 'like', "%{$search}%");
            })
            ->take(50)
            ->get();
            
        return response()->json($medicaments);
    }

    public function destroyProtocole(ProtocoleTraitement $protocole)
    {
        abort_unless(Auth::user()->can('infectiologie.aide_prescription'), 403, 'Accès non autorisé : vous n\'avez pas la permission de supprimer un protocole.');

        $protocole->delete();
        return redirect()->back()->with('success', 'Protocole supprimé avec succès.');
    }



    public function aidePrescription()
    {
        abort_unless(Auth::user()->can('infectiologie.aide_prescription'), 403, 'Accès non autorisé.');

        return view('application.infectiologie.aide_prescription');
    }

    /** Nombre de jours sans activité au-delà duquel un patient est considéré « perdu de vue ». */
    private const SUIVI_JOURS_PERDU_DE_VUE = 30;

    public function suivi(Request $request)
    {
        abort_unless(Auth::user()->can('infectiologie.suivi'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir le suivi.');

        $data = $this->buildSuiviCohorte($request);
        $data['maladies'] = Maladie::whereHas('protocole')->orderBy('nom')->get(['id', 'nom']);
        $data['maladieId'] = $request->input('maladie_id');
        $data['joursPerduDeVue'] = self::SUIVI_JOURS_PERDU_DE_VUE;

        return view('application.infectiologie.suivi', $data);
    }

    public function suiviPdf(Request $request)
    {
        abort_unless(Auth::user()->can('infectiologie.suivi'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir le suivi.');

        $data = $this->buildSuiviCohorte($request);
        $data['annee'] = session('exercice_year', date('Y'));
        $data['maladieFiltre'] = $request->filled('maladie_id') ? Maladie::find($request->maladie_id) : null;

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('application.infectiologie.suivi_pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('suivi_cohorte_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Construit la cohorte : une ligne par patient ET par pathologie (et non par consultation).
     * Sources d'évolution : suivis_traitements (évolution cotée) et rendez-vous de suivi réalisés.
     */
    private function buildSuiviCohorte(Request $request): array
    {
        $year = session('exercice_year', date('Y'));
        $maladieId = $request->input('maladie_id');

        $consultations = \App\Models\Consultation::with([
                'patient',
                'maladies.protocole',
                'suivis',
                'rendezVous',
            ])
            ->whereYear('date_consultation', $year)
            ->whereHas('patient', fn ($q) => $q->forUserStructure(Auth::user()))
            ->whereHas('maladies', function ($q) use ($maladieId) {
                $q->whereHas('protocole');
                if ($maladieId) {
                    $q->where('maladies.id', $maladieId);
                }
            })
            ->orderBy('date_consultation')
            ->get();

        $now = now();
        $cas = [];

        foreach ($consultations as $c) {
            foreach ($c->maladies as $maladie) {
                if (!$maladie->protocole || ($maladieId && $maladie->id != $maladieId)) {
                    continue;
                }

                $key = $c->patient_id . '-' . $maladie->id;
                $cas[$key] ??= [
                    'patient' => $c->patient,
                    'maladie' => $maladie,
                    'protocole' => $maladie->protocole,
                    'debut' => \Carbon\Carbon::parse($c->date_consultation),
                    'consultation_uuid' => $c->uuid,
                    'nb_consultations' => 0,
                    'evenements' => [],      // [date, evolution|null]
                    'dernier_contact' => \Carbon\Carbon::parse($c->date_consultation),
                    'prochain_rdv' => null,
                ];

                $cas[$key]['nb_consultations']++;
                $cas[$key]['consultation_uuid'] = $c->uuid; // dernière consultation (tri croissant)
                $consultDate = \Carbon\Carbon::parse($c->date_consultation);
                if ($consultDate->gt($cas[$key]['dernier_contact'])) {
                    $cas[$key]['dernier_contact'] = $consultDate;
                }

                foreach ($c->suivis as $sv) {
                    $d = \Carbon\Carbon::parse($sv->date_suivi);
                    $cas[$key]['evenements'][] = [$d, $sv->evolution];
                    if ($d->gt($cas[$key]['dernier_contact'])) {
                        $cas[$key]['dernier_contact'] = $d;
                    }
                }

                foreach ($c->rendezVous as $rdv) {
                    $d = \Carbon\Carbon::parse($rdv->date_heure);
                    if ($rdv->statut === 'realise' && $rdv->resultat) {
                        $cas[$key]['evenements'][] = [$d, null];
                        if ($d->gt($cas[$key]['dernier_contact'])) {
                            $cas[$key]['dernier_contact'] = $d;
                        }
                    } elseif (in_array($rdv->statut, ['prevu', 'en_attente']) && $d->gte($now->copy()->startOfDay())) {
                        if (!$cas[$key]['prochain_rdv'] || $d->lt($cas[$key]['prochain_rdv'])) {
                            $cas[$key]['prochain_rdv'] = $d;
                        }
                    }
                }
            }
        }

        $atteinte = 0;
        $evalues = 0;

        $cohorte = collect($cas)->map(function ($c) use ($now, &$atteinte, &$evalues) {
            // Dernière évolution cotée (les rendez-vous réalisés comptent comme contact mais n'ont pas de cotation)
            $cotes = collect($c['evenements'])->filter(fn ($e) => $e[1])->sortByDesc(fn ($e) => $e[0]->timestamp);
            $c['evolution'] = $cotes->first()[1] ?? null;
            $c['nb_suivis'] = count($c['evenements']);

            if ($c['evolution']) {
                $evalues++;
                if (in_array($c['evolution'], ['Amélioration', 'Guérison'])) {
                    $atteinte++;
                }
            }

            if ($c['evolution'] === 'Guérison') {
                $c['statut'] = 'Guéri';
            } elseif ($c['dernier_contact']->diffInDays($now) > self::SUIVI_JOURS_PERDU_DE_VUE && !$c['prochain_rdv']) {
                $c['statut'] = 'Perdu de vue';
            } else {
                $c['statut'] = 'En cours';
            }

            return $c;
        })->sortByDesc(fn ($c) => $c['dernier_contact']->timestamp)->values();

        return [
            'cohorte' => $cohorte,
            'totalCas' => $cohorte->count(),
            'enCours' => $cohorte->where('statut', 'En cours')->count(),
            'perdusDeVue' => $cohorte->where('statut', 'Perdu de vue')->count(),
            'gueris' => $cohorte->where('statut', 'Guéri')->count(),
            'evalues' => $evalues,
            'atteinte' => $atteinte,
            'tauxAtteinte' => $evalues > 0 ? round(($atteinte / $evalues) * 100) : null,
        ];
    }

    public function getProtocole($maladieId)
    {
        // Utilisé à la fois par le module infectiologie et par la suggestion
        // automatique de protocole lors de la création d'une consultation.
        abort_unless(
            Auth::user()->can('infectiologie.view') || Auth::user()->can('consultations.create'),
            403,
            'Accès non autorisé.'
        );

        // Chaque médicament du protocole est proposé avec le produit du stock de
        // l'établissement relié à sa fiche ; sans produit, il est cité par son nom seul
        $protocoles = ProtocoleTraitement::with('lignes.fiche')->where('maladie_id', $maladieId)->where('actif', true)->get();
        // Références des molécules (liste OMS, contre-indications, noms commerciaux) du guide clinique
        $references = \App\Models\DciReference::parDci($protocoles->flatMap->lignes
            ->map(fn($l) => \App\Services\Referentiel\ImportGuideClinique::dciDeLigne((string) $l->nom_medicament))->unique()->values()->all());

        $protocoles = $protocoles->map(function ($protocole) use ($references) {
                $donnees = $protocole->toArray();
                unset($donnees['lignes']);
                $donnees['medicaments'] = $protocole->lignes->map(function ($ligne) use ($references) {
                    $produit = $ligne->produitEnStock();
                    $reference = $references[mb_strtolower(\App\Services\Referentiel\ImportGuideClinique::dciDeLigne((string) $ligne->nom_medicament))] ?? null;

                    return [
                        'id'         => $produit?->id,
                        'nom'        => $produit?->nom ?? $ligne->nom_medicament,
                        'dci'        => $ligne->nom_medicament,
                        'prix_vente' => $produit?->prix_vente,
                        'stock'      => $produit?->stock,
                        'fiche'      => $ligne->fiche ? ['uuid' => $ligne->fiche->uuid, 'nom' => $ligne->fiche->nom] : null,
                        'pivot'      => ['type' => $ligne->type, 'posologie' => $ligne->posologie, 'duree' => $ligne->duree],
                        'alerte'     => $ligne->alerte,
                        'reference'  => $reference ? [
                            'liste_oms'          => $reference->liste_oms,
                            'posologie'          => $reference->posologie_reference,
                            'contre_indications' => $reference->contre_indications ?? $ligne->fiche?->precautions,
                            'noms_commerciaux'   => $reference->noms_commerciaux,
                        ] : null,
                    ];
                })->values();

                return $donnees;
            });
        if ($protocoles->count() > 0) {
            $maladie = \App\Models\Maladie::find($maladieId);

            return response()->json([
                'success' => true,
                'protocoles' => $protocoles,
                'maladie' => $maladie ? [
                    'uuid'     => $maladie->uuid,
                    'nom'      => $maladie->nom,
                    'chapitre' => $maladie->chapitre,
                    'gravite'  => $maladie->signesGraviteListe(),
                    'alertes'  => $maladie->alertesOms(),
                    'examens'  => $maladie->examens,
                    'traitement' => $maladie->traitement,
                    'guide_url' => \Illuminate\Support\Facades\Route::has('guide-clinique.show') ? route('guide-clinique.show', $maladie) : null,
                ] : null,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun protocole défini pour cette pathologie.'
        ]);
    }
}
