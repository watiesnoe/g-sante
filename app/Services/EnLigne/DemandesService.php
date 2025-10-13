<?php

namespace App\Services\EnLigne;

use App\Models\DemandeEnLigne;
use App\Models\Etablissement;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\SynchroSortante;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Serveur local : demandes de rendez-vous venues du serveur en ligne (RG-17).
 * Une demande ne devient un rendez-vous qu'après confirmation par l'accueil ;
 * en cas de conflit, ce qui a été fait sur place l'emporte et le patient est prévenu.
 */
class DemandesService
{
    public function __construct(private CreneauxService $creneaux, private SmsService $sms) {}

    /** Applique un événement reçu du serveur en ligne. */
    public function appliquer(Etablissement $etablissement, string $type, array $donnees): void
    {
        match ($type) {
            'demande.creee'   => $this->recevoir($etablissement, $donnees),
            'demande.annulee' => $this->annulationPatient($donnees['uuid'] ?? ''),
            'demande.acceptee' => $this->propositionAcceptee($donnees),
            default           => null, // type inconnu (serveur en ligne plus récent) : ignoré
        };
    }

    public function recevoir(Etablissement $etablissement, array $d): DemandeEnLigne
    {
        if ($existante = DemandeEnLigne::where('uuid', $d['uuid'])->first()) {
            return $existante; // déjà reçue (événement renvoyé après une coupure)
        }

        $medecin = User::deLEtablissement()->where('uuid', $d['medecin_uuid'] ?? null)->first();
        $creneau = Carbon::parse($d['creneau']);
        $demande = DemandeEnLigne::create([
            'uuid'              => $d['uuid'],
            'etablissement_id'  => $etablissement->id,
            'medecin_id'        => $medecin?->id,
            'creneau'           => $creneau,
            'motif'             => $d['motif'] ?? null,
            'patient_nom'       => $d['patient_nom'],
            'patient_prenom'    => $d['patient_prenom'],
            'patient_telephone' => $d['patient_telephone'],
            'remplace_uuid'     => $d['remplace_uuid'] ?? null,
            'statut'            => 'recue',
        ]);
        // Un déplacement garde le dossier déjà rattaché à la demande remplacée
        if ($demande->remplace_uuid) {
            $demande->update(['patient_id' => DemandeEnLigne::where('uuid', $demande->remplace_uuid)->value('patient_id')]);
        }

        // REC-20 : le créneau a été pris sur place pendant la coupure → le local fait foi
        if (!$medecin || $this->creneaux->creneauPris($medecin->id, $creneau)) {
            $this->decider($demande, 'conflit', [
                'message' => 'Ce créneau a été attribué entre-temps. Merci de choisir un autre horaire.',
            ], 'Votre demande de rendez-vous du ' . $creneau->format('d/m à H\hi') . ' ne peut pas être retenue : le créneau n\'est plus libre. Choisissez un autre horaire en ligne.');
        }

        return $demande;
    }

    /** Le patient accepte le créneau proposé : la demande revient à l'accueil sur ce créneau. */
    public function propositionAcceptee(array $d): void
    {
        $demande = DemandeEnLigne::where('uuid', $d['uuid'] ?? null)->where('statut', 'proposee')->first();
        if (!$demande) {
            return;
        }
        $demande->update(['creneau' => Carbon::parse($d['creneau']), 'creneau_propose' => null, 'statut' => 'recue', 'message' => null]);
        if (!$demande->medecin_id || $this->creneaux->creneauPris($demande->medecin_id, $demande->creneau)) {
            $this->decider($demande, 'conflit', ['message' => 'Ce créneau a été attribué entre-temps. Merci de choisir un autre horaire.'],
                'Le créneau du ' . $demande->creneau->format('d/m à H\hi') . ' n\'est plus libre. Choisissez un autre horaire en ligne.');
        }
    }

    /** Le patient a annulé en ligne ; un rendez-vous déjà confirmé est annulé aussi. */
    public function annulationPatient(string $uuid): void
    {
        $demande = DemandeEnLigne::where('uuid', $uuid)->first();
        if (!$demande || in_array($demande->statut, ['annulee', 'refusee', 'conflit'], true)) {
            return;
        }
        DB::transaction(function () use ($demande) {
            if ($demande->rendezVous && in_array($demande->rendezVous->statut, ['prevu', 'en_attente'], true)) {
                $demande->rendezVous->update(['statut' => 'annule', 'resultat' => 'Annulé par le patient en ligne']);
            }
            $demande->update(['statut' => 'annulee']);
        });
    }

