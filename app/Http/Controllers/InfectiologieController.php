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

    public function pathologies()
    {
        abort_unless(Auth::user()->can('infectiologie.view'), 403, 'Accès non autorisé.');

        $pathologies = \App\Models\Maladie::with(['protocole', 'symptomes'])
            ->withCount('consultations')
            ->get();

        return view('application.infectiologie.pathologies', compact('pathologies'));
    }

    public function pathogenes()
    {
        abort_unless(Auth::user()->can('infectiologie.view'), 403, 'Accès non autorisé.');

        $pathologies = \App\Models\Maladie::with(['protocole', 'symptomes'])->get();
        return view('application.infectiologie.pathogenes', compact('pathologies'));
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
