<?php

namespace App\Services;

use App\Models\Abonnement;
use App\Models\Etablissement;
use App\Models\Formule;
use App\Models\PaiementAbonnement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Abonnements de la plateforme : souscription, paiement, échéances et limites.
 *
 * Échéance : rappels à J-15 et J-3 ; après la date de fin, période de grâce ;
 * ensuite, les modules prévus par la formule sont coupés (jamais les urgences,
 * l'hospitalisation ni la maternité). Les données ne sont jamais touchées, et
 * tout est rétabli dès le paiement enregistré.
 */
class AbonnementService
{
    public const RAPPELS = ['J-15' => 15, 'J-3' => 3];

    public function __construct(private SmsService $sms) {}

    /** Nouvel abonnement ; l'éventuel précédent est clos. Les modules de l'espace suivent la formule. */
    public function souscrire(Etablissement $etablissement, Formule $formule, Carbon $debut, ?int $joursEssai = null, ?string $notes = null): Abonnement
    {
        return DB::transaction(function () use ($etablissement, $formule, $debut, $joursEssai, $notes) {
            Abonnement::where('etablissement_id', $etablissement->id)->where('statut', '!=', 'termine')->update(['statut' => 'termine']);

            $abonnement = Abonnement::create([
                'etablissement_id' => $etablissement->id,
                'formule_id'       => $formule->id,
                'debut'            => $debut->toDateString(),
                'fin'              => ($joursEssai ? $debut->copy()->addDays($joursEssai) : $debut->copy()->addMonthsNoOverflow($formule->duree_mois)->subDay())->toDateString(),
                'essai'            => (bool) $joursEssai,
                'statut'           => 'actif',
                'notes'            => $notes,
            ]);
            $this->appliquerFormule($etablissement, $formule);

            return $abonnement;
        });
    }

    /** Passage à une autre formule, sans changer les dates (montée ou descente de gamme). */
    public function changerFormule(Abonnement $abonnement, Formule $formule): Abonnement
    {
        $abonnement->update(['formule_id' => $formule->id]);
        $this->appliquerFormule($abonnement->etablissement, $formule);
        if ($abonnement->statut === 'restreint') {
            $this->restreindre($abonnement->fresh('formule'));
        }

        return $abonnement;
    }

    /**
     * Paiement reçu : la période suivante commence au lendemain de l'échéance,
     * ou aujourd'hui si l'espace était déjà restreint. Tout est rétabli.
     */
    public function enregistrerPaiement(Abonnement $abonnement, float $montant, string $mode, ?string $reference, Carbon $payeLe): PaiementAbonnement
    {
        return DB::transaction(function () use ($abonnement, $montant, $mode, $reference, $payeLe) {
            $abonnement = Abonnement::with(['formule', 'etablissement'])->lockForUpdate()->findOrFail($abonnement->id);
            if ($abonnement->statut === 'termine') {
                throw ValidationException::withMessages(['abonnement' => 'Cet abonnement est terminé : créez-en un nouveau.']);
            }

            $debut = $abonnement->statut === 'restreint' ? today() : $abonnement->fin->copy()->addDay();
            if ($abonnement->essai) {
                // Fin d'essai : la première période payée commence au lendemain de l'essai, ou aujourd'hui si échu
                $debut = $abonnement->fin->isPast() ? today() : $abonnement->fin->copy()->addDay();
            }
            $fin = $debut->copy()->addMonthsNoOverflow($abonnement->formule->duree_mois)->subDay();

            $paiement = PaiementAbonnement::create([
                'numero'        => $this->numeroRecu(),
                'abonnement_id' => $abonnement->id,
                'montant'       => $montant,
                'mode'          => $mode,
                'reference'     => $reference,
                'paye_le'       => $payeLe->toDateString(),
                'periode_debut' => $debut->toDateString(),
                'periode_fin'   => $fin->toDateString(),
                'user_id'       => Auth::id(),
            ]);

            $abonnement->update(['fin' => $fin->toDateString(), 'statut' => 'actif', 'essai' => false, 'dernier_rappel' => null]);
            $abonnement->etablissement->forceFill(['modules_suspendus' => null])->save();

            return $paiement;
        });
    }

    /**
     * Passage quotidien (planifié) : rappels, période de grâce, coupure.
     *
     * @return array<string, int> nombre d'abonnements par action
     */
    public function verifier(?Carbon $aujourdhui = null): array
    {
        $aujourdhui ??= today();
        $compte = ['rappels' => 0, 'grace' => 0, 'restreints' => 0];

        $enCours = Abonnement::with(['formule', 'etablissement'])->where('statut', '!=', 'termine')->get()
            ->filter(fn($a) => $a->etablissement && $a->etablissement->abonnement?->id === $a->id);

        foreach ($enCours as $abonnement) {
            $jours = (int) $aujourdhui->diffInDays($abonnement->fin, false);

            foreach (self::RAPPELS as $cle => $seuil) {
                if ($jours >= 0 && $jours <= $seuil && $abonnement->dernier_rappel !== $cle && ($cle === 'J-3' || $abonnement->dernier_rappel === null)) {
                    $this->rappeler($abonnement, $jours);
                    $abonnement->update(['dernier_rappel' => $cle]);
                    $compte['rappels']++;
                    break;
                }
            }

            if ($aujourdhui->gt($abonnement->finGrace())) {
                if ($abonnement->statut !== 'restreint') {
                    $this->restreindre($abonnement);
                    $compte['restreints']++;
                }
            } elseif ($aujourdhui->gt($abonnement->fin) && $abonnement->statut === 'actif') {
                $abonnement->update(['statut' => 'grace']);
                $compte['grace']++;
            }
        }

        return $compte;
    }

    /**
     * Limite de la formule atteinte ? Renvoie le message à afficher, ou null.
     *
     * @param  'utilisateurs'|'lits'|'sms'  $limite
     */
    public function depassement(Etablissement $etablissement, string $limite): ?string
    {
        $formule = $etablissement->abonnement?->formule;
        $max = match ($limite) {
            'utilisateurs' => $formule?->max_utilisateurs,
            'lits'         => $formule?->max_lits,
            'sms'          => $formule?->max_sms_mois,
        };
        if ($max === null) {
            return null; // pas d'abonnement ou limite non fixée : illimité
        }

        $utilise = match ($limite) {
            'utilisateurs' => User::deLEtablissement($etablissement->id)->where('statut', 'actif')
                ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['super_admin', 'superadmin']))->count(),
            'lits'         => DB::table('lits')->where('etablissement_id', $etablissement->id)->count(),
            'sms'          => DB::table('sms_envois')->where('etablissement_id', $etablissement->id)->where('statut', 'envoye')
                ->where('created_at', '>=', now()->startOfMonth())->count(),
        };

        return $utilise >= $max
            ? match ($limite) {
                'utilisateurs' => "Votre formule « {$formule->nom} » permet {$max} utilisateur(s) actif(s). Désactivez un compte ou passez à une formule supérieure.",
                'lits'         => "Votre formule « {$formule->nom} » permet {$max} lit(s). Contactez la plateforme pour passer à une formule supérieure.",
                'sms'          => "Quota de {$max} SMS du mois atteint (formule « {$formule->nom} »).",
            }
            : null;
    }

    private function appliquerFormule(Etablissement $etablissement, Formule $formule): void
    {
        $etablissement->forceFill(['modules' => array_values($formule->modules ?? []), 'modules_suspendus' => null])->save();
    }

    private function restreindre(Abonnement $abonnement): void
    {
        $coupes = array_values(array_diff($abonnement->formule->modules_coupes ?? [], Formule::MODULES_PROTEGES));
        $abonnement->update(['statut' => 'restreint']);
        $abonnement->etablissement->forceFill(['modules_suspendus' => $coupes])->save();
    }

    /** Rappel au(x) propriétaire(s) : SMS (journal tant qu'aucun opérateur n'est branché). */
    private function rappeler(Abonnement $abonnement, int $jours): void
    {
        $message = "G-SANTÉ : l'abonnement « {$abonnement->formule->nom} » de {$abonnement->etablissement->nom} arrive à échéance "
            . ($jours === 0 ? "aujourd'hui" : "dans {$jours} jour(s)") . ' (' . $abonnement->fin->format('d/m/Y') . '). Pensez au renouvellement.';

        $proprietaires = User::deLEtablissement($abonnement->etablissement_id)->whereHas('roles', fn($q) => $q->where('name', 'admin'))
            ->whereNotNull('telephone')->get();

        app()->instance('etablissement.courant', $abonnement->etablissement);
        try {
            foreach ($proprietaires as $proprietaire) {
                $this->sms->envoyer($proprietaire->telephone, $message, $abonnement, horsQuota: true);
            }
        } finally {
            Etablissement::oublierCourant();
        }
    }

    private function numeroRecu(): string
    {
        $prefixe = 'AB-' . now()->format('Y') . '-';
        $dernier = PaiementAbonnement::where('numero', 'like', $prefixe . '%')->lockForUpdate()->orderByDesc('numero')->value('numero');

        return $prefixe . str_pad((string) ((int) substr((string) $dernier, strlen($prefixe)) + 1), 6, '0', STR_PAD_LEFT);
    }
}
