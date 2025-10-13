<?php

namespace Tests\Support;

use App\Models\CaisseSession;
use App\Models\Consultation;
use App\Models\Etablissement;
use App\Models\Famille;
use App\Models\Medicament;
use App\Models\Ordonnance;
use App\Models\OrdonnanceMedicament;
use App\Models\Patient;
use App\Models\Prestation;
use App\Models\ServiceMedical;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Jeux de données fictifs pour les tests de recette (section 14 du cahier des charges).
 */
class Fabrique
{
    /** Configure l'établissement de test : tous les modules, paramètres au choix. */
    public static function etablissement(string $type = 'clinique', array $parametres = []): Etablissement
    {
        $etablissement = Etablissement::query()->firstOrFail();
        $etablissement->update([
            'type' => $type,
            'modules' => array_keys(Etablissement::MODULES),
            'parametres' => $parametres ?: null,
            'configure' => true,
        ]);

        return Etablissement::courant();
    }

    public static function utilisateur(array $permissions = []): User
    {
        $user = User::factory()->create(['name' => 'user_'.Str::random(8)]);
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        $user->givePermissionTo($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    public static function ouvrirCaisse(User $user, float $fond = 0): CaisseSession
    {
        return CaisseSession::create([
            'user_id' => $user->id,
            'solde_initial' => $fond,
            'solde_theorique' => $fond,
            'statut' => 'ouverte',
            'opened_at' => now(),
        ]);
    }

    public static function medicament(string $nom = 'Paracétamol 500', float $prixVente = 100): Medicament
    {
        $famille = Famille::firstOrCreate(['nom' => 'Famille test']);
        $medicament = Medicament::create(['nom' => $nom, 'famille_id' => $famille->id, 'stock' => 0, 'stock_min' => 0]);
        $medicament->unites()->create([
            'nom' => 'Comprimé', 'symbole' => 'cp', 'facteur' => 1,
            'prix_achat' => $prixVente / 2, 'prix_vente' => $prixVente, 'is_default' => true,
        ]);

        return $medicament->fresh();
    }

    public static function patient(): Patient
    {
        return Patient::create([
            'nom' => 'Traoré', 'prenom' => 'Awa', 'genre' => 'F',
            'telephone' => '7'.random_int(1000000, 9999999), 'age' => 30,
        ]);
    }

    public static function prestation(float $prix = 5000): Prestation
    {
        $service = ServiceMedical::firstOrCreate(['nom' => 'Médecine générale']);

        return Prestation::create(['service_medical_id' => $service->id, 'nom' => 'Consultation', 'prix' => $prix]);
    }

    /** Ordonnance d'un patient comprenant un médicament en quantité donnée. */
    public static function ordonnance(Medicament $medicament, int $quantite, ?User $medecin = null): Ordonnance
    {
        $medecin ??= self::utilisateur();
        $consultation = Consultation::create(['patient_id' => self::patient()->id, 'medecin_id' => $medecin->id, 'motif' => 'Test']);
        $ordonnance = Ordonnance::create(['consultation_id' => $consultation->id, 'date' => today(), 'statutordo' => 'impaye']);
        OrdonnanceMedicament::create([
            'ordonnance_id' => $ordonnance->id, 'medicament_id' => $medicament->id,
            'posologie' => '1 x 3', 'duree_jours' => 5, 'quantite' => $quantite,
        ]);

        return $ordonnance;
    }

    /** Second établissement, avec un compte qui n'y est pas affecté par défaut. */
    public static function autreEtablissement(string $type = 'clinique'): Etablissement
    {
        return Etablissement::create([
            'type' => $type, 'nom' => 'Autre établissement', 'code' => 'AUTRE',
            'modules' => Etablissement::PROFILS[$type], 'configure' => true, 'actif' => true,
        ]);
    }

    /** Exécute un traitement comme si l'établissement donné était l'établissement courant. */
    public static function dans(Etablissement $etablissement, callable $traitement)
    {
        app()->instance('etablissement.courant', $etablissement);
        try {
            return $traitement();
        } finally {
            Etablissement::oublierCourant();
        }
    }
}
