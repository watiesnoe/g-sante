<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\ServiceMedical;
use App\Services\RapportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/** EF-13.02 : rapports filtrés par période et service, export Excel et PDF. */
class RapportController extends Controller
{
    public function index(Request $request, RapportService $service)
    {
        abort_unless(Auth::user()->can('rapports.view'), 403, 'Accès non autorisé.');

        [$du, $au, $serviceId] = $this->filtres($request);
        $rapport = $service->generer($du, $au, $serviceId);

        return match ($request->format) {
            'xlsx'  => $this->excel($service->lignes($rapport), $du, $au),
            'pdf'   => Pdf::loadView('application.rapport.pdf', compact('rapport') + ['service' => ServiceMedical::find($serviceId)])
                ->setPaper('a4')->download('rapport_' . $du->format('Ymd') . '_' . $au->format('Ymd') . '.pdf'),
            default => view('application.rapport.index', [
                'rapport'   => $rapport,
                'services'  => ServiceMedical::orderBy('nom')->get(['id', 'nom']),
                'serviceId' => $serviceId,
            ]),
        };
    }

    private function filtres(Request $request): array
    {
        $request->validate(['du' => 'nullable|date', 'au' => 'nullable|date|after_or_equal:du', 'service_id' => 'nullable|integer']);

        return [
            $request->du ? Carbon::parse($request->du) : today()->startOfMonth(),
            $request->au ? Carbon::parse($request->au) : today(),
            $request->integer('service_id') ?: null,
        ];
    }

    private function excel(array $lignes, Carbon $du, Carbon $au)
    {
        $classeur = new Spreadsheet();
        $feuille = $classeur->getActiveSheet()->setTitle('Rapport');
        $feuille->setCellValue('A1', Etablissement::courant()->nom . ' — rapport du ' . $du->format('d/m/Y') . ' au ' . $au->format('d/m/Y'));
        $feuille->fromArray($lignes, null, 'A3');
        foreach (['A', 'B', 'C'] as $col) {
            $feuille->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(fn() => (new Xlsx($classeur))->save('php://output'),
            'rapport_' . $du->format('Ymd') . '_' . $au->format('Ymd') . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
