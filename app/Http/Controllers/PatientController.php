<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'genre' => 'required|in:M,F',
            'telephone' => 'required|string|max:20|unique:patients,telephone',
            'ethnie' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
        ]);

        $patient = Patient::create($validated);

        return response()->json([
            'success' => true,
            'patient' => $patient
        ]);
    }
    public function search(Request $request)
    {
        $patients = Patient::where('nom', 'like', '%'.$request->q.'%')
            ->orWhere('telephone', 'like', '%'.$request->q.'%')
            ->limit(10)
            ->get(['id','nom','telephone']);

        return response()->json($patients);
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        //
    }
}
