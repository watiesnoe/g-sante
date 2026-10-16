<?php

namespace App\Models\EnLigne;

use Illuminate\Database\Eloquent\Model;

/** Établissement connu du serveur en ligne, avec ses propres identifiants de synchronisation (SEC-14). */
class Site extends Model
{
    protected $table = 'en_ligne_sites';

    protected $fillable = ['uuid', 'nom', 'type', 'region', 'district', 'telephone', 'cle', 'secret', 'actif', 'derniere_synchro_at', 'delai_annulation_heures'];

    protected $hidden = ['secret'];

    protected $casts = ['secret' => 'encrypted', 'actif' => 'boolean', 'derniere_synchro_at' => 'datetime'];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function medecins()
    {
        return $this->hasMany(Medecin::class);
    }

    public function creneaux()
    {
        return $this->hasMany(Creneau::class);
    }

    public function demandes()
    {
        return $this->hasMany(Demande::class);
    }

    /** Ajoute un événement à la file que le serveur local récupérera. */
    public function publier(string $type, array $donnees): Evenement
    {
        return Evenement::create(['site_id' => $this->id, 'type' => $type, 'donnees' => $donnees]);
    }
}
