<?php

namespace App\Models\EnLigne;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** Compte de l'espace patient, identifié par son numéro de téléphone vérifié (EF-14.02). */
class Compte extends Model
{
    use HasUuid;

    protected $table = 'en_ligne_comptes';

    protected $fillable = ['uuid', 'telephone', 'nom', 'prenom', 'verifie_at', 'derniere_connexion_at'];

    protected $casts = ['verifie_at' => 'datetime', 'derniere_connexion_at' => 'datetime'];

    public function demandes()
    {
        return $this->hasMany(Demande::class, 'patient_compte_id');
    }

    public function profilComplet(): bool
    {
        return filled($this->nom) && filled($this->prenom);
    }

    /** Numéro réduit à ses chiffres, sans l'indicatif du Mali (223). */
    public static function normaliser(string $telephone): string
    {
        $chiffres = preg_replace('/\D+/', '', $telephone);
        if (strlen($chiffres) === 11 && str_starts_with($chiffres, '223')) {
            $chiffres = substr($chiffres, 3);
        }
        if (strlen($chiffres) === 13 && str_starts_with($chiffres, '00223')) {
            $chiffres = substr($chiffres, 5);
        }

        return $chiffres;
    }
}
