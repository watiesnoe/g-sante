<?php

namespace App\Services\EnLigne;

use App\Models\Etablissement;
use App\Models\SynchroJournal;
use App\Models\SynchroSortante;
use App\Support\SignatureSynchro;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Serveur local : échange avec le serveur en ligne (EF-14.01, SEC-14).
 *
 * C'est toujours le serveur local qui appelle : il n'a pas besoin d'être joignable
 * depuis Internet. À chaque passage : récupération des demandes des patients,
 * envoi des décisions en attente, publication du catalogue (créneaux libres).
 * Sans connexion, rien n'est perdu : les décisions restent en file et les
 * demandes attendent sur le serveur en ligne jusqu'au passage suivant.
 */
class SynchroService
{
    public function __construct(private CreneauxService $creneaux, private DemandesService $demandes) {}

    public function synchroniser(Etablissement $etablissement): SynchroJournal
    {
        $journal = SynchroJournal::create(['etablissement_id' => $etablissement->id, 'debut' => now(), 'statut' => 'erreur']);
        $envoyes = 0;
        $recus = 0;

        try {
            if (!$etablissement->connecteEnLigne()) {
                throw new RuntimeException('Serveur en ligne non configuré pour cet établissement.');
            }
            // Demandes d'abord : les décisions qu'elles entraînent (conflits) partent
            // dans le même passage, et les créneaux publiés en tiennent compte
            $recus = $this->recupererDemandes($etablissement);
            $envoyes = $this->envoyerDecisions($etablissement);
            $this->publierCatalogue($etablissement);

            $journal->update(['statut' => 'ok', 'fin' => now(), 'envoyes' => $envoyes, 'recus' => $recus]);
            $etablissement->forceFill(['en_ligne_synchro_at' => now(), 'en_ligne_erreur' => null])->saveQuietly();
        } catch (Throwable $e) {
            $message = mb_substr($e->getMessage(), 0, 250);
            $journal->update(['fin' => now(), 'envoyes' => $envoyes, 'recus' => $recus, 'message' => $message]);
            $etablissement->forceFill(['en_ligne_erreur' => $message])->saveQuietly();
        }

        return $journal;
    }

    /** Créneaux libres et médecins ; liste vide si la publication est désactivée. */
    private function publierCatalogue(Etablissement $etablissement): void
    {
        $publier = (bool) $etablissement->parametre('en_ligne.rendez_vous');
        $medecins = $publier ? $this->creneaux->medecins() : collect();

        $this->appeler($etablissement, 'POST', '/api/synchro/catalogue', [
            'etablissement' => $etablissement->only(['nom', 'type', 'region', 'district', 'telephone'])
                + ['delai_annulation_heures' => (int) $etablissement->parametre('en_ligne.delai_annulation_heures')],
            'medecins'      => $medecins->map(fn($m) => ['uuid' => $m->uuid, 'nom' => $m->name, 'service' => $m->service->nom ?? null])->values()->all(),
            'creneaux'      => $publier ? $this->creneaux->disponibles($etablissement) : [],
        ]);
    }

    private function envoyerDecisions(Etablissement $etablissement): int
    {
        $total = 0;
        do {
            $lot = SynchroSortante::where('etablissement_id', $etablissement->id)->whereNull('envoye_at')
                ->orderBy('id')->limit(config('gsante.synchro.lot'))->get();
            if ($lot->isEmpty()) {
                break;
            }
            $reponse = $this->appeler($etablissement, 'POST', '/api/synchro/evenements', [
                'evenements' => $lot->map(fn($e) => ['uuid' => $e->uuid, 'type' => $e->type, 'donnees' => $e->donnees])->all(),
            ]);
            $acquis = (array) $reponse->json('acquis', []);
            SynchroSortante::whereIn('uuid', $acquis)->update(['envoye_at' => now()]);
            $total += count($acquis);
        } while (count($acquis) === $lot->count());

        return $total;
    }

    private function recupererDemandes(Etablissement $etablissement): int
    {
        $total = 0;
        do {
            $evenements = (array) $this->appeler($etablissement, 'GET', '/api/synchro/evenements?apres=' . (int) $etablissement->en_ligne_curseur)
                ->json('evenements', []);
            foreach ($evenements as $evenement) {
                // Un événement et l'avancée du curseur sont enregistrés ensemble
                DB::transaction(function () use ($etablissement, $evenement) {
                    $this->demandes->appliquer($etablissement, $evenement['type'], (array) $evenement['donnees']);
                    $etablissement->forceFill(['en_ligne_curseur' => $evenement['id']])->saveQuietly();
                });
                $total++;
            }
        } while (count($evenements) === config('gsante.synchro.lot'));

        return $total;
    }

    private function appeler(Etablissement $etablissement, string $methode, string $chemin, ?array $donnees = null): Response
    {
        $url = rtrim($etablissement->en_ligne_url, '/') . $chemin;
        $corps = $donnees === null ? '' : json_encode($donnees, JSON_UNESCAPED_UNICODE);

        $requete = $this->client()
            ->withHeaders(SignatureSynchro::entetes($etablissement->en_ligne_cle, $etablissement->en_ligne_secret, $methode, $url, $corps));
        $reponse = $methode === 'GET'
            ? $requete->get($url)
            : $requete->withBody($corps, 'application/json')->send($methode, $url);

        if (!$reponse->successful()) {
            throw new RuntimeException('Serveur en ligne : réponse ' . $reponse->status() . ' sur ' . $chemin . '.');
        }

        return $reponse;
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()->timeout(config('gsante.synchro.delai'))->connectTimeout(10);
    }
}
