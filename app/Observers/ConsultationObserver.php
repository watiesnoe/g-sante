<?php

namespace App\Observers;

use App\Models\Consultation;
use App\Models\Suivi;
use Carbon\Carbon;

class ConsultationObserver
{
    public function created(Consultation $consultation)
    {
        $date_base = Carbon::parse($consultation->date_heure);

        // calculer date selon fréquence
        if($consultation->frequence_suivi === 'mois'){
            $date_suivi = $date_base->addMonth();
        } else {
            $date_suivi = $date_base->addWeek();
        }

        if($consultation->jours_suivi){
            $date_suivi->addDays($consultation->jours_suivi);
        }

        Suivi::create([
            'consultation_id' => $consultation->id,
            'patient_id'      => $consultation->patient_id,
            'medecin_id'      => $consultation->medecin_id,
            'date_heure'      => $date_suivi,
            'motif'           => 'Suivi post-consultation',
            'resultat'        => 'À remplir par le médecin',
            'statut'          => 'prévu'
        ]);
    }
}
