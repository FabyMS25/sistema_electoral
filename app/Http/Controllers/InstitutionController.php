<?php
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

class InstitutionController extends Controller
{
    const ITEMS_PER_PAGE = 20;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_recintos')->only(['index', 'show', 'exportAll', 'exportSelected', 'downloadTemplate']);
        $this->middleware('permission:create_recintos')->only(['create', 'store']);
        $this->middleware('permission:edit_recintos')->only(['edit', 'update']);
        $this->middleware('permission:delete_recintos')->only(['destroy', 'deleteMultiple']);
    }

    private function validationMessages()
    {
        return [
            'name.required' => 'El nombre del recinto es obligatorio.',
            'name.unique' => 'Ya existe un recinto con este nombre.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',

            'code.unique' => 'El código ya está siendo utilizado por otro recinto.',
            'code.max' => 'El código no puede exceder los 20 caracteres.',

            'short_name.max' => 'El nombre corto no puede exceder los 100 caracteres.',

            'department_id.required' => 'Debe seleccionar un departamento.',
            'department_id.exists' => 'El departamento seleccionado no existe.',

            'province_id.required' => 'Debe seleccionar una provincia.',
            'province_id.exists' => 'La provincia seleccionada no existe.',

            'municipality_id.required' => 'Debe seleccionar un municipio.',
            'municipality_id.exists' => 'El municipio seleccionado no existe.',

            'locality_id.required' => 'Debe seleccionar una localidad.',
            'locality_id.exists' => 'La localidad seleccionada no existe.',

            'district_id.exists' => 'El distrito seleccionado no existe.',
            'zone_id.exists' => 'La zona seleccionada no existe.',

            'latitude.numeric' => 'La latitud debe ser un valor numérico.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',

            'longitude.numeric' => 'La longitud debe ser un valor numérico.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',

            'phone.max' => 'El teléfono no puede exceder los 20 caracteres.',

            'email.email' => 'El email debe tener un formato válido.',
            'email.max' => 'El email no puede exceder los 255 caracteres.',

            'responsible_name.max' => 'El nombre del responsable no puede exceder los 255 caracteres.',

            'registered_citizens.integer' => 'Los ciudadanos habilitados deben ser un número entero.',
            'registered_citizens.min' => 'Los ciudadanos habilitados deben ser mayor o igual a 0.',

            'total_computed_records.integer' => 'Las actas computadas deben ser un número entero.',
            'total_annulled_records.integer' => 'Las actas anuladas deben ser un número entero.',
            'total_enabled_records.integer' => 'Las actas habilitadas deben ser un número entero.',

            'status.in' => 'El estado seleccionado no es válido.',

            'observations.string' => 'Las observaciones deben ser texto.',
        ];
    }

    private function validationRules($id = null)
    {
        return [
            'name' => 'required|string|max:255|unique:institutions,name,' . $id,
            'short_name' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:20|unique:institutions,code,' . $id,

            'department_id' => 'required|exists:departments,id',
            'province_id' => 'required|exists:provinces,id',
            'municipality_id' => 'required|exists:municipalities,id',
            'locality_id' => 'required|exists:localities,id',
            'district_id' => 'nullable|exists:districts,id',
            'zone_id' => 'nullable|exists:zones,id',

            'address' => 'nullable|string|max:500',
            'reference' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',

            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'responsible_name' => 'nullable|string|max:255',

            'registered_citizens' => 'nullable|integer|min:0',
            'total_computed_records' => 'nullable|integer|min:0',
            'total_annulled_records' => 'nullable|integer|min:0',
            'total_enabled_records' => 'nullable|integer|min:0',

            'status' => 'nullable|in:activo,inactivo,en_mantenimiento',
            'is_operative' => 'nullable|boolean',
            'observations' => 'nullable|string',
        ];
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
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('code', 'ilike', "%{$search}%")
                      ->orWhere('short_name', 'ilike', "%{$search}%")
                      ->orWhere('address', 'ilike', "%{$search}%");
                });
            }

            if ($request->filled('department_id')) {
                $query->whereHas('locality.municipality.province', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('operative')) {
                $query->where('is_operative', $request->operative === 'true');
            }
            $sortField = $request->get('sort', 'name');
            $sortDirection = $request->get('direction', 'asc');
            $allowedSortFields = ['name', 'code', 'registered_citizens', 'status'];
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->orderBy('name');
            }

            $perPage = $request->get('per_page', self::ITEMS_PER_PAGE);
            $institutions = $query->paginate($perPage)->withQueryString();

            $departments = Department::orderBy('name')->get();
            $statusOptions = [
                'activo' => 'Activo',
                'inactivo' => 'Inactivo',
                'en_mantenimiento' => 'En Mantenimiento'
            ];

            return view('institutions.index', compact('institutions', 'departments', 'statusOptions', 'sortField', 'sortDirection'));

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
            $statusOptions = [
                'activo' => 'Activo',
                'inactivo' => 'Inactivo',
                'en_mantenimiento' => 'En Mantenimiento'
            ];
            return view('institutions.create', compact('departments', 'statusOptions'));
        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage());
            return redirect()->route('institutions.index')->with('error', 'Error al cargar el formulario.');
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('=== STORE: Datos completos del formulario ===', $request->all());

            $validated = $request->validate(
                $this->validationRules(),
                $this->validationMessages()
            );
            if (empty($validated['code'])) {
                $validated['code'] = $this->generateInstitutionCode($validated['name']);
            }
            $validated['is_operative'] = $request->has('is_operative');
            $validated['status'] = $validated['status'] ?? 'activo';
            $validated['created_by'] = Auth::id();
            $validated['registered_citizens'] = $validated['registered_citizens'] ?? 0;
            $validated['total_computed_records'] = $validated['total_computed_records'] ?? 0;
            $validated['total_annulled_records'] = $validated['total_annulled_records'] ?? 0;
            $validated['total_enabled_records'] = $validated['total_enabled_records'] ?? 0;

            DB::beginTransaction();

            try {
                Institution::create($validated);
                DB::commit();

                Log::info('=== STORE: Recinto creado exitosamente ===');

                return redirect()->route('institutions.index')
                                ->with('success', '✅ El recinto fue creado exitosamente.');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            Log::error('=== STORE: Errores de validación ===', $e->errors());
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();
        } catch (\Exception $e) {
            Log::error('=== STORE: Error general ===', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                            ->withInput()
                            ->with('error', '❌ Error al crear el recinto: ' . $e->getMessage());
        }
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
                },
                'createdBy',
                'updatedBy'
            ])->findOrFail($id);

            return view('institutions.show', compact('institution'));
        } catch (\Exception $e) {
            Log::error('Error showing institution: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('institutions.index')
                            ->with('error', 'Error al cargar los detalles de la institución.');
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
            $statusOptions = [
                'activo' => 'Activo',
                'inactivo' => 'Inactivo',
                'en_mantenimiento' => 'En Mantenimiento'
            ];

            return view('institutions.edit', compact('institution', 'departments', 'statusOptions'));

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
            Log::info('=== UPDATE: Datos completos del formulario ===', $request->all());
            $validated = $request->validate(
                $this->validationRules($id),
                $this->validationMessages()
            );
            if (empty($validated['code'])) {
                $validated['code'] = $this->generateInstitutionCode($validated['name'], $id);
            }
            $validated['is_operative'] = $request->has('is_operative');
            $validated['updated_by'] = Auth::id();
            DB::beginTransaction();
            try {
                $institution->update($validated);
                DB::commit();
                return redirect()->route('institutions.show', $id)
                                ->with('success', '✅ El recinto fue actualizado exitosamente.');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (ValidationException $e) {
            Log::error('=== UPDATE: Errores de validación ===', $e->errors());
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();
        } catch (\Exception $e) {
            Log::error('=== UPDATE: Error general ===', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                            ->withInput()
                            ->with('error', '❌ Error al actualizar el recinto: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $institution = Institution::withCount('votingTables')->findOrFail($id);

            if ($institution->voting_tables_count > 0) {
                return redirect()->back()
                                ->with('error', '❌ No se puede eliminar el recinto porque tiene mesas de votación asociadas.');
            }

            DB::beginTransaction();

            try {
                $institution->delete();
                DB::commit();

                return redirect()->route('institutions.index')
                                ->with('success', '✅ El recinto fue eliminado correctamente.');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error deleting institution: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                            ->with('error', '❌ Error al eliminar el recinto.');
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

            DB::beginTransaction();

            try {
                $deleted = Institution::whereIn('id', $ids)->delete();
                DB::commit();

                if ($deleted) {
                    return response()->json([
                        'success' => true,
                        'message' => "✅ Se eliminaron {$count} recintos correctamente.",
                        'deleted_count' => $deleted
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudieron eliminar los recintos.'
                ], 500);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error deleting multiple institutions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '❌ Ocurrió un error inesperado al eliminar los recintos.'
            ], 500);
        }
    }

    public function exportAll(Request $request)
    {
        try {
            $filters = $request->only(['search', 'department_id', 'status', 'operative']);
            $export = new InstitutionsExport();
            $filePath = $export->export($filters);

            return response()->download(storage_path("app/{$filePath}"))
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error exporting all institutions: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al exportar: ' . $e->getMessage());
        }
    }

    public function exportSelected(Request $request)
    {
        try {
            $request->validate([
                'selected_ids' => 'required|json'
            ]);

            $selectedIds = json_decode($request->selected_ids, true);

            if (empty($selectedIds)) {
                return redirect()->back()->with('error', '❌ No se seleccionaron recintos para exportar.');
            }

            $export = new InstitutionsExport();
            $filePath = $export->export(['selected_ids' => $selectedIds]);

            return response()->download(storage_path("app/{$filePath}"))
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error exporting selected institutions: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al exportar los recintos seleccionados: ' . $e->getMessage());
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
                                ->with('error', '❌ Error durante la importación.');
            }

            if (!empty($result['errors']) && $result['success_count'] === 0) {
                return redirect()->route('institutions.index')
                                ->with('import_errors', $result['errors'])
                                ->with('error', '❌ No se pudo importar ningún recinto.');
            } elseif (!empty($result['errors'])) {
                return redirect()->route('institutions.index')
                                ->with('import_errors', $result['errors'])
                                ->with('success_count', $result['success_count'])
                                ->with('warning', "⚠️ Se importaron {$result['success_count']} recintos. Algunas filas tuvieron errores.");
            }

            return redirect()->route('institutions.index')
                            ->with('success', "✅ Se importaron {$result['success_count']} recintos correctamente.");

        } catch (\Exception $e) {
            Log::error('Error importing institutions: ' . $e->getMessage());
            return redirect()->route('institutions.index')
                            ->with('error', '❌ Error durante la importación: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $export = new InstitutionsExport();
            $filePath = $export->downloadTemplate();
            return response()->download(storage_path("app/{$filePath}"))
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al generar la plantilla.');
        }
    }

    public function getProvinces(Request $request, $departmentId)
    {
        try {
            $provinces = Province::where('department_id', $departmentId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
            return response()->json($provinces);
        } catch (\Exception $e) {
            Log::error('Error getting provinces: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar provincias'], 500);
        }
    }

    public function getMunicipalities(Request $request, $provinceId)
    {
        try {
            $municipalities = Municipality::where('province_id', $provinceId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
            return response()->json($municipalities);
        } catch (\Exception $e) {
            Log::error('Error getting municipalities: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar municipios'], 500);
        }
    }

    public function getLocalities(Request $request, $municipalityId)
    {
        try {
            $localities = Locality::where('municipality_id', $municipalityId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
            return response()->json($localities);
        } catch (\Exception $e) {
            Log::error('Error getting localities: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar localidades'], 500);
        }
    }

    public function getDistricts(Request $request, $localityId)
    {
        try {
            $locality = Locality::findOrFail($localityId);
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

    public function getZones(Request $request, $districtId)
    {
        try {
            $zones = Zone::where('district_id', $districtId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
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
