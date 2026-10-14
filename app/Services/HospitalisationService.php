<?php

namespace App\Services;

use App\Models\Hospitalisation;
use App\Models\Lit;
use App\Models\OccupationLit;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Séjours hospitaliers (EF-10.01, RG-12) : un lit n'accueille qu'un patient à
 * la fois et un patient n'a qu'un séjour ouvert par établissement. Chaque
 * transfert ferme une occupation de lit et en ouvre une autre (section 7.2).
 */
class HospitalisationService
{
    public const MOTIFS_SORTIE = ['Guérison', 'Amélioration', 'Transfert', 'Contre avis médical', 'Décès', 'Évasion'];

    public function admettre(Patient $patient, Lit $lit, array $donnees): Hospitalisation
    {
        return DB::transaction(function () use ($patient, $lit, $donnees) {
            if (Hospitalisation::where('patient_id', $patient->id)->where('etat', 'en cours')->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['patient_id' => 'Ce patient a déjà un séjour en cours (RG-12).']);
            }
            $lit = $this->verrouillerLitLibre($lit);

            $hospitalisation = Hospitalisation::create([
                'patient_id'      => $patient->id,
                'consultation_id' => $donnees['consultation_id'] ?? null,
                'salles_id'       => $lit->salle_id,
                'lit_id'          => $lit->id,
                'service_id'      => $donnees['service_id'] ?? ($lit->salle->service_medical_id ?? null),
                'date_entree'     => $donnees['date_entree'] ?? now(),
                'motif'           => $donnees['motif'] ?? null,
                'observations'    => $donnees['observations'] ?? null,
                'etat'            => 'en cours',
            ]);
            $this->occuper($hospitalisation, $lit);

            return $hospitalisation;
        });
    }

    public function transferer(Hospitalisation $hospitalisation, Lit $nouveauLit): Hospitalisation
    {
        if ($hospitalisation->etat !== 'en cours') {
            throw ValidationException::withMessages(['lit_id' => 'Ce séjour est clos.']);
        }

        return DB::transaction(function () use ($hospitalisation, $nouveauLit) {
            $nouveauLit = $this->verrouillerLitLibre($nouveauLit);
            $this->liberer($hospitalisation);
            $hospitalisation->update(['lit_id' => $nouveauLit->id, 'salles_id' => $nouveauLit->salle_id]);
            $this->occuper($hospitalisation, $nouveauLit);

            return $hospitalisation;
        });
    }

    public function sortir(Hospitalisation $hospitalisation, string $motif, $date = null): Hospitalisation
    {
        if (!in_array($motif, self::MOTIFS_SORTIE, true)) {
            throw ValidationException::withMessages(['statut_sortie' => 'Motif de sortie inconnu.']);
        }

        return DB::transaction(function () use ($hospitalisation, $motif, $date) {
            $this->liberer($hospitalisation);
            $hospitalisation->update(['etat' => 'terminé', 'date_sortie' => $date ?? now(), 'statut_sortie' => $motif]);

            $patient = $hospitalisation->patient ?? $hospitalisation->consultation?->patient;
            if ($motif === 'Décès' && $patient) {
                $patient->update(['est_decede' => true, 'date_deces' => $date ?? now()]);
            }

            return $hospitalisation;
        });
    }

    private function verrouillerLitLibre(Lit $lit): Lit
    {
        $lit = Lit::whereKey($lit->id)->lockForUpdate()->firstOrFail();
        $occupe = OccupationLit::where('lit_id', $lit->id)->whereNull('fin')->exists();
        if ($occupe || $lit->statut !== 'Libre') {
            throw ValidationException::withMessages(['lit_id' => "Le lit {$lit->numero} n'est pas libre."]);
        }

        return $lit;
    }

    private function occuper(Hospitalisation $hospitalisation, Lit $lit): void
    {
        OccupationLit::create(['hospitalisation_id' => $hospitalisation->id, 'lit_id' => $lit->id, 'debut' => now(), 'user_id' => Auth::id()]);
        $lit->update(['statut' => 'Occupé']);
    }

    private function liberer(Hospitalisation $hospitalisation): void
    {
        foreach (OccupationLit::where('hospitalisation_id', $hospitalisation->id)->whereNull('fin')->get() as $occupation) {
            $occupation->update(['fin' => now()]);
            Lit::whereKey($occupation->lit_id)->update(['statut' => 'Libre']);
        }
        // Séjours antérieurs à l'historique d'occupation
        if ($hospitalisation->lit_id) {
            Lit::whereKey($hospitalisation->lit_id)->where('statut', 'Occupé')->update(['statut' => 'Libre']);
        }
    }

    /**
     * Relevé complet du séjour (REC-16) : hébergement par lit occupé (prix de la
     * chambre × nuits), actes du bloc au tarif en vigueur, consommables.
     *
     * @return array{lignes: array, total: float}
     */
    public function releve(Hospitalisation $hospitalisation): array
    {
        $lignes = [];
        $occupations = $hospitalisation->occupations()->with('lit.salle')->get();

        if ($occupations->isEmpty() && $hospitalisation->salles_id) {
            // Séjour antérieur à l'historique d'occupation
            $nuits = max(1, (int) \Illuminate\Support\Carbon::parse($hospitalisation->date_entree)->diffInDays(\Illuminate\Support\Carbon::parse($hospitalisation->date_sortie ?? now())));
            $prix = (float) (\App\Models\Salle::find($hospitalisation->salles_id)->prix ?? 0);
            $lignes[] = ['type' => 'hebergement', 'libelle' => 'Hébergement', 'quantite' => $nuits, 'prix_unitaire' => $prix, 'montant' => $nuits * $prix];
        }
        foreach ($occupations as $o) {
            $nuits = max(1, (int) $o->debut->copy()->startOfDay()->diffInDays(($o->fin ?? now())->copy()->startOfDay()));
            $prix = (float) ($o->lit->salle->prix ?? 0);
            $lignes[] = ['type' => 'hebergement', 'libelle' => 'Hébergement ' . ($o->lit->salle->nom ?? '') . ' — lit ' . ($o->lit->numero ?? ''), 'quantite' => $nuits, 'prix_unitaire' => $prix, 'montant' => $nuits * $prix];
        }

        foreach (\App\Models\Intervention::with(['prestation', 'consommables.medicament'])->where('hospitalisation_id', $hospitalisation->id)->where('statut', 'terminee')->get() as $intervention) {
            if ($intervention->prestation) {
                $prix = $intervention->prestation->prixAu($intervention->debut_reel ?? $intervention->debut_prevu);
                $lignes[] = ['type' => 'acte', 'libelle' => 'Bloc : ' . $intervention->libelle, 'quantite' => 1, 'prix_unitaire' => $prix, 'montant' => $prix];
            }
            foreach ($intervention->consommables as $c) {
                $lignes[] = ['type' => 'consommable', 'libelle' => $c->medicament->nom ?? 'Consommable', 'quantite' => $c->quantite, 'prix_unitaire' => $c->prix_unitaire, 'montant' => $c->quantite * $c->prix_unitaire];
            }
        }

        return ['lignes' => $lignes, 'total' => round(collect($lignes)->sum('montant'), 2)];
    }
}
