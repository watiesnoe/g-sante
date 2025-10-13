<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'description', 'total', 'date_validite', 'statut'
    ];

    protected $dates = ['date_validite'];

    protected static function booted()
    {
        // 1️⃣ Quand on crée un ticket → ajouter une date de validité (7 jours par défaut)
        static::creating(function ($ticket) {
            if (!$ticket->date_validite) {
                $ticket->date_validite = now()->addWeek();
            }
            if (!$ticket->statut) {
                $ticket->statut = 'valide';
            }
        });

        // 2️⃣ Quand on récupère un ticket → vérifier s’il est expiré
        static::retrieved(function ($ticket) {
            if ($ticket->date_validite < now() && $ticket->statut === 'valide') {
                $ticket->update(['statut' => 'expire']);
            }
        });
    }

    // Vérifier si le ticket est expiré
    public function estExpire()
    {
        return $this->date_validite && $this->date_validite->isPast();
    }

    // 🔗 Un ticket appartient à un patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // 🔗 Un ticket a plusieurs items
    public function items()
    {
        return $this->hasMany(TicketItem::class);
    }
}
