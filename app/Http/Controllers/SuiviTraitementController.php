<?php

namespace App\Http\Controllers;

use App\Models\SuiviTraitement;
use App\Models\Consultation;
use Illuminate\Http\Request;

class SuiviTraitementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'date_suivi'      => 'required|date',
            'evolution'       => 'required|in:Amélioration,Stagnation,Aggravation,Guérison',
            'observations'    => 'nullable|string',
            'recommandations' => 'nullable|string',
            'temperature'     => 'nullable|string',
            'tension'         => 'nullable|string',
        ]);

        SuiviTraitement::create($validated);

        return response()->json(['success' => true, 'message' => 'Suivi enregistré avec succès !']);
    }

    public function getByConsultation($id)
    {
        $suivis = SuiviTraitement::where('consultation_id', $id)->orderBy('date_suivi', 'desc')->get();
        return response()->json($suivis);
    }
}
