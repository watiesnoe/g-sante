<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        'protocoles'  => ['model' => \App\Models\ProtocoleTraitement::class, 'permission' => 'infectiologie.aide_prescription', 'unique_by' => 'maladie_id', 'label' => 'Protocoles de traitement'],
        'medicaments' => ['model' => \App\Models\Medicament::class,      'permission' => 'stock.medicaments',    'unique_by' => 'nom',    'label' => 'Médicaments'],
        'familles'    => ['model' => \App\Models\Famille::class,         'permission' => 'stock.familles',       'unique_by' => 'nom',    'label' => 'Familles'],
        'diagnostics' => ['model' => \App\Models\Diagnostic::class,      'permission' => 'parametres.maladies',  'unique_by' => 'code',   'label' => 'Nomenclature des diagnostics'],
        'unites'      => ['model' => \App\Models\Unite::class,           'permission' => 'stock.unites',         'unique_by' => ['nom', 'medicament_id'], 'label' => 'Unités'],
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

        // 🔒 Chaque module déclare sa permission dédiée : elle doit être vérifiée
        // avant tout export (sans quoi n'importe quel utilisateur connecté pourrait
        // exporter des données sensibles comme la table des utilisateurs).
        abort_unless(Auth::user()->can($config['permission']), 403, 'Accès non autorisé.');

        $isTemplate = $request->boolean('template');
        $label    = $config['label'];
        $modelClass = $config['model'];

        // Fetch fillable fields for columns
        $instance = new $modelClass();
        $fillable = $instance->getFillable();

        // Remove UUID / foreign key noise for template — et jamais le mot de passe,
        // même haché, qui ne doit jamais figurer dans un export téléchargeable.
        $exportColumns = collect($fillable)->filter(fn($f) =>
            ! in_array($f, ['uuid', 'deleted_at', 'password'])
        )->values()->toArray();

        if ($module === 'maladies') {
            $exportColumns[] = 'symptomes';
            $exportColumns[] = 'protocole';
            $exportColumns[] = 'traitement_principal';
            $exportColumns[] = 'posologie_principale';
        }

        // Build CSV
        $rows = [];
        $rows[] = $exportColumns; // header

        if (! $isTemplate) {
            $records = $module === 'maladies'
                ? $modelClass::with(['symptomes', 'protocole'])->get()
                : $modelClass::all();
            foreach ($records as $record) {
                $row = [];
                foreach ($exportColumns as $col) {
                    if ($module === 'maladies') {
                        if ($col === 'symptomes') {
                            $row[] = $record->symptomes ? $record->symptomes->pluck('nom')->implode(', ') : '';
                        } elseif ($col === 'protocole') {
                            $row[] = $record->protocole ? $record->protocole->titre : '';
                        } elseif ($col === 'traitement_principal') {
                            $row[] = $record->protocole ? $record->protocole->traitement_principal : '';
                        } elseif ($col === 'posologie_principale') {
                            $row[] = $record->protocole ? $record->protocole->posologie_principale : '';
                        } else {
                            $row[] = $record->{$col} ?? '';
                        }
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

    /**
     * Export a module's data (or a blank template) as an XLSX file.
     * Route: GET /export-xlsx/{module}?template=1
     */
    public function exportXlsx(Request $request, string $module)
    {
        if (! array_key_exists($module, $this->modules)) {
            abort(404, 'Module inconnu.');
        }

        $config     = $this->modules[$module];
        abort_unless(Auth::user()->can($config['permission']), 403, 'Accès non autorisé.');

        $isTemplate = $request->boolean('template');
        $label      = $config['label'];
        $modelClass = $config['model'];

        $instance = new $modelClass();
        $fillable = $instance->getFillable();

        $exportColumns = collect($fillable)->filter(fn($f) =>
            ! in_array($f, ['uuid', 'deleted_at', 'password'])
        )->values()->toArray();

        if ($module === 'maladies') {
            $exportColumns[] = 'symptomes';
            $exportColumns[] = 'protocole';
            $exportColumns[] = 'traitement_principal';
            $exportColumns[] = 'posologie_principale';
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle($label);

        // Header row with styling
        $sheet->fromArray($exportColumns, null, 'A1');
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($exportColumns));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0665D0']],
        ]);
        foreach (range(1, count($exportColumns)) as $colIdx) {
            $sheet->getColumnDimensionByColumn($colIdx)->setWidth(22);
        }
        $sheet->freezePane('A2');

        if (! $isTemplate) {
            $records = $module === 'maladies'
                ? $modelClass::with(['symptomes', 'protocole'])->get()
                : $modelClass::all();
            $dataRows = [];
            foreach ($records as $record) {
                $row = [];
                foreach ($exportColumns as $col) {
                    if ($module === 'maladies') {
                        if ($col === 'symptomes') {
                            $row[] = $record->symptomes ? $record->symptomes->pluck('nom')->implode(', ') : '';
                        } elseif ($col === 'protocole') {
                            $row[] = $record->protocole ? $record->protocole->titre : '';
                        } elseif ($col === 'traitement_principal') {
                            $row[] = $record->protocole ? $record->protocole->traitement_principal : '';
                        } elseif ($col === 'posologie_principale') {
                            $row[] = $record->protocole ? $record->protocole->posologie_principale : '';
                        } else {
                            $row[] = $record->{$col} ?? '';
                        }
                    } else {
                        $row[] = $record->{$col} ?? '';
                    }
                }
                $dataRows[] = $row;
            }
            $sheet->fromArray($dataRows, null, 'A2');
        }

        $filename = Str::slug($label) . ($isTemplate ? '_modele' : '_export') . '_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // IMPORT
    // ──────────────────────────────────────────────────────────────────────────

    public function import(Request $request, string $module)
    {
        if (! array_key_exists($module, $this->modules)) {
            return response()->json(['success' => false, 'message' => 'Module inconnu.'], 404);
        }

        $config = $this->modules[$module];

        // 🔒 Sans ce contrôle, n'importe quel utilisateur connecté pouvait importer un CSV
        // "users" avec une colonne password pour écraser le mot de passe de n'importe quel
        // compte (y compris super_admin) via la correspondance sur l'email.
        abort_unless(Auth::user()->can($config['permission']), 403, 'Accès non autorisé.');

        // 🔒 Le module "users" ne doit jamais pouvoir être modifié par ce mécanisme
        // d'import générique (mot de passe, statut, service... doivent passer par les
        // flux dédiés avec leurs propres règles métier et journalisation).
        if ($module === 'users') {
            return response()->json(['success' => false, 'message' => 'L\'import n\'est pas autorisé pour ce module.'], 403);
        }

        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['csv', 'txt', 'xlsx'])) {
            return response()->json(['success' => false, 'message' => 'Le fichier doit être au format CSV, TXT ou XLSX.'], 422);
        }

        $modelClass = $config['model'];
        $uniqueBy   = $config['unique_by']; // may be string or array of fields

        try {
            // ── Lecture du fichier selon son extension ────────────────────────
            if ($extension === 'xlsx') {
                // Le fichier temporaire PHP n'a pas d'extension → IOFactory::load() ne peut pas
                // deviner le format. On utilise un reader explicite pour éviter l'erreur.
                $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file->getPathname());
                $sheetData   = $spreadsheet->getActiveSheet()->toArray(
                    null,   // null pour les cellules vides
                    true,   // calculer les formules
                    true,   // formater les valeurs
                    false   // indices numériques (0-based)
                );

                if (empty($sheetData)) {
                    return response()->json(['success' => false, 'message' => 'Fichier XLSX vide ou invalide.']);
                }

                // Première ligne = en-têtes (peut contenir null si cellule vide)
                $headers           = array_shift($sheetData);
                $headers           = array_map(fn($h) => (string)($h ?? ''), $headers);
                $normalizedHeaders = array_map([$this, 'normalizeHeader'], $headers);

                // Ignorer les lignes entièrement vides (fréquent en fin de XLSX)
                $allRows = array_filter($sheetData, fn($row) =>
                    array_filter((array) $row, fn($v) => $v !== null && $v !== '')
                );
            } else {
                // ── Lecture CSV / TXT ─────────────────────────────────────────
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

                // Read all remaining rows into memory so the rest of the logic is unified
                $allRows = [];
                while (($row = fgetcsv($handle, 0, $separator)) !== false) {
                    $allRows[] = $row;
                }
                fclose($handle);
            }

            $instance  = new $modelClass();
            // RG-08 : le stock ne s'importe pas, il évolue uniquement par mouvements ;
            // l'établissement et les champs de traçabilité ne viennent jamais d'un fichier.
            $fillable  = array_values(array_diff($instance->getFillable(), ['stock', 'etablissement_id', 'statut', 'annule_par', 'annule_at', 'motif_annulation', 'fusionne_dans_id', 'numero_dossier']));

            $created = 0;
            $updated = 0;
            $skipped = 0;
            $errors  = [];
            $lineNum = 1;

            foreach ($allRows as $row) {
                $lineNum++;
                $row = array_values((array) $row); // normalise XLSX rows (may be associative)

                $headerCount = count($normalizedHeaders);
                $rowCount    = count($row);

                // Ligne strictement plus longue que les headers → probablement mal formée
                if ($rowCount > $headerCount) {
                    $skipped++;
                    continue;
                }

                // Ligne plus courte (cellules vides en fin de ligne dans XLSX) → compléter avec null
                if ($rowCount < $headerCount) {
                    $row = array_pad($row, $headerCount, null);
                }

                // Map CSV columns → model fillable fields
                $data = [];
                $symptomesVal = null;
                $protocoleVal = null;
                $traitementVal = null;
                $posologieVal = null;

                foreach ($normalizedHeaders as $idx => $col) {
                    $normalizedCol = $this->normalizeHeader($col);

                    if ($module === 'maladies' && in_array($normalizedCol, ['symptomes', 'symptome', 'symptomes_cles'])) {
                        $symptomesVal = trim($row[$idx] ?? '');
                        continue;
                    }
                    if ($module === 'maladies' && in_array($normalizedCol, ['protocole', 'titre_protocole'])) {
                        $protocoleVal = trim($row[$idx] ?? '');
                        continue;
                    }
                    if ($module === 'maladies' && in_array($normalizedCol, ['traitement_principal', 'traitement'])) {
                        $traitementVal = trim($row[$idx] ?? '');
                        continue;
                    }
                    if ($module === 'maladies' && in_array($normalizedCol, ['posologie_principale', 'posologie'])) {
                        $posologieVal = trim($row[$idx] ?? '');
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

                        // Allow specific aliases like 'pathologie' mapping to 'nom'
                        $isAliasMatch = false;
                        if ($module === 'maladies' && $normalizedField === 'nom' && in_array($normalizedCol, ['pathologie', 'maladie'])) {
                            $isAliasMatch = true;
                        }

                        if ($normalizedField === $normalizedCol || $isRelationMatch || $isAliasMatch) {
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
                                        // 🔒 service_medical_id est obligatoire (NOT NULL) sur la table
                                        // salles : sans ceci, l'insertion échoue en base (erreur 1364).
                                        $serviceParDefaut = \App\Models\ServiceMedical::firstOrCreate(
                                            ['nom' => 'Non classé'],
                                            ['description' => 'Service créé automatiquement pour rattacher les salles importées sans service précisé.']
                                        );
                                        $salle = \App\Models\Salle::create([
                                            'nom' => $val,
                                            'type' => 'Standard',
                                            'capacite' => 10,
                                            'prix' => 0,
                                            'service_medical_id' => $serviceParDefaut->id,
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
                                } elseif ($field === 'maladie_id' && ! is_numeric($val)) {
                                    $maladie = \App\Models\Maladie::where('nom', 'like', $val)->first();
                                    if (! $maladie) {
                                        $maladie = \App\Models\Maladie::create([
                                            'nom' => $val,
                                            'description' => 'Créée automatiquement via import.'
                                        ]);
                                    }
                                    $val = $maladie->id;
                                } elseif ($field === 'medicament_id' && ! is_numeric($val)) {
                                    $medicament = \App\Models\Medicament::where('nom', 'like', $val)->first();
                                    if (! $medicament) {
                                        // 🔒 famille_id est obligatoire (NOT NULL) sur la table
                                        // medicaments : sans ceci, l'insertion échoue en base (erreur 1364,
                                        // exactement l'erreur remontée lors de l'import du module "unites").
                                        $familleParDefaut = \App\Models\Famille::firstOrCreate(
                                            ['nom' => 'Non classé'],
                                            ['description' => 'Famille créée automatiquement pour rattacher les médicaments importés sans famille précisée.']
                                        );
                                        $medicament = \App\Models\Medicament::create([
                                            'nom' => $val,
                                            'uuid' => (string) \Illuminate\Support\Str::uuid(),
                                            'description' => 'Créé automatiquement via import.',
                                            'famille_id' => $familleParDefaut->id,
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

                // Build unique-by check (supports single field or composite array)
                $uniqueFields = is_array($uniqueBy) ? $uniqueBy : [$uniqueBy];
                $missingUniqueField = false;
                foreach ($uniqueFields as $uf) {
                    if (! isset($data[$uf])) {
                        $missingUniqueField = true;
                        break;
                    }
                }

                if (empty($data) || $missingUniqueField) {
                    $skipped++;
                    continue;
                }

                try {
                    // Build composite unique query
                    $query = $modelClass::query();
                    foreach ($uniqueFields as $uf) {
                        $query->where($uf, $data[$uf]);
                    }
                    $record = $query->first();

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

                    // Attach / Create ProtocoleTraitement if importing maladies module
                    if ($module === 'maladies' && $record) {
                        if (!empty($protocoleVal) || !empty($traitementVal) || !empty($posologieVal)) {
                            $protocole = \App\Models\ProtocoleTraitement::firstOrNew(['maladie_id' => $record->id]);
                            
                            if (!$protocole->exists && empty($protocoleVal)) {
                                $protocole->titre = 'Protocole ' . $record->nom;
                            } elseif (!empty($protocoleVal)) {
                                $protocole->titre = $protocoleVal;
                            }
                            
                            if (!empty($traitementVal)) $protocole->traitement_principal = $traitementVal;
                            if (!empty($posologieVal)) $protocole->posologie_principale = $posologieVal;
                            
                            $protocole->save();
                        }
                    }
                } catch (\Throwable $e) {
                    $errors[] = "Ligne $lineNum : " . $e->getMessage();
                }
            }

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
