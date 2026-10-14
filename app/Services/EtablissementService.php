<?php

namespace App\Services;

use App\Models\Etablissement;
use App\Models\Medicament;
use App\Models\Prestation;
use App\Models\ServiceMedical;
use App\Models\Unite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Création d'un établissement (EF-01.01) : son profil fixe les modules et les
 * paramètres de départ (section 4) ; le catalogue peut être repris d'un autre
 * établissement (services, prestations et tarifs, médicaments sans stock).
 */
class EtablissementService
{
    public function creer(array $donnees, ?Etablissement $modele = null): Etablissement
    {
        return DB::transaction(function () use ($donnees, $modele) {
            $etablissement = Etablissement::create(array_merge($donnees, [
                'modules'    => Etablissement::PROFILS[$donnees['type']],
                'parametres' => null,
                'configure'  => true,
                'actif'      => true,
            ]));

            $etablissement->utilisateurs()->syncWithoutDetaching([Auth::id()]);

            if ($modele) {
                $this->copierCatalogue($modele, $etablissement);
            }

            return $etablissement;
        });
    }

    private function copierCatalogue(Etablissement $source, Etablissement $cible): void
    {
        $services = [];
        foreach (ServiceMedical::sansCloisonnement()->where('etablissement_id', $source->id)->get() as $service) {
            $copie = $service->replicate(['uuid']);
            $copie->etablissement_id = $cible->id;
            $copie->save();
            $services[$service->id] = $copie->id;
        }

        foreach (Prestation::sansCloisonnement()->where('etablissement_id', $source->id)->get() as $prestation) {
            $copie = $prestation->replicate(['uuid']);
            $copie->etablissement_id = $cible->id;
            $copie->service_medical_id = $services[$prestation->service_medical_id] ?? $prestation->service_medical_id;
            $copie->save();
        }

        Medicament::sansCloisonnement()->where('etablissement_id', $source->id)->with(['unites' => fn($q) => $q->withoutGlobalScope('etablissement')])
            ->chunkById(200, function ($medicaments) use ($cible) {
                foreach ($medicaments as $medicament) {
                    $copie = $medicament->replicate(['uuid', 'stock']);
                    $copie->etablissement_id = $cible->id;
                    $copie->stock = 0;
                    $copie->save();

                    foreach ($medicament->unites as $unite) {
                        $u = $unite->replicate();
                        $u->etablissement_id = $cible->id;
                        $u->medicament_id = $copie->id;
                        $u->save();
                    }
                }
            });
    }
}
