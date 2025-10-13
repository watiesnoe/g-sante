<?php
$_ENV['APP_ENV'] = 'testing';
putenv('APP_ENV=testing');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$consoleKernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$consoleKernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Medicament;
use App\Models\Unite;
use Illuminate\Http\Request;

// Login as superadmin
$user = User::first();
Auth::login($user);

// Fetch a medicament and its units
$medicament = Medicament::with('unites')->first();
if (!$medicament) {
    echo "No medicaments found!\n";
    exit;
}

$firstUnit = $medicament->unites->first();
if (!$firstUnit) {
    echo "No units found for medicament " . $medicament->nom . "\n";
    exit;
}

echo "Original unit info:\n";
echo " - Nom: " . $firstUnit->nom . "\n";
echo " - Prix Achat: " . $firstUnit->prix_achat . "\n";
echo " - Prix Vente: " . $firstUnit->prix_vente . "\n";

// Use HTTP Kernel to handle PUT request
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$newNom = $firstUnit->nom . " Updated";
$newPrixAchat = $firstUnit->prix_achat + 10;
$newPrixVente = $firstUnit->prix_vente + 15;

$request = Request::create('/medicaments/' . $medicament->uuid, 'PUT', [
    'nom' => $medicament->nom,
    'description' => $medicament->description,
    'stock' => $medicament->stock,
    'stock_min' => $medicament->stock_min,
    'famille_id' => $medicament->famille_id,
    'default_unit_idx' => 0,
    'unites' => [
        [
            'id' => $firstUnit->id,
            'nom' => $newNom,
            'symbole' => $firstUnit->symbole,
            'facteur' => $firstUnit->facteur,
            'prix_achat' => $newPrixAchat,
            'prix_vente' => $newPrixVente,
        ]
    ]
]);

// Mark as AJAX
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

try {
    $response = $httpKernel->handle($request);
    echo "\nResponse Status: " . $response->getStatusCode() . "\n";
    echo "Response Content:\n";
    echo $response->getContent() . "\n";

    // Refresh database and check updated values
    $firstUnit->refresh();
    echo "\nUpdated unit info in DB:\n";
    echo " - Nom: " . $firstUnit->nom . "\n";
    echo " - Prix Achat: " . $firstUnit->prix_achat . "\n";
    echo " - Prix Vente: " . $firstUnit->prix_vente . "\n";

    // Revert the changes to keep DB clean
    $firstUnit->update([
        'nom' => str_replace(" Updated", "", $newNom),
        'prix_achat' => $firstUnit->prix_achat - 10,
        'prix_vente' => $firstUnit->prix_vente - 15,
    ]);
    echo "\nReverted changes back successfully.\n";

} catch (\Exception $e) {
    echo "Error during test: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

$httpKernel->terminate($request, $response);
