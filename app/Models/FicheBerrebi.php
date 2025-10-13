<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * Fiche du livre Berrebi (2005) : symptôme → démarche diagnostique → traitement.
 * Référentiel commun à la plateforme. Le texte du traitement et les posologies des
 * ordonnances ont été lus par OCR : ils sont affichés « à vérifier » et ne sont
 * jamais proposés à la prescription.
 */
class FicheBerrebi extends Model
{
    use HasUuid;

    protected $table = 'fiches_berrebi';

    public const SOURCE = 'Berrebi — Guide de thérapeutique (2005)';

    protected $fillable = ['specialite', 'titre', 'pages', 'pages_lisibles', 'demarche', 'medicaments_cites', 'traitement'];

    public function maladies()
    {
        return $this->belongsToMany(Maladie::class, 'fiche_berrebi_maladie');
    }

    public function ordonnances()
    {
        return $this->hasMany(FicheBerrebiOrdonnance::class)->orderBy('id');
    }

    /** Éléments séparés par « ; » dans le classeur. */
    public static function liste(?string $texte): array
    {
        return array_values(array_filter(array_map('trim', explode(';', (string) $texte)), fn($x) => $x !== '' && $x !== ':'));
    }
}
