<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketItem extends Model
{
    use HasFactory;

    // Champs assignables
    protected $fillable = [
        'ticket_id',
        'prestation_id',
        'service',
        'prix_unitaire',
        'quantite',
        'remise',
        'sous_total',
    ];

    // Relation : un item appartient à un ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relation : un item appartient à une prestation
    public function prestation()
    {
        return $this->belongsTo(Prestation::class);
    }
}
