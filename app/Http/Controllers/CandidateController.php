<?php
namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\ElectionType;
use App\Models\ElectionCategory;
use App\Models\ElectionTypeCategory;
use App\Models\Department;
use App\Models\Province;
use App\Models\Municipality;
use Illuminate\Http\Request;
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
                'municipality'
            ])->where('active', true);
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('party', 'like', "%{$search}%")
                      ->orWhere('party_full_name', 'like', "%{$search}%")
                      ->orWhere('list_name', 'like', "%{$search}%");
                });
            }
            if ($request->has('election_type_category_id') && !empty($request->election_type_category_id)) {
                $query->where('election_type_category_id', $request->election_type_category_id);
            }
            if ($request->has('department_id') && !empty($request->department_id)) {
                $query->where('department_id', $request->department_id);
            }
            if ($request->has('province_id') && !empty($request->province_id)) {
                $query->where('province_id', $request->province_id);
            }
            if ($request->has('municipality_id') && !empty($request->municipality_id)) {
                $query->where('municipality_id', $request->municipality_id);
            }
            $sort = $request->get('sort', 'name');
            $direction = $request->get('direction', 'asc');
            if ($sort === 'election_type') {
                $query->join('election_type_categories', 'candidates.election_type_category_id', '=', 'election_type_categories.id')
                      ->join('election_types', 'election_type_categories.election_type_id', '=', 'election_types.id')
                      ->orderBy('election_types.name', $direction)
                      ->select('candidates.*');
            } elseif ($sort === 'election_category') {
                $query->join('election_type_categories', 'candidates.election_type_category_id', '=', 'election_type_categories.id')
                      ->join('election_categories', 'election_type_categories.election_category_id', '=', 'election_categories.id')
                      ->orderBy('election_categories.name', $direction)
                      ->select('candidates.*');
            } else {
                $query->orderBy($sort, $direction);
            }

            $perPage = $request->get('per_page', 20);
            $candidates = $query->paginate($perPage)->withQueryString();

            $electionTypeCategories = ElectionTypeCategory::with(['electionType', 'electionCategory'])
                ->whereHas('electionType', function($q) {
                    $q->where('active', true);
                })
                ->orderBy('ballot_order')
                ->get();

            $departments = Department::orderBy('name')->get();
            $provinces = collect();
            $municipalities = collect();

            if ($request->has('department_id') && !empty($request->department_id)) {
                $provinces = Province::where('department_id', $request->department_id)->orderBy('name')->get();
            }

            if ($request->has('province_id') && !empty($request->province_id)) {
                $municipalities = Municipality::where('province_id', $request->province_id)->orderBy('name')->get();
            }

        } catch (\Exception $e) {
            Log::error('Error loading candidates: ' . $e->getMessage());
            $candidates = collect();
            $electionTypeCategories = collect();
            $departments = collect();
            $provinces = collect();
            $municipalities = collect();
            session()->flash('error', 'Error loading candidates data.');
        }

        return view('candidates.index', compact(
            'candidates',
            'electionTypeCategories',
            'departments',
            'provinces',
            'municipalities'
        ));
    }

    public function create()
    {
        try {
            $electionTypeCategories = ElectionTypeCategory::with(['electionType', 'electionCategory'])
                ->whereHas('electionType', function($q) {
                    $q->where('active', true);
                })
                ->orderBy('ballot_order')
                ->get();

            $departments = Department::orderBy('name')->get();

            return view('candidates.create', compact('electionTypeCategories', 'departments'));
        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage());
            return redirect()->route('candidates.index')
                ->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'party' => 'required|string|max:255',
                'party_full_name' => 'nullable|string|max:255',
                'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'election_type_category_id' => 'required|exists:election_type_categories,id',
                'list_order' => 'nullable|integer|min:1',
                'list_name' => 'nullable|string|max:255',
                'municipality_id' => 'nullable|exists:municipalities,id',
                'province_id' => 'nullable|exists:provinces,id',
                'department_id' => 'nullable|exists:departments,id',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'party_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'active' => 'boolean',
            ]);

            $data = [
                'name' => $validated['name'],
                'party' => $validated['party'],
                'party_full_name' => $validated['party_full_name'] ?? null,
                'color' => $validated['color'] ?? null,
                'election_type_category_id' => $validated['election_type_category_id'],
                'list_order' => $validated['list_order'] ?? null,
                'list_name' => $validated['list_name'] ?? null,
                'municipality_id' => $validated['municipality_id'] ?? null,
                'province_id' => $validated['province_id'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'active' => $validated['active'] ?? true,
            ];

            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('candidates/photos', 'public');
            }

            if ($request->hasFile('party_logo')) {
                $data['party_logo'] = $request->file('party_logo')->store('candidates/party-logos', 'public');
            }

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
                'department',
                'province',
                'municipality'
            ])->findOrFail($id);

            $electionTypeCategories = ElectionTypeCategory::with(['electionType', 'electionCategory'])
                ->whereHas('electionType', function($q) {
                    $q->where('active', true);
                })
                ->orderBy('ballot_order')
                ->get();

            $departments = Department::orderBy('name')->get();
            $provinces = $candidate->department_id
                ? Province::where('department_id', $candidate->department_id)->orderBy('name')->get()
                : collect();
            $municipalities = $candidate->province_id
                ? Municipality::where('province_id', $candidate->province_id)->orderBy('name')->get()
                : collect();

            return view('candidates.edit', compact(
                'candidate',
                'electionTypeCategories',
                'departments',
                'provinces',
                'municipalities'
            ));
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

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'party' => 'required|string|max:255',
                'party_full_name' => 'nullable|string|max:255',
                'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'election_type_category_id' => 'required|exists:election_type_categories,id',
                'list_order' => 'nullable|integer|min:1',
                'list_name' => 'nullable|string|max:255',
                'municipality_id' => 'nullable|exists:municipalities,id',
                'province_id' => 'nullable|exists:provinces,id',
                'department_id' => 'nullable|exists:departments,id',
                'active' => 'boolean',
            ]);

            $data = [
                'name' => $validated['name'],
                'party' => $validated['party'],
                'party_full_name' => $validated['party_full_name'] ?? null,
                'color' => $validated['color'] ?? null,
                'election_type_category_id' => $validated['election_type_category_id'],
                'list_order' => $validated['list_order'] ?? null,
                'list_name' => $validated['list_name'] ?? null,
                'municipality_id' => $validated['municipality_id'] ?? null,
                'province_id' => $validated['province_id'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'active' => $validated['active'] ?? true,
            ];
            if ($request->hasFile('photo')) {
                if ($candidate->photo) {
                    Storage::disk('public')->delete($candidate->photo);
                }
                $data['photo'] = $request->file('photo')->store('candidates/photos', 'public');
            }
            if ($request->hasFile('party_logo')) {
                if ($candidate->party_logo) {
                    Storage::disk('public')->delete($candidate->party_logo);
                }
                $data['party_logo'] = $request->file('party_logo')->store('candidates/party-logos', 'public');
            }

            $candidate->update($data);

            return redirect()->route('candidates.index')
                ->with('success', '✅ El candidato fue actualizado con éxito.');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating candidate: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error al actualizar el candidato.');
        }
    }

    public function destroy($id)
    {
        try {
            $candidate = Candidate::findOrFail($id);
            $candidate->update(['active' => false]);

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
                'ids' => 'required|array',
                'ids.*' => 'exists:candidates,id'
            ]);

            $count = Candidate::whereIn('id', $request->ids)
                ->update(['active' => false]);

            return redirect()->route('candidates.index')
                ->with('success', "✅ Se eliminaron {$count} candidatos correctamente.");

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
            return response()->json(['error' => 'Error loading provinces'], 500);
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
            return response()->json(['error' => 'Error loading municipalities'], 500);
        }
    }

    public function exportAll(Request $request)
    {
        try {
            $query = Candidate::with([
                'electionTypeCategory.electionType',
                'electionTypeCategory.electionCategory',
                'department',
                'province',
                'municipality'
            ])->where('active', true);
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('party', 'like', "%{$search}%")
                      ->orWhere('party_full_name', 'like', "%{$search}%");
                });
            }
            if ($request->has('election_type_category_id') && !empty($request->election_type_category_id)) {
                $query->where('election_type_category_id', $request->election_type_category_id);
            }
            $candidates = $query->get();
            $filename = 'candidatos_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];
            $callback = function() use ($candidates) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($file, [
                    'ID',
                    'Nombre',
                    'Partido',
                    'Nombre Completo Partido',
                    'Color',
                    'Tipo Elección',
                    'Categoría',
                    'Código Categoría',
                    'Orden Lista',
                    'Nombre Lista',
                    'Departamento',
                    'Provincia',
                    'Municipio',
                    'Activo'
                ]);

                foreach ($candidates as $candidate) {
                    fputcsv($file, [
                        $candidate->id,
                        $candidate->name,
                        $candidate->party,
                        $candidate->party_full_name,
                        $candidate->color,
                        $candidate->electionTypeCategory?->electionType?->name ?? 'N/A',
                        $candidate->electionTypeCategory?->electionCategory?->name ?? 'N/A',
                        $candidate->electionTypeCategory?->electionCategory?->code ?? 'N/A',
                        $candidate->list_order,
                        $candidate->list_name,
                        $candidate->department->name ?? 'N/A',
                        $candidate->province->name ?? 'N/A',
                        $candidate->municipality->name ?? 'N/A',
                        $candidate->active ? 'Sí' : 'No'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting candidates: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al exportar candidatos.');
        }
    }

    public function exportSelected(Request $request)
    {
        try {
            $request->validate([
                'selected_ids' => 'required|array',
                'selected_ids.*' => 'exists:candidates,id'
            ]);

            $candidates = Candidate::with([
                'electionTypeCategory.electionType',
                'electionTypeCategory.electionCategory',
                'department',
                'province',
                'municipality'
            ])
                ->whereIn('id', $request->selected_ids)
                ->where('active', true)
                ->get();

            $filename = 'candidatos_seleccionados_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() use ($candidates) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($file, [
                    'ID',
                    'Nombre',
                    'Partido',
                    'Nombre Completo Partido',
                    'Color',
                    'Tipo Elección',
                    'Categoría',
                    'Código Categoría',
                    'Orden Lista',
                    'Nombre Lista',
                    'Departamento',
                    'Provincia',
                    'Municipio',
                    'Activo'
                ]);

                foreach ($candidates as $candidate) {
                    fputcsv($file, [
                        $candidate->id,
                        $candidate->name,
                        $candidate->party,
                        $candidate->party_full_name,
                        $candidate->color,
                        $candidate->electionTypeCategory?->electionType?->name ?? 'N/A',
                        $candidate->electionTypeCategory?->electionCategory?->name ?? 'N/A',
                        $candidate->electionTypeCategory?->electionCategory?->code ?? 'N/A',
                        $candidate->list_order,
                        $candidate->list_name,
                        $candidate->department->name ?? 'N/A',
                        $candidate->province->name ?? 'N/A',
                        $candidate->municipality->name ?? 'N/A',
                        $candidate->active ? 'Sí' : 'No'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting selected candidates: ' . $e->getMessage());
            return response()->json(['error' => 'Error al exportar candidatos seleccionados.'], 500);
        }
    }

    public function template()
    {
        try {
            $filename = 'plantilla_candidatos.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($file, [
                    'nombre',
                    'partido',
                    'nombre_completo_partido',
                    'color',
                    'election_type_category_id',
                    'orden_lista',
                    'nombre_lista',
                    'department_id',
                    'province_id',
                    'municipality_id'
                ]);

                fputcsv($file, [
                    'Juan Pérez',
                    'PARTIDO A',
                    'Partido A - Nombre Completo',
                    '#1b8af8',
                    '1', // election_type_category_id
                    '1', // orden_lista
                    'Lista 1',
                    '', // department_id
                    '', // province_id
                    ''  // municipality_id
                ]);

                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al generar plantilla.');
        }
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'import_file' => 'required|file|mimes:csv,txt|max:5120'
            ]);
            $file = $request->file('import_file');
            $path = $file->getRealPath();
            $handle = fopen($path, 'r');
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }
            $headers = fgetcsv($handle, 0, ',');
            $expectedHeaders = [
                'nombre',
                'partido',
                'nombre_completo_partido',
                'color',
                'election_type_category_id',
                'orden_lista',
                'nombre_lista',
                'department_id',
                'province_id',
                'municipality_id'
            ];
            $headers = array_map(function($header) {
                return trim(str_replace("\xEF\xBB\xBF", '', $header));
            }, $headers);

            if ($headers !== $expectedHeaders) {
                fclose($handle);
                return redirect()->back()->with('error', 'El archivo CSV no tiene el formato correcto. Por favor use la plantilla proporcionada.');
            }
            $imported = 0;
            $errors = [];
            $rowNumber = 1;
            while (($row = fgetcsv($handle, 0, ',')) !== FALSE) {
                $rowNumber++;
                if (count(array_filter($row)) === 0) {
                    continue;
                }
                if (count($row) !== count($headers)) {
                    $errors[] = "Fila {$rowNumber}: Número incorrecto de columnas";
                    continue;
                }
                $data = array_combine($headers, $row);
                try {
                    $validator = Validator::make($data, [
                        'nombre' => 'required|string|max:255',
                        'partido' => 'required|string|max:255',
                        'nombre_completo_partido' => 'nullable|string|max:255',
                        'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                        'election_type_category_id' => 'required|exists:election_type_categories,id',
                        'orden_lista' => 'nullable|integer|min:1',
                        'nombre_lista' => 'nullable|string|max:255',
                        'department_id' => 'nullable|exists:departments,id',
                        'province_id' => 'nullable|exists:provinces,id',
                        'municipality_id' => 'nullable|exists:municipalities,id',
                    ]);
                    if ($validator->fails()) {
                        $errors[] = "Fila {$rowNumber}: " . implode(', ', $validator->errors()->all());
                        continue;
                    }
                    Candidate::create([
                        'name' => $data['nombre'],
                        'party' => $data['partido'],
                        'party_full_name' => $data['nombre_completo_partido'] ?: null,
                        'color' => $data['color'] ?: null,
                        'election_type_category_id' => $data['election_type_category_id'],
                        'list_order' => $data['orden_lista'] ?: null,
                        'list_name' => $data['nombre_lista'] ?: null,
                        'department_id' => $data['department_id'] ?: null,
                        'province_id' => $data['province_id'] ?: null,
                        'municipality_id' => $data['municipality_id'] ?: null,
                        'active' => true
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Fila {$rowNumber}: Error al procesar - " . $e->getMessage();
                }
            }
            fclose($handle);
            if ($imported > 0) {
                $message = "✅ Se importaron {$imported} candidatos correctamente.";
                if (!empty($errors)) {
                    return redirect()->route('candidates.index')
                        ->with('success', $message)
                        ->with('import_errors', $errors);
                }
                return redirect()->route('candidates.index')
                    ->with('success', $message);
            } else {
                return redirect()->route('candidates.index')
                    ->with('error', '❌ No se pudo importar ningún candidato.')
                    ->with('import_errors', $errors);
            }
        } catch (\Exception $e) {
            Log::error('Error importing candidates: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al importar candidatos: ' . $e->getMessage());
        }
    }
}
