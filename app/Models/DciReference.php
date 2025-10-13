<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Référence d'une molécule (DCI), commune à la plateforme : présence sur la liste OMS
 * et dans le guide MSF, noms commerciaux (Vidal 2018, statut 2022), Dorosz 2022,
 * posologies de référence et contre-indications.
 */
class DciReference extends Model
{
    protected $table = 'dci_references';

    protected $fillable = [
        'dci', 'liste_oms', 'guide_msf', 'noms_commerciaux', 'noms_arretes', 'dorosz',
        'posologie_reference', 'contre_indications', 'posologie_vidal', 'fiche_url', 'fiche_medicament_id',
    ];

    public function fiche()
    {
        return $this->belongsTo(FicheMedicament::class, 'fiche_medicament_id');
    }

    /** Références indexées par DCI en minuscules. */
    public static function parDci(array $dcis): \Illuminate\Support\Collection
    {
        return static::whereIn('dci', $dcis)->get()->keyBy(fn($r) => mb_strtolower($r->dci));
    }
}
