<?php

namespace App\Services;

use App\Models\Etablissement;
use App\Models\WorkflowTransition;
use Illuminate\Validation\ValidationException;

/**
 * Cycles de vie (section 7.2) : les transitions autorisées sont stockées en base
 * et vérifiées ici. Une transition peut dépendre d'un module actif ou d'un
 * paramètre de l'établissement (ex. le triage n'existe que s'il est activé).
 */
class WorkflowService
{
    public function autorisee(string $objet, string $de, string $vers): bool
    {
        $transition = WorkflowTransition::where(compact('objet', 'de', 'vers'))->first();

        return $transition !== null && $this->conditionRemplie($transition->condition);
    }

    /** @throws ValidationException transition non prévue, avec un message clair */
    public function exiger(string $objet, string $de, string $vers, array $libelles = []): void
    {
        if (!$this->autorisee($objet, $de, $vers)) {
            $l = fn($s) => $libelles[$s] ?? $s;
            throw ValidationException::withMessages([
                'statut' => "Passage de « {$l($de)} » à « {$l($vers)} » non autorisé pour cet établissement.",
            ]);
        }
    }

    /** Statuts accessibles depuis un statut donné. */
    public function suivants(string $objet, string $de): array
    {
        return WorkflowTransition::where('objet', $objet)->where('de', $de)->get()
            ->filter(fn($t) => $this->conditionRemplie($t->condition))
            ->pluck('vers')->all();
    }

    private function conditionRemplie(?string $condition): bool
    {
        if (!$condition) {
            return true;
        }

        [$type, $cle] = explode(':', $condition, 2);
        $etablissement = Etablissement::courant();

        return match ($type) {
            'module'    => $etablissement->moduleActif($cle),
            'parametre' => (bool) $etablissement->parametre($cle),
            default     => false,
        };
    }
}
