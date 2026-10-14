<?php

namespace App\Services;

use App\Models\Passage;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Parcours du patient (EF-03.01, EF-03.02) : ouverture d'un passage et
 * changements de statut selon le workflow actif.
 */
class PassageService
{
    public function __construct(private WorkflowService $workflow, private NumerotationService $numerotation) {}

    /** Ouvre un passage, ou renvoie celui déjà ouvert aujourd'hui pour ce patient. */
    public function ouvrir(Patient $patient, array $donnees = []): Passage
    {
        return DB::transaction(function () use ($patient, $donnees) {
            $ouvert = Passage::where('patient_id', $patient->id)->ouverts()->whereDate('arrive_at', today())->latest('id')->first();
            if ($ouvert) {
                if (!empty($donnees['ticket_id']) && !$ouvert->ticket_id) {
                    $ouvert->update(['ticket_id' => $donnees['ticket_id']]);
                }

                return $ouvert;
            }

            $passage = Passage::create([
                'numero'             => $this->numerotation->suivant('passage'),
                'patient_id'         => $patient->id,
                'service_medical_id' => $donnees['service_medical_id'] ?? null,
                'ticket_id'          => $donnees['ticket_id'] ?? null,
                'motif'              => $donnees['motif'] ?? null,
                'statut'             => 'enregistre',
                'arrive_at'          => now(),
                'user_id'            => Auth::id(),
            ]);
            $passage->historiques()->create(['de' => null, 'vers' => 'enregistre', 'user_id' => Auth::id()]);

            return $passage;
        });
    }

    /**
     * Rattache un ticket au passage du jour du patient (encaissement avant ou
     * après la consultation) ; n'ouvre un passage que s'il n'en existe aucun.
     */
    public function rattacherTicket(Patient $patient, int $ticketId, array $donnees = []): Passage
    {
        $passage = Passage::where('patient_id', $patient->id)->whereNull('ticket_id')->whereDate('arrive_at', today())->latest('id')->first();
        if ($passage) {
            $passage->update(['ticket_id' => $ticketId]);

            return $passage;
        }

        return $this->ouvrir($patient, ['ticket_id' => $ticketId] + $donnees);
    }

    /** Change le statut d'un passage si la transition est prévue (section 7.2). */
    public function passerA(Passage $passage, string $vers, array $donnees = []): Passage
    {
        if ($passage->statut === $vers) {
            return $passage;
        }

        $this->workflow->exiger('passage', $passage->statut, $vers, Passage::STATUTS);

        return DB::transaction(function () use ($passage, $vers, $donnees) {
            $de = $passage->statut;
            $maj = ['statut' => $vers];
            if (isset($donnees['priorite'])) {
                $maj['priorite'] = (int) $donnees['priorite'];
            }
            if ($vers === 'en_consultation' && !$passage->pris_en_charge_at) {
                $maj['pris_en_charge_at'] = now();
            }
            if (in_array($vers, Passage::FINAUX, true)) {
                $maj['termine_at'] = now();
            }

            $passage->update($maj);
            $passage->historiques()->create(['de' => $de, 'vers' => $vers, 'user_id' => Auth::id()]);

            return $passage;
        });
    }

    /**
     * Fait avancer le passage si la transition est permise, sans bloquer l'acte
     * médical sinon (utilisé par la consultation, l'hospitalisation, les transferts).
     */
    public function avancerSiPossible(?Passage $passage, string $vers): void
    {
        if (!$passage || !$passage->estOuvert()) {
            return;
        }
        // Un passage encore « enregistré » passe d'abord en consultation
        if ($passage->statut !== 'en_consultation' && $vers !== 'en_consultation' && $this->workflow->autorisee('passage', $passage->statut, 'en_consultation')) {
            $this->passerA($passage, 'en_consultation');
        }
        if ($this->workflow->autorisee('passage', $passage->statut, $vers)) {
            $this->passerA($passage, $vers);
        }
    }
}
