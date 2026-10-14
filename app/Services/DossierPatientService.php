<?php

namespace App\Services;

use App\Models\FusionPatient;
use App\Models\Ordonnance;
use App\Models\Passage;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Synthèse du dossier (EF-02.05) et fusion de deux dossiers d'un même patient
 * (EF-02.03) : opération motivée, journalisée et réversible.
 */
class DossierPatientService
{
    /** Tables métier rattachées à un patient, déplacées lors d'une fusion. */
    public const TABLES_PATIENT = ['consultations', 'tickets', 'rendezvous', 'transferts', 'grossesses', 'passages', 'documents_medicaux'];

    public function synthese(Patient $patient): array
    {
        $ordonnancesRecentes = Ordonnance::with('medicaments')
            ->whereHas('consultation', fn($q) => $q->where('patient_id', $patient->id))
            ->where('created_at', '>=', now()->subDays(60))->latest()->get();

        // Traitement « en cours » : prescrit il y a moins longtemps que sa durée
        $traitements = $ordonnancesRecentes->flatMap(fn($o) => $o->medicaments
            ->filter(fn($m) => $o->created_at->copy()->addDays((int) ($m->pivot->duree_jours ?: 0))->gte(today()))
            ->map(fn($m) => ['nom' => $m->nom, 'posologie' => $m->pivot->posologie, 'jusqu_au' => $o->created_at->copy()->addDays((int) $m->pivot->duree_jours)]));

        return [
            'antecedents' => $patient->antecedents,
            'allergies'   => $patient->allergies,
            'traitements' => $traitements->values(),
            'passages'    => Passage::with('service')->where('patient_id', $patient->id)->latest('arrive_at')->limit(5)->get(),
        ];
    }

    public function fusionner(Patient $conserve, Patient $doublon, string $motif): FusionPatient
    {
        if ($conserve->is($doublon)) {
            throw ValidationException::withMessages(['patient' => 'Choisissez deux dossiers différents.']);
        }
        if ($doublon->fusionne_dans_id || $conserve->fusionne_dans_id) {
            throw ValidationException::withMessages(['patient' => 'L\'un des dossiers a déjà été fusionné.']);
        }
        if (trim($motif) === '') {
            throw ValidationException::withMessages(['motif' => 'Le motif de la fusion est obligatoire.']);
        }

        return DB::transaction(function () use ($conserve, $doublon, $motif) {
            $deplacements = [];
            foreach (self::TABLES_PATIENT as $table) {
                $ids = DB::table($table)->where('patient_id', $doublon->id)->pluck('id')->all();
                if ($ids) {
                    DB::table($table)->whereIn('id', $ids)->update(['patient_id' => $conserve->id]);
                    $deplacements[$table] = $ids;
                }
            }

            // Les informations médicales du doublon sont ajoutées, jamais perdues
            $completer = fn($a, $b) => collect([$a, $b])->filter()->unique()->implode("\n");
            $conserve->update([
                'antecedents' => $completer($conserve->antecedents, $doublon->antecedents),
                'allergies'   => $completer($conserve->allergies, $doublon->allergies),
            ]);
            $doublon->update(['fusionne_dans_id' => $conserve->id]);

            return FusionPatient::create([
                'patient_conserve_id' => $conserve->id,
                'patient_fusionne_id' => $doublon->id,
                'motif'               => $motif,
                'deplacements'        => $deplacements,
                'user_id'             => Auth::id(),
            ]);
        });
    }

    public function annuler(FusionPatient $fusion): void
    {
        if ($fusion->annulee_at) {
            throw ValidationException::withMessages(['fusion' => 'Cette fusion est déjà annulée.']);
        }

        DB::transaction(function () use ($fusion) {
            foreach ($fusion->deplacements as $table => $ids) {
                DB::table($table)->whereIn('id', $ids)->where('patient_id', $fusion->patient_conserve_id)
                    ->update(['patient_id' => $fusion->patient_fusionne_id]);
            }
            Patient::whereKey($fusion->patient_fusionne_id)->update(['fusionne_dans_id' => null]);
            $fusion->update(['annulee_at' => now(), 'annulee_par' => Auth::id()]);
        });
    }
}
