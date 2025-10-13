<?php

namespace Database\Seeders;

use App\Models\Etablissement;
use App\Models\ServiceMedical;
use App\Models\User;
use App\Services\EtablissementService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Établissements de démonstration, chacun avec le personnel de sa structure.
 * Relançable sans doublon : un établissement ou un compte existant est réutilisé.
 *
 *   php artisan db:seed --class=EtablissementsDemoSeeder
 *
 * Comptes : <rôle>[N]@<domaine de l'établissement>, mot de passe MOT_DE_PASSE.
 */
class EtablissementsDemoSeeder extends Seeder
{
    public const MOT_DE_PASSE = 'Sante2026demo';

    /** Personnel type par structure : rôle => nombre de comptes. */
    private const PERSONNEL = [
        'cabinet'  => ['admin' => 1, 'medecin' => 1, 'secretaire' => 1],
        'clinique' => ['admin' => 1, 'medecin' => 2, 'secretaire' => 1, 'infirmier' => 2, 'pharmacien' => 1, 'laborantin' => 1, 'comptable' => 1],
        'csref'    => ['admin' => 1, 'medecin' => 2, 'sage_femme' => 2, 'infirmier' => 2, 'secretaire' => 1, 'pharmacien' => 1, 'gestionnaire_stock' => 1, 'laborantin' => 1, 'comptable' => 1],
        'hopital'  => ['admin' => 1, 'medecin' => 3, 'sage_femme' => 1, 'infirmier' => 3, 'secretaire' => 2, 'pharmacien' => 1, 'gestionnaire_stock' => 1, 'laborantin' => 1, 'comptable' => 1],
    ];

    /** Services médicaux de départ par structure. */
    private const SERVICES = [
        'cabinet'  => ['Médecine générale'],
        'clinique' => ['Médecine générale', 'Pédiatrie', 'Gynécologie'],
        'csref'    => ['Médecine générale', 'Maternité', 'Pédiatrie', 'Chirurgie'],
        'hopital'  => ['Urgences', 'Médecine interne', 'Pédiatrie', 'Gynécologie-obstétrique', 'Chirurgie', 'Bloc opératoire'],
    ];

    private const ETABLISSEMENTS = [
        ['nom' => 'CABINET WASSA',              'type' => 'cabinet',  'code' => 'WASSA',   'domaine' => 'cabinet-wassa.ml'],
        ['nom' => 'HOPITAL NIANNAKORO FOMBA',   'type' => 'hopital',  'code' => 'HNF',     'domaine' => 'hopital-nf.ml'],
        ['nom' => 'CLINIQUE AMITIE',            'type' => 'clinique', 'code' => 'AMITIE',  'domaine' => 'clinique-amitie.ml'],
        ['nom' => 'CSREF FAMORY DOUMBIA',       'type' => 'csref',    'code' => 'CSREFFD', 'domaine' => 'csref-fd.ml'],
    ];

    private const LIBELLES = [
        'admin' => 'Propriétaire', 'medecin' => 'Médecin', 'secretaire' => 'Secrétaire', 'infirmier' => 'Infirmier', 'sage_femme' => 'Sage-femme',
        'pharmacien' => 'Pharmacien', 'laborantin' => 'Laborantin', 'comptable' => 'Caissier', 'gestionnaire_stock' => 'Magasinier',
    ];

    public function run(EtablissementService $service): void
    {
        // La création rattache l'auteur à l'établissement : on agit comme un super-admin
        $superAdmin = User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->orderBy('id')->first();
        if ($superAdmin) {
            Auth::setUser($superAdmin);
        }

        foreach (self::ETABLISSEMENTS as $donnees) {
            $etablissement = Etablissement::where('code', $donnees['code'])->first()
                ?? $service->creer(['nom' => $donnees['nom'], 'type' => $donnees['type'], 'code' => $donnees['code']]);

            app()->instance('etablissement.courant', $etablissement);
            try {
                foreach (self::SERVICES[$donnees['type']] as $nomService) {
                    ServiceMedical::firstOrCreate(['nom' => $nomService]);
                }
                $serviceParDefaut = ServiceMedical::orderBy('id')->value('id');

                $comptes = 0;
                foreach (self::PERSONNEL[$donnees['type']] as $role => $nombre) {
                    for ($i = 1; $i <= $nombre; $i++) {
                        $this->compte($etablissement, $donnees['domaine'], $role, $i, $nombre, $role === 'medecin' ? $serviceParDefaut : null);
                        $comptes++;
                    }
                }
            } finally {
                Etablissement::oublierCourant();
            }

            $this->command?->info(sprintf('%-28s %-9s %2d comptes  (*@%s)', $etablissement->nom, $etablissement->type, $comptes, $donnees['domaine']));
        }
        $this->command?->line('Mot de passe de tous ces comptes : ' . self::MOT_DE_PASSE);
    }

    private function compte(Etablissement $etablissement, string $domaine, string $role, int $rang, int $nombre, ?int $serviceId): User
    {
        $suffixe = $nombre > 1 ? $rang : '';
        $email = ($role === 'admin' ? 'proprietaire' : str_replace('_', '', $role)) . $suffixe . '@' . $domaine;

        $user = User::firstOrNew(['email' => $email]);
        $user->fill([
            'name'               => trim(self::LIBELLES[$role] . ' ' . $suffixe) . ' — ' . $etablissement->nom,
            'nom'                => mb_strtoupper(self::LIBELLES[$role]) . ($suffixe ? ' ' . $suffixe : ''),
            'prenom'             => Str::title(mb_strtolower(explode(' ', $etablissement->nom, 2)[1] ?? $etablissement->nom)),
            'password'           => self::MOT_DE_PASSE,
            'statut'             => 'actif',
            'service_medical_id' => $serviceId,
        ]);
        $user->forceFill(['uuid' => $user->uuid ?? (string) Str::uuid(), 'doit_changer_mot_de_passe' => false])->save();
        $user->syncRoles([$role]);
        $user->etablissements()->syncWithoutDetaching([$etablissement->id]);

        return $user;
    }
}
