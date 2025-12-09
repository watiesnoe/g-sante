<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'fournisseur_id',
        'date_commande',
        'statut',
        'total',
        'StatutPaiement'
    ];

    protected $casts = [
        'date_commande' => 'datetime',
        'total' => 'decimal:2'
    ];

    protected static function booted()
    {
        static::creating(function ($commande) {
            $commande->reference = 'CMD-' . strtoupper(Str::random(6));
        });
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function lignes()
    {
        return $this->hasMany(CommandeMedicaments::class, 'commande_id');
    }

    public function medicaments()
    {
        return $this->belongsToMany(Medicament::class, 'commande_medicaments')
            ->withPivot('quantite', 'prix_unitaire', 'total')
            ->withTimestamps();
    }

    public function paiements()
    {
        return $this->hasMany(PaiementCommande::class);
    }

    /**
     * Pour récupérer le montant total payé
     */
    public function montantPaye()
    {
        return $this->paiements()->sum('montant');
    }

    /**
     * Pour savoir le reste à payer
     */
    public function getResteAPayerAttribute()
    {
        $total = $this->total ?? 0;
        $montantPaye = $this->montantPaye();
        return max(0, $total - $montantPaye);
    }

    /**
     * Accessors pour l'interface
     */
    public function getPaymentStatusColorAttribute()
    {
        return match($this->StatutPaiement) {
            'total' => 'success',
            'partielle' => 'warning',
            'en_cours' => 'danger',
            default => 'secondary'
        };
    }

    public function getPaymentStatusTextAttribute()
    {
        return match($this->StatutPaiement) {
            'total' => 'Payée',
            'partielle' => 'Partielle',
            'en_cours' => 'Impayée',
            default => $this->StatutPaiement
        };
    }

    /**
     * Vérifier si la commande est complètement payée
     */
    public function getEstCompletementPayeeAttribute()
    {
        return $this->StatutPaiement === 'total';
    }

    /**
     * Vérifier si la commande est partiellement payée
     */
    public function getEstPartiellementPayeeAttribute()
    {
        return $this->StatutPaiement === 'partielle';
    }

    /**
     * Vérifier si la commande n'a aucun paiement
     */
    public function getEstImpayeeAttribute()
    {
        return $this->StatutPaiement === 'en_cours';
    }

    /**
     * Mettre à jour le statut de paiement manuellement
     */
    public function updateStatutPaiement()
    {
        $montantPaye = $this->montantPaye();
        $total = $this->total;

        if ($montantPaye >= $total) {
            $this->update(['StatutPaiement' => 'total']);
        } elseif ($montantPaye > 0) {
            $this->update(['StatutPaiement' => 'partielle']);
        } else {
            $this->update(['StatutPaiement' => 'en_cours']);
        }
    }

    /**
     * Scope pour les commandes par statut de paiement
     */
    public function scopeStatutPaiement($query, $statut)
    {
        return $query->where('StatutPaiement', $statut);
    }

    /**
     * Scope pour les commandes impayées
     */
    public function scopeUnpaid($query)
    {
        return $query->where('StatutPaiement', 'en_cours');
    }

    /**
     * Scope pour les commandes partiellement payées
     */
    public function scopePartiallyPaid($query)
    {
        return $query->where('StatutPaiement', 'partielle');
    }

    /**
     * Scope pour les commandes complètement payées
     */
    public function scopeFullyPaid($query)
    {
        return $query->where('StatutPaiement', 'total');
    }
}
