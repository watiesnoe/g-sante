<?php

namespace App\Services\EnLigne;

use App\Models\EnLigne\Creneau;
use App\Models\EnLigne\Demande;
use App\Models\EnLigne\Medecin;
use App\Models\EnLigne\Site;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Serveur en ligne : demandes des patients et application des décisions reçues
 * des serveurs locaux. Le serveur local fait foi (RG-17) : ici, on ne fait que
 * relayer et refléter ses décisions.
 */
class PortailService
{
    /** Demande de rendez-vous sur un créneau publié par l'établissement (EF-14.03). */
    public function demander(Site $site, array $donnees): Demande
    {
        return DB::transaction(function () use ($site, $donnees) {
            $debut = Carbon::parse($donnees['creneau']);
            $creneau = Creneau::where('site_id', $site->id)->where('medecin_uuid', $donnees['medecin_uuid'])
                ->where('debut', $debut)->lockForUpdate()->first();
            if (!$creneau || !Medecin::where('site_id', $site->id)->where('uuid', $donnees['medecin_uuid'])->exists()) {
                throw ValidationException::withMessages(['creneau' => 'Ce créneau n\'est plus proposé. Choisissez-en un autre.']);
            }

            $demande = Demande::create([
                'site_id'           => $site->id,
                'medecin_uuid'      => $donnees['medecin_uuid'],
                'creneau'           => $debut,
                'motif'             => $donnees['motif'] ?? null,
                'patient_nom'       => $donnees['patient_nom'],
                'patient_prenom'    => $donnees['patient_prenom'],
                'patient_telephone' => $donnees['patient_telephone'],
                'patient_compte_id' => $donnees['patient_compte_id'] ?? null,
                'remplace_uuid'     => $donnees['remplace_uuid'] ?? null,
                'statut'            => 'en_attente',
            ]);
            // Le créneau n'est plus proposé aux autres patients
            $creneau->delete();

            $site->publier('demande.creee', $this->exporter($demande));

            return $demande;
        });
    }

    public function annuler(Demande $demande): Demande
    {
        if (!$demande->modifiable()) {
            throw ValidationException::withMessages(['demande' => 'Ce rendez-vous ne peut plus être annulé en ligne : le délai est dépassé. Appelez l\'établissement.']);
        }
        $demande->update(['statut' => 'annulee']);
        $demande->site->publier('demande.annulee', ['uuid' => $demande->uuid]);

        return $demande;
    }

    /** Déplacement (EF-14.04) : nouvelle demande sur un autre créneau, l'ancienne est annulée. */
    public function deplacer(Demande $ancienne, array $donnees): Demande
    {
        if (!$ancienne->modifiable()) {
            throw ValidationException::withMessages(['demande' => 'Ce rendez-vous ne peut plus être déplacé en ligne : le délai est dépassé. Appelez l\'établissement.']);
        }

        return DB::transaction(function () use ($ancienne, $donnees) {
            $nouvelle = $this->demander($ancienne->site, $donnees + ['remplace_uuid' => $ancienne->uuid]);
            $this->annuler($ancienne);

            return $nouvelle;
        });
    }

    /** Le patient accepte le créneau proposé par l'établissement ; l'accueil le confirme ensuite. */
    public function accepterProposition(Demande $demande): Demande
    {
        if ($demande->statut !== 'proposee' || !$demande->creneau_propose) {
            throw ValidationException::withMessages(['demande' => 'Aucune proposition à accepter.']);
        }
        $demande->update(['statut' => 'en_attente', 'creneau' => $demande->creneau_propose, 'creneau_propose' => null, 'message' => null]);
        $demande->site->publier('demande.acceptee', ['uuid' => $demande->uuid, 'creneau' => $demande->creneau->format('Y-m-d H:i:s')]);

        return $demande;
    }

    /** Applique un événement envoyé par le serveur local ; sans effet s'il a déjà été appliqué. */
    public function appliquer(Site $site, array $evenement): void
    {
        $nouveau = DB::table('en_ligne_recus')->insertOrIgnore([
            'site_id' => $site->id, 'evenement_uuid' => $evenement['uuid'], 'created_at' => now(),
        ]);
        if (!$nouveau) {
            return;
        }

        $d = $evenement['donnees'] ?? [];
        if ($evenement['type'] === 'demande.statut') {
            $demande = Demande::where('site_id', $site->id)->where('uuid', $d['uuid'] ?? null)->first();
            $demande?->update(array_merge(
                [
                    'statut'          => $d['statut'],
                    'creneau_propose' => $d['creneau_propose'] ?? null,
                    'message'         => $d['message'] ?? null,
                    'rdv_uuid'        => $d['rdv_uuid'] ?? null,
                ],
                isset($d['creneau']) ? ['creneau' => $d['creneau']] : []
            ));
        }
        // Type inconnu (serveur local plus récent) : ignoré
    }

    /** Remplace le catalogue publié par l'établissement : médecins et créneaux libres. */
    public function publierCatalogue(Site $site, array $catalogue): void
    {
        DB::transaction(function () use ($site, $catalogue) {
            $site->update(array_filter([
                'nom'       => $catalogue['etablissement']['nom'] ?? null,
                'type'      => $catalogue['etablissement']['type'] ?? null,
                'region'    => $catalogue['etablissement']['region'] ?? null,
                'district'  => $catalogue['etablissement']['district'] ?? null,
                'telephone' => $catalogue['etablissement']['telephone'] ?? null,
            ]) + [
                'derniere_synchro_at'     => now(),
                'delai_annulation_heures' => (int) ($catalogue['etablissement']['delai_annulation_heures'] ?? 24),
            ]);

            $site->medecins()->delete();
            foreach ($catalogue['medecins'] ?? [] as $m) {
                $site->medecins()->create(['uuid' => $m['uuid'], 'nom' => $m['nom'], 'service' => $m['service'] ?? null]);
            }

            // Les créneaux déjà demandés en ligne et pas encore traités restent retirés
            $pris = $site->demandes()->whereIn('statut', ['en_attente', 'confirmee'])->get(['medecin_uuid', 'creneau'])
                ->map(fn($d) => $d->medecin_uuid . '|' . $d->creneau->format('Y-m-d H:i:s'))->flip();
            $site->creneaux()->delete();
            $lignes = collect($catalogue['creneaux'] ?? [])
                ->map(fn($c) => ['site_id' => $site->id, 'medecin_uuid' => $c['medecin_uuid'], 'debut' => Carbon::parse($c['debut'])->format('Y-m-d H:i:s')])
                ->reject(fn($c) => $pris->has($c['medecin_uuid'] . '|' . $c['debut']));
            foreach ($lignes->chunk(500) as $paquet) {
                Creneau::insert($paquet->values()->all());
            }
        });
    }

    public function exporter(Demande $demande): array
    {
        return [
            'uuid'              => $demande->uuid,
            'medecin_uuid'      => $demande->medecin_uuid,
            'creneau'           => $demande->creneau->format('Y-m-d H:i:s'),
            'motif'             => $demande->motif,
            'patient_nom'       => $demande->patient_nom,
            'patient_prenom'    => $demande->patient_prenom,
            'patient_telephone' => $demande->patient_telephone,
            'remplace_uuid'     => $demande->remplace_uuid,
        ];
    }
}
