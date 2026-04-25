<?php

namespace App\Http\Controllers;

use App\Models\Maladie;
use App\Models\Symptome;
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    /**
     * Interrogate the knowledge base using provided symptom IDs.
     */
    public function interrogate(Request $request)
    {
        $selectedSymptomes = $request->input('symptomes', []);
        
        if (empty($selectedSymptomes)) {
            return response()->json([]);
        }

        // Fetch all pathologies with their linked symptoms
        $maladies = Maladie::with('symptomes')->get();
        
        $results = [];
        
        foreach ($maladies as $maladie) {
            $maladieSymptomes = $maladie->symptomes->pluck('id')->toArray();
            
            if (empty($maladieSymptomes)) continue;
            
            $intersect = array_intersect($selectedSymptomes, $maladieSymptomes);
            
            if (count($intersect) > 0) {
                $score = (count($intersect) / count($maladieSymptomes)) * 100;
                
                $results[] = [
                    'id' => $maladie->id,
                    'nom' => $maladie->nom,
                    'score' => round($score, 1),
                    'matched_count' => count($intersect),
                    'total_count' => count($maladieSymptomes),
                    'matched_names' => $maladie->symptomes->whereIn('id', $intersect)->pluck('nom')->toArray()
                ];
            }
        }

        // Sort by score descending
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return response()->json(array_slice($results, 0, 10));
    }

    /**
     * Suggest further symptoms to check based on current suspicion.
     */
    public function suggestFollowUp(Request $request)
    {
        $selectedSymptomes = $request->input('symptomes', []);
        
        if (empty($selectedSymptomes)) {
            return response()->json([]);
        }

        // Get the top suspected diseases
        $topMatches = $this->interrogate($request)->getData();
        
        if (empty($topMatches)) {
            return response()->json([]);
        }

        $topMaladieId = $topMatches[0]->id;
        $maladie = Maladie::with('symptomes')->find($topMaladieId);
        
        // Find symptoms of this disease that aren't selected yet
        $recommended = $maladie->symptomes
            ->whereNotIn('id', $selectedSymptomes)
            ->map(function($s) {
                return [
                    'id' => $s->id,
                    'nom' => $s->nom
                ];
            })
            ->values();

        return response()->json($recommended);
    }
}
