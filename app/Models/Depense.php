<?php

namespace App\Models;

use App\Traits\AppartientEtablissement;
use App\Traits\Audite;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** Dépense avec circuit de validation (EF-12.01). */
class Depense extends Model
{
    use AppartientEtablissement, Audite, HasUuid;

    protected $fillable = [
        'etablissement_id', 'numero', 'categorie_depense_id', 'ligne_budgetaire_id', 'service_medical_id', 'objet', 'beneficiaire',
        'montant', 'date_depense', 'piece_justificative', 'statut', 'created_by', 'validee_par', 'validee_at',
        'motif_rejet', 'payee_par', 'payee_at', 'mode_paiement',
    ];

    protected $casts = [
        'montant'      => 'decimal:2',
        'date_depense' => 'date',
        'validee_at'   => 'datetime',
        'payee_at'     => 'datetime',
    ];

    public const STATUTS = [
        'brouillon' => 'Brouillon',
        'soumise'   => 'À valider',
        'validee'   => 'Validée, à payer',
        'payee'     => 'Payée',
        'rejetee'   => 'Rejetée',
        'annulee'   => 'Annulée',
    ];

    public const COULEURS = [
        'brouillon' => 'secondary', 'soumise' => 'warning', 'validee' => 'info',
        'payee' => 'success', 'rejetee' => 'danger', 'annulee' => 'dark',
    ];

    public function categorie()
    {
        return $this->belongsTo(CategorieDepense::class, 'categorie_depense_id');
    }

    public function ligneBudgetaire()
    {
        return $this->belongsTo(LigneBudgetaire::class);
    }

    public function service()
    {
        return $this->belongsTo(ServiceMedical::class, 'service_medical_id');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validee_par');
    }

    public function payeur()
    {
        return $this->belongsTo(User::class, 'payee_par');
    }
}
