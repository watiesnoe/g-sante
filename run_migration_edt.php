<?php
/**
 * Script temporaire pour exécuter la migration emploi_du_temps
 * Exécuter avec : php run_migration_edt.php depuis la racine du projet
 */

define('LARAVEL_START', microtime(true));

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Migration Emploi du Temps ===\n\n";

if (Schema::hasTable('emploi_du_temps')) {
    echo "✅ Table 'emploi_du_temps' existe déjà.\n";
} else {
    try {
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_07_30_200000_create_emploi_du_temps_table.php',
            '--force' => true,
        ]);
        echo Artisan::output();
        echo "✅ Migration réussie !\n";
    } catch (\Exception $e) {
        echo "❌ Erreur : " . $e->getMessage() . "\n";
    }
}

echo "\n=== Fin ===\n";
