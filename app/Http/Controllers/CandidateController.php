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
            $query = Candidate::with(['electionTypeCategory.electionType', 'electionTypeCategory.electionCategory'])
                ->where('active', true);
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
            if ($request->has('type') && !empty($request->type)) {
                $query->where('type', $request->type);
            }
            $sort = $request->get('sort', 'name');
            $direction = $request->get('direction', 'asc');
            $query->orderBy($sort, $direction);
            $perPage = $request->get('per_page', 20);
            $candidates = $query->paginate($perPage)->withQueryString();
            $electionTypeCategories = ElectionTypeCategory::with(['electionType', 'electionCategory'])
                ->whereHas('electionType', function($q) {
                    $q->where('active', true);
                })
                ->get();
            
            $typeOptions = [
                'candidato' => 'Candidato',
                'blank_votes' => 'Votos en Blanco',
                'null_votes' => 'Votos Nulos'
            ];
            
        } catch (\Exception $e) {
            Log::error('Error loading candidates: ' . $e->getMessage());
            $candidates = collect();
            $electionTypeCategories = collect();
            $typeOptions = [];
            session()->flash('error', 'Error loading candidates data.');
        }

        $departments = Department::orderBy('name')->get();
        return view('candidates.index', compact('candidates', 'electionTypeCategories', 'typeOptions', 'departments'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'party' => 'required|string|max:255',
                'party_full_name' => 'nullable|string|max:255',
                'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'election_type_category_id' => 'required|exists:election_type_categories,id',
                'type' => 'required|in:candidato,blank_votes,null_votes',
                'list_order' => 'nullable|integer',
                'list_name' => 'nullable|string|max:255',
                'municipality_id' => 'nullable|exists:municipalities,id',
                'province_id' => 'nullable|exists:provinces,id',
                'department_id' => 'nullable|exists:departments,id',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'party_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'active' => 'boolean',
            ]);

            $data = $request->only([
                'name', 'party', 'party_full_name', 'color', 
                'election_type_category_id', 'type',
                'list_order', 'list_name', 'municipality_id', 
                'province_id', 'department_id', 'active'
            ]);

            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('candidates/photos', 'public');
            }

            if ($request->hasFile('party_logo')) {
                $data['party_logo'] = $request->file('party_logo')->store('candidates/party-logos', 'public');
            }

            Candidate::create($data);

            return redirect()->route('candidates.index')
                ->with('success', 'El candidato fue creado con éxito.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating candidate: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Error al crear el candidato. Por favor intente nuevamente.');
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
            'type' => 'required|in:candidato,blank_votes,null_votes',
            'list_order' => 'nullable|integer',
            'list_name' => 'nullable|string|max:255',
            'municipality_id' => 'nullable|exists:municipalities,id',
            'province_id' => 'nullable|exists:provinces,id',
            'department_id' => 'nullable|exists:departments,id',
            'active' => 'boolean',
        ]);

        // Preparar los datos para actualizar
        $data = [
            'name' => $validated['name'],
            'party' => $validated['party'],
            'party_full_name' => $validated['party_full_name'] ?? null,
            'color' => $validated['color'] ?? null,
            'election_type_category_id' => $validated['election_type_category_id'],
            'type' => $validated['type'],
            'list_order' => $validated['list_order'] ?? null,
            'list_name' => $validated['list_name'] ?? null,
            'municipality_id' => $validated['municipality_id'] ?? null,
            'province_id' => $validated['province_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'active' => $validated['active'] ?? true,
        ];

        // Procesar la foto si se subió una nueva
        if ($request->hasFile('photo')) {
            // Eliminar la foto anterior si existe
            if ($candidate->photo) {
                Storage::disk('public')->delete($candidate->photo);
            }
            $data['photo'] = $request->file('photo')->store('candidates/photos', 'public');
        }

        // Procesar el logo del partido si se subió uno nuevo
        if ($request->hasFile('party_logo')) {
            // Eliminar el logo anterior si existe
            if ($candidate->party_logo) {
                Storage::disk('public')->delete($candidate->party_logo);
            }
            $data['party_logo'] = $request->file('party_logo')->store('candidates/party-logos', 'public');
        }

        // Actualizar el candidato
        $candidate->update($data);

        return redirect()->route('candidates.index')
            ->with('success', '✅ El candidato fue actualizado con éxito.');
            
    } catch (ValidationException $e) {
        return redirect()->back()
            ->withErrors($e->validator)
            ->withInput();
    } catch (\Exception $e) {
        Log::error('Error updating candidate: ' . $e->getMessage(), [
            'id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()
            ->withInput()
            ->with('error', '❌ Error al actualizar el candidato: ' . $e->getMessage());
    }
}

    public function destroy($id)
    {
        try {
            $candidate = Candidate::findOrFail($id);
            $candidate->update(['active' => false]);
            return redirect()->route('candidates.index')
                ->with('success', 'El candidato fue eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error deleting candidate: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar el candidato. Por favor intente nuevamente.');
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
                ->with('success', "Se eliminaron {$count} candidatos correctamente.");
                
        } catch (\Exception $e) {
            Log::error('Error deleting multiple candidates: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar candidatos.');
        }
    }
    
    public function create()
    {
        try {
            $electionTypeCategories = ElectionTypeCategory::with(['electionType', 'electionCategory'])
                ->whereHas('electionType', function($q) {
                    $q->where('active', true);
                })
                ->get();
            
            $typeOptions = [
                'candidato' => 'Candidato',
                'blank_votes' => 'Votos en Blanco',
                'null_votes' => 'Votos Nulos'
            ];
            
            $departments = Department::orderBy('name')->get();
            
            return view('candidates.create', compact('electionTypeCategories', 'typeOptions', 'departments'));
        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage());
            return redirect()->route('candidates.index')
                ->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    public function edit($id)
    {
        try {
            $candidate = Candidate::with(['electionTypeCategory.electionType', 'electionTypeCategory.electionCategory'])->findOrFail($id);
            
            $electionTypeCategories = ElectionTypeCategory::with(['electionType', 'electionCategory'])
                ->whereHas('electionType', function($q) {
                    $q->where('active', true);
                })
                ->get();
            
            $typeOptions = [
                'candidato' => 'Candidato',
                'blank_votes' => 'Votos en Blanco',
                'null_votes' => 'Votos Nulos'
            ];
            
            $departments = Department::orderBy('name')->get();
            $provinces = $candidate->department_id ? Province::where('department_id', $candidate->department_id)->get() : collect();
            $municipalities = $candidate->province_id ? Municipality::where('province_id', $candidate->province_id)->get() : collect();
            
            return view('candidates.edit', compact('candidate', 'electionTypeCategories', 'typeOptions', 'departments', 'provinces', 'municipalities'));
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage());
            return redirect()->route('candidates.index')
                ->with('error', 'Error al cargar el formulario de edición.');
        }
    }
    
    public function getProvinces($departmentId)
    {
        try {
            $provinces = Province::where('department_id', $departmentId)
                ->orderBy('name')
                ->get();
                
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
                ->get();
                
            return response()->json($municipalities);
        } catch (\Exception $e) {
            Log::error('Error loading municipalities: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading municipalities'], 500);
        }
    }

    public function exportAll(Request $request)
    {
        try {
            $query = Candidate::with(['electionCategory', 'electionType'])->where('active', true);
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('party', 'like', "%{$search}%")
                      ->orWhere('party_full_name', 'like', "%{$search}%");
                });
            }
            
            if ($request->has('election_category_id') && !empty($request->election_category_id)) {
                $query->where('election_category_id', $request->election_category_id);
            }
            
            if ($request->has('type') && !empty($request->type)) {
                $query->where('type', $request->type);
            }
            
            $candidates = $query->get();
            $filename = 'candidatos_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];
            $callback = function() use ($candidates) {
                $file = fopen('php://output', 'w');
                fputcsv($file, [
                    'ID', 'Nombre', 'Partido', 'Nombre Completo Partido', 
                    'Color', 'Categoría Elección', 'Tipo Elección', 'Tipo',
                    'Orden Lista', 'Nombre Lista', 'Municipio', 'Provincia', 
                    'Departamento', 'Activo'
                ]);                
                foreach ($candidates as $candidate) {
                    fputcsv($file, [
                        $candidate->id,
                        $candidate->name,
                        $candidate->party,
                        $candidate->party_full_name,
                        $candidate->color,
                        $candidate->electionCategory->name ?? 'N/A',
                        $candidate->electionType->name ?? 'N/A',
                        $candidate->type,
                        $candidate->list_order,
                        $candidate->list_name,
                        $candidate->municipality->name ?? 'N/A',
                        $candidate->province->name ?? 'N/A',
                        $candidate->department->name ?? 'N/A',
                        $candidate->active ? 'Sí' : 'No'
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Error exporting candidates: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar candidatos.');
        }
    }

    public function exportSelected(Request $request)
    {
        try {
            $request->validate([
                'selected_ids' => 'required|array',
                'selected_ids.*' => 'exists:candidates,id'
            ]);            
            $candidates = Candidate::with(['electionCategory', 'electionType'])
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
                fputcsv($file, [
                    'ID', 'Nombre', 'Partido', 'Nombre Completo Partido', 
                    'Color', 'Categoría Elección', 'Tipo Elección', 'Tipo',
                    'Orden Lista', 'Nombre Lista', 'Activo'
                ]);
                foreach ($candidates as $candidate) {
                    fputcsv($file, [
                        $candidate->id,
                        $candidate->name,
                        $candidate->party,
                        $candidate->party_full_name,
                        $candidate->color,
                        $candidate->electionCategory->name ?? 'N/A',
                        $candidate->electionType->name ?? 'N/A',
                        $candidate->type,
                        $candidate->list_order,
                        $candidate->list_name,
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
                fputcsv($file, [
                    'nombre',
                    'partido',
                    'nombre_completo_partido',
                    'color',
                    'categoria_eleccion_id',
                    'tipo_eleccion_id',
                    'tipo',
                    'orden_lista',
                    'nombre_lista'
                ]);
                fputcsv($file, [
                    'Juan Pérez',
                    'PARTIDO A',
                    'Partido A - Nombre Completo',
                    '#1b8af8',
                    '1',
                    '1',
                    'candidato',
                    '1',
                    'Lista 1'
                ]);
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar plantilla.');
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
            $headers = fgetcsv($handle, 0, ',');
            $expectedHeaders = [
                'nombre', 'partido', 'nombre_completo_partido', 'color',
                'categoria_eleccion_id', 'tipo_eleccion_id', 'tipo',
                'orden_lista', 'nombre_lista'
            ];
            if ($headers !== $expectedHeaders) {
                fclose($handle);
                return redirect()->back()->with('error', 'El archivo CSV no tiene el formato correcto. Por favor use la plantilla proporcionada.');
            }
            
            $imported = 0;
            $errors = [];
            $rowNumber = 1;
            
            while (($row = fgetcsv($handle, 0, ',')) !== FALSE) {
                $rowNumber++;
                $data = array_combine($headers, $row);
                try {
                    $validator = Validator::make($data, [
                        'nombre' => 'required|string|max:255',
                        'partido' => 'required|string|max:255',
                        'nombre_completo_partido' => 'nullable|string|max:255',
                        'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                        'categoria_eleccion_id' => 'required|exists:election_categories,id',
                        'tipo_eleccion_id' => 'required|exists:election_types,id',
                        'tipo' => 'required|in:candidato,blank_votes,null_votes',
                        'orden_lista' => 'nullable|integer',
                        'nombre_lista' => 'nullable|string|max:255',
                    ]);                    
                    if ($validator->fails()) {
                        $errors[] = "Fila {$rowNumber}: " . implode(', ', $validator->errors()->all());
                        continue;
                    }
                    Candidate::create([
                        'name' => $data['nombre'],
                        'party' => $data['partido'],
                        'party_full_name' => $data['nombre_completo_partido'],
                        'color' => $data['color'],
                        'election_category_id' => $data['categoria_eleccion_id'],
                        'election_type_id' => $data['tipo_eleccion_id'],
                        'type' => $data['tipo'],
                        'list_order' => $data['orden_lista'],
                        'list_name' => $data['nombre_lista'],
                        'active' => true
                    ]);                    
                    $imported++;                    
                } catch (\Exception $e) {
                    $errors[] = "Fila {$rowNumber}: Error al procesar - " . $e->getMessage();
                }
            }
            
            fclose($handle);            
            if ($imported > 0) {
                $message = "Se importaron {$imported} candidatos correctamente.";
                if (!empty($errors)) {
                    return redirect()->route('candidates.index')
                        ->with('success', $message)
                        ->with('import_errors', $errors);
                }
                return redirect()->route('candidates.index')
                    ->with('success', $message);
            } else {
                return redirect()->route('candidates.index')
                    ->with('error', 'No se pudo importar ningún candidato.')
                    ->with('import_errors', $errors);
            }            
        } catch (\Exception $e) {
            Log::error('Error importing candidates: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al importar candidatos: ' . $e->getMessage());
        }
    }
}