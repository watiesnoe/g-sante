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

    /**
     * Règles de fonctionnement saisies (les règles « fixes » ne sont jamais modifiées).
     *
     * @return array<string, mixed>
     */
    public function reglagesDepuis(\Illuminate\Http\Request $request, Etablissement $etablissement): array
    {
        return collect(Etablissement::PARAMETRES)
            ->reject(fn($definition) => !empty($definition['fixe']))
            ->mapWithKeys(function ($definition, $cle) use ($request, $etablissement) {
                $champ = 'parametres.' . str_replace('.', '_', $cle);

                return [$cle => match ($definition['type'] ?? 'booleen') {
                    'booleen' => $request->boolean($champ),
                    'nombre'  => max(0, (int) $request->input($champ, $etablissement->parametre($cle))),
                    'texte'   => trim((string) $request->input($champ)) ?: $etablissement->parametre($cle),
                    'choix'   => array_key_exists($request->input($champ), $definition['choix']) ? $request->input($champ) : $etablissement->parametre($cle),
                    'liste'   => array_values(array_intersect((array) $request->input($champ, []), array_keys($definition['choix']))) ?: ['especes'],
                }];
            })
            ->all();
    }

    public function historique(Etablissement $etablissement): \Illuminate\Support\Collection
    {
        if (!$etablissement->exists) {
            return collect();
        }

        $texte = fn($v) => match (true) {
            is_bool($v)  => $v ? 'oui' : 'non',
            is_array($v) => implode(', ', array_map(fn($x) => is_bool($x) ? ($x ? 'oui' : 'non') : (string) $x, $v)),
            $v === null  => '—',
            default      => (string) $v,
        };
        $libelleModule = fn($code) => Etablissement::MODULES[$code]['label'] ?? $code;

        return \Spatie\Activitylog\Models\Activity::with('causer')
            ->where('subject_type', Etablissement::class)->where('subject_id', $etablissement->id)->where('event', 'updated')
            ->latest()->limit(30)->get()
            ->flatMap(function ($activite) use ($texte, $libelleModule) {
                $avant = $activite->properties['old'] ?? [];
                $ligne = fn($champ, $a, $b) => [
                    'date'   => $activite->created_at->format('d/m/Y H:i'),
                    'auteur' => $activite->causer->name ?? 'Système',
                    'champ'  => $champ,
                    'avant'  => \Illuminate\Support\Str::limit($a, 120),
                    'apres'  => \Illuminate\Support\Str::limit($b, 120),
                ];

                return collect($activite->properties['attributes'] ?? [])->flatMap(function ($valeur, $champ) use ($avant, $texte, $ligne, $libelleModule) {
                    $ancien = $avant[$champ] ?? null;

                    // Modules : ce qui a été activé ou désactivé, plutôt que la liste complète
                    if ($champ === 'modules') {
                        $ancien = (array) (is_string($ancien) ? json_decode($ancien, true) : $ancien);
                        $valeur = (array) (is_string($valeur) ? json_decode($valeur, true) : $valeur);
                        $retires = array_map($libelleModule, array_values(array_diff($ancien, $valeur)));
                        $ajoutes = array_map($libelleModule, array_values(array_diff($valeur, $ancien)));

                        return ($retires || $ajoutes) ? [$ligne('Modules', $retires ? 'désactivés : ' . implode(', ', $retires) : '—', $ajoutes ? 'activés : ' . implode(', ', $ajoutes) : '—')] : [];
                    }

                    // Règles de fonctionnement : une ligne par règle modifiée, avec son libellé
                    if ($champ === 'parametres') {
                        $ancien = (array) (is_string($ancien) ? json_decode($ancien, true) : $ancien);
                        $valeur = (array) (is_string($valeur) ? json_decode($valeur, true) : $valeur);

                        return collect(array_unique(array_merge(array_keys($ancien), array_keys($valeur))))
                            ->filter(fn($cle) => ($ancien[$cle] ?? null) !== ($valeur[$cle] ?? null))
                            ->map(function ($cle) use ($ancien, $valeur, $texte, $ligne) {
                                // Valeurs à choix : le libellé affiché à l'écran plutôt que le code
                                $choix = Etablissement::PARAMETRES[$cle]['choix'] ?? [];
                                $lisible = fn($v) => $texte(is_array($v) ? array_map(fn($x) => $choix[$x] ?? $x, $v) : (is_string($v) ? ($choix[$v] ?? $v) : $v));

                                return $ligne(\Illuminate\Support\Str::limit(Etablissement::PARAMETRES[$cle]['label'] ?? $cle, 70), $lisible($ancien[$cle] ?? null), $lisible($valeur[$cle] ?? null));
                            })
                            ->values()->all();
                    }

                    return [$ligne($champ, $texte($ancien), $texte($valeur))];
                })->values();
            });
    }
}
