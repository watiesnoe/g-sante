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

        $query = \App\Models\Maladie::with(['protocole', 'symptomes'])
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

        $pathologies = Maladie::with(['protocole', 'symptomes'])
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

        $pathologies = \App\Models\Maladie::with(['protocole', 'symptomes'])->get();
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

        return response()->json(Maladie::all());
    }

    public function protocoles()
    {
        abort_unless(Auth::user()->can('infectiologie.view'), 403, 'Accès non autorisé.');

        return view('application.infectiologie.protocoles');
    }

    public function showProtocole(ProtocoleTraitement $protocole)
    {
        abort_unless(Auth::user()->can('infectiologie.view'), 403, 'Accès non autorisé.');

        $protocole->load(['maladie', 'medicaments']);
        return view('application.infectiologie.show', compact('protocole'));
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

        // Synchronisation des médicaments dans la table pivot
        if ($request->has('medicaments_ids')) {
            $syncData = [];
            foreach ($request->medicaments_ids as $medId) {
                $syncData[$medId] = [
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'principal',
                    'posologie' => $request->posologie_principale ?? 'Selon protocole',
                    'duree' => '7 jours'
                ];
            }
            $protocole->medicaments()->sync($syncData);
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

    public function suivi()
    {
        abort_unless(Auth::user()->can('infectiologie.suivi'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir le suivi.');

        // Consultations liées à au moins une maladie (via la table pivot)
        $suivis = \App\Models\Consultation::with(['patient', 'maladies.protocole', 'suivis' => function($q) {
            $q->orderBy('date_suivi', 'desc');
        }])
            ->whereHas('maladies', function($q) {
                $q->whereHas('protocole');
            })
            ->orderBy('date_consultation', 'desc')
            ->get();

        // Calcul du Taux d'atteinte cible (Amélioration ou Guérison)
        $totalSuivis = $suivis->count();
        $atteinte = 0;
        
        foreach ($suivis as $s) {
            $latestEvolution = $s->suivis->first()->evolution ?? null;
            if (in_array($latestEvolution, ['Amélioration', 'Guérison'])) {
                $atteinte++;
            }
        }
        
        $tauxAtteinte = $totalSuivis > 0 ? round(($atteinte / $totalSuivis) * 100) : 0;

        return view('application.infectiologie.suivi', compact('suivis', 'tauxAtteinte'));
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

        $protocoles = ProtocoleTraitement::with('medicaments')->where('maladie_id', $maladieId)->get();
        if ($protocoles->count() > 0) {
            return response()->json([
                'success' => true,
                'protocoles' => $protocoles
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Aucun protocole défini pour cette pathologie.'
        ]);
    }
}
