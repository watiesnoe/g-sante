<?php

namespace App\Models;
use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProtocoleMedicament extends Pivot
{
    use HasUuid;
    /**
     * Indique si la table pivot possède un ID auto-incrémenté.
     * Comme nous avons mis $table->id() dans la migration, on met true.
     */
    public $incrementing = true;

    /**
     * Le nom de la table pivot.
     */
    protected $table = 'protocole_medicament';

    /**
     * Les champs que l'on peut remplir.
     */
    protected $fillable = [
        'protocole_id',
        'medicament_id',
        'nom_medicament',      // nom tel que dans le protocole (commun à la plateforme)
        'fiche_medicament_id', // fiche du guide des médicaments, si reliée
        'type',      // principal ou alternatif
        'posologie', // ex: 1g 3x/jour
        'duree',     // ex: 10 jours
        'alerte',    // alerte OMS 2025 du guide clinique (molécule retirée, restriction d'âge)
    ];

    /**
     * Relation inverse vers le protocole (Optionnel mais utile)
     */
    public function protocole()
    {
        return $this->belongsTo(ProtocoleTraitement::class, 'protocole_id');
    }

    /**
     * Relation inverse vers le médicament (Optionnel mais utile)
     */
    public function medicament()
    {
        return $this->belongsTo(Medicament::class, 'medicament_id');
    }

    public function fiche()
    {
        return $this->belongsTo(FicheMedicament::class, 'fiche_medicament_id');
    }

    /**
     * Produit du stock de l'établissement courant pour cette ligne : celui relié à
     * la fiche du guide, sinon un produit de même nom.
     */
    public function produitEnStock(): ?Medicament
    {
        return ($this->fiche_medicament_id ? Medicament::with('uniteDefault')->where('fiche_medicament_id', $this->fiche_medicament_id)->orderByDesc('stock')->first() : null)
            ?? ($this->nom_medicament ? Medicament::with('uniteDefault')->where('nom', $this->nom_medicament)->first() : null);
    }
}