<?php

namespace App\Http\Controllers;

use App\Models\SuiviTraitement;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuiviTraitementController extends Controller
{
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('consultations.suivi'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer un suivi.');

        $validated = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'date_suivi'      => 'required|date|before_or_equal:today',
            'evolution'       => 'required|in:Amélioration,Stagnation,Aggravation,Guérison',
            'observations'    => 'nullable|string',
            'recommandations' => 'nullable|string',
            'temperature'     => 'nullable|string',
            'tension'         => 'nullable|string',
        ]);

        // Un suivi ne peut pas précéder la consultation qu'il suit
        $consultation = Consultation::findOrFail($validated['consultation_id']);
        if (\Carbon\Carbon::parse($validated['date_suivi'])->startOfDay()->lt(\Carbon\Carbon::parse($consultation->date_consultation)->startOfDay())) {
            return response()->json([
                'success' => false,
                'message' => 'La date du suivi ne peut pas être antérieure à la consultation.',
            ], 422);
        }

        SuiviTraitement::create($validated);

        return response()->json(['success' => true, 'message' => 'Suivi enregistré avec succès !']);
    }

    public function getByConsultation($id)
    {
        abort_unless(Auth::user()->can('consultations.suivi'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les suivis.');

        $suivis = SuiviTraitement::where('consultation_id', $id)->orderBy('date_suivi', 'desc')->get();
        return response()->json($suivis);
    }
}
