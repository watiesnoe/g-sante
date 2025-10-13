<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    Schema::disableForeignKeyConstraints();
    
    $tables = DB::select('SHOW TABLES');
    echo "Dropping " . count($tables) . " tables:\n";
    
    foreach ($tables as $tableObj) {
        $table = current((array)$tableObj);
        Schema::dropIfExists($table);
        echo "Dropped table: $table\n";
    }
    
    Schema::enableForeignKeyConstraints();
    echo "Database has been completely reset!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
