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

echo "Original unit info:\n";
echo " - Nom: " . $firstUnit->nom . "\n";
echo " - Prix Achat: " . $firstUnit->prix_achat . "\n";
echo " - Prix Vente: " . $firstUnit->prix_vente . "\n";

$oldNom = $firstUnit->nom;
$oldPrixAchat = $firstUnit->prix_achat;
$oldPrixVente = $firstUnit->prix_vente;

$newNom = "GeluleTest";
$newPrixAchat = 10;
$newPrixVente = 15;

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

try {
    // Call controller action directly to bypass all middlewares (including CSRF)
    $controller = $app->make(MedicamentController::class);
    $response = $controller->update($request, $medicament->uuid);

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
        'nom' => $oldNom,
        'prix_achat' => $oldPrixAchat,
        'prix_vente' => $oldPrixVente,
    ]);
    echo "\nReverted changes back successfully.\n";

} catch (\Exception $e) {
    echo "Error during test: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
