<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ordonnance extends Model
{
    protected $fillable = ['consultation_id', 'date','statutordo','date_paiement'];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    // Si tu veux accéder directement aux lignes de l’ordonnance
    public function lignes()
    {
        return $this->hasMany(OrdonnanceMedicament::class);
    }

    // Si tu veux accéder directement aux médicaments (via belongsToMany)
    public function medicaments()
    {
        return $this->belongsToMany(Medicament::class, 'ordonnance_medicaments')
            ->withPivot(['posologie', 'duree_jours', 'quantite', 'qte_vendu', 'statut_vente']);
    }
    public function paiements()
    {
        return $this->hasMany(OrdonnancePaiement::class);
    }
}
