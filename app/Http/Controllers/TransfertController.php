<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Hospitalisation;
use App\Models\Transfert;
use App\Models\User;
use App\Models\ServiceMedical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransfertController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'type' => 'required|in:medecin,service,hopital_externe',
            'motif' => 'required|string',
            'consultation_id' => 'nullable|exists:consultations,id',
            'hospitalisation_id' => 'nullable|exists:hospitalisations,id',
            'dest_medecin_id' => 'required_if:type,medecin|nullable|exists:users,id',
            'dest_service_id' => 'required_if:type,service|nullable|exists:service_medicals,id',
            'hopital_destination' => 'required_if:type,hopital_externe|nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $transfert = new Transfert();
            $transfert->patient_id = $request->patient_id;
            $transfert->type = $request->type;
            $transfert->motif = $request->motif;
            $transfert->date_transfert = now();
            $transfert->user_id = Auth::id();
            $transfert->consultation_id = $request->consultation_id ?: null;
            $transfert->hospitalisation_id = $request->hospitalisation_id ?: null;

            if ($request->type === 'medecin') {
                if (!$transfert->consultation_id) {
                    throw new \Exception("Une consultation est requise pour transférer à un autre médecin.");
                }
                $consultation = Consultation::findOrFail($transfert->consultation_id);
                $transfert->source_medecin_id = $consultation->medecin_id;
                $transfert->dest_medecin_id = $request->dest_medecin_id;

                // Update consultation medecin
                $consultation->medecin_id = $request->dest_medecin_id;
                $consultation->save();
            } else {
                // Pour service ou hopital_externe, on a besoin d'une hospitalisation
                $hId = $transfert->hospitalisation_id;
                if (!$hId && $transfert->consultation_id) {
                    $h = Hospitalisation::where('consultation_id', $transfert->consultation_id)
                        ->where('etat', 'en cours')
                        ->first();
                    $hId = $h?->id;
                }

                if (!$hId) {
                    throw new \Exception("Le transfert vers un service ou un autre hôpital nécessite une hospitalisation en cours.");
                }

                $hospitalisation = Hospitalisation::findOrFail($hId);
                $transfert->hospitalisation_id = $hId;

                if ($request->type === 'service') {
                    $transfert->source_service_id = $hospitalisation->service_id;
                    $transfert->dest_service_id = $request->dest_service_id;

                    // Update hospitalisation service
                    $hospitalisation->service_id = $request->dest_service_id;
                    $hospitalisation->save();
                } elseif ($request->type === 'hopital_externe') {
                    $transfert->hopital_destination = $request->hopital_destination;
                    
                    $hospitalisation->etat = 'terminé';
                    $hospitalisation->date_sortie = now();
                    $hospitalisation->observations = ($hospitalisation->observations ? $hospitalisation->observations . "\n" : "") . "Transféré vers: " . $request->hopital_destination . ". Motif: " . $request->motif;
                    $hospitalisation->save();

                    // Libérer le lit si présent
                    if ($hospitalisation->lit_id) {
                        \App\Models\Lit::where('id', $hospitalisation->lit_id)->update(['statut' => 'Libre']);
                    }
                }
            }

            $transfert->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transfert enregistré avec succès ✅',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du transfert : ' . $e->getMessage(),
            ], 500);
        }
    }
}
