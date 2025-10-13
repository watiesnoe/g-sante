<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * Fiche de synthèse « du symptôme à la prescription » d'un établissement.
 * Toute modification d'une fiche validée la renvoie en validation.
 */
class FicheSynthese extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $table = 'fiches_syntheses';

    protected $fillable = [
        'etablissement_id', 'titre', 'specialite', 'motif', 'maladie_id', 'demarche', 'diagnostics', 'examens', 'gravite', 'traitement', 'suivi',
        'statut', 'auteur_id', 'valide_par', 'valide_at',
    ];

    protected $casts = ['valide_at' => 'datetime'];

    public const STATUTS = ['brouillon' => 'Brouillon', 'a_valider' => 'À valider', 'validee' => 'Validée'];
    public const COULEURS = ['brouillon' => 'secondary', 'a_valider' => 'warning', 'validee' => 'success'];

    /** Sections cliniques, dans l'ordre de la démarche. */
    public const SECTIONS = [
        'demarche'    => ['Démarche clinique', 'Interrogatoire, examen physique, éléments d\'orientation'],
        'diagnostics' => ['Diagnostics à évoquer', 'Du plus grave au plus fréquent'],
        'examens'     => ['Examens complémentaires', 'Biologie, imagerie, ECG…'],
        'gravite'     => ['Signes de gravité', 'Quand hospitaliser ou référer'],
        'traitement'  => ['Stratégie thérapeutique', 'Les posologies figurent dans les fiches médicaments reliées'],
        'suivi'       => ['Surveillance et suivi', 'Évolution attendue, prévention'],
    ];

    /** Ouvrages proposés pour les références (aucun texte n'en est reproduit). */
    public const OUVRAGES = [
        'MSF — Médicaments essentiels (2026)',
        'Berrebi — Diagnostics et thérapeutique, du symptôme à la prescription',
        'Dorosz — Guide pratique des médicaments',
        'OMS — Recommandations',
        'Protocole national (Mali)',
    ];

    public function medicaments()
    {
        return $this->belongsToMany(FicheMedicament::class, 'fiche_synthese_medicament', 'fiche_synthese_id', 'fiche_medicament_id')
            ->withPivot(['usage', 'ordre'])->orderBy('fiche_synthese_medicament.ordre');
    }

    public function references()
    {
        return $this->hasMany(FicheSyntheseReference::class);
    }

    public function maladie()
    {
        return $this->belongsTo(Maladie::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
