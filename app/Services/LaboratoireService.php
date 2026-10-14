<?php

namespace App\Services;

use App\Models\Etablissement;
use App\Models\PrescriptionExamen;
use App\Models\ResultatExamen;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Laboratoire et imagerie (EF-09.02 à EF-09.04) : étapes de la demande,
 * saisie du résultat avec valeurs de référence, validation tracée.
 */
class LaboratoireService
{
    public function __construct(private WorkflowService $workflow) {}

    public function passerA(PrescriptionExamen $examen, string $vers, array $donnees = []): PrescriptionExamen
    {
        $this->workflow->exiger('examen', $examen->statut, $vers, PrescriptionExamen::STATUTS);

        $maj = ['statut' => $vers];
        match ($vers) {
            'preleve'  => $maj += ['preleve_at' => now(), 'preleve_par' => Auth::id()],
            'planifie' => $maj += ['planifie_le' => $donnees['planifie_le'] ?? now()],
            'realise'  => $maj += ['realise_at' => now()],
            'annule'   => $maj += ['motif_annulation' => $donnees['motif'] ?? null],
            default    => null,
        };
        if ($vers === 'annule' && empty($donnees['motif'])) {
            throw ValidationException::withMessages(['motif' => 'Le motif de l\'annulation est obligatoire.']);
        }

        $examen->update($maj);

        return $examen;
    }

    /**
     * Saisie du résultat (EF-09.03, EF-09.04) : la demande avance jusqu'à « saisi »,
     * la valeur est comparée aux valeurs de référence du catalogue.
     */
    public function saisir(PrescriptionExamen $examen, array $donnees): ResultatExamen
    {
        if (in_array($examen->statut, ['valide', 'annule'], true)) {
            throw ValidationException::withMessages(['resultat' => 'Cet examen est ' . PrescriptionExamen::STATUTS[$examen->statut] . ' : son résultat ne peut plus être saisi.']);
        }

        return DB::transaction(function () use ($examen, $donnees) {
            // Étapes intermédiaires non enregistrées séparément : on les franchit avec l'heure de saisie
            $chemin = $examen->type === 'imagerie' ? ['demande' => 'planifie', 'planifie' => 'realise'] : ['demande' => 'preleve', 'preleve' => 'realise'];
            while (isset($chemin[$examen->statut])) {
                $this->passerA($examen, $chemin[$examen->statut]);
            }

            $catalogue = $examen->catalogue;
            $min = $donnees['valeur_min'] ?? $catalogue?->valeur_min;
            $max = $donnees['valeur_max'] ?? $catalogue?->valeur_max;
            $valeur = $donnees['valeur'] ?? null;
            $anormal = is_numeric($valeur) && (($min !== null && (float) $valeur < (float) $min) || ($max !== null && (float) $valeur > (float) $max));

            $resultat = ResultatExamen::create([
                'prescription_examen_id' => $examen->id,
                'resultat'   => $donnees['resultat'] ?? null,
                'fichier'    => $donnees['fichier'] ?? null,
                'valeur'     => $valeur,
                'unite'      => $donnees['unite'] ?? $catalogue?->unite,
                'valeur_min' => $min,
                'valeur_max' => $max,
                'anormal'    => $anormal,
                'saisi_par'  => Auth::id(),
            ]);

            if ($examen->statut === 'realise') {
                $this->passerA($examen, 'saisi');
            }

            return $resultat;
        });
    }

    /** Validation biologique (EF-09.04) ; avec la séparation des tâches, pas par celui qui a saisi. */
    public function valider(PrescriptionExamen $examen): PrescriptionExamen
    {
        if (!Auth::user()->can('examens.valider')) {
            throw new AuthorizationException('Vous n\'avez pas la permission de valider un résultat.');
        }
        $saisiPar = ResultatExamen::where('prescription_examen_id', $examen->id)->latest('id')->value('saisi_par');
        if ($saisiPar === Auth::id() && Etablissement::courant()->parametre('securite.separation_taches')) {
            throw new AuthorizationException('Séparation des tâches : un autre professionnel doit valider ce résultat.');
        }

        $this->workflow->exiger('examen', $examen->statut, 'valide', PrescriptionExamen::STATUTS);
        $examen->update(['statut' => 'valide', 'valide_at' => now(), 'valide_par' => Auth::id()]);

        return $examen;
    }
}
