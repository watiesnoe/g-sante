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

        // Consultations par mois (pour les maladies infectieuses)
        $monthlyStats = \App\Models\Consultation::selectRaw('COUNT(*) as count, MONTH(date_consultation) as month')
            ->whereHas('maladies')
            ->groupBy('month')
            ->get();

        // Stock antibiotiques critiques (colonnes correctes: stock et stock_min)
        $lowStockCount = \App\Models\Medicament::where(function($q) {
                $q->where('nom', 'like', '%Amoxi%')
                  ->orWhere('nom', 'like', '%Ceftri%')
                  ->orWhere('nom', 'like', '%Cipro%')
                  ->orWhere('nom', 'like', '%Lumef%');
            })
            ->whereColumn('stock', '<=', 'stock_min')
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
        $protocole = ProtocoleTraitement::with(['maladie', 'medicaments'])->findOrFail($id);
        return view('application.infectiologie.show', compact('protocole'));
    }

    public function storeProtocole(Request $request)
    {
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

    public function getMedicaments()
    {
        return response()->json(\App\Models\Medicament::select('id', 'nom')->get());
    }

    public function destroyProtocole($id)
    {
        $protocole = ProtocoleTraitement::findOrFail($id);
        $protocole->delete();

        return redirect()->back()->with('success', 'Protocole supprimé avec succès.');
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
        // Consultations liées à au moins une maladie (via la table pivot)
        $suivis = \App\Models\Consultation::with(['patient', 'maladies.protocole'])
            ->whereHas('maladies', function($q) {
                $q->whereHas('protocole');
            })
            ->orderBy('date_consultation', 'desc')
            ->get();

        return view('application.infectiologie.suivi', compact('suivis'));
    }

    public function getProtocole($maladieId)
    {
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
