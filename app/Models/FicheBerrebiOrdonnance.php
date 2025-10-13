<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Ligne d'ordonnance d'une fiche Berrebi, avec le statut actuel du médicament. */
class FicheBerrebiOrdonnance extends Model
{
    protected $table = 'fiches_berrebi_ordonnances';

    public $timestamps = false;

    protected $fillable = [
        'fiche_berrebi_id', 'dci', 'nom_commercial', 'posologie_ocr', 'nom_vidal', 'statut_2022',
        'liste_oms', 'guide_msf', 'posologie_reference', 'statut_2025',
    ];

    /** Retrait ou arrêt certain (statut 2022) ou alerte 2025 : signalé en rouge. */
    public function estObsolete(): bool
    {
        return filled($this->statut_2025)
            || (filled($this->statut_2022) && !$this->estIncertain() && preg_match('/arr[êe]t|retir/iu', $this->statut_2022));
    }

    /** Produit introuvable dans les références récentes (« peut-être arrêté ») : signalé à vérifier. */
    public function estIncertain(): bool
    {
        return (bool) preg_match('/peut-être|non retrouvé/iu', (string) $this->statut_2022);
    }
}
