<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ExerciceController extends Controller
{
    /**
     * Change l'année de l'exercice en session.
     */
    public function setYear(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100'
        ]);

        Session::put('exercice_year', $request->year);

        return redirect()->back()->with('success', "L'exercice a été changé pour l'année " . $request->year . ".");
    }
}
