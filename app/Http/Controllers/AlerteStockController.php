<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Lot;
use App\Models\Medicament;
use Illuminate\Support\Facades\Auth;

/**
 * EF-08.04 : alertes de stock — ruptures, stock sous le seuil, lots périmés et
 * lots qui périment dans le délai paramétré.
 */
class AlerteStockController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('stock.view'), 403, 'Accès non autorisé.');

        return view('application.stock.alertes', self::alertes());
    }

    /** Alertes de l'établissement courant ; aussi utilisé par le tableau de bord. */
    public static function alertes(): array
    {
        $jours = (int) Etablissement::courant()->parametre('stock.alerte_peremption_jours');

        return [
            'jours'      => $jours,
            'ruptures'   => Medicament::where('stock', '<=', 0)->where('stock_min', '>', 0)->orderBy('nom')->get(['id', 'uuid', 'nom', 'stock', 'stock_min']),
            'sousSeuil'  => Medicament::where('stock', '>', 0)->whereColumn('stock', '<=', 'stock_min')->orderBy('nom')->get(['id', 'uuid', 'nom', 'stock', 'stock_min']),
            'perimes'    => Lot::with('medicament:id,nom')->where('quantite', '>', 0)->whereDate('date_peremption', '<', today())->orderBy('date_peremption')->get(),
            'bientot'    => Lot::with('medicament:id,nom')->where('quantite', '>', 0)->whereBetween('date_peremption', [today(), today()->addDays($jours)])->orderBy('date_peremption')->get(),
        ];
    }
}
