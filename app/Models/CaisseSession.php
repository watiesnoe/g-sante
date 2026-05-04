<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class CaisseSession extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'solde_initial',
        'solde_theorique',
        'solde_reel',
        'ecart',
        'statut',
        'opened_at',
        'closed_at'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mouvements()
    {
        return $this->hasMany(CaisseMouvement::class);
    }

    /**
     * Vérifie si l'utilisateur a une caisse ouverte.
     */
    public static function hasOpenSession()
    {
        return self::where('user_id', auth()->id())->where('statut', 'ouverte')->exists();
    }

    /**
     * Helper pour enregistrer un mouvement dans la caisse de l'utilisateur connecté.
     * @throws \Exception
     */
    public static function enregistrerMouvement($montant, $motif, $type = 'entree', $reference = null)
    {
        if ($montant <= 0) return false;

        $session = self::where('user_id', auth()->id())->where('statut', 'ouverte')->first();
        if (!$session) {
            throw new \Exception("Aucune session de caisse ouverte. Veuillez ouvrir votre caisse pour effectuer cette opération.");
        }

        $mouvement = new CaisseMouvement([
            'user_id' => auth()->id(),
            'type' => $type,
            'montant' => $montant,
            'motif' => $motif,
        ]);

        if ($reference) {
            $mouvement->reference_type = get_class($reference);
            $mouvement->reference_id = $reference->id;
        }

        $session->mouvements()->save($mouvement);
        return true;
    }
}
