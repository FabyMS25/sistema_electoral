<?php
// app/Http/Controllers/InstitutionController.php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Department;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\Locality;
use App\Models\District;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\InstitutionsExport;
use App\Imports\InstitutionsImport;
use Maatwebsite\Excel\Facades\Excel;

class InstitutionController extends Controller
{
    const ITEMS_PER_PAGE = 20;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_recintos')->only(['index', 'show', 'export', 'downloadTemplate', 'getProvinces', 'getMunicipalities', 'getLocalities', 'getDistricts', 'getZones', 'getByLocality']);
        $this->middleware('permission:create_recintos')->only(['create', 'store']);
        $this->middleware('permission:edit_recintos')->only(['edit', 'update']);
        $this->middleware('permission:delete_recintos')->only(['destroy', 'deleteMultiple']);
    }

    public function index(Request $request)
    {
        try {
            $query = Institution::with([
                'locality.municipality.province.department',
                'district',
                'zone',
                'votingTables'
            ])->withCount('votingTables');

            // Búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('code', 'ilike', "%{$search}%")
                      ->orWhere('short_name', 'ilike', "%{$search}%")
                      ->orWhere('address', 'ilike', "%{$search}%");
                });
            }

            // Filtro por departamento
            if ($request->filled('department_id')) {
                $query->whereHas('locality.municipality.province', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }

            $institutions = $query->orderBy('name')->paginate(self::ITEMS_PER_PAGE);
            
            $departments = Department::orderBy('name')->get();

            return view('institutions.index', compact('institutions', 'departments'));

        } catch (\Exception $e) {
            Log::error('Error loading institutions: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return redirect()->back()->with('error', 'Error al cargar los datos de recintos.');
        }   
    }

    public function create()
    {
        try {
            $departments = Department::orderBy('name')->get();
            return view('institutions.create', compact('departments'));
        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage());
            return redirect()->route('institutions.index')->with('error', 'Error al cargar el formulario.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:institutions,name',
                'short_name' => 'nullable|string|max:100',
                'code' => 'nullable|string|max:20|unique:institutions,code',
                'address' => 'nullable|string|max:500',
                'reference' => 'nullable|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'responsible_name' => 'nullable|string|max:255',
                'locality_id' => 'required|exists:localities,id',
                'district_id' => 'nullable|exists:districts,id',
                'zone_id' => 'nullable|exists:zones,id',
                'registered_citizens' => 'nullable|integer|min:0',
                'total_computed_records' => 'nullable|integer|min:0',
                'total_annulled_records' => 'nullable|integer|min:0',
                'total_enabled_records' => 'nullable|integer|min:0',
                'status' => 'nullable|in:activo,inactivo,en_mantenimiento',
                'is_operative' => 'nullable|boolean',
                'observations' => 'nullable|string',
            ]);

            // Generar código si está vacío
            if (empty($validated['code'])) {
                $validated['code'] = $this->generateInstitutionCode($validated['name']);
            }

            // Valores por defecto
            $validated['is_operative'] = $request->has('is_operative');
            $validated['status'] = $validated['status'] ?? 'activo';
            $validated['created_by'] = Auth::id();

            DB::transaction(function () use ($validated) {
                Institution::create($validated);
            });

            return redirect()->route('institutions.index')
                            ->with('success', 'El recinto fue creado exitosamente.');
                            
        } catch (ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating institution: ' . $e->getMessage(), [
                'data' => $request->except('_token'),
                'trace' => $e->getTraceAsString()
            ]);            
            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al crear el recinto. Por favor intente nuevamente.');
        }
    }

    public function edit($id)
    {
        try {
            $institution = Institution::with([
                'locality.municipality.province.department',
                'district',
                'zone'
            ])->findOrFail($id);
            
            $departments = Department::orderBy('name')->get();
            
            return view('institutions.edit', compact('institution', 'departments'));
            
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('institutions.index')
                            ->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $institution = Institution::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:institutions,name,' . $id,
                'short_name' => 'nullable|string|max:100',
                'code' => 'nullable|string|max:20|unique:institutions,code,' . $id,
                'address' => 'nullable|string|max:500',
                'reference' => 'nullable|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'responsible_name' => 'nullable|string|max:255',
                'locality_id' => 'required|exists:localities,id',
                'district_id' => 'nullable|exists:districts,id',
                'zone_id' => 'nullable|exists:zones,id',
                'registered_citizens' => 'nullable|integer|min:0',
                'total_computed_records' => 'nullable|integer|min:0',
                'total_annulled_records' => 'nullable|integer|min:0',
                'total_enabled_records' => 'nullable|integer|min:0',
                'status' => 'nullable|in:activo,inactivo,en_mantenimiento',
                'is_operative' => 'nullable|boolean',
                'observations' => 'nullable|string',
            ]);

            // Generar código si está vacío
            if (empty($validated['code'])) {
                $validated['code'] = $this->generateInstitutionCode($validated['name'], $id);
            }

            $validated['is_operative'] = $request->has('is_operative');
            $validated['updated_by'] = Auth::id();

            DB::transaction(function () use ($institution, $validated) {
                $institution->update($validated);
            });            
            
            return redirect()->route('institutions.show', $id)
                            ->with('success', 'El recinto fue actualizado exitosamente.');
                            
        } catch (ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating institution: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $request->except('_token'),
                'trace' => $e->getTraceAsString()
            ]);            
            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al actualizar el recinto. Por favor intente nuevamente.');
        }
    }

    public function destroy($id)
    {
        try {
            $institution = Institution::withCount('votingTables')->findOrFail($id);
            
            if ($institution->voting_tables_count > 0) {
                return redirect()->back()
                                ->with('error', 'No se puede eliminar el recinto porque tiene mesas de votación asociadas. Elimine primero las mesas de votación.');
            }            
            
            DB::transaction(function () use ($institution) {
                $institution->delete();
            });            
            
            return redirect()->route('institutions.index')
                            ->with('success', 'El recinto fue eliminado correctamente.');
                            
        } catch (\Exception $e) {
            Log::error('Error deleting institution: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);            
            
            return redirect()->back()
                            ->with('error', 'Error al eliminar el recinto. Por favor intente nuevamente.');
        }
    }

    public function deleteMultiple(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:institutions,id'
            ]);

            $ids = $request->input('ids');
            $count = count($ids);
            
            $institutionsWithTables = Institution::whereIn('id', $ids)
                ->whereHas('votingTables')
                ->count();

            if ($institutionsWithTables > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar recintos que tienen mesas de votación asociadas.'
                ], 422);
            }

            $deleted = Institution::whereIn('id', $ids)->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => "Se eliminaron {$count} recintos correctamente.",
                    'deleted_count' => $deleted
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudieron eliminar los recintos.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error deleting multiple institutions: ' . $e->getMessage());            
            
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al eliminar los recintos.'
            ], 500);
        }
    }

    public function export()
    {
        try {
            return Excel::download(new InstitutionsExport, 'recintos_' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Error exporting institutions: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los recintos.');
        }
    }
    
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            ]);

            $import = new InstitutionsImport();
            $result = $import->import($request->file('file'));

            if (!$result['success']) {
                return redirect()->route('institutions.index')
                                ->with('import_errors', $result['errors'])
                                ->with('error', 'Error durante la importación.');
            }

            if (!empty($result['errors']) && $result['success_count'] === 0) {
                return redirect()->route('institutions.index')
                                ->with('import_errors', $result['errors'])
                                ->with('error', 'No se pudo importar ningún recinto.');
            } elseif (!empty($result['errors'])) {
                return redirect()->route('institutions.index')
                                ->with('import_errors', $result['errors'])
                                ->with('success_count', $result['success_count'])
                                ->with('warning', "Se importaron {$result['success_count']} recintos. Algunas filas tuvieron errores.");
            }

            return redirect()->route('institutions.index')
                            ->with('success', "Se importaron {$result['success_count']} recintos correctamente.");

        } catch (\Exception $e) {
            Log::error('Error importing institutions: ' . $e->getMessage());
            return redirect()->route('institutions.index')
                            ->with('error', 'Error durante la importación: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            return Excel::download(new InstitutionsExport, 'plantilla_recintos.xlsx');
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar la plantilla.');
        }
    }

    public function getProvinces(Department $department)
    {
        try {
            $provinces = $department->provinces()->select('id', 'name')->orderBy('name')->get();
            return response()->json($provinces);
        } catch (\Exception $e) {
            Log::error('Error getting provinces: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar provincias'], 500);
        }
    }

    public function getMunicipalities(Province $province)
    {
        try {
            $municipalities = $province->municipalities()->select('id', 'name')->orderBy('name')->get();
            return response()->json($municipalities);
        } catch (\Exception $e) {
            Log::error('Error getting municipalities: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar municipios'], 500);
        }
    }

    public function getLocalities(Municipality $municipality)
    {
        try {
            $localities = $municipality->localities()->select('id', 'name')->orderBy('name')->get();
            return response()->json($localities);
        } catch (\Exception $e) {
            Log::error('Error getting localities: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar localidades'], 500);
        }
    }

    public function getDistricts(Locality $locality)
    {
        try {
            $districts = District::where('municipality_id', $locality->municipality_id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();                
            return response()->json($districts);
        } catch (\Exception $e) {
            Log::error('Error getting districts: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar distritos'], 500);
        }
    }    

    public function getZones(District $district)
    {
        try {
            $zones = $district->zones()->select('id', 'name')->orderBy('name')->get();
            return response()->json($zones);
        } catch (\Exception $e) {
            Log::error('Error getting zones: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar zonas'], 500);
        }
    }

    private function generateInstitutionCode($name, $excludeId = null)
    {
        $baseCode = 'INST' . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        $counter = 1;
        $code = $baseCode . sprintf('%03d', $counter);
        
        $query = Institution::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        while ($query->exists()) {
            $counter++;
            $code = $baseCode . sprintf('%03d', $counter);
            $query = Institution::where('code', $code);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }
        
        return $code;
    }

    public function show($id)
    {
        try {
            $institution = Institution::with([
                'locality.municipality.province.department',
                'district',
                'zone',
                'votingTables' => function($query) {
                    $query->orderBy('number');
                }
            ])->findOrFail($id);
            
            return view('institutions.show', compact('institution'));            
        } catch (\Exception $e) {
            Log::error('Error showing institution: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('institutions.index')
                            ->with('error', 'Error al cargar los detalles de la institución.');
        }
    }

    public function getByLocality($localityId)
    {
        try {
            $institutions = Institution::where('locality_id', $localityId)
                ->where('status', 'activo')
                ->where('is_operative', true)
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();                
            return response()->json($institutions);
        } catch (\Exception $e) {
            Log::error('Error getting institutions by locality: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar instituciones'], 500);
        }
    }
}