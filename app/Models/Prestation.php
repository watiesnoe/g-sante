<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestation extends Model
{
    use AppartientEtablissement;
    use Audite;
    use HasUuid;
    use HasFactory;

    protected $fillable = ['service_medical_id', 'nom', 'description', 'quantifiable', 'prix'];

    public function serviceMedical()
    {
        return $this->belongsTo(ServiceMedical::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'prestation_user');
    }

    public function tarifs()
    {
        return $this->hasMany(Tarif::class)->orderByDesc('date_effet')->orderByDesc('id');
    }

    /** Prix en vigueur à une date (EF-05.01) ; à défaut de tarif daté, le prix de la fiche. */
    public function prixAu($date = null): float
    {
        $date = $date ? \Illuminate\Support\Carbon::parse($date)->toDateString() : today()->toDateString();
        $tarif = $this->tarifs()->whereDate('date_effet', '<=', $date)->first();

        return (float) ($tarif->prix ?? $this->prix);
    }
}
