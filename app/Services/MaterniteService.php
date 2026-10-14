<?php

namespace App\Services;

use App\Models\Accouchement;
use App\Models\Grossesse;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * EF-10.04 : enregistrement de l'accouchement et des nouveau-nés. Un dossier
 * est ouvert pour chaque enfant né vivant ; le suivi de grossesse est clos.
 */
class MaterniteService
{
    public function enregistrerAccouchement(Patient $mere, array $donnees, array $enfants): Accouchement
    {
        return DB::transaction(function () use ($mere, $donnees, $enfants) {
            $grossesse = Grossesse::where('patient_id', $mere->id)->where('statut', 'En cours')->latest('id')->first();

            $accouchement = Accouchement::create($donnees + [
                'patient_id'    => $mere->id,
                'grossesse_id'  => $grossesse?->id,
                'accoucheur_id' => Auth::id(),
            ]);

            foreach ($enfants as $i => $enfant) {
                $dossier = null;
                if (($enfant['etat'] ?? 'vivant') === 'vivant') {
                    $dossier = Patient::create([
                        'nom'            => $mere->nom,
                        'prenom'         => $enfant['prenom'] ?? ('Bébé ' . ($i + 1) . ' de ' . $mere->prenom),
                        'genre'          => in_array($enfant['sexe'], ['M', 'F'], true) ? $enfant['sexe'] : 'F',
                        // Un nouveau-né n'a pas de téléphone : on reprend celui de la mère, suffixé pour rester unique
                        'telephone'      => $mere->telephone . '-NN' . $accouchement->id . ($i + 1),
                        'ethnie'         => $mere->ethnie ?? '-',
                        'age'            => 0,
                        'date_naissance' => $accouchement->date_heure->toDateString(),
                        'adresse'        => $mere->adresse,
                    ]);
                }
                $accouchement->nouveauNes()->create(collect($enfant)->except('prenom')->all() + ['patient_id' => $dossier?->id]);
            }

            if ($grossesse) {
                $mortNes = collect($enfants)->every(fn($e) => str_starts_with($e['etat'] ?? '', 'mort_ne'));
                $grossesse->update([
                    'statut'   => 'Terminée',
                    'issue'    => $mortNes ? 'Mort-né' : ($accouchement->mode === 'cesarienne' ? 'Césarienne' : 'Accouchement normal'),
                    'date_fin' => $accouchement->date_heure->toDateString(),
                ]);
            }

            if ($accouchement->etat_mere === 'deces') {
                $mere->update(['est_decede' => true, 'date_deces' => $accouchement->date_heure]);
            }

            return $accouchement;
        });
    }
}
