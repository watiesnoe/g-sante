<?php

namespace App\Http\Controllers;

use App\Models\EmploiDuTemps;
use App\Models\User;
use App\Models\ServiceMedical;
use App\Models\Salle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmploiDuTempsController extends Controller
{
    /**
     * Affiche la vue principale (planning hebdomadaire de tous les médecins)
     */
    public function index(Request $request)
    {
        $medecins = User::role('medecin')
            ->where('statut', 'actif')
            ->with(['emploiDuTemps' => fn($q) => $q->actif()->orderBy('jour_semaine')->orderBy('heure_debut')])
            ->orderBy('nom')
            ->get();

        $jours = EmploiDuTemps::$jours;

        // Vue "par jour" : médecins disponibles pour chaque jour
        $parJour = [];
        foreach ($jours as $num => $nom) {
            $parJour[$num] = $medecins->filter(function ($medecin) use ($num) {
                return $medecin->emploiDuTemps->where('jour_semaine', $num)->isNotEmpty();
            })->values();
        }

        // Jour courant de la semaine (1 = Lundi)
        $jourCourant = Carbon::now()->dayOfWeekIso; // 1-7

        $allCreneaux = EmploiDuTemps::with('medecin')->get()->map(function ($c) {
            return [
                'id'           => $c->id,
                'medecin_id'   => $c->medecin_id,
                'jour_semaine' => $c->jour_semaine,
                'heure_debut'  => substr($c->heure_debut, 0, 5),
                'heure_fin'    => substr($c->heure_fin, 0, 5),
                'service'      => $c->service,
                'lieu'         => $c->lieu,
                'notes'        => $c->notes,
            ];
        });

        $servicesList = ServiceMedical::with('salles')->orderBy('nom')->get();
        $allSalles = Salle::orderBy('nom')->get();

        return view('application.emploi_du_temps.index', compact(
            'medecins', 'jours', 'parJour', 'jourCourant', 'allCreneaux', 'servicesList', 'allSalles'
        ));
    }

    /**
     * Retourne les créneaux d'un médecin (API JSON pour le modal)
     */
    public function getMedecinCreneaux(Request $request)
    {
        $medecinId = $request->medecin_id;

        $creneaux = EmploiDuTemps::where('medecin_id', $medecinId)
            ->actif()
            ->orderBy('jour_semaine')
            ->orderBy('heure_debut')
            ->get()
            ->map(function ($c) {
                return [
                    'id'          => $c->id,
                    'uuid'        => $c->uuid,
                    'jour_semaine'=> $c->jour_semaine,
                    'jour_nom'    => $c->jour_nom,
                    'heure_debut' => substr($c->heure_debut, 0, 5),
                    'heure_fin'   => substr($c->heure_fin, 0, 5),
                    'service'     => $c->service,
                    'lieu'        => $c->lieu,
                    'notes'       => $c->notes,
                ];
            });

        return response()->json(['creneaux' => $creneaux]);
    }

    /**
     * Retourne les médecins disponibles un jour donné (1-7)
     */
    public function disponiblesParJour(Request $request)
    {
        $jour = (int) $request->get('jour', Carbon::now()->dayOfWeekIso);

        $medecins = User::role('medecin')
            ->where('statut', 'actif')
            ->whereHas('emploiDuTemps', fn($q) => $q->where('jour_semaine', $jour)->actif())
            ->with(['emploiDuTemps' => fn($q) => $q->where('jour_semaine', $jour)->actif()->orderBy('heure_debut')])
            ->orderBy('nom')
            ->get()
            ->map(function ($medecin) {
                return [
                    'id'       => $medecin->id,
                    'uuid'     => $medecin->uuid,
                    'nom'      => $medecin->prenom . ' ' . $medecin->nom,
                    'photo'    => $medecin->photo ? asset('storage/' . $medecin->photo) : null,
                    'creneaux' => $medecin->emploiDuTemps->map(fn($c) => [
                        'heure_debut' => substr($c->heure_debut, 0, 5),
                        'heure_fin'   => substr($c->heure_fin, 0, 5),
                        'service'     => $c->service,
                        'lieu'        => $c->lieu,
                    ]),
                ];
            });

        return response()->json(['medecins' => $medecins, 'jour' => $jour]);
    }

    /**
     * Enregistre ou met à jour un créneau
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'medecin_id'   => 'required|exists:users,id',
            'jour_semaine' => 'required|integer|between:1,7',
            'heure_debut'  => 'required|date_format:H:i',
            'heure_fin'    => 'required|date_format:H:i|after:heure_debut',
            'service'      => 'nullable|string|max:100',
            'lieu'         => 'nullable|string|max:150',
            'notes'        => 'nullable|string|max:500',
        ]);

        $creneau = EmploiDuTemps::updateOrCreate(
            ['id' => $request->id],
            array_merge($validated, ['statut' => 'actif'])
        );

        return response()->json([
            'success' => true,
            'message' => $request->id ? 'Créneau mis à jour.' : 'Créneau ajouté.',
            'data'    => $creneau,
        ]);
    }

    /**
     * Supprime (désactive) un créneau
     */
    public function destroy(EmploiDuTemps $emploiDuTemp)
    {
        $emploiDuTemp->delete();

        return response()->json(['success' => true, 'message' => 'Créneau supprimé.']);
    }
}
