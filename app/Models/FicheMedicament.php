<?php

namespace App\Models;

use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * Fiche du référentiel des médicaments (commune à la plateforme). Importée d'un
 * guide de référence, elle reste « à valider » dans chaque établissement tant que
 * son médecin ou son pharmacien ne l'a pas relue.
 */
class FicheMedicament extends Model
{
    use Audite, HasUuid;

    protected $table = 'fiches_medicaments';

    protected $fillable = [
        'nom', 'voie', 'categorie', 'deconseille', 'avertissement', 'mention', 'action_therapeutique', 'indications', 'presentation',
        'posologie', 'duree', 'precautions', 'remarques', 'conservation', 'source', 'page', 'mise_a_jour_source',
    ];

    protected $casts = ['deconseille' => 'boolean'];

    public const VOIES = ['oral' => 'Voie orale', 'injectable' => 'Injectable', 'perfusion' => 'Perfusion', 'vaccin' => 'Vaccins et sérums', 'externe' => 'Usage externe', 'autre' => 'Voie non précisée'];

    /** Rubriques affichées, dans l'ordre de la source. */
    public const RUBRIQUES = [
        'action_therapeutique' => 'Action thérapeutique',
        'indications'          => 'Indications',
        'presentation'         => 'Présentation',
        'posologie'            => 'Posologie',
        'duree'                => 'Durée',
        'precautions'          => 'Contre-indications, effets indésirables, précautions',
        'remarques'            => 'Remarques',
        'conservation'         => 'Conservation',
    ];

    public function validations()
    {
        return $this->hasMany(ValidationFiche::class);
    }

    /** Validation de l'établissement courant, si elle porte encore sur le contenu actuel. */
    public function validationCourante(): ?ValidationFiche
    {
        $validation = $this->validations()->where('etablissement_id', Etablissement::courantId())->with('user')->first();

        return $validation && $validation->empreinte === $this->empreinte() ? $validation : null;
    }

    /** Empreinte du contenu clinique : toute modification invalide les validations. */
    public function empreinte(): string
    {
        return sha1(json_encode([$this->nom, $this->voie, $this->avertissement, ...array_map(fn($c) => $this->$c, array_keys(self::RUBRIQUES))]));
    }

    /** Fiches validées par l'établissement courant, sur leur contenu actuel. */
    public function scopeValideesIci($query, bool $validees = true)
    {
        $ids = ValidationFiche::where('etablissement_id', Etablissement::courantId())->get()
            ->filter(fn($v) => $v->fiche && $v->empreinte === $v->fiche->empreinte())->pluck('fiche_medicament_id');

        return $validees ? $query->whereIn('id', $ids) : $query->whereNotIn('id', $ids);
    }

    public function scopeRecherche($query, ?string $texte)
    {
        return $query->when($texte, fn($q) => $q->where(fn($w) => $w->where('nom', 'like', '%' . $texte . '%')->orWhere('indications', 'like', '%' . $texte . '%')));
    }
}
