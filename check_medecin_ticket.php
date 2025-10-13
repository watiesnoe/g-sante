<?php
// Diagnostic : vérifier hasModuleAccess pour tous les rôles et modules

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$modules = [
    'patient', 'ticket', 'consultation', 'rendezvous', 'ordonnance',
    'examens', 'hospitalisation', 'transfert', 'maternity', 'infectiologie',
    'stock', 'paiements', 'caisse', 'parametre', 'users', 'roles',
];

// Prendre un utilisateur de chaque rôle
$roles = ['medecin', 'secretaire', 'infirmier', 'pharmacien', 'laborantin', 'comptable', 'gestionnaire_stock', 'sage_femme'];

foreach ($roles as $roleName) {
    $user = User::role($roleName)->first();
    if (!$user) {
        echo "⚠️  Aucun utilisateur avec le rôle [$roleName]\n";
        continue;
    }

    echo "\n════════════════════════════════════════\n";
    echo "👤 {$user->name} — rôle : {$roleName}\n";
    echo "════════════════════════════════════════\n";

    $allowed = [];
    $denied  = [];

    foreach ($modules as $module) {
        if ($user->hasModuleAccess($module)) {
            $allowed[] = $module;
        } else {
            $denied[] = $module;
        }
    }

    echo "  ✅ Accès accordé  : " . (empty($allowed) ? '(aucun)' : implode(', ', $allowed)) . "\n";
    echo "  ❌ Accès refusé   : " . (empty($denied)  ? '(aucun)' : implode(', ', $denied))  . "\n";

    // Vérifier la cohérence avec les permissions Spatie
    $spatieModules = $user->getAllPermissions()
        ->map(fn($p) => explode('.', $p->name)[0])
        ->unique()
        ->values()
        ->toArray();
    echo "  🔑 Préfixes Spatie : " . implode(', ', $spatieModules) . "\n";
}

echo "\n✅ Diagnostic terminé.\n";
echo "ℹ️  Seul le seeder (PermissionRoleSeeder) contrôle les accès.\n";
