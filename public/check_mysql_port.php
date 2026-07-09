<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

try {
    $config = DB::connection()->getConfig();
    header('Content-Type: text/plain; charset=utf-8');
    echo "Laravel DB Config:\n";
    print_r($config);
    
    $tables = DB::select('SHOW TABLES');
    echo "\nTables count: " . count($tables) . "\n";
    print_r($tables);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
