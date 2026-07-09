<?php
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

echo "Running migrate...\n";
$exitCode = Artisan::call('migrate', ['--force' => true]);
echo Artisan::output();
echo "\nExit code: $exitCode\n";

echo "\nColumns in unites table:\n";
$columns = DB::select('SHOW COLUMNS FROM unites');
foreach ($columns as $col) {
    echo "  - " . $col->Field . " (" . $col->Type . ")\n";
}

echo "\nColumns in medicaments table:\n";
$columns = DB::select('SHOW COLUMNS FROM medicaments');
foreach ($columns as $col) {
    echo "  - " . $col->Field . " (" . $col->Type . ")\n";
}
