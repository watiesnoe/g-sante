<?php

namespace App\Http\Controllers;

use App\Services\IndicateursHospitaliersService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/** EF-13.05 : taux d'occupation, durée moyenne de séjour, décès. */
class IndicateursHospitaliersController extends Controller
{
    public function index(Request $request, IndicateursHospitaliersService $service)
    {
        abort_unless(Auth::user()->can('hospitalisations.view'), 403, 'Accès non autorisé.');

        $du = $request->du ? Carbon::parse($request->du) : today()->startOfMonth();
        $au = $request->au ? Carbon::parse($request->au) : today();

        return view('application.hospitalisation.indicateurs', ['i' => $service->calculer($du, $au), 'du' => $du, 'au' => $au]);
    }
}
