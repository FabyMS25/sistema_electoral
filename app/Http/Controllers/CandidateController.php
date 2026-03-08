<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\ElectionTypeCategory;
use App\Models\Department;
use App\Models\Province;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Candidate::with([
                'electionTypeCategory.electionType',
                'electionTypeCategory.electionCategory',
                'department',
                'province',
                'municipality',
            ])->where('candidates.active', true);
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('candidates.name', 'like', "%{$search}%")
                      ->orWhere('candidates.party', 'like', "%{$search}%")
                      ->orWhere('candidates.party_full_name', 'like', "%{$search}%")
                      ->orWhere('candidates.list_name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('election_type_category_id')) {
                $query->where('candidates.election_type_category_id', $request->election_type_category_id);
            }

            if ($request->filled('department_id')) {
                $query->where('candidates.department_id', $request->department_id);
            }

            if ($request->filled('province_id')) {
                $query->where('candidates.province_id', $request->province_id);
            }

            if ($request->filled('municipality_id')) {
                $query->where('candidates.municipality_id', $request->municipality_id);
            }

            // ── Sorting ───────────────────────────
            $sort      = $request->get('sort', 'name');
            $direction = in_array($request->get('direction', 'asc'), ['asc', 'desc'])
                ? $request->get('direction', 'asc')
                : 'asc';

            match ($sort) {
                'election_type' => $query
                    ->join('election_type_categories as etc_sort', 'candidates.election_type_category_id', '=', 'etc_sort.id')
                    ->join('election_types as et_sort', 'etc_sort.election_type_id', '=', 'et_sort.id')
                    ->orderBy('et_sort.name', $direction)
                    ->select('candidates.*'),

                'election_category' => $query
                    ->join('election_type_categories as etc_sort2', 'candidates.election_type_category_id', '=', 'etc_sort2.id')
                    ->join('election_categories as ec_sort', 'etc_sort2.election_category_id', '=', 'ec_sort.id')
                    ->orderBy('ec_sort.name', $direction)
                    ->select('candidates.*'),

                default => $query->orderBy("candidates.{$sort}", $direction),
            };

            $perPage    = (int) $request->get('per_page', 20);
            $perPage    = in_array($perPage, [20, 50, 100, 200]) ? $perPage : 20;
            $candidates = $query->paginate($perPage)->withQueryString();

            // ── Dropdown data ─────────────────────
            $electionTypeCategories = $this->getActiveElectionTypeCategories();
            $departments            = Department::orderBy('name')->get();
            $provinces              = $request->filled('department_id')
                ? Province::where('department_id', $request->department_id)->orderBy('name')->get()
                : collect();
            $municipalities         = $request->filled('province_id')
                ? Municipality::where('province_id', $request->province_id)->orderBy('name')->get()
                : collect();

            // ── Stats (computed here, NOT in the view) ───
            $stats = $this->buildStats();

        } catch (\Exception $e) {
            Log::error('Error loading candidates: ' . $e->getMessage());
            $candidates             = collect();
            $electionTypeCategories = collect();
            $departments            = collect();
            $provinces              = collect();
            $municipalities         = collect();
            $stats                  = $this->emptyStats();
            session()->flash('error', 'Error al cargar los candidatos.');
        }

        return view('candidates.index', compact(
            'candidates',
            'electionTypeCategories',
            'departments',
            'provinces',
            'municipalities',
            'stats',
        ));
    }

    public function create()
    {
        try {
            return view('candidates.create', [
                'electionTypeCategories' => $this->getActiveElectionTypeCategories(),
                'departments'            => Department::orderBy('name')->get(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage());
            return redirect()->route('candidates.index')
                ->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->candidateRules());
            $data      = $this->prepareData($validated);
            $data      = $this->handleImages($request, $data);

            Candidate::create($data);

            return redirect()->route('candidates.index')
                ->with('success', '✅ El candidato fue creado con éxito.');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating candidate: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', '❌ Error al crear el candidato. Por favor intente nuevamente.');
        }
    }

    public function edit($id)
    {
        try {
            $candidate = Candidate::with([
                'electionTypeCategory.electionType',
                'electionTypeCategory.electionCategory',
                'department', 'province', 'municipality',
            ])->findOrFail($id);

            return view('candidates.edit', [
                'candidate'              => $candidate,
                'electionTypeCategories' => $this->getActiveElectionTypeCategories(),
                'departments'            => Department::orderBy('name')->get(),
                'provinces'              => $candidate->department_id
                    ? Province::where('department_id', $candidate->department_id)->orderBy('name')->get()
                    : collect(),
                'municipalities'         => $candidate->province_id
                    ? Municipality::where('province_id', $candidate->province_id)->orderBy('name')->get()
                    : collect(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage());
            return redirect()->route('candidates.index')
                ->with('error', '❌ Error al cargar el formulario de edición.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $candidate = Candidate::findOrFail($id);
            $validated = $request->validate($this->candidateRules());
            $data      = $this->prepareData($validated);
            $data      = $this->handleImages($request, $data, $candidate);

            $candidate->update($data);

            return redirect()->route('candidates.index')
                ->with('success', '✅ El candidato fue actualizado con éxito.');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating candidate: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->back()->withInput()
                ->with('error', '❌ Error al actualizar el candidato.');
        }
    }

    public function destroy($id)
    {
        try {
            Candidate::findOrFail($id)->update(['active' => false]);
            return redirect()->route('candidates.index')
                ->with('success', '✅ El candidato fue eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error deleting candidate: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al eliminar el candidato.');
        }
    }

    public function multipleDelete(Request $request)
    {
        try {
            $request->validate([
                'ids'   => 'required|array|min:1',
                'ids.*' => 'integer|exists:candidates,id',
            ]);

            $count = Candidate::whereIn('id', $request->ids)->update(['active' => false]);

            return redirect()->route('candidates.index')
                ->with('success', "✅ Se eliminaron {$count} candidatos correctamente.");

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator);
        } catch (\Exception $e) {
            Log::error('Error deleting multiple candidates: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al eliminar candidatos.');
        }
    }

    public function getProvinces($departmentId)
    {
        try {
            $provinces = Province::where('department_id', $departmentId)
                ->orderBy('name')
                ->get(['id', 'name']);
            return response()->json($provinces);
        } catch (\Exception $e) {
            Log::error('Error loading provinces: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar provincias'], 500);
        }
    }

    public function getMunicipalities($provinceId)
    {
        try {
            $municipalities = Municipality::where('province_id', $provinceId)
                ->orderBy('name')
                ->get(['id', 'name']);
            return response()->json($municipalities);
        } catch (\Exception $e) {
            Log::error('Error loading municipalities: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar municipios'], 500);
        }
    }

    public function exportAll(Request $request)
    {
        try {
            $query = Candidate::with([
                'electionTypeCategory.electionType',
                'electionTypeCategory.electionCategory',
                'department', 'province', 'municipality',
            ])->where('active', true);

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('party', 'like', "%{$s}%")
                      ->orWhere('party_full_name', 'like', "%{$s}%")
                      ->orWhere('list_name', 'like', "%{$s}%");
                });
            }
            if ($request->filled('election_type_category_id')) {
                $query->where('election_type_category_id', $request->election_type_category_id);
            }
            if ($request->filled('department_id'))   $query->where('department_id',   $request->department_id);
            if ($request->filled('province_id'))     $query->where('province_id',     $request->province_id);
            if ($request->filled('municipality_id')) $query->where('municipality_id', $request->municipality_id);

            $candidates = $query->get();
            $filename   = 'candidatos_' . date('Y-m-d_His') . '.csv';

            return response()->streamDownload(
                fn () => $this->writeCsv($candidates),
                $filename,
                ['Content-Type' => 'text/csv; charset=UTF-8']
            );

        } catch (\Exception $e) {
            Log::error('Error exporting candidates: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al exportar candidatos.');
        }
    }

    public function exportSelected(Request $request)
    {
        try {
            $request->validate([
                'selected_ids'   => 'required|array|min:1',
                'selected_ids.*' => 'integer|exists:candidates,id',
            ]);

            $candidates = Candidate::with([
                'electionTypeCategory.electionType',
                'electionTypeCategory.electionCategory',
                'department', 'province', 'municipality',
            ])
                ->whereIn('id', $request->selected_ids)
                ->where('active', true)
                ->get();

            if ($candidates->isEmpty()) {
                return redirect()->back()->with('error', '❌ No se encontraron candidatos seleccionados.');
            }

            $filename = 'candidatos_seleccionados_' . date('Y-m-d_His') . '.csv';

            return response()->streamDownload(
                fn () => $this->writeCsv($candidates),
                $filename,
                ['Content-Type' => 'text/csv; charset=UTF-8']
            );

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator);
        } catch (\Exception $e) {
            Log::error('Error exporting selected candidates: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al exportar candidatos seleccionados.');
        }
    }

    public function template()
    {
        try {
            $etcs = ElectionTypeCategory::with(['electionType', 'electionCategory'])
                ->whereHas('electionType', fn($q) => $q->where('active', true))
                ->orderBy('ballot_order')
                ->get();

            $departments = \App\Models\Department::orderBy('name')->get();

            return response()->streamDownload(function () use ($etcs, $departments) {
                $f = fopen('php://output', 'w');
                fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($f, ['=== PLANTILLA DE IMPORTACIÓN DE CANDIDATOS ===']);
                fputcsv($f, ['Instrucciones:']);
                fputcsv($f, ['  1. Complete sus datos debajo de la fila de ENCABEZADOS (la fila que empieza con "nombre").']);
                fputcsv($f, ['  2. NO modifique ni elimine la fila de encabezados.']);
                fputcsv($f, ['  3. Use los valores EXACTOS de las hojas de referencia (sección 2 y 3 de este archivo).']);
                fputcsv($f, ['  4. Deje en blanco los campos geográficos si el candidato es de ámbito nacional.']);
                fputcsv($f, ['  5. Si llena "municipio", el sistema auto-completa provincia y departamento.']);
                fputcsv($f, []);

                // ── SECTION 2: Import data (actual header + example row) ──
                fputcsv($f, [
                    'nombre', 'partido', 'nombre_completo_partido', 'color',
                    'tipo_eleccion', 'codigo_categoria',
                    'orden_lista', 'nombre_lista',
                    'departamento', 'provincia', 'municipio',
                ]);

                $firstEtc = $etcs->first();
                fputcsv($f, [
                    'Juan Pérez González',
                    'PARTIDO A',
                    'Partido A - Nombre Completo Oficial',
                    '#1b8af8',
                    $firstEtc?->electionType?->name ?? 'Elecciones Subnacionales 2026',
                    $firstEtc?->electionCategory?->code ?? 'ALC',
                    '1',
                    'Lista Única',
                    'Cochabamba',
                    'Quillacollo',
                    'Quillacollo',
                ]);

                fputcsv($f, []);
                fputcsv($f, []);

                // ── SECTION 3: Combo reference ───────────────────────────
                fputcsv($f, ['=== REFERENCIA: Valores válidos para tipo_eleccion + codigo_categoria ===']);
                fputcsv($f, [
                    'tipo_eleccion (copiar exacto)',
                    'codigo_categoria',
                    'nombre_categoria',
                    'franja',
                    'votos_por_persona',
                    'ambito_geografico',
                    'nota_geografica',
                ]);

                $scopeNotes = [
                    'nacional'      => 'Dejar departamento/provincia/municipio en blanco',
                    'departamental' => 'Completar departamento (mínimo)',
                    'provincial'    => 'Completar departamento + provincia (mínimo)',
                    'municipal'     => 'Completar municipio (o departamento+provincia+municipio)',
                    'indigena_ioc'  => 'Completar municipio',
                ];

                foreach ($etcs as $etc) {
                    $scope = $etc->electionCategory?->geographic_scope ?? '';
                    fputcsv($f, [
                        $etc->electionType?->name,
                        $etc->electionCategory?->code,
                        $etc->electionCategory?->name,
                        $etc->ballot_order,
                        $etc->votes_per_person ?? 1,
                        $scope,
                        $scopeNotes[$scope] ?? '',
                    ]);
                }

                fputcsv($f, []);
                fputcsv($f, []);

                // ── SECTION 4: Department reference ─────────────────────
                fputcsv($f, ['=== REFERENCIA: Nombres exactos de departamentos ===']);
                fputcsv($f, ['departamento (copiar exacto)']);
                foreach ($departments as $dept) {
                    fputcsv($f, [$dept->name]);
                }

                fclose($f);
            }, 'plantilla_candidatos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);

        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al generar la plantilla.');
        }
    }

    // ═══════════════════════════════════════════════════════
    //  IMPORT — name-based, full validation + duplicate checks
    // ═══════════════════════════════════════════════════════
    public function import(Request $request)
    {
        try {
            $request->validate([
                'import_file' => 'required|file|mimes:csv,txt|max:5120',
            ]);

            $path   = $request->file('import_file')->getRealPath();
            $handle = fopen($path, 'r');

            // ── Strip UTF-8 BOM ─────────────────────────────────────────
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
                rewind($handle);
            }

            // ── Locate header row (skip instruction/comment rows) ────────
            $expectedHeaders = [
                'nombre', 'partido', 'nombre_completo_partido', 'color',
                'tipo_eleccion', 'codigo_categoria',
                'orden_lista', 'nombre_lista',
                'departamento', 'provincia', 'municipio',
            ];

            $headers        = null;
            $headerRowFound = false;

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $cleaned = array_map(
                    fn($h) => trim(strtolower(str_replace("\xEF\xBB\xBF", '', $h))),
                    $row
                );
                if ($cleaned === $expectedHeaders) {
                    $headers        = $cleaned;
                    $headerRowFound = true;
                    break;
                }
            }

            if (!$headerRowFound) {
                fclose($handle);
                return redirect()->back()
                    ->with('error',
                        '❌ No se encontró la fila de encabezados. '
                        . 'Asegúrese de usar la plantilla oficial sin modificar los nombres de columna.');
            }

            // ── Pre-load lookup tables (one query each) ──────────────────

            // ETC lookup: "tipo_lower|CODE_UPPER" → ['id', 'scope', 'allows_list']
            $etcLookup = ElectionTypeCategory::with(['electionType', 'electionCategory'])
                ->whereHas('electionType', fn($q) => $q->where('active', true))
                ->get()
                ->mapWithKeys(fn($etc) => [
                    strtolower(trim($etc->electionType?->name ?? ''))
                    . '|'
                    . strtoupper(trim($etc->electionCategory?->code ?? '')) => [
                        'id'          => $etc->id,
                        'scope'       => $etc->electionCategory?->geographic_scope ?? '',
                        'allows_list' => (bool) ($etc->electionCategory?->allows_list ?? false),
                    ],
                ]);

            // Geo lookups
            $deptLookup = \App\Models\Department::get()
                ->mapWithKeys(fn($d) => [strtolower(trim($d->name)) => $d->id]);

            $provLookup = \App\Models\Province::get()
                ->mapWithKeys(fn($p) => [
                    strtolower(trim($p->name)) => [
                        'id'      => $p->id,
                        'dept_id' => $p->department_id,
                    ],
                ]);

            $munLookup = \App\Models\Municipality::with('province')
                ->get()
                ->mapWithKeys(fn($m) => [
                    strtolower(trim($m->name)) => [
                        'id'      => $m->id,
                        'prov_id' => $m->province_id,
                        'dept_id' => $m->province?->department_id,
                    ],
                ]);

            // ── Pre-load existing active candidates for duplicate checks ─
            // Key: "name_lower|party_lower|etc_id"
            $existingCandidates = \App\Models\Candidate::where('active', true)
                ->get(['name', 'party', 'election_type_category_id',
                       'list_order', 'list_name', 'municipality_id',
                       'province_id', 'department_id'])
                ->mapWithKeys(fn($c) => [
                    strtolower(trim($c->name))
                    . '|' . strtolower(trim($c->party))
                    . '|' . $c->election_type_category_id => true,
                ]);

            // list_order uniqueness per (etc_id, list_name) already in DB
            $existingListOrders = \App\Models\Candidate::where('active', true)
                ->whereNotNull('list_order')
                ->get(['election_type_category_id', 'list_name', 'list_order'])
                ->map(fn($c) => $c->election_type_category_id
                    . '|' . strtolower(trim($c->list_name ?? ''))
                    . '|' . $c->list_order)
                ->flip()
                ->all(); // ['etc_id|list_name|order' => 0]

            // ── Tracking sets for intra-file duplicate detection ─────────
            $seenInFile       = [];   // "name_lower|party_lower|etc_id"
            $seenListOrders   = [];   // "etc_id|list_name_lower|order"

            // ── Result counters ──────────────────────────────────────────
            $toImport   = [];   // rows that passed all validation
            $errors     = [];   // ['row' => N, 'messages' => [...], 'data' => $data]
            $skipped    = [];   // duplicate skips (informational)
            $rowNumber  = 1;    // header = row 1

            // ── First pass: validate every data row ──────────────────────
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $rowNumber++;

                // Skip blank rows
                if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                    continue;
                }

                // Stop when we hit a reference section header
                if (isset($row[0]) && str_starts_with(trim($row[0]), '===')) {
                    break;
                }

                $rowErrors = [];

                // ── Column count ─────────────────────────────────────────
                if (count($row) !== count($headers)) {
                    $errors[] = [
                        'row'      => $rowNumber,
                        'messages' => [
                            "Se esperaban " . count($headers) . " columnas, "
                            . "se encontraron " . count($row) . ".",
                        ],
                        'data' => implode(', ', $row),
                    ];
                    continue;
                }

                $data = array_map('trim', array_combine($headers, $row));

                // ── Field-level validation ───────────────────────────────
                $validator = \Illuminate\Support\Facades\Validator::make($data, [
                    'nombre'  => [
                        'required', 'string', 'max:255',
                        'regex:/^[\p{L}\p{M}\s\.\-\']+$/u',
                    ],
                    'partido'                 => 'required|string|max:50',
                    'nombre_completo_partido' => 'nullable|string|max:255',
                    'color'  => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                    'tipo_eleccion'    => 'required|string|max:255',
                    'codigo_categoria' => ['required', 'string', 'regex:/^[A-Z]{2,5}$/'],
                    'orden_lista'  => 'nullable|integer|min:1|max:9999',
                    'nombre_lista' => 'nullable|string|max:255',
                    'departamento' => 'nullable|string|max:100',
                    'provincia'    => 'nullable|string|max:100',
                    'municipio'    => 'nullable|string|max:100',
                ], [
                    'nombre.required'             => 'El nombre del candidato es obligatorio.',
                    'nombre.max'                  => 'El nombre no puede superar los 255 caracteres.',
                    'nombre.regex'                => 'El nombre solo puede contener letras, espacios, puntos, guiones y apóstrofes.',
                    'partido.required'            => 'La sigla del partido es obligatoria.',
                    'partido.max'                 => 'La sigla del partido no puede superar los 50 caracteres.',
                    'color.regex'                 => 'El color debe ser un valor hexadecimal válido (ej: #1b8af8).',
                    'tipo_eleccion.required'      => 'El tipo de elección es obligatorio.',
                    'codigo_categoria.required'   => 'El código de categoría es obligatorio.',
                    'codigo_categoria.regex'      => 'El código de categoría debe ser solo letras mayúsculas (ej: ALC, GOB, CON).',
                    'orden_lista.integer'         => 'El orden de lista debe ser un número entero.',
                    'orden_lista.min'             => 'El orden de lista debe ser mayor a 0.',
                    'orden_lista.max'             => 'El orden de lista no puede superar 9999.',
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'row'      => $rowNumber,
                        'messages' => $validator->errors()->all(),
                        'data'     => $data['nombre'] ?? '(sin nombre)',
                    ];
                    continue;
                }

                // ── Resolve election_type_category ───────────────────────
                $etcKey  = strtolower($data['tipo_eleccion']) . '|' . strtoupper($data['codigo_categoria']);
                $etcMeta = $etcLookup[$etcKey] ?? null;

                if (!$etcMeta) {
                    $errors[] = [
                        'row'      => $rowNumber,
                        'messages' => [
                            "La combinación tipo_eleccion=\"{$data['tipo_eleccion']}\" + "
                            . "codigo_categoria=\"{$data['codigo_categoria']}\" no existe o no está activa. "
                            . "Use los valores exactos de la hoja de referencia en la plantilla.",
                        ],
                        'data' => $data['nombre'],
                    ];
                    continue;
                }

                $etcId = $etcMeta['id'];
                $scope = $etcMeta['scope'];

                // ── Geographic resolution ────────────────────────────────
                $departmentId   = null;
                $provinceId     = null;
                $municipalityId = null;

                // Municipio
                if ($data['municipio'] !== '') {
                    $munKey  = strtolower($data['municipio']);
                    $munData = $munLookup[$munKey] ?? null;
                    if (!$munData) {
                        $errors[] = [
                            'row'      => $rowNumber,
                            'messages' => ["Municipio \"{$data['municipio']}\" no encontrado en la base de datos."],
                            'data'     => $data['nombre'],
                        ];
                        continue;
                    }
                    $municipalityId = $munData['id'];
                    $provinceId     = $munData['prov_id'];
                    $departmentId   = $munData['dept_id'];

                    // Cross-check explicit province
                    if ($data['provincia'] !== '') {
                        $explicitProvKey = strtolower($data['provincia']);
                        $explicitProv    = $provLookup[$explicitProvKey] ?? null;
                        if ($explicitProv && $explicitProv['id'] != $provinceId) {
                            $errors[] = [
                                'row'      => $rowNumber,
                                'messages' => [
                                    "El municipio \"{$data['municipio']}\" no pertenece "
                                    . "a la provincia \"{$data['provincia']}\".",
                                ],
                                'data' => $data['nombre'],
                            ];
                            continue;
                        }
                    }

                    // Cross-check explicit department
                    if ($data['departamento'] !== '') {
                        $explicitDeptKey = strtolower($data['departamento']);
                        $explicitDeptId  = $deptLookup[$explicitDeptKey] ?? null;
                        if ($explicitDeptId && $explicitDeptId != $departmentId) {
                            $errors[] = [
                                'row'      => $rowNumber,
                                'messages' => [
                                    "El municipio \"{$data['municipio']}\" no pertenece "
                                    . "al departamento \"{$data['departamento']}\".",
                                ],
                                'data' => $data['nombre'],
                            ];
                            continue;
                        }
                    }

                } elseif ($data['provincia'] !== '') {
                    // Provincia (no municipio)
                    $provKey  = strtolower($data['provincia']);
                    $provData = $provLookup[$provKey] ?? null;
                    if (!$provData) {
                        $errors[] = [
                            'row'      => $rowNumber,
                            'messages' => ["Provincia \"{$data['provincia']}\" no encontrada en la base de datos."],
                            'data'     => $data['nombre'],
                        ];
                        continue;
                    }
                    $provinceId   = $provData['id'];
                    $departmentId = $provData['dept_id'];

                    // Cross-check explicit department
                    if ($data['departamento'] !== '') {
                        $explicitDeptId = $deptLookup[strtolower($data['departamento'])] ?? null;
                        if ($explicitDeptId && $explicitDeptId != $departmentId) {
                            $errors[] = [
                                'row'      => $rowNumber,
                                'messages' => [
                                    "La provincia \"{$data['provincia']}\" no pertenece "
                                    . "al departamento \"{$data['departamento']}\".",
                                ],
                                'data' => $data['nombre'],
                            ];
                            continue;
                        }
                    }

                } elseif ($data['departamento'] !== '') {
                    // Departamento only
                    $deptKey      = strtolower($data['departamento']);
                    $departmentId = $deptLookup[$deptKey] ?? null;
                    if (!$departmentId) {
                        $errors[] = [
                            'row'      => $rowNumber,
                            'messages' => [
                                "Departamento \"{$data['departamento']}\" no encontrado. "
                                . "Use los nombres exactos de la hoja de referencia.",
                            ],
                            'data' => $data['nombre'],
                        ];
                        continue;
                    }
                }

                // ── Geographic scope validation ───────────────────────────
                // Ensures the geographic data provided matches what the category requires
                $scopeError = $this->validateGeographicScope(
                    $scope,
                    $departmentId,
                    $provinceId,
                    $municipalityId,
                    $data
                );

                if ($scopeError) {
                    $errors[] = [
                        'row'      => $rowNumber,
                        'messages' => [$scopeError],
                        'data'     => $data['nombre'],
                    ];
                    continue;
                }

                // ── list_order uniqueness ─────────────────────────────────
                if ($data['orden_lista'] !== '') {
                    $listKey = $etcId
                        . '|' . strtolower($data['nombre_lista'])
                        . '|' . $data['orden_lista'];

                    // Already taken in DB?
                    if (isset($existingListOrders[$listKey])) {
                        $errors[] = [
                            'row'      => $rowNumber,
                            'messages' => [
                                "El orden {$data['orden_lista']} en la lista \"{$data['nombre_lista']}\" "
                                . "ya está asignado a otro candidato en la base de datos "
                                . "(categoría: {$data['tipo_eleccion']} / {$data['codigo_categoria']}).",
                            ],
                            'data' => $data['nombre'],
                        ];
                        continue;
                    }

                    // Already seen in this file?
                    if (isset($seenListOrders[$listKey])) {
                        $errors[] = [
                            'row'      => $rowNumber,
                            'messages' => [
                                "El orden {$data['orden_lista']} en la lista \"{$data['nombre_lista']}\" "
                                . "ya fue asignado a otro candidato en este mismo archivo "
                                . "(fila {$seenListOrders[$listKey]}).",
                            ],
                            'data' => $data['nombre'],
                        ];
                        continue;
                    }
                    $seenListOrders[$listKey] = $rowNumber;
                }

                // ── Duplicate: same candidate already in DB ───────────────
                $dupKey = strtolower($data['nombre'])
                    . '|' . strtolower($data['partido'])
                    . '|' . $etcId;

                if (isset($existingCandidates[$dupKey])) {
                    $skipped[] = [
                        'row'  => $rowNumber,
                        'info' => "Candidato ya existe: \"{$data['nombre']}\" "
                            . "({$data['partido']}) en {$data['tipo_eleccion']} / {$data['codigo_categoria']}.",
                    ];
                    continue;
                }

                // ── Duplicate: repeated within this file ──────────────────
                if (isset($seenInFile[$dupKey])) {
                    $errors[] = [
                        'row'      => $rowNumber,
                        'messages' => [
                            "Candidato duplicado en el archivo: \"{$data['nombre']}\" "
                            . "({$data['partido']}) para {$data['tipo_eleccion']} / {$data['codigo_categoria']} "
                            . "ya aparece en la fila {$seenInFile[$dupKey]}.",
                        ],
                        'data' => $data['nombre'],
                    ];
                    continue;
                }
                $seenInFile[$dupKey] = $rowNumber;

                // ── Passed all checks — queue for import ──────────────────
                $toImport[] = [
                    'name'                      => $data['nombre'],
                    'party'                     => $data['partido'],
                    'party_full_name'            => $data['nombre_completo_partido'] ?: null,
                    'color'                     => $data['color'] ?: null,
                    'election_type_category_id' => $etcId,
                    'list_order'                => $data['orden_lista'] !== '' ? (int) $data['orden_lista'] : null,
                    'list_name'                 => $data['nombre_lista'] ?: null,
                    'department_id'             => $departmentId,
                    'province_id'               => $provinceId,
                    'municipality_id'           => $municipalityId,
                    'active'                    => true,
                ];
            }

            fclose($handle);

            // ── Second pass: bulk insert inside a transaction ─────────────
            $imported     = 0;
            $insertErrors = [];

            if (!empty($toImport)) {
                \Illuminate\Support\Facades\DB::transaction(function () use (
                    $toImport, &$imported, &$insertErrors
                ) {
                    foreach ($toImport as $idx => $candidateData) {
                        try {
                            \App\Models\Candidate::create($candidateData);
                            $imported++;
                        } catch (\Exception $e) {
                            $insertErrors[] = [
                                'row'      => "Lote fila " . ($idx + 1),
                                'messages' => ["Error al guardar: " . $e->getMessage()],
                                'data'     => $candidateData['name'],
                            ];
                        }
                    }
                });
            }

            // Merge insert errors into main error list
            $allErrors = array_merge($errors, $insertErrors);

            // ── Build response ────────────────────────────────────────────
            return $this->buildImportResponse($imported, $allErrors, $skipped);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error importing candidates: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', '❌ Error al importar: ' . $e->getMessage());
        }
    }

    // ───────────────────────────────────────────────────────
    //  PRIVATE: validate geographic scope requirements
    // ───────────────────────────────────────────────────────
    private function validateGeographicScope(
        string $scope,
        ?int $departmentId,
        ?int $provinceId,
        ?int $municipalityId,
        array $data
    ): ?string {
        return match ($scope) {
            'departamental' => !$departmentId
                ? "La categoría \"{$data['codigo_categoria']}\" es de ámbito departamental. "
                  . "Debe indicar el departamento."
                : null,

            'provincial' => !$provinceId
                ? "La categoría \"{$data['codigo_categoria']}\" es de ámbito provincial. "
                  . "Debe indicar al menos la provincia."
                : null,

            'municipal', 'indigena_ioc' => !$municipalityId
                ? "La categoría \"{$data['codigo_categoria']}\" es de ámbito municipal. "
                  . "Debe indicar el municipio."
                : null,

            'nacional' => ($departmentId || $provinceId || $municipalityId)
                ? "La categoría \"{$data['codigo_categoria']}\" es de ámbito nacional. "
                  . "No debe indicar departamento, provincia ni municipio."
                : null,

            default => null,
        };
    }

    // ───────────────────────────────────────────────────────
    //  PRIVATE: build the redirect response after import
    // ───────────────────────────────────────────────────────
    private function buildImportResponse(int $imported, array $errors, array $skipped): \Illuminate\Http\RedirectResponse
    {
        $parts = [];
        if ($imported > 0) $parts[] = "✅ {$imported} candidato(s) importados correctamente.";
        if (!empty($skipped)) $parts[] = "⏭️ " . count($skipped) . " fila(s) omitidas por duplicado.";
        if (!empty($errors))  $parts[] = "❌ " . count($errors)  . " fila(s) con errores (ver detalle).";

        // Attach skipped as informational errors so the user can see them
        $allNotices = array_merge(
            array_map(fn($s) => ['row' => $s['row'], 'messages' => [$s['info']], 'data' => '', 'type' => 'skip'], $skipped),
            array_map(fn($e) => array_merge($e, ['type' => 'error']), $errors)
        );

        usort($allNotices, fn($a, $b) => $a['row'] <=> $b['row']);

        $flashErrors = array_map(function ($notice) {
            $prefix = ($notice['type'] ?? 'error') === 'skip' ? '⏭️ [OMITIDA]' : '❌ [ERROR]';
            return $prefix . " Fila {$notice['row']}"
                . ($notice['data'] ? " ({$notice['data']})" : '')
                . ": " . implode(' | ', $notice['messages']);
        }, $allNotices);

        if ($imported > 0) {
            return redirect()->route('candidates.index')
                ->with('success', implode(' ', $parts))
                ->with('import_errors', $flashErrors);
        }

        return redirect()->route('candidates.index')
            ->with('error', implode(' ', $parts) ?: '❌ No se importó ningún candidato.')
            ->with('import_errors', $flashErrors);
    }

    /** Shared validation rules for store / update */
    private function candidateRules(): array
    {
        return [
            'name'                      => 'required|string|max:255',
            'party'                     => 'required|string|max:255',
            'party_full_name'           => 'nullable|string|max:255',
            'color'                     => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'election_type_category_id' => 'required|exists:election_type_categories,id',
            'list_order'                => 'nullable|integer|min:1',
            'list_name'                 => 'nullable|string|max:255',
            'municipality_id'           => 'nullable|exists:municipalities,id',
            'province_id'               => 'nullable|exists:provinces,id',
            'department_id'             => 'nullable|exists:departments,id',
            'photo'                     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'party_logo'                => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active'                    => 'boolean',
        ];
    }

    /** Map validated data to DB columns */
    private function prepareData(array $v): array
    {
        return [
            'name'                      => $v['name'],
            'party'                     => $v['party'],
            'party_full_name'           => $v['party_full_name'] ?? null,
            'color'                     => $v['color'] ?? null,
            'election_type_category_id' => $v['election_type_category_id'],
            'list_order'                => $v['list_order'] ?? null,
            'list_name'                 => $v['list_name'] ?? null,
            'municipality_id'           => $v['municipality_id'] ?? null,
            'province_id'               => $v['province_id'] ?? null,
            'department_id'             => $v['department_id'] ?? null,
            'active'                    => $v['active'] ?? true,
        ];
    }

    /** Handle photo / party_logo uploads (and delete old files on update) */
    private function handleImages(Request $request, array $data, ?Candidate $existing = null): array
    {
        foreach (['photo' => 'candidates/photos', 'party_logo' => 'candidates/party-logos'] as $field => $folder) {
            if ($request->hasFile($field)) {
                if ($existing && $existing->{$field}) {
                    Storage::disk('public')->delete($existing->{$field});
                }
                $data[$field] = $request->file($field)->store($folder, 'public');
            }
        }
        return $data;
    }

    /** Write CSV rows to php://output (used by export* methods) */
    private function writeCsv($candidates): void
    {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($file, [
            'ID', 'Nombre', 'Partido', 'Nombre Completo Partido', 'Color',
            'Tipo Elección', 'Categoría', 'Código Categoría',
            'Franja (Ballot Order)', 'Votos por Persona',
            'Orden Lista', 'Nombre Lista',
            'Departamento', 'Provincia', 'Municipio', 'Activo',
        ]);

        foreach ($candidates as $c) {
            fputcsv($file, [
                $c->id,
                $c->name,
                $c->party,
                $c->party_full_name ?? '',
                $c->color ?? '',
                $c->electionTypeCategory?->electionType?->name  ?? 'N/A',
                $c->electionTypeCategory?->electionCategory?->name ?? 'N/A',
                $c->electionTypeCategory?->electionCategory?->code ?? 'N/A',
                $c->electionTypeCategory?->ballot_order       ?? '',
                $c->electionTypeCategory?->votes_per_person   ?? 1,
                $c->list_order  ?? '',
                $c->list_name   ?? '',
                $c->department?->name  ?? 'N/A',
                $c->province?->name    ?? 'N/A',
                $c->municipality?->name ?? 'N/A',
                $c->active ? 'Sí' : 'No',
            ]);
        }

        fclose($file);
    }

    /** Eager-loaded election type categories for dropdowns */
    private function getActiveElectionTypeCategories()
    {
        return ElectionTypeCategory::with(['electionType', 'electionCategory'])
            ->whereHas('electionType', fn ($q) => $q->where('active', true))
            ->orderBy('ballot_order')
            ->get();
    }

    /** Build all stats arrays for the index view */
    private function buildStats(): array
    {
        try {
            $byCategory = Candidate::where('active', true)
                ->select('election_type_category_id', DB::raw('count(*) as total'))
                ->whereNotNull('election_type_category_id')
                ->groupBy('election_type_category_id')
                ->with('electionTypeCategory.electionType', 'electionTypeCategory.electionCategory')
                ->get();

            $byDepartment = Candidate::where('active', true)
                ->select('department_id', DB::raw('count(*) as total'))
                ->whereNotNull('department_id')
                ->groupBy('department_id')
                ->with('department')
                ->get();

            $byElectionType = $byCategory
                ->groupBy(fn ($i) => $i->electionTypeCategory?->electionType?->name ?? 'Sin tipo')
                ->map(fn ($g) => $g->sum('total'));

            $geo = [
                'nacional'      => Candidate::where('active', true)->whereNull('department_id')->whereNull('province_id')->whereNull('municipality_id')->count(),
                'departamental' => Candidate::where('active', true)->whereNotNull('department_id')->whereNull('province_id')->whereNull('municipality_id')->count(),
                'provincial'    => Candidate::where('active', true)->whereNotNull('province_id')->whereNull('municipality_id')->count(),
                'municipal'     => Candidate::where('active', true)->whereNotNull('municipality_id')->count(),
            ];

            return compact('byCategory', 'byDepartment', 'byElectionType', 'geo');

        } catch (\Exception $e) {
            Log::warning('Could not build candidate stats: ' . $e->getMessage());
            return $this->emptyStats();
        }
    }

    private function emptyStats(): array
    {
        return [
            'byCategory'      => collect(),
            'byDepartment'    => collect(),
            'byElectionType'  => collect(),
            'geo'             => ['nacional' => 0, 'departamental' => 0, 'provincial' => 0, 'municipal' => 0],
        ];
    }
}
