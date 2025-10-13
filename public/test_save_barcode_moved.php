<?php
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
use App\Http\Controllers\MedicamentController;

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

echo "Original medicament info:\n";
echo " - Nom: " . $medicament->nom . "\n";
echo " - Code Barre: " . ($medicament->code_barre ?? 'NULL') . "\n";

$oldNom = $medicament->nom;
$oldCodeBarre = $medicament->code_barre;
$oldPrixAchat = $firstUnit->prix_achat;
$oldPrixVente = $firstUnit->prix_vente;

$newCodeBarre = '1234567890123';

$request = Request::create('/medicaments/' . $medicament->uuid, 'PUT', [
    'nom' => $medicament->nom,
    'code_barre' => $newCodeBarre,
    'description' => $medicament->description,
    'stock' => $medicament->stock,
    'stock_min' => $medicament->stock_min,
    'famille_id' => $medicament->famille_id,
    'default_unit_idx' => 0,
    'unites' => [
        [
            'id' => $firstUnit->id,
            'nom' => $firstUnit->nom,
            'symbole' => $firstUnit->symbole,
            'facteur' => $firstUnit->facteur,
            'prix_achat' => 10,
            'prix_vente' => 15,
        ]
    ]
]);

try {
    // Call controller action directly to bypass all middlewares (including CSRF)
    $controller = $app->make(MedicamentController::class);
    $response = $controller->update($request, $medicament->uuid);

    echo "\nResponse Status: " . $response->getStatusCode() . "\n";
    echo "Response Content:\n";
    echo $response->getContent() . "\n";

    // Refresh database and check updated values
    $medicament->refresh();
    echo "\nUpdated medicament info in DB:\n";
    echo " - Nom: " . $medicament->nom . "\n";
    echo " - Code Barre: " . ($medicament->code_barre ?? 'NULL') . "\n";

    // Revert the changes to keep DB clean
    $medicament->update([
        'nom' => $oldNom,
        'code_barre' => $oldCodeBarre,
    ]);
    $firstUnit->update([
        'prix_achat' => $oldPrixAchat,
        'prix_vente' => $oldPrixVente,
    ]);
    echo "\nReverted changes back successfully.\n";

} catch (\Exception $e) {
    echo "Error during test: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
