<?php
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Columns in unites table:\n";
$columns = DB::select('SHOW COLUMNS FROM unites');
foreach ($columns as $col) {
    echo "  - " . $col->Field . " (" . $col->Type . ")\n";
}

echo "\nVerifying code_barre data in unites:\n";
$unites = DB::select('SELECT id, nom, code_barre FROM unites LIMIT 5');
foreach ($unites as $u) {
    echo "  ID: {$u->id}, Nom: {$u->nom}, Code Barre: " . ($u->code_barre ?? 'NULL') . "\n";
}

echo "\nOK - Migration applied and column exists!\n";
