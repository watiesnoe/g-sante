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

        if ($module === 'maladies') {
            $exportColumns[] = 'symptomes';
        }

        // Build CSV
        $rows = [];
        $rows[] = $exportColumns; // header

        if (! $isTemplate) {
            $records = $module === 'maladies'
                ? $modelClass::with('symptomes')->get()
                : $modelClass::all();
            foreach ($records as $record) {
                $row = [];
                foreach ($exportColumns as $col) {
                    if ($col === 'symptomes' && $module === 'maladies') {
                        $row[] = $record->symptomes ? $record->symptomes->pluck('nom')->implode(', ') : '';
                    } else {
                        $row[] = $record->{$col} ?? '';
                    }
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
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['csv', 'txt'])) {
            return response()->json(['success' => false, 'message' => 'Le fichier doit être au format CSV ou TXT.'], 422);
        }

        $config     = $this->modules[$module];
        $modelClass = $config['model'];
        $uniqueBy   = $config['unique_by'];

        try {
            $handle  = fopen($file->getPathname(), 'r');

            // Read a portion of the first line to detect separator (';' or ',')
            $firstLine = fgets($handle);
            rewind($handle);

            // Strip UTF-8 BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            // Detect separator: count occurrences of ';' vs ',' in the first line
            $separator = ';';
            if ($firstLine !== false) {
                $semicolons = substr_count($firstLine, ';');
                $commas = substr_count($firstLine, ',');
                if ($commas > $semicolons) {
                    $separator = ',';
                }
            }

            // Read header row
            $headers = fgetcsv($handle, 0, $separator);
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

            while (($row = fgetcsv($handle, 0, $separator)) !== false) {
                $lineNum++;
                if (count($row) !== count($normalizedHeaders)) {
                    $skipped++;
                    continue;
                }

                // Map CSV columns → model fillable fields
                $data = [];
                $symptomesVal = null;

                foreach ($normalizedHeaders as $idx => $col) {
                    $normalizedCol = $this->normalizeHeader($col);

                    if ($module === 'maladies' && in_array($normalizedCol, ['symptomes', 'symptome', 'symptomes_cles'])) {
                        $symptomesVal = trim($row[$idx] ?? '');
                        continue;
                    }

                    // Match to fillable
                    foreach ($fillable as $field) {
                        $normalizedField = $this->normalizeHeader($field);
                        // Also match relation names without _id, e.g. service_medical matching service_medical_id
                        $isRelationMatch = false;
                        if (str_ends_with($normalizedField, '_id')) {
                            $baseName = substr($normalizedField, 0, -3);
                            if ($baseName === $normalizedCol) {
                                $isRelationMatch = true;
                            }
                        }

                        if ($normalizedField === $normalizedCol || $isRelationMatch) {
                            $val = trim($row[$idx] ?? '');
                            if ($val !== '') {
                                // Normalize boolean values from Excel (e.g. VRAI/FAUX, TRUE/FALSE)
                                $lowerVal = mb_strtolower($val);
                                if ($lowerVal === 'vrai' || $lowerVal === 'true') {
                                    $val = 1;
                                } elseif ($lowerVal === 'faux' || $lowerVal === 'false') {
                                    $val = 0;
                                }

                                // Resolving relationships dynamically if it's not a numeric ID
                                if ($field === 'service_medical_id' && ! is_numeric($val)) {
                                    $service = \App\Models\ServiceMedical::where('nom', 'like', $val)->first();
                                    if (! $service) {
                                        $service = \App\Models\ServiceMedical::create([
                                            'nom' => $val,
                                            'description' => 'Créé automatiquement via import.'
                                        ]);
                                    }
                                    $val = $service->id;
                                } elseif ($field === 'salle_id' && ! is_numeric($val)) {
                                    $salle = \App\Models\Salle::where('nom', 'like', $val)->first();
                                    if (! $salle) {
                                        $salle = \App\Models\Salle::create([
                                            'nom' => $val,
                                            'type' => 'Standard',
                                            'capacite' => 10,
                                            'prix' => 0
                                        ]);
                                    }
                                    $val = $salle->id;
                                } elseif ($field === 'famille_id' && ! is_numeric($val)) {
                                    $famille = \App\Models\Famille::where('nom', 'like', $val)->first();
                                    if (! $famille) {
                                        $famille = \App\Models\Famille::create([
                                            'nom' => $val,
                                            'description' => 'Créée automatiquement via import.'
                                        ]);
                                    }
                                    $val = $famille->id;
                                } elseif ($field === 'medicament_id' && ! is_numeric($val)) {
                                    $medicament = \App\Models\Medicament::where('nom', 'like', $val)->first();
                                    if (! $medicament) {
                                        $medicament = \App\Models\Medicament::create([
                                            'nom' => $val,
                                            'uuid' => (string) \Illuminate\Support\Str::uuid(),
                                            'description' => 'Créé automatiquement via import.'
                                        ]);
                                    }
                                    $val = $medicament->id;
                                }

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
                        if (empty($data['uuid']) && in_array('uuid', $fillable)) {
                            $data['uuid'] = (string) Str::uuid();
                        }
                        $record = $modelClass::create($data);
                        $created++;
                    }

                    // Attach / Sync symptomes if importing maladies module
                    if ($module === 'maladies' && $record && ! empty($symptomesVal)) {
                        $symptomeNames = array_map('trim', preg_split('/[,;|]/', $symptomesVal));
                        $symptomeIds = [];
                        foreach ($symptomeNames as $sName) {
                            if ($sName === '') continue;
                            $symptome = \App\Models\Symptome::firstOrCreate(
                                ['nom' => $sName],
                                ['description' => 'Créé automatiquement via import.']
                            );
                            $symptomeIds[] = $symptome->id;
                        }
                        if (! empty($symptomeIds)) {
                            $record->symptomes()->sync($symptomeIds);
                        }
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
        $str = Str::ascii($str);
        $str = preg_replace('/[^a-z0-9_]/', '_', $str);
        return trim($str, '_');
    }
}
