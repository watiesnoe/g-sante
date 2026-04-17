<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Maladie;
use App\Models\ProtocoleTraitement;

class InfectiologieController extends Controller
{
    public function statistiques()
    {
        // Top 5 maladies
        $topMaladies = \App\Models\Maladie::withCount('consultations')
            ->orderBy('consultations_count', 'desc')
            ->take(5)
            ->get();

        // Consultations par mois (simulé ou réel si les données existent)
        $monthlyStats = \App\Models\Consultation::selectRaw('COUNT(*) as count, MONTH(date_consultation) as month')
            ->whereNotNull('protocole_id')
            ->groupBy('month')
            ->get();

        // Stock antibiotiques critiques
        $lowStockCount = \App\Models\Medicament::where(function($q) {
                $q->where('nom', 'like', '%Amoxi%')
                  ->orWhere('nom', 'like', '%Ceftri%')
                  ->orWhere('nom', 'like', '%Cipro%')
                  ->orWhere('nom', 'like', '%Lumef%');
            })
            ->whereColumn('stock_actuel', '<=', 'stock_mini')
            ->count();

        return view('application.infectiologie.statistiques', compact('topMaladies','monthlyStats', 'lowStockCount'));
    }

    public function pathologies()
    {
        $pathologies = \App\Models\Maladie::with(['protocole', 'symptomes'])
            ->withCount('consultations')
            ->get();

        return view('application.infectiologie.pathologies', compact('pathologies'));
    }

    public function pathogenes()
    {
        $pathologies = \App\Models\Maladie::with(['protocole', 'symptomes'])->get();
        return view('application.infectiologie.pathogenes', compact('pathologies'));
    }

    public function protocoles()
    {
        return view('application.infectiologie.protocoles');
    }

    public function showProtocole($id)
    {
        $protocole = ProtocoleTraitement::with('maladie')->findOrFail($id);
        return view('application.infectiologie.show', compact('protocole'));
    }

    public function storeProtocole(Request $request)
    {
        $request->validate([
            'maladie_id' => 'required|exists:maladies,id',
            'titre' => 'required|string',
        ]);

        ProtocoleTraitement::updateOrCreate(
            ['maladie_id' => $request->maladie_id],
            $request->except('_token')
        );

        return redirect()->back()->with('success', 'Protocole expert enregistré avec succès.');
    }

    public function antibiotiques()
    {
        $famillesIds = \App\Models\Famille::whereIn('nom', ['Antibiotiques', 'Antipaludiques'])->pluck('id');
        $antibiotiques = \App\Models\Medicament::with(['unite', 'famille'])
            ->whereIn('famille_id', $famillesIds)
            ->get();

        return view('application.infectiologie.antibiotiques', compact('antibiotiques'));
    }

    public function aidePrescription()
    {
        return view('application.infectiologie.aide_prescription');
    }

    public function suivi()
    {
        $suivis = \App\Models\Consultation::with(['patient', 'protocole', 'maladies'])
            ->whereNotNull('protocole_id')
            ->orderBy('date_consultation', 'desc')
            ->get();

        return view('application.infectiologie.suivi', compact('suivis'));
    }

    public function getProtocole($maladieId)
    {
        $protocoles = ProtocoleTraitement::where('maladie_id', $maladieId)->get();
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
