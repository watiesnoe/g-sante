<?php

namespace App\Http\Controllers;

use App\Models\Certificat;
use App\Models\Consultation;
use App\Models\Hospitalisation;
use App\Models\Lit;
use App\Models\Maladie;
use App\Models\Medicament;
use App\Models\ConsultationSuggestion;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        abort_unless(Auth::user()->can('consultations.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les consultations.');

        if ($request->ajax()) {
            $year = session('exercice_year', date('Y'));
            $consultations = Consultation::with(['patient', 'medecin', 'ticket'])
                ->whereYear('created_at', $year)
                ->latest();

            // Un médecin ne voit que ses propres consultations
            if (Auth::user()->hasRole('medecin')) {
                $consultations->where('medecin_id', Auth::id());
            }

            return DataTables::of($consultations)
                ->addColumn('patient', function ($row) {
                    return $row->patient ? $row->patient->prenom . ' ' . $row->patient->nom : '-';
                })
                ->addColumn('medecin', function ($row) {
                    return $row->medecin ? $row->medecin->name : '-';
                })
                ->addColumn('ticket', function ($row) {
                    return $row->ticket ? $row->ticket->id : '-';
                })
                ->addColumn('actions', function ($row) {
                    $user = Auth::user();
                    $html = '';

                    if ($user->can('consultations.view')) {
                        $html .= '<a href="' . route('consultations.show', $row) . '" class="btn btn-sm btn-outline-primary" title="Voir"><i class="fa fa-eye"></i></a>';
                    }
                    if ($user->can('consultations.edit')) {
                        $html .= '<a href="' . route('consultations.edit', $row) . '" class="btn btn-sm btn-outline-info" title="Modifier"><i class="fa fa-pencil-alt"></i></a>';
                    }
                    if ($user->can('transferts.create') && $row->patient) {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-success" onclick="openTransfertModal(\'' . $row->patient->uuid . '\', \'' . $row->uuid . '\', \'\')" title="Transférer"><i class="fa fa-exchange-alt"></i></button>';
                    }
                    if ($user->can('rendezvous.create')) {
                        $html .= '<a href="' . route('rendezvous.index', ['consultation' => $row->uuid]) . '" class="btn btn-sm btn-outline-warning" title="Prendre Rendez-vous"><i class="fa fa-calendar-plus"></i></a>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">' . $html . '</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        // Si ce n’est pas AJAX, on retourne la vue normale
        return view('application.consultation.index');
    }

    public function listeAttente(Request $request)
    {
        abort_unless(Auth::user()->can('consultations.liste_attente'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir la liste d\'attente.');

        $today = Carbon::today();
        $ticketsQuery = Ticket::with(['patient', 'items', 'medecin'])
            ->where('statut', 'en_attente')
            ->where('date_validite', '>=', $today)
            ->orderBy('created_at', 'asc');

        // Un médecin ne voit que les tickets qui lui sont assignés ou sans médecin assigné
        if (Auth::user()->hasRole('medecin')) {
            $ticketsQuery->where(function ($q) {
                $q->where('medecin_id', Auth::id())
                  ->orWhereNull('medecin_id');
            });
        }

        $tickets = $ticketsQuery->get();

        if ($request->ajax()) {
            return DataTables::of($tickets)
                ->addColumn('patient', function ($row) {
                    return $row->patient ? $row->patient->prenom . ' ' . $row->patient->nom : '-';
                })
                ->addColumn('age', function ($row) {
                    return $row->patient ? $row->patient->age . ' ans' : '-';
                })
                ->addColumn('medecin', function ($row) {
                    return $row->medecin_id && $row->medecin ? $row->medecin->name : '<span class="badge bg-secondary">Tout médecin</span>';
                })
                ->addColumn('motif', function ($row) {
                    return $row->items->pluck('libelle')->implode(', ');
                })
                ->addColumn('actions', function ($row) {
                    if (Auth::user()->can('consultations.create')) {
                        return '<a href="' . route('consultations.create', ['ticket_id' => $row->uuid]) . '" class="btn btn-sm btn-outline-primary"><i class="fa fa-stethoscope me-1"></i> Consulter</a>';
                    }
                    return '-';
                })
                ->rawColumns(['medecin', 'actions'])
                ->make(true);
        }

        return view('application.consultation.liste_attente');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Consultation $consultation)
    {
        abort_unless(Auth::user()->can('consultations.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer une consultation.');

        $today = Carbon::today();
        $tickets = Ticket::with(['patient', 'items'])
            ->where('statut', 'en_attente')
            ->where('date_validite', '>=', $today)
            ->orderBy('created_at', 'asc')
            ->get();

        $selectedTicketId = $request->get('ticket_id');
        $selectedPatientId = $request->get('patient_id');
        $selectedGrossesseId = $request->get('grossesse_id');

        // Pré-remplissage si un ticket est spécifié
        if ($selectedTicketId) {
            // Check if it's a UUID or ID (for backward compatibility or if some links still use ID)
            $ticket = Ticket::with(['patient', 'items'])
                ->where('uuid', $selectedTicketId)
                ->orWhere('id', $selectedTicketId)
                ->first();
            if ($ticket) {
                $selectedPatientId = $selectedPatientId ?: $ticket->patient_id;
                if (!$consultation->motif) {
                    $consultation->motif = $ticket->items->pluck('libelle')->filter()->implode(', ') ?: $ticket->description;
                }
            }
        }

        $patients = Patient::all();
        $symptomes = Symptome::with('maladies')->get();
        $maladies = Maladie::with(['protocole', 'symptomes'])->get();
        $medicaments = Medicament::all();
        $salles = Salle::all();
        $lits = Lit::all();

        $symptomeMaladieMap = $symptomes->mapWithKeys(function ($s) {
            return [$s->id => $s->maladies->pluck('id')->toArray()];
        });

        $maladieSymptomesDetails = $maladies->mapWithKeys(function ($m) {
            return [$m->id => [
                'nom' => $m->nom,
                'symptomes' => $m->symptomes->pluck('id')->toArray()
            ]];
        });

        return view('application.consultation.create', compact(
            'tickets',
            'patients',
            'symptomes',
            'maladies',
            'symptomeMaladieMap',
            'maladieSymptomesDetails',
            'medicaments',
            'salles',
            'lits',
            'consultation',
            'selectedTicketId',
            'selectedPatientId',
            'selectedGrossesseId'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function litsLibres($salleId)
    {
        abort_unless(Auth::user()->can('consultations.view'), 403, 'Accès non autorisé.');

        // On récupère les lits de la salle qui sont libres
        $lits = Lit::where('salle_id', $salleId)
            ->where('statut', 'Libre')
            ->get();

        return response()->json($lits);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('consultations.create'), 403, 'Accès non autorisé : vous n\'avez pas la permission de créer une consultation.');

        DB::beginTransaction();
        //            dd($request->all());
        try {
            // 🔹 Validation minimale
            $request->validate([
                'patient_id'   => 'required|exists:patients,id',
                'medecin_id'   => 'required|exists:users,id',
                'poids'        => 'nullable|numeric',
                'taille'       => 'nullable|numeric',
                'temperature'  => 'nullable|numeric',
                'tension'      => 'nullable|string',
                'motif'        => 'nullable|string',
                'antecedents'  => 'nullable|string',
                'symptomes'    => 'nullable|array',
                'maladie_id'   => 'nullable|exists:maladies,id',
                'imc'          => 'nullable|numeric',
                'groupe_sanguin' => 'nullable|string',
                'adresse_patient' => 'nullable|string',
                'diagnostic'   => 'required|string',
                'ticket_id'    => 'nullable|exists:tickets,id',
                'grossesse_id' => 'nullable|exists:grossesses,id',
                'protocole_id' => 'nullable|exists:protocole_traitements,id',
                'quantites'    => 'array',
                'suggestions'  => 'nullable|array',
            ]);


            // 🔹 Création de la consultation
            $consultation = Consultation::create([
                'ticket_id'        => $request->ticket_id,
                'grossesse_id'     => $request->grossesse_id,
                'patient_id'       => $request->patient_id,
                'medecin_id'       => $request->medecin_id,
                'protocole_id'     => $request->protocole_id, // ✅ track the applied protocol
                'date_consultation' => now(),
                'motif'            => $request->motif,
                'maladie_id'       => $request->maladie_id,
                'taille'           => $request->taille,
                'diagnostic'       => $request->diagnostic,
                'notes'            => $request->antecedents,
                'poids'            => $request->poids,
                'temperature'      => $request->temperature,
                'tension'          => $request->tension,
                'imc'              => $request->imc,
                'groupe_sanguin'   => $request->groupe_sanguin,
                'adresse_patient'  => $request->adresse_patient,
            ]);

            // 🔹 Symptômes liés
            if ($request->filled('symptomes')) {
                $consultation->symptomes()->sync($request->symptomes);
            }

            // 🔹 Maladie concernée
            if ($request->filled('maladie_id')) {
                $consultation->maladies()->sync([$request->maladie_id]);
            }

            // 🔹 Suggestions IA de Diagnostic
            if ($request->filled('suggestions')) {
                foreach ($request->suggestions as $suggestion) {
                    ConsultationSuggestion::create([
                        'consultation_id'  => $consultation->id,
                        'pathologie_id'    => $suggestion['pathologie_id'],
                        'score'            => $suggestion['score'],
                        'niveau_confiance' => $suggestion['niveau_confiance'] ?? null
                    ]);
                }
            }

            // 🔹 Ordonnance + Médicaments
            if ($request->filled('medicaments')) {
                $ordonnance = Ordonnance::create([
                    'consultation_id' => $consultation->id,
                    'date'            => now(),
                ]);

                foreach ($request->medicaments as $i => $medId) {
                    if (!$medId) continue; // Ignorer les lignes de sélection vides

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
                    'date_entree'     => $request->date_entree ?: now(),
                    'motif'           => $request->motif ?? 'Hospitalisation suite consultation',
                    'etat'            => 'en cours',
                    'service_id'      => \App\Models\Salle::find($request->salle_id)->service_medical_id ?? 1,
                    'observations'    => $request->observations,
                ]);
                Lit::where('id', $request->lit_id)->update(['statut' => 'Occupé']);
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
        abort_unless(Auth::user()->can('consultations.view'), 403, 'Accès non autorisé : vous n\'avez pas la permission de voir les consultations.');

        // Un médecin ne peut voir que ses propres consultations
        if (Auth::user()->hasRole('medecin') && $consultation->medecin_id !== Auth::id()) {
            abort(403, 'Accès refusé : cette consultation ne vous appartient pas.');
        }
        // Charger toutes les relations nécessaires
        $consultation->load([
            'patient',
            'medecin',
            'ticket',
            'protocole.medicaments',
            'ordonnances.medicaments',
            'examens',
            'rendezVous',
            'certificat',
            'hospitalisation.salle',
            'hospitalisation.lit',
            'symptomes',
            'maladies'
        ]);

        // Retourner la vue show
        return view('application.consultation.show', compact('consultation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Consultation $consultation)
    {
        abort_unless(Auth::user()->can('consultations.edit'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier une consultation.');

        // Un médecin ne peut modifier que ses propres consultations
        if (Auth::user()->hasRole('medecin') && $consultation->medecin_id !== Auth::id()) {
            abort(403, 'Accès refusé : vous ne pouvez modifier que vos propres consultations.');
        }
        // ✅ Tickets en attente
        $tickets = Ticket::with('patient')
            ->where('statut', 'en attente')
            ->get();

        // ✅ Ajouter le ticket actuel s'il n'existe pas dans la liste
        if ($consultation->ticket_id) {
            $ticketActuel = Ticket::with('patient')->find($consultation->ticket_id);

            if ($ticketActuel && !$tickets->contains('id', $ticketActuel->id)) {
                $tickets->push($ticketActuel);
            }
        }

        $symptomes = Symptome::all();
        $maladies = Maladie::with('protocole')->get();

        $symptomeMaladieMap = Symptome::with('maladies')
            ->get()
            ->mapWithKeys(function ($s) {
                return [$s->id => $s->maladies->pluck('id')->toArray()];
            });

        $medicaments = Medicament::all();
        $salles = Salle::all();

        // ✅ Chargement des relations
        $consultation->load([
            'ordonnances.medicaments',
            'examens',
            'rendezVous',
            'hospitalisation',
            'symptomes',
            'maladies',
            'certificat'
        ]);

        $maladieSymptomesDetails = $maladies->mapWithKeys(function ($m) {
            return [$m->id => [
                'nom' => $m->nom,
                'symptomes' => $m->symptomes->pluck('id')->toArray()
            ]];
        });

        return view('application.consultation.create', compact(
            'tickets',
            'symptomes',
            'maladies',
            'symptomeMaladieMap',
            'maladieSymptomesDetails',
            'medicaments',
            'salles',
            'consultation'
        ));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Consultation $consultation)
    {
        abort_unless(Auth::user()->can('consultations.edit'), 403, 'Accès non autorisé : vous n\'avez pas la permission de modifier une consultation.');

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
                        'date_entree'     => $request->date_entree ?: now(),
                        'motif'           => $request->motif ?? 'Hospitalisation suite consultation',
                        'etat'            => 'en cours',
                        'service_id'      => \App\Models\Salle::find($request->salle_id)->service_medical_id ?? 1,
                        'observations'    => $request->observations,
                    ]);

                    // 🔹 Mettre à jour le statut du lit
                    Lit::where('id', $request->lit_id)->update(['statut' => 'Occupé']);
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
        abort_unless(Auth::user()->can('consultations.print'), 403, 'Accès non autorisé : vous n\'avez pas la permission d\'imprimer une consultation.');

        $consultation->load([
            'patient',
            'medecin',
            'ordonnances.medicaments',
            'examens',
            'rendezVous',
            'certificat',
            'hospitalisation.salle',
            'hospitalisation.lit',
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
        abort_unless(Auth::user()->can('consultations.delete'), 403, 'Accès non autorisé : vous n\'avez pas la permission de supprimer une consultation.');
        //
    }
}
