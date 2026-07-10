<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class MigrationReportController extends Controller
{
    /**
     * Parse migration files and generate a comprehensive PDF report.
     */
    public function downloadPdf()
    {
        // Check if user has permission (allow super_admin, superadmin, admin roles)
        $user = Auth::user();
        if (!$user || !$user->hasRole(['super_admin', 'superadmin', 'admin'])) {
            abort(403, 'Accès non autorisé : vous devez être administrateur pour générer ce rapport.');
        }

        // Get run migrations from database
        $runMigrations = [];
        try {
            $runMigrations = DB::table('migrations')
                ->orderBy('id', 'asc')
                ->pluck('batch', 'migration')
                ->toArray();
        } catch (\Exception $e) {
            // Safe fallback if DB is not reachable
        }

        // Get migrations files
        $migrationsPath = database_path('migrations');
        if (!is_dir($migrationsPath)) {
            abort(404, 'Dossier des migrations introuvable.');
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

            // Extract table creation or alteration
            $tablesCreated = [];
            $tablesAltered = [];
            $tablesDropped = [];

            // Braces parsing to isolate the up() function content
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

            // Find create/table/drop commands
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

            // Extract columns definition
            $columns = [];
            preg_match_all('/\$table->([a-zA-Z0-9_]+)\(([^)]*)\)/i', $upContent, $columnMatches, PREG_SET_ORDER);
            foreach ($columnMatches as $match) {
                $type = $match[1];
                $args = $match[2];

                // Skip keywords that are not column definitions
                if (in_array($type, ['foreign', 'unique', 'index', 'dropForeign', 'dropUnique', 'dropIndex', 'primary', 'onDelete', 'onUpdate'])) {
                    continue;
                }

                // Extract column name
                $colName = '';
                if (preg_match('/^\s*[\'"]([a-zA-Z0-9_]+)[\'"]/', $args, $nameMatch)) {
                    $colName = $nameMatch[1];
                }

                // Friendly type description
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

            // Clean name for display (e.g. "create_service_medicals_table" -> "Create Service Medicals Table")
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

        // Generate PDF using DomPDF
        $pdf = Pdf::loadView('application.migrations.report', compact('migrations', 'stats'));
        
        $pdf->setPaper('a4', 'portrait');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

        // Save a copy in public folder for easy local access
        try {
            $pdfContent = $pdf->output();
            $publicPath = public_path('rapport_migrations.pdf');
            file_put_contents($publicPath, $pdfContent);
        } catch (\Exception $e) {
            // Suppress error if public folder is read-only
        }

        return $pdf->download('rapport_migrations_g_sante.pdf');
    }
}