    /**
     * L'accueil confirme : rendez-vous créé pour le dossier vérifié, ou pour un
     * nouveau dossier dont l'accueil fournit le sexe et l'âge (EF-14.02).
     */
    public function confirmer(DemandeEnLigne $demande, ?Patient $patient, array $nouveauDossier = []): RendezVous
    {
        $this->exigerStatut($demande, ['recue']);
        if (!$demande->medecin_id) {
            throw ValidationException::withMessages(['demande' => 'Le médecin demandé n\'est plus dans l\'établissement : proposez un autre créneau.']);
        }
        if ($this->creneaux->creneauPris($demande->medecin_id, $demande->creneau)) {
            throw ValidationException::withMessages(['demande' => 'Ce créneau est déjà pris : proposez un autre horaire au patient.']);
        }

        if (!$patient && (empty($nouveauDossier['genre']) || !isset($nouveauDossier['age']))) {
            throw ValidationException::withMessages(['patient' => 'Choisissez le dossier du patient, ou indiquez son sexe et son âge pour en créer un.']);
        }

        return DB::transaction(function () use ($demande, $patient, $nouveauDossier) {
            $patient ??= Patient::create([
                'nom'       => $demande->patient_nom,
                'prenom'    => $demande->patient_prenom,
                'telephone' => $demande->patient_telephone,
                'genre'     => $nouveauDossier['genre'],
                'age'       => (int) $nouveauDossier['age'],
            ]);
            $rdv = RendezVous::create([
                'patient_id' => $patient->id,
                'medecin_id' => $demande->medecin_id,
                'date_heure' => $demande->creneau,
                'motif'      => $demande->motif ?: 'Rendez-vous demandé en ligne',
                'statut'     => 'prevu',
            ]);
            $demande->update(['patient_id' => $patient->id, 'rendezvous_id' => $rdv->id]);
            $this->decider($demande, 'confirmee', ['rdv_uuid' => $rdv->uuid, 'creneau' => $demande->creneau->format('Y-m-d H:i:s')],
                'Rendez-vous confirmé le ' . $demande->creneau->format('d/m/Y à H\hi') . ' avec ' . ($demande->medecin->name ?? 'le médecin') . ' — ' . Etablissement::courant()->nom . '.');

            return $rdv;
        });
    }

    public function proposer(DemandeEnLigne $demande, Carbon $creneau, ?string $message): void
    {
        $this->exigerStatut($demande, ['recue', 'conflit']);
        $demande->creneau_propose = $creneau;
        $this->decider($demande, 'proposee', ['creneau_propose' => $creneau->format('Y-m-d H:i:s'), 'message' => $message],
            'Votre rendez-vous ne peut pas avoir lieu à l\'heure demandée. Nous vous proposons le ' . $creneau->format('d/m/Y à H\hi') . '. Confirmez en ligne.');
    }

    public function refuser(DemandeEnLigne $demande, string $message): void
    {
        $this->exigerStatut($demande, ['recue', 'conflit']);
        $this->decider($demande, 'refusee', ['message' => $message], 'Votre demande de rendez-vous n\'a pas pu être acceptée : ' . $message);
    }

    /** Enregistre la décision, la met en file pour le serveur en ligne et prévient le patient. */
    private function decider(DemandeEnLigne $demande, string $statut, array $details, string $sms): void
    {
        $demande->update(array_filter([
            'statut'          => $statut,
            'message'         => $details['message'] ?? null,
            'creneau_propose' => $demande->creneau_propose,
            'traite_par'      => Auth::id(),
            'traite_at'       => now(),
        ], fn($v) => $v !== null) + ['statut' => $statut]);

        SynchroSortante::ajouter($demande->etablissement_id, 'demande.statut', ['uuid' => $demande->uuid, 'statut' => $statut] + $details);
        $this->sms->envoyer($demande->patient_telephone, $sms, $demande);
    }

    private function exigerStatut(DemandeEnLigne $demande, array $statuts): void
    {
        if (!in_array($demande->statut, $statuts, true)) {
            throw ValidationException::withMessages(['demande' => 'Cette demande a déjà été traitée (' . DemandeEnLigne::STATUTS[$demande->statut] . ').']);
        }
    }
}
