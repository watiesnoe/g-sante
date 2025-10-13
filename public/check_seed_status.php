<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

header('Content-Type: text/plain; charset=utf-8');
try {
    $counts = [
        'users' => DB::table('users')->count(),
        'medicaments' => DB::table('medicaments')->count(),
        'unites' => DB::table('unites')->count(),
        'symptomes' => DB::table('symptomes')->count(),
        'maladies' => DB::table('maladies')->count(),
    ];
    echo "Seed status counts:\n";
    print_r($counts);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
