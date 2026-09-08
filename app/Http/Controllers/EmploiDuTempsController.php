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
     * Affiche la vue principale
     * - Pour un médecin: affiche son propre emploi du temps.
     * - Pour un admin/superadmin: affiche l'emploi du temps de tous les médecins et permet la planification.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $isAdmin = $user->hasRole(['super_admin', 'superadmin', 'admin']);
        $isMedecinOnly = $user->hasRole('medecin') && !$isAdmin;

        if ($isMedecinOnly) {
            $medecins = User::where('id', $user->id)
                ->with(['emploiDuTemps' => fn($q) => $q->actif()->orderBy('jour_semaine')->orderBy('heure_debut')])
                ->get();
        } else {
            $medecins = User::role('medecin')
                ->where('statut', 'actif')
                ->with(['emploiDuTemps' => fn($q) => $q->actif()->orderBy('jour_semaine')->orderBy('heure_debut')])
                ->orderBy('nom')
                ->get();
        }

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

        $allCreneauxQuery = EmploiDuTemps::with('medecin');
        if ($isMedecinOnly) {
            $allCreneauxQuery->where('medecin_id', $user->id);
        }

        $allCreneaux = $allCreneauxQuery->get()->map(function ($c) {
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
            'medecins', 'jours', 'parJour', 'jourCourant', 'allCreneaux', 'servicesList', 'allSalles', 'isMedecinOnly', 'isAdmin'
        ));
    }

    /**
     * Retourne les créneaux d'un médecin (API JSON pour le modal)
     */
    public function getMedecinCreneaux(Request $request)
    {
        $user = Auth::user();
        $medecinId = $request->medecin_id;

        if ($user->hasRole('medecin') && !$user->hasRole(['super_admin', 'superadmin', 'admin'])) {
            $medecinId = $user->id;
        }

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
     * Enregistre ou met à jour un créneau (réservé aux admins/superadmins)
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole(['super_admin', 'superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé. Seuls les administrateurs peuvent planifier l\'emploi du temps.'
            ], 403);
        }

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
     * Supprime (désactive) un créneau (réservé aux admins/superadmins)
     */
    public function destroy(EmploiDuTemps $emploiDuTemp)
    {
        if (!Auth::user()->hasRole(['super_admin', 'superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé. Seuls les administrateurs peuvent modifier l\'emploi du temps.'
            ], 403);
        }

        $emploiDuTemp->delete();

        return response()->json(['success' => true, 'message' => 'Créneau supprimé.']);
    }
}
