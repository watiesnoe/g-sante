<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventaire extends Model
{
    use HasUuid, HasFactory;

    protected $fillable = [
        'uuid',
        'reference',
        'date_inventaire',
        'observations',
        'statut',
        'user_id',
    ];

    protected $casts = [
        'date_inventaire' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lignes()
    {
        return $this->hasMany(InventaireLigne::class);
    }

    /**
     * Nombre de lignes avec écart
     */
    public function getNbEcartsAttribute(): int
    {
        return $this->lignes->where('ecart', '!=', 0)->count();
    }

    /**
     * Taux de conformité (% de lignes sans écart)
     */
    public function getTauxConformiteAttribute(): float
    {
        $total = $this->lignes->count();
        if ($total === 0) return 100;
        $conformes = $this->lignes->where('ecart', 0)->count();
        return round(($conformes / $total) * 100, 1);
    }

    /**
     * Générer une référence unique
     */
    public static function genererReference(): string
    {
        $annee = date('Y');
        $count = static::whereYear('created_at', $annee)->count() + 1;
        return 'INV-' . $annee . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
