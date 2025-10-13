<?php

namespace App\Http\Controllers;

use App\Services\EspaceTravailService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Tableau de bord de chaque compte : ses chiffres du jour, ses listes « à
     * traiter » et ses raccourcis, selon ses droits et les modules actifs.
     */
    public function index(EspaceTravailService $espace)
    {
        return view('tableau_de_bord', ['espace' => $espace->pour(Auth::user())]);
    }
}
