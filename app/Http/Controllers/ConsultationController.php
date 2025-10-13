<?php

namespace App\Http\Controllers;

use App\Models\Certificat;
use App\Models\Consultation;
use App\Models\Hospitalisation;
use App\Models\Lit;
use App\Models\Maladie;
use App\Models\Medicament;
use App\Models\Ordonnance;
use App\Models\OrdonnanceMedicament;
use App\Models\Patient;
use App\Models\PrescriptionExamen;
use App\Models\Rendezvous;
use App\Models\Salle;
use App\Models\Symptome;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $today = Carbon::today();
        $tickets = Ticket::with(['patient', 'items'])
            ->where('statut', 'en_attente')
            ->where('date_validite', '>=', $today)
            ->orderBy('created_at', 'asc')
            ->get();

        $patients = Patient::all();
        $symptomes = Symptome::all();
        $maladies = Maladie::all();
        $medicaments = Medicament::all();
        $salles = Salle::all();
        $lits = Lit::all(); // 🔹 ajout des lits

        // Mapping Symptômes → Maladies
        $symptomeMaladieMap = [];
        foreach ($symptomes as $s) {
            $symptomeMaladieMap[$s->id] = $s->maladies()->pluck('maladies.id')->toArray();
        }

        return view('application.consultation.create', compact(
            'tickets',
            'patients',
            'symptomes',
            'maladies',
            'symptomeMaladieMap',
            'medicaments',
            'salles',
            'lits' // 🔹 ajout ici
        ));
    }
    /**
     * Show the form for creating a new resource.
     */
//    public function create(Patient $patient)
//    {
//        $symptomes = Symptome::all();
//        $maladies = Maladie::all();
//
//        return view('application.consultations.create', compact('patient', 'symptomes', 'maladies'))->with('datas', $patient);
//    }
    /**
     * Store a newly created resource in storage.
     */
    public function litsLibres($salleId)
    {
        // On récupère les lits de la salle qui sont libres
        $lits = Lit::where('salle_id', $salleId)
            ->where('statut', 'libre')
            ->get();

        return response()->json($lits);
    }

     public function store(Request $request)
     {
         DB::beginTransaction();
//            dd($request->all());
         try {
             // 🔹 Validation minimale
          $request->validate([
                 'patient_id'   => 'required|exists:patients,id',
                 'medecin_id'   => 'required|exists:users,id',
                 'diagnostic'   => 'required|string',
                 'ticket_id'    => 'nullable|exists:tickets,id',
                 'quantites'    => 'array',
             ]);

             // 🔹 Création de la consultation
             $consultation = Consultation::create([
                 'ticket_id'        => $request->ticket_id, // ✅ ajouté
                 'patient_id'       => $request->patient_id,
                 'medecin_id'       => $request->medecin_id,
                 'date_consultation'=> now(),
                 'motif'            => $request->motif,
                 'diagnostic'       => $request->diagnostic,
                 'notes'            => $request->antecedents,
                 'poids'            => $request->poids,
                 'temperature'      => $request->temperature,
                 'tension'          => $request->tension,
             ]);

             // 🔹 Symptômes liés
             if ($request->filled('symptomes')) {
                 $consultation->symptomes()->sync($request->symptomes);
             }

             // 🔹 Maladie concernée
             if ($request->filled('maladie_id')) {
                 $consultation->maladies()->sync([$request->maladie_id]);
             }

             // 🔹 Ordonnance + Médicaments
             if ($request->filled('medicaments')) {
                 $ordonnance = Ordonnance::create([
                     'consultation_id' => $consultation->id,
                     'date'            => now(),
                 ]);

                 foreach ($request->medicaments as $i => $medId) {
                     OrdonnanceMedicament::create([
                         'ordonnance_id'  => $ordonnance->id,
                         'medicament_id'  => $medId,
                         'posologie'      => $request->posologies[$i] ?? '',
                         'duree_jours'    => $request->duree_jours[$i] ?? null,
                         'quantite'       => $request->quantites[$i] ?? 1,
                     ]);
                 }
             }

             // 🔹 Examens prescrits
             if ($request->filled('examens')) {
                 foreach ($request->examens as $examen) {
                     PrescriptionExamen::create([
                         'consultation_id' => $consultation->id,
                         'examen'          => $examen
                     ]);
                 }
             }

             // 🔹 Rendez-vous
             if ($request->filled('rdv_motifs')) {
                 foreach ($request->rdv_motifs as $i => $motif) {
                     Rendezvous::create([
                         'consultation_id' => $consultation->id,
                         'patient_id'      => $consultation->patient_id,
                         'medecin_id'      => $consultation->medecin_id,
                         'motif'           => $motif,
                         'date_heure'      => $request->rdv_dates[$i] . ' ' . $request->rdv_heures[$i],
                     ]);
                 }
             }

             // 🔹 Certificat
             if ($request->filled('certificat')) {
                 Certificat::create([
                     'consultation_id' => $consultation->id,
                     'contenu'         => $request->certificat,
                     'date'            => now(),
                 ]);
             }

             // 🔹 Hospitalisation
             if ($request->filled('hospitalisation')) {
                 Hospitalisation::create([
                     'consultation_id' => $consultation->id,
                     'salles_id'       => $request->salle_id,
                     'lit_id'          => $request->lit_id,
                     'date_entree'     => $request->date_entree,
                     'motif'           => $request->motif ?? 'Hospitalisation suite consultation',
                     'etat'            => 'en cours',
                     'service_id'      => $request->service_id ?? 1,
                     'observations'    => $request->observations,
                 ]);
             }

             // 🔹 Mettre à jour le statut du ticket (si lié)
             if ($consultation->ticket_id) {
                 Ticket::where('id', $consultation->ticket_id)
                     ->update(['statut' => 'valide']);
             }

             DB::commit();

             return response()->json([
                 'success' => true,
                 'data'    => 'Consultation enregistrée avec succès ✅'
             ]);

         } catch (\Exception $e) {
             DB::rollBack();
             return response()->json([
                 'success' => false,
                 'error'   => $e->getMessage()
             ], 500);
         }
     }

    /**
     * Display the specified resource.
     */
    public function show(Consultation $consultation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Consultation $consultation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Consultation $consultation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consultation $consultation)
    {
        //
    }
}
