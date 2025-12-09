<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'description', 'total', 'date_validite', 'statut','user_id'];

    protected $dates = ['date_validite'];

    protected $appends = ['nombre_prestations', 'total_prestations']; // Ajout

    protected static function booted()
    {
        static::creating(function ($ticket) {
            if (!$ticket->date_validite) {
                $ticket->date_validite = now()->addWeek();
            }
            if (!$ticket->statut) {
                $ticket->statut = 'valide';
            }
        });

        static::retrieved(function ($ticket) {
            if ($ticket->date_validite < now() && $ticket->statut === 'valide') {
                $ticket->update(['statut' => 'expire']);
            }
        });
    }

    public function estExpire()
    {
        return $this->date_validite && $this->date_validite->isPast();
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function items()
    {
        return $this->hasMany(TicketItem::class);
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ Accesseur pour le nombre de prestations
    public function getNombrePrestationsAttribute()
    {
        return $this->items()->count();
    }

    // ✅ Accesseur pour le total des prestations (utilise déjà le champ total)
    public function getTotalPrestationsAttribute()
    {
        return $this->total;
    }

    // ✅ Si vous avez besoin du total calculé depuis les items
    public function getTotalCalculeAttribute()
    {
        return $this->items()->sum('sous_total');
    }
}
