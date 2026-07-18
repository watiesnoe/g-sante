<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExportImportController extends Controller
{
    /**
     * Mapping module slug → [model class, permission gate, unique_by field, label]
     */
    protected array $modules = [
        'services'    => ['model' => \App\Models\ServiceMedical::class, 'permission' => 'parametres.services',  'unique_by' => 'nom',    'label' => 'Structures'],
        'prestations' => ['model' => \App\Models\Prestation::class,     'permission' => 'parametres.prestations','unique_by' => 'nom',    'label' => 'Prestations'],
        'salles'      => ['model' => \App\Models\Salle::class,           'permission' => 'parametres.salles',    'unique_by' => 'nom',    'label' => 'Salles'],
        'lits'        => ['model' => \App\Models\Lit::class,             'permission' => 'parametres.lits',      'unique_by' => 'numero', 'label' => 'Lits'],
        'assurances'  => ['model' => \App\Models\Assurance::class,       'permission' => 'parametres.assurances','unique_by' => 'nom',    'label' => 'Assurances'],
        'symptomes'   => ['model' => \App\Models\Symptome::class,        'permission' => 'parametres.symptomes', 'unique_by' => 'nom',    'label' => 'Symptômes'],
        'maladies'    => ['model' => \App\Models\Maladie::class,         'permission' => 'parametres.maladies',  'unique_by' => 'nom',    'label' => 'Maladies'],
        'medicaments' => ['model' => \App\Models\Medicament::class,      'permission' => 'stock.medicaments',    'unique_by' => 'nom',    'label' => 'Médicaments'],
        'familles'    => ['model' => \App\Models\Famille::class,         'permission' => 'stock.familles',       'unique_by' => 'nom',    'label' => 'Familles'],
        'unites'      => ['model' => \App\Models\Unite::class,           'permission' => 'stock.unites',         'unique_by' => 'nom',    'label' => 'Unités'],
        'roles'       => ['model' => \Spatie\Permission\Models\Role::class,'permission'=> 'roles.view',          'unique_by' => 'name',   'label' => 'Rôles'],
        'users'       => ['model' => \App\Models\User::class,            'permission' => 'users.view',           'unique_by' => 'email',  'label' => 'Utilisateurs'],
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // EXPORT
    // ──────────────────────────────────────────────────────────────────────────

    public function export(Request $request, string $module)
    {
        if (! array_key_exists($module, $this->modules)) {
            abort(404, 'Module inconnu.');
        }

        $config   = $this->modules[$module];
        $isTemplate = $request->boolean('template');
        $label    = $config['label'];
        $modelClass = $config['model'];

        // Fetch fillable fields for columns
        $instance = new $modelClass();
        $fillable = $instance->getFillable();

        // Remove UUID / foreign key noise for template
        $exportColumns = collect($fillable)->filter(fn($f) =>
            ! in_array($f, ['uuid', 'deleted_at'])
        )->values()->toArray();

        // Build CSV
        $rows = [];
        $rows[] = $exportColumns; // header

        if (! $isTemplate) {
            $records = $modelClass::all();
            foreach ($records as $record) {
                $row = [];
                foreach ($exportColumns as $col) {
                    $row[] = $record->{$col} ?? '';
                }
                $rows[] = $row;
            }
        }

        $filename = Str::slug($label) . ($isTemplate ? '_modele' : '_export') . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // IMPORT
    // ──────────────────────────────────────────────────────────────────────────

    public function import(Request $request, string $module)
    {
        if (! array_key_exists($module, $this->modules)) {
            return response()->json(['success' => false, 'message' => 'Module inconnu.'], 404);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $config     = $this->modules[$module];
        $modelClass = $config['model'];
        $uniqueBy   = $config['unique_by'];

        try {
            $file    = $request->file('file');
            $handle  = fopen($file->getPathname(), 'r');

            // Strip UTF-8 BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            // Read header row
            $headers = fgetcsv($handle, 0, ';');
            if (! $headers) {
                fclose($handle);
                return response()->json(['success' => false, 'message' => 'Fichier CSV vide ou invalide.']);
            }

            // Normalize headers (remove accents, lowercase)
            $normalizedHeaders = array_map([$this, 'normalizeHeader'], $headers);

            $instance  = new $modelClass();
            $fillable  = $instance->getFillable();

            $created = 0;
            $updated = 0;
            $skipped = 0;
            $errors  = [];
            $lineNum = 1;

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $lineNum++;
                if (count($row) !== count($normalizedHeaders)) {
                    $skipped++;
                    continue;
                }

                // Map CSV columns → model fillable fields
                $data = [];
                foreach ($normalizedHeaders as $idx => $col) {
                    $normalizedCol = $this->normalizeHeader($col);
                    // Match to fillable
                    foreach ($fillable as $field) {
                        if ($this->normalizeHeader($field) === $normalizedCol) {
                            $val = trim($row[$idx] ?? '');
                            if ($val !== '') {
                                $data[$field] = $val;
                            }
                            break;
                        }
                    }
                }

                if (empty($data) || ! isset($data[$uniqueBy])) {
                    $skipped++;
                    continue;
                }

                try {
                    $record = $modelClass::where($uniqueBy, $data[$uniqueBy])->first();
                    if ($record) {
                        $record->update($data);
                        $updated++;
                    } else {
                        $modelClass::create($data);
                        $created++;
                    }
                } catch (\Throwable $e) {
                    $errors[] = "Ligne $lineNum : " . $e->getMessage();
                }
            }

            fclose($handle);

            $summary = "Importation terminée : $created créé(s), $updated mis à jour, $skipped ignoré(s).";
            if (! empty($errors)) {
                $summary .= ' Erreurs : ' . implode('; ', array_slice($errors, 0, 5));
            }

            return response()->json(['success' => true, 'message' => $summary]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function normalizeHeader(string $str): string
    {
        $str = mb_strtolower(trim($str));
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        $str = preg_replace('/[^a-z0-9_]/', '_', $str);
        return trim($str, '_');
    }
}
