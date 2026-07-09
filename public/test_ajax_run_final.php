<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

// Bootstrap Laravel via Console Kernel
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php'; // Use require instead of require_once

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

// Login as user with permission to bypass auth checks
$user = User::first();
if (!$user) {
    echo "No users found in database!\n";
    exit;
}
Auth::login($user);

echo "Logged in as User ID: " . $user->id . " (" . $user->email . ")\n";

// Use Laravel Http Kernel to handle sub-requests
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test 1: Standard Ajax Datatable fetch
echo "\n--- Test 1: Fetching medicaments via Ajax ---\n";
$request = Request::create('/medicaments', 'GET', [
    'draw' => 1,
    'columns' => [
        ['data' => 'checkbox', 'name' => 'checkbox', 'searchable' => 'false', 'orderable' => 'false'],
        ['data' => 'nom', 'name' => 'nom', 'searchable' => 'true', 'orderable' => 'true'],
        ['data' => 'unite', 'name' => 'unite', 'searchable' => 'false', 'orderable' => 'false'],
        ['data' => 'famille', 'name' => 'famille', 'searchable' => 'false', 'orderable' => 'false'],
        ['data' => 'stock', 'name' => 'stock', 'searchable' => 'true', 'orderable' => 'true'],
        ['data' => 'stock_min', 'name' => 'stock_min', 'searchable' => 'true', 'orderable' => 'true'],
        ['data' => 'prix_achat', 'name' => 'prix_achat', 'searchable' => 'false', 'orderable' => 'false'],
        ['data' => 'prix_vente', 'name' => 'prix_vente', 'searchable' => 'false', 'orderable' => 'false'],
        ['data' => 'actions', 'name' => 'actions', 'searchable' => 'false', 'orderable' => 'false']
    ],
    'order' => [
        ['column' => 1, 'dir' => 'asc']
    ],
    'start' => 0,
    'length' => 10,
    'search' => ['value' => '', 'regex' => 'false']
]);

// Mark as AJAX
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

try {
    $response = $httpKernel->handle($request);
    echo "Response Status: " . $response->getStatusCode() . "\n";
    $content = $response->getContent();
    $data = json_decode($content, true);

    if (isset($data['data'])) {
        echo "Fetched " . count($data['data']) . " records successfully.\n";
        if (count($data['data']) > 0) {
            echo "First item nom: " . $data['data'][0]['nom'] . "\n";
            echo "First item default unit: " . ($data['data'][0]['unite'] ?? 'None') . "\n";
            echo "First item prix_achat: " . ($data['data'][0]['prix_achat'] ?? 'None') . "\n";
        }
    } else {
        echo "Error fetching data or invalid JSON response:\n";
        echo substr($content, 0, 2000) . "\n";
    }
} catch (\Exception $e) {
    echo "Exception during Test 1: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

// Test 2: Search for "parac" (this used to crash!)
echo "\n--- Test 2: Searching for 'parac' (Datatable) ---\n";
$requestSearch = Request::create('/medicaments', 'GET', [
    'draw' => 2,
    'columns' => [
        ['data' => 'checkbox', 'name' => 'checkbox', 'searchable' => 'false', 'orderable' => 'false'],
        ['data' => 'nom', 'name' => 'nom', 'searchable' => 'true', 'orderable' => 'true'],
        ['data' => 'unite', 'name' => 'unite', 'searchable' => 'false', 'orderable' => 'false'],
        ['data' => 'famille', 'name' => 'famille', 'searchable' => 'false', 'orderable' => 'false'],
        ['data' => 'stock', 'name' => 'stock', 'searchable' => 'true', 'orderable' => 'true'],
        ['data' => 'stock_min', 'name' => 'stock_min', 'searchable' => 'true', 'orderable' => 'true'],
        ['data' => 'prix_achat', 'name' => 'prix_achat', 'searchable' => 'false', 'orderable' => 'false'],
        ['data' => 'prix_vente', 'name' => 'prix_vente', 'searchable' => 'false', 'orderable' => 'false'],
        ['data' => 'actions', 'name' => 'actions', 'searchable' => 'false', 'orderable' => 'false']
    ],
    'order' => [
        ['column' => 1, 'dir' => 'asc']
    ],
    'start' => 0,
    'length' => 10,
    'search' => ['value' => 'parac', 'regex' => 'false']
]);

$requestSearch->headers->set('X-Requested-With', 'XMLHttpRequest');

try {
    $responseSearch = $httpKernel->handle($requestSearch);
    echo "Response Status: " . $responseSearch->getStatusCode() . "\n";
    $contentSearch = $responseSearch->getContent();
    $dataSearch = json_decode($contentSearch, true);

    if (isset($dataSearch['data'])) {
        echo "Search results count: " . count($dataSearch['data']) . " records.\n";
        foreach ($dataSearch['data'] as $item) {
            echo " - " . $item['nom'] . " (Default unit: " . $item['unite'] . ", P. Achat: " . $item['prix_achat'] . ")\n";
        }
    } else {
        echo "Error searching data:\n";
        echo substr($contentSearch, 0, 2000) . "\n";
    }
} catch (\Exception $e) {
    echo "Exception during Test 2: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

$httpKernel->terminate($request, $response);
