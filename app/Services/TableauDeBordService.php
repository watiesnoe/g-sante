<?php

namespace App\Services;

use App\Http\Controllers\AlerteStockController;
use App\Models\CaisseSession;
use App\Models\Depense;
use App\Models\Etablissement;
use App\Models\Grossesse;
use App\Models\Hospitalisation;
use App\Models\Passage;
use Illuminate\Support\Facades\Auth;

/**
 * EF-13.01 : indicateurs du jour adaptés au profil de l'établissement (modules
 * actifs) et au rôle de l'utilisateur (permissions).
 */
class TableauDeBordService
{
    /** @return array<int, array{titre: string, valeur: string, detail: string, icone: string, couleur: string, lien: ?string}> */
    public function indicateurs(): array
    {
        $user = Auth::user();
        $etablissement = Etablissement::courant();
        $tuiles = [];

        if ($user->can('accueil.view')) {
            $jour = Passage::whereDate('arrive_at', today());
            $tuiles[] = $this->tuile('Passages du jour', (clone $jour)->count(), (clone $jour)->ouverts()->count() . ' encore en cours', 'fa-door-open', 'primary', route('passages.index'));
        }

        $session = CaisseSession::where('user_id', $user->id)->where('statut', 'ouverte')->first();
        if ($session) {
            $tuiles[] = $this->tuile('Ma caisse', number_format(app(CaisseService::class)->soldeTheorique($session), 0, ',', ' ') . ' F', 'Solde théorique', 'fa-cash-register', 'success', route('caisse.my_session'));
        }
        if ($user->can('caisse.valider_cloture')) {
            $aValider = CaisseSession::where('statut', 'fermee')->count();
            if ($aValider) {
                $tuiles[] = $this->tuile('Clôtures à valider', $aValider, 'Sessions de caisse', 'fa-check-double', 'warning', route('caisse.index'));
            }
        }

        if ($etablissement->moduleActif('finances') && $user->can('depenses.valider')) {
            $tuiles[] = $this->tuile('Dépenses à valider', Depense::where('statut', 'soumise')->count(), number_format((float) Depense::where('statut', 'validee')->sum('montant'), 0, ',', ' ') . ' F validées à payer', 'fa-file-invoice-dollar', 'info', route('depenses.index', ['statut' => 'soumise']));
        }

        if ($etablissement->moduleActif('stock') && $user->can('stock.view')) {
            $alertes = AlerteStockController::alertes();
            $tuiles[] = $this->tuile('Alertes de stock', $alertes['ruptures']->count() + $alertes['sousSeuil']->count(), $alertes['perimes']->count() . ' lot(s) périmé(s), ' . $alertes['bientot']->count() . ' bientôt', 'fa-exclamation-triangle', 'danger', route('stock.alertes'));
        }

        if ($etablissement->moduleActif('hospitalisation') && $user->can('hospitalisations.view')) {
            $tuiles[] = $this->tuile('Patients hospitalisés', Hospitalisation::where('etat', 'en cours')->count(), 'Séjours en cours', 'fa-procedures', 'dark', route('hospitalisations.index'));
        }

        if ($etablissement->moduleActif('maternity') && $user->can('maternity.view')) {
            $tuiles[] = $this->tuile('Grossesses suivies', Grossesse::where('statut', 'En cours')->count(), 'Suivis en cours', 'fa-baby', 'danger', route('maternity.index'));
        }

        return $tuiles;
    }

    private function tuile(string $titre, $valeur, string $detail, string $icone, string $couleur, ?string $lien): array
    {
        return ['titre' => $titre, 'valeur' => (string) $valeur, 'detail' => $detail, 'icone' => $icone, 'couleur' => $couleur, 'lien' => $lien];
    }
}
