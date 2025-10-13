<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Lot extends Model
{
    use AppartientEtablissement;
    use Audite;
    use HasUuid;

    protected $fillable = ['etablissement_id', 'medicament_id', 'magasin_id', 'numero', 'date_peremption', 'quantite'];

    public function magasin()
    {
        return $this->belongsTo(Magasin::class);
    }

    protected $casts = [
        'date_peremption' => 'date',
        'quantite' => 'integer',
    ];

    public function medicament()
    {
        return $this->belongsTo(Medicament::class);
    }

    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function estPerime(): bool
    {
        return $this->date_peremption !== null && $this->date_peremption->lt(today());
    }

    /** Lots encore utilisables : quantité restante et non périmés. */
    public function scopeDisponibles($query)
    {
        return $query->where('quantite', '>', 0)
            ->where(fn ($q) => $q->whereNull('date_peremption')->orWhere('date_peremption', '>=', today()));
    }

    /** Ordre FEFO : le lot qui périme le premier sort le premier ; les lots sans date en dernier. */
    public function scopeFefo($query)
    {
        return $query->orderByRaw('date_peremption IS NULL')->orderBy('date_peremption')->orderBy('id');
    }
}
