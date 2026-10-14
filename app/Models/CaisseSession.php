<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class CaisseSession extends Model
{
    use AppartientEtablissement;
    use Audite;
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'guichet_id',
        'solde_initial',
        'solde_theorique',
        'solde_reel',
        'ecart',
        'statut',
        'justification_ecart',
        'opened_at',
        'closed_at',
        'validee_par',
        'validee_at',
    ];

    protected $casts = [
        'opened_at'  => 'datetime',
        'closed_at'  => 'datetime',
        'validee_at' => 'datetime',
    ];

    public const STATUTS = [
        'ouverte' => 'Ouverte',
        'fermee'  => 'Clôturée',
        'validee' => 'Validée',
    ];

    /**
     * Cycle de vie : ouverte → fermée → validée, sans retour en arrière.
     * Une session fermée n'accepte plus que sa validation ; une session validée est figée (RG-02).
     */
    protected static function booted(): void
    {
        static::updating(function (CaisseSession $session) {
            $avant = $session->getOriginal('statut');
            if ($avant === 'validee') {
                throw new \LogicException('Session de caisse validée : plus aucune modification possible.');
            }
            if ($avant === 'fermee') {
                $modifies = array_diff(array_keys($session->getDirty()), ['statut', 'validee_par', 'validee_at', 'updated_at']);
                if ($modifies || $session->statut !== 'validee') {
                    throw new \LogicException('Session de caisse clôturée : seule sa validation est possible.');
                }
            }
        });

        static::deleting(function () {
            throw new \LogicException('Une session de caisse ne se supprime pas.');
        });
    }

    public function guichet()
    {
        return $this->belongsTo(Guichet::class);
    }

    public function valideePar()
    {
        return $this->belongsTo(User::class, 'validee_par');
    }

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
     * Enregistre un mouvement dans la caisse ouverte de l'utilisateur connecté.
     * Conservé pour les appels existants ; la logique est dans CaisseService.
     */
    public static function enregistrerMouvement($montant, $motif, $type = 'entree', $reference = null, $mode = 'especes')
    {
        if ($montant <= 0) return false;
        app(\App\Services\CaisseService::class)->enregistrer((float) $montant, $motif, $type, $reference, $mode ?: 'especes');
        return true;
    }

    /** Totaux par mode de paiement, annulations comprises (voir CaisseService::soldeTheorique). */
    public function totauxParMode(): array
    {
        return $this->mouvements()
            ->selectRaw('mode, type, SUM(montant) as total')->groupBy('mode', 'type')->get()
            ->groupBy('mode')
            ->map(fn($lignes) => ['entree' => (float) $lignes->where('type', 'entree')->sum('total'), 'sortie' => (float) $lignes->where('type', 'sortie')->sum('total')])
            ->all();
    }
}
