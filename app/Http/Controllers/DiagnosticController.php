<?php

namespace App\Http\Controllers;

use App\Models\Diagnostic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

/** EF-04.02 : nomenclature des diagnostics, importée depuis un fichier officiel. */
class DiagnosticController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('parametres.maladies'), 403, 'Accès non autorisé.');

        if ($request->ajax()) {
            return DataTables::of(Diagnostic::query())->make(true);
        }

        return view('application.parametre.diagnostics', ['total' => Diagnostic::count()]);
    }
}
