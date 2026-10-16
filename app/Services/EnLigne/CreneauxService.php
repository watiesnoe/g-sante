<?php

namespace App\Services\EnLigne;

use App\Models\DemandeEnLigne;
use App\Models\EmploiDuTemps;
use App\Models\Etablissement;
use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Serveur local : créneaux libres à publier en ligne, calculés à partir de
 * l'emploi du temps des médecins, moins les rendez-vous déjà pris et les
 * demandes en ligne en attente. Le calcul reste local : seul le résultat part.
 */
class CreneauxService
{
    /** @return Collection<int, User> médecins de l'établissement ayant un emploi du temps actif */
    public function medecins(): Collection
    {
        return User::deLEtablissement()
            ->whereHas('roles', fn($q) => $q->where('name', 'medecin'))
            ->whereIn('id', EmploiDuTemps::where('statut', 'actif')->select('medecin_id'))
            ->with('service')
            ->orderBy('name')->get();
    }

    /** @return array<int, array{medecin_uuid:string, debut:string}> */
    public function disponibles(Etablissement $etablissement, ?Carbon $maintenant = null): array
    {
        $maintenant ??= now();
        $duree = max(10, (int) $etablissement->parametre('en_ligne.duree_creneau'));
        $jours = min(60, max(1, (int) $etablissement->parametre('en_ligne.jours_publies')));
        $fin = $maintenant->copy()->addDays($jours)->endOfDay();
        // Pas de créneau trop proche : l'accueil doit avoir le temps de confirmer
        $premier = $maintenant->copy()->addHours(2);

        $medecins = $this->medecins()->keyBy('id');
        if ($medecins->isEmpty()) {
            return [];
        }

        $horaires = EmploiDuTemps::where('statut', 'actif')->whereIn('medecin_id', $medecins->keys())->get()->groupBy('medecin_id');
        $occupes = RendezVous::whereIn('medecin_id', $medecins->keys())->whereIn('statut', ['prevu', 'en_attente'])
            ->whereBetween('date_heure', [$maintenant, $fin])->get(['medecin_id', 'date_heure'])->toBase()
            ->map(fn($r) => [$r->medecin_id, Carbon::parse($r->date_heure)]);
        $demandes = DemandeEnLigne::where('statut', 'recue')->whereBetween('creneau', [$maintenant, $fin])->get(['medecin_id', 'creneau'])->toBase()
            ->map(fn($d) => [$d->medecin_id, $d->creneau]);
        // Collections simples : la fusion d'une collection Eloquent vide avec des tableaux échouerait
        $pris = $occupes->merge($demandes)->groupBy(0);

        $creneaux = [];
        for ($jour = $maintenant->copy()->startOfDay(); $jour->lte($fin); $jour->addDay()) {
            foreach ($horaires as $medecinId => $lignes) {
                foreach ($lignes->where('jour_semaine', $jour->dayOfWeekIso) as $h) {
                    $debut = $jour->copy()->setTimeFromTimeString($h->heure_debut);
                    $limite = $jour->copy()->setTimeFromTimeString($h->heure_fin);
                    for ($c = $debut; $c->copy()->addMinutes($duree)->lte($limite); $c = $c->copy()->addMinutes($duree)) {
                        if ($c->lt($premier) || $this->chevauche($pris[$medecinId] ?? collect(), $c, $duree)) {
                            continue;
                        }
                        $creneaux[] = ['medecin_uuid' => $medecins[$medecinId]->uuid, 'debut' => $c->format('Y-m-d H:i:s')];
                    }
                }
            }
        }

        return $creneaux;
    }

    /** Un rendez-vous existe-t-il déjà dans ce créneau pour ce médecin ? */
    public function creneauPris(int $medecinId, Carbon $debut, ?int $duree = null): bool
    {
        $duree ??= max(10, (int) Etablissement::courant()->parametre('en_ligne.duree_creneau'));

        return RendezVous::where('medecin_id', $medecinId)->whereIn('statut', ['prevu', 'en_attente'])
            ->where('date_heure', '>', $debut->copy()->subMinutes($duree))
            ->where('date_heure', '<', $debut->copy()->addMinutes($duree))
            ->exists();
    }

    private function chevauche(Collection $pris, Carbon $debut, int $duree): bool
    {
        return $pris->contains(fn($p) => abs($p[1]->diffInMinutes($debut, false)) < $duree);
    }
}
