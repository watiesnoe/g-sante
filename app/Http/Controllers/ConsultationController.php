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
use App\Models\RendezVous;
use App\Models\Salle;
use App\Models\Symptome;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;


class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $consultations = Consultation::with(['patient', 'medecin', 'ticket'])->latest();

            return DataTables::of($consultations)
                ->addColumn('patient', function($row) {
                    return $row->patient ? $row->patient->prenom.' '.$row->patient->nom : '-';
                })
                ->addColumn('medecin', function($row) {
                    return $row->medecin ? $row->medecin->name : '-';
                })
                ->addColumn('ticket', function($row) {
                    return $row->ticket ? $row->ticket->id : '-';
                })
                ->addColumn('actions', function($row){
                    return '
                    <a href="'.route('consultations.show', $row->id).'" class="btn btn-info btn-sm">Voir</a>
                    <a href="'.route('consultations.edit', $row->id).'" class="btn btn-warning btn-sm">Modifier</a>
                ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        // Si ce n’est pas AJAX, on retourne la vue normale
        return view('application.consultation.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Patient $patient)
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
        $lits = Lit::all();

        $symptomeMaladieMap = [];
        foreach ($symptomes as $s) {
            $symptomeMaladieMap[$s->id] = $s->maladies()->pluck('maladies.id')->toArray();
        }

        // 🔹 ajouter cette ligne :
        $consultation = null;

        return view('application.consultation.create', compact(
            'tickets',
            'patients',
            'symptomes',
            'maladies',
            'symptomeMaladieMap',
            'medicaments',
            'salles',
            'lits',
            'consultation' // ✅
        ));
    }

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
                     RendezVous::create([
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
                 'data'    => 'Consultation enregistrée avec succès ✅',
                 'redirect' => route('consultations.index')
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
        // Charger toutes les relations nécessaires
        $consultation->load([
            'patient',
            'medecin',
            'ticket',
            'ordonnances.medicaments',
            'examens',
            'rendezVous',
            'certificat',
            'hospitalisation',
            'symptomes',
            'maladies'
        ]);

        // Retourner la vue show
        return view('application.consultation.show', compact('consultation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Consultation $consultation) {
        $tickets = Ticket::where('statut', 'en attente')->get();
        $symptomes = Symptome::all();
        $maladies = Maladie::all();
        $symptomeMaladieMap = Symptome::with('maladies')->get()->mapWithKeys(function($s){
            return [$s->id => $s->maladies->pluck('id')->toArray()];
        });
        $medicaments = Medicament::all();
        $salles = Salle::all();

        // ✅ Chargement correct avec 'ordonnances' au pluriel
        $consultation->load([
            'ordonnances.medicaments',    // Relation au pluriel
            'examens',
            'rendezVous',
            'hospitalisation',
            'symptomes',
            'maladies',
            'certificat'                  // Ajout de la relation certificat
        ]);

        return view('application.consultation.create', compact(
            'tickets','symptomes','maladies','symptomeMaladieMap','medicaments','salles','consultation'
        ));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Consultation $consultation)
    {
        DB::beginTransaction();
        try {
            // Validation minimale
            $request->validate([
                'patient_id'   => 'required|exists:patients,id',
                'medecin_id'   => 'required|exists:users,id',
                'diagnostic'   => 'required|string',
                'ticket_id'    => 'nullable|exists:tickets,id',
                'quantites'    => 'array',
            ]);

            // 🔹 Mise à jour de la consultation
            $consultation->update([
                'ticket_id'        => $request->ticket_id,
                'patient_id'       => $request->patient_id,
                'medecin_id'       => $request->medecin_id,
                'motif'            => $request->motif,
                'diagnostic'       => $request->diagnostic,
                'notes'            => $request->antecedents,
                'poids'            => $request->poids,
                'temperature'      => $request->temperature,
                'tension'          => $request->tension,
                'taille'           => $request->taille,
                'groupe_sanguin'   => $request->groupe_sanguin,
                'adresse_patient'  => $request->adresse_patient,
            ]);

            // 🔹 Symptômes et maladies
            $consultation->symptomes()->sync($request->symptomes ?? []);
            $consultation->maladies()->sync($request->maladie_id ? [$request->maladie_id] : []);

            // 🔹 Ordonnances : supprimer les anciennes et créer les nouvelles
            $consultation->ordonnances()->delete();

            if ($request->filled('medicaments')) {
                $ordonnance = Ordonnance::create([
                    'consultation_id' => $consultation->id,
                    'date'            => now(),
                ]);

                foreach ($request->medicaments as $i => $medId) {
                    OrdonnanceMedicament::create([
                        'ordonnance_id' => $ordonnance->id,
                        'medicament_id' => $medId,
                        'posologie'     => $request->posologies[$i] ?? '',
                        'duree_jours'   => $request->duree_jours[$i] ?? null,
                        'quantite'      => $request->quantites[$i] ?? 1,
                    ]);
                }
            }

            // 🔹 Examens : supprimer anciens et créer les nouveaux
            $consultation->examens()->delete();
            foreach ($request->examens ?? [] as $examen) {
                PrescriptionExamen::create([
                    'consultation_id' => $consultation->id,
                    'examen'          => $examen,
                ]);
            }

            // 🔹 Rendez-vous : supprimer anciens et recréer
            $consultation->rendezVous()->delete();
            foreach ($request->rdv_motifs ?? [] as $i => $motif) {
                RendezVous::create([
                    'consultation_id' => $consultation->id,
                    'patient_id'      => $consultation->patient_id,
                    'medecin_id'      => $consultation->medecin_id,
                    'motif'           => $motif,
                    'date_heure'      => $request->rdv_dates[$i] . ' ' . $request->rdv_heures[$i],
                ]);
            }

            // 🔹 Certificat
            if ($request->filled('certificat')) {
                $certificat = $consultation->certificat;
                if ($certificat) {
                    $certificat->update(['contenu' => $request->certificat, 'date' => now()]);
                } else {
                    Certificat::create([
                        'consultation_id' => $consultation->id,
                        'contenu'         => $request->certificat,
                        'date'            => now(),
                    ]);
                }
            }

            // 🔹 Hospitalisation
            if ($request->filled('hospitalisation')) {
                $hospitalisation = $consultation->hospitalisation;
                if ($hospitalisation) {
                    $hospitalisation->update([
                        'salles_id'    => $request->salle_id,
                        'lit_id'       => $request->lit_id,
                        'date_entree'  => $request->date_entree,
                        'motif'        => $request->motif ?? 'Hospitalisation suite consultation',
                        'etat'         => 'en cours',
                        'service_id'   => $request->service_id ?? 1,
                        'observations' => $request->observations,
                    ]);
                } else {
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
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => 'Consultation mise à jour avec succès ✅'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function print(Consultation $consultation)
    {
        $consultation->load([
            'patient',
            'medecin',
            'ordonnances.medicaments',
            'examens',
            'rendezVous',
            'certificat',
            'hospitalisation',
            'symptomes',
            'maladies'
        ]);

        // Option 1 : Affichage HTML pour impression directe
        return view('application.consultation.pdf', compact('consultation'));

        // Option 2 : Générer un PDF
        // $pdf = PDF::loadView('consultations.print', compact('consultation'));
        // return $pdf->stream('consultation_'.$consultation->id.'.pdf');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consultation $consultation)
    {
        //
    }
}
