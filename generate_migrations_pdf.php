<?php

// Boot Laravel framework
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

echo "=========================================================\n";
echo "🏥 G-Santé : Génération du Rapport Technique PDF...\n";
echo "=========================================================\n";

// Get run migrations from database
$runMigrations = [];
try {
    $runMigrations = DB::table('migrations')
        ->orderBy('id', 'asc')
        ->pluck('batch', 'migration')
        ->toArray();
    echo "Connexion à la base de données réussie.\n";
} catch (\Exception $e) {
    echo "Note: Impossible de contacter la base de données (" . $e->getMessage() . ").\n";
    echo "Le rapport sera basé sur les fichiers locaux et marquera le statut comme 'En attente'.\n";
}

$migrationsPath = database_path('migrations');
if (!is_dir($migrationsPath)) {
    die("Erreur: Le dossier des migrations [database/migrations] est introuvable.\n");
}

$files = scandir($migrationsPath);
$migrations = [];

$totalCount = 0;
$runCount = 0;
$pendingCount = 0;
$createCount = 0;
$alterCount = 0;

foreach ($files as $file) {
    if ($file === '.' || $file === '..' || !str_ends_with($file, '.php')) {
        continue;
    }

    $migrationName = str_replace('.php', '', $file);
    $content = file_get_contents($migrationsPath . '/' . $file);
    
    // Check status
    $isRun = isset($runMigrations[$migrationName]);
    $batch = $isRun ? $runMigrations[$migrationName] : null;

    if ($isRun) {
        $runCount++;
    } else {
        $pendingCount++;
    }
    $totalCount++;

    $tablesCreated = [];
    $tablesAltered = [];
    $tablesDropped = [];

    // Up content extraction
    $upContent = '';
    $posUp = strpos($content, 'function up(');
    if ($posUp !== false) {
        $posBrace = strpos($content, '{', $posUp);
        if ($posBrace !== false) {
            $braceCount = 1;
            $idx = $posBrace + 1;
            $len = strlen($content);
            while ($idx < $len && $braceCount > 0) {
                if ($content[$idx] == '{') {
                    $braceCount++;
                } elseif ($content[$idx] == '}') {
                    $braceCount--;
                }
                $idx++;
            }
            $upContent = substr($content, $posBrace + 1, $idx - $posBrace - 2);
        }
    }
    if (empty($upContent)) {
        $upContent = $content;
    }

    preg_match_all("/Schema::create\(\s*['\"]([^'\"]+)['\"]/i", $upContent, $matchesCreate);
    preg_match_all("/Schema::table\(\s*['\"]([^'\"]+)['\"]/i", $upContent, $matchesTable);
    preg_match_all("/Schema::drop(?:IfExists)?\(\s*['\"]([^'\"]+)['\"]/i", $upContent, $matchesDrop);

    if (!empty($matchesCreate[1])) {
        $tablesCreated = array_unique($matchesCreate[1]);
        $createCount += count($tablesCreated);
    }
    if (!empty($matchesTable[1])) {
        $tablesAltered = array_unique($matchesTable[1]);
        $alterCount += count($tablesAltered);
    }
    if (!empty($matchesDrop[1])) {
        $tablesDropped = array_unique($matchesDrop[1]);
    }

    $columns = [];
    preg_match_all('/\$table->([a-zA-Z0-9_]+)\(([^)]*)\)/i', $upContent, $columnMatches, PREG_SET_ORDER);
    foreach ($columnMatches as $match) {
        $type = $match[1];
        $args = $match[2];

        if (in_array($type, ['foreign', 'unique', 'index', 'dropForeign', 'dropUnique', 'dropIndex', 'primary', 'onDelete', 'onUpdate'])) {
            continue;
        }

        $colName = '';
        if (preg_match('/^\s*[\'"]([a-zA-Z0-9_]+)[\'"]/', $args, $nameMatch)) {
            $colName = $nameMatch[1];
        }

        $typeDesc = match ($type) {
            'id' => 'Clé primaire auto-incrémentée',
            'uuid' => 'UUID unique',
            'string' => 'Chaîne de caractères',
            'text', 'longText', 'mediumText' => 'Texte long (CLOB)',
            'integer', 'unsignedInteger', 'bigInteger', 'unsignedBigInteger', 'tinyInteger', 'smallInteger' => 'Entier numérique',
            'decimal', 'float', 'double' => 'Nombre décimal',
            'boolean' => 'Booléen (vrai/faux)',
            'date' => 'Date simple',
            'dateTime', 'timestamp', 'timestamps' => 'Horodatage (date & heure)',
            'foreignId', 'foreignIdFor' => 'Clé étrangère (relation)',
            'dropColumn' => 'Suppression de colonne',
            default => 'Colonne de type ' . $type
        };

        if ($type === 'timestamps') {
            $columns[] = [
                'name' => 'created_at, updated_at',
                'type' => 'timestamps',
                'desc' => $typeDesc
            ];
        } elseif ($type === 'id' && empty($colName)) {
            $columns[] = [
                'name' => 'id',
                'type' => 'id',
                'desc' => $typeDesc
            ];
        } elseif ($colName) {
            $columns[] = [
                'name' => $colName,
                'type' => $type,
                'desc' => $typeDesc
            ];
        }
    }

    $cleanName = ucwords(str_replace('_', ' ', preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migrationName)));

    $migrations[] = [
        'file' => $file,
        'name' => $cleanName,
        'status' => $isRun ? 'Exécutée' : 'En attente',
        'batch' => $batch,
        'created_tables' => $tablesCreated,
        'altered_tables' => $tablesAltered,
        'dropped_tables' => $tablesDropped,
        'columns' => $columns
    ];
}

$stats = [
    'total' => $totalCount,
    'run' => $runCount,
    'pending' => $pendingCount,
    'created' => $createCount,
    'altered' => $alterCount
];

// Compile and output PDF
try {
    $pdf = Pdf::loadView('application.migrations.report', compact('migrations', 'stats'));
    $pdf->setPaper('a4', 'portrait');
    $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
    $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

    $pdfContent = $pdf->output();
    $outputPath = __DIR__ . '/rapport_migrations.pdf';
    $publicOutputPath = __DIR__ . '/public/rapport_migrations.pdf';
    
    file_put_contents($outputPath, $pdfContent);
    file_put_contents($publicOutputPath, $pdfContent);
    
    echo "---------------------------------------------------------\n";
    echo "✅ Succès! Le rapport PDF a été généré avec succès :\n";
    echo "  -> Racine du projet : $outputPath\n";
    echo "  -> Dossier public   : $publicOutputPath\n";
    echo "=========================================================\n";
} catch (\Exception $e) {
    die("❌ Erreur lors du rendu ou de l'écriture du PDF : " . $e->getMessage() . "\n");
}
