<?php
namespace App\Http\Controllers;

use App\Models\VotingTable;
use App\Models\Institution;
use App\Models\ElectionType;
use App\Models\ElectionCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Exports\VotingTablesExport;
use App\Imports\VotingTablesImport;
use App\Models\User;

class VotingTableController extends Controller
{
    const ITEMS_PER_PAGE = 20;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_mesas')->only(['index', 'show', 'exportAll', 'exportSelected']);
        $this->middleware('permission:create_mesas')->only(['create', 'store']);
        $this->middleware('permission:edit_mesas')->only(['edit', 'update']);
        $this->middleware('permission:delete_mesas')->only(['destroy', 'deleteMultiple']);
    }

    private function validationMessages()
    {
        return [
            // Número de mesa
            'number.required' => 'El número de mesa es obligatorio.',
            'number.integer' => 'El número de mesa debe ser un valor numérico.',
            'number.min' => 'El número de mesa debe ser mayor o igual a 1.',

            // Códigos
            'oep_code.unique' => 'El código OEP ya está siendo utilizado por otra mesa.',
            'oep_code.max' => 'El código OEP no puede exceder los 20 caracteres.',
            'internal_code.unique' => 'El código interno ya está siendo utilizado por otra mesa.',
            'internal_code.max' => 'El código interno no puede exceder los 20 caracteres.',

            // Institución
            'institution_id.required' => 'Debe seleccionar una institución/recinto.',
            'institution_id.exists' => 'La institución seleccionada no existe en el sistema.',

            // Tipo de elección
            'election_type_id.required' => 'Debe seleccionar un tipo de elección.',
            'election_type_id.exists' => 'El tipo de elección seleccionado no existe.',

            // Letra
            'letter.max' => 'La letra debe tener máximo 1 carácter.',

            // Tipo de mesa
            'type.in' => 'El tipo de mesa debe ser: mixta, masculina o femenina.',

            // Rango de votantes
            'voter_range_start_name.max' => 'El apellido inicial no puede exceder los 255 caracteres.',
            'voter_range_end_name.max' => 'El apellido final no puede exceder los 255 caracteres.',

            // Datos electorales
            'expected_voters.integer' => 'Los votantes esperados deben ser un valor numérico.',
            'expected_voters.min' => 'Los votantes esperados deben ser mayor o igual a 0.',
            'ballots_received.integer' => 'Las papeletas recibidas deben ser un valor numérico.',
            'ballots_received.min' => 'Las papeletas recibidas deben ser mayor o igual a 0.',
            'ballots_spoiled.integer' => 'Las papeletas deterioradas deben ser un valor numérico.',
            'ballots_spoiled.min' => 'Las papeletas deterioradas deben ser mayor o igual a 0.',

            // Estado
            'status.required' => 'El estado de la mesa es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',

            // Fechas y horas
            'election_date.date' => 'La fecha de elección no tiene un formato válido.',
            'opening_time.date_format' => 'La hora de apertura debe tener formato HH:MM.',
            'closing_time.date_format' => 'La hora de cierre debe tener formato HH:MM.',
            'closing_time.after' => 'La hora de cierre debe ser posterior a la hora de apertura.',

            // Observaciones
            'observations.string' => 'Las observaciones deben ser texto.',
        ];
    }

    private function validationRules($id = null, $request = null)
    {
        $statusValues = implode(',', array_keys(VotingTable::getStatuses()));

        $rules = [
            'oep_code' => 'nullable|string|max:20|unique:voting_tables,oep_code' . ($id ? ',' . $id : ''),
            'internal_code' => 'nullable|string|max:20|unique:voting_tables,internal_code' . ($id ? ',' . $id : ''),
            'number' => 'required|integer|min:1',
            'letter' => 'nullable|string|max:1',
            'type' => 'nullable|in:mixta,masculina,femenina',

            'voter_range_start_name' => 'nullable|string|max:255',
            'voter_range_end_name' => 'nullable|string|max:255',
            'voter_range_start_id' => 'nullable|integer|min:0',
            'voter_range_end_id' => 'nullable|integer|min:0',

            'expected_voters' => 'nullable|integer|min:0',
            'ballots_received' => ['nullable', 'integer', 'min:0'],
            'ballots_spoiled' => ['nullable', 'integer', 'min:0'],

            'status' => 'required|in:' . $statusValues,
            'institution_id' => 'required|exists:institutions,id',
            'election_type_id' => 'required|exists:election_types,id',

            'opening_time' => 'nullable',
            'closing_time' => 'nullable',
            'election_date' => 'nullable|date',

            'acta_number' => 'nullable|string|max:50',
            'observations' => 'nullable|string',

            'president_id' => 'nullable|exists:users,id',
            'secretary_id' => 'nullable|exists:users,id',
            'vocal1_id' => 'nullable|exists:users,id',
            'vocal2_id' => 'nullable|exists:users,id',
            'vocal3_id' => 'nullable|exists:users,id',
            'vocal4_name' => 'nullable|string|max:255',
        ];

        if ($request) {
            $rules['ballots_received'][] = function ($attribute, $value, $fail) use ($request) {
                if ($value > 0 && $request->input('expected_voters', 0) > 0) {
                    if ($value < $request->input('expected_voters')) {
                        $fail('Las papeletas recibidas no pueden ser menores a los votantes esperados.');
                    }
                }
            };

            $rules['ballots_spoiled'][] = function ($attribute, $value, $fail) use ($request) {
                if ($value > $request->input('ballots_received', 0)) {
                    $fail('Las papeletas deterioradas no pueden ser mayores a las papeletas recibidas.');
                }
            };
        }

        return $rules;
    }

    public function index(Request $request)
    {
        try {
            $query = VotingTable::with([
                'institution.locality.municipality.province.department',
                'electionType',
                'president',
                'secretary',
                'vocal1',
                'vocal2',
                'vocal3',
            ]);

            // Filtros
            if ($request->filled('institution_id')) {
                $query->where('institution_id', $request->institution_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('election_type_id')) {
                $query->where('election_type_id', $request->election_type_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('oep_code', 'ilike', "%{$search}%")
                      ->orWhere('internal_code', 'ilike', "%{$search}%")
                      ->orWhere('number', 'ilike', "%{$search}%")
                      ->orWhereHas('institution', function($subq) use ($search) {
                          $subq->where('name', 'ilike', "%{$search}%")
                               ->orWhere('code', 'ilike', "%{$search}%");
                      });
                });
            }

            // Sorting
            $sortField = $request->get('sort', 'institution_id');
            $sortDirection = $request->get('direction', 'asc');

            $allowedSortFields = [
                'institution_id', 'number', 'oep_code', 'internal_code', 'expected_voters',
                'total_voters', 'status', 'institution_name'
            ];

            if (in_array($sortField, $allowedSortFields)) {
                if ($sortField === 'institution_name') {
                    $query->leftJoin('institutions', 'voting_tables.institution_id', '=', 'institutions.id')
                          ->orderBy('institutions.name', $sortDirection)
                          ->select('voting_tables.*');
                } else {
                    $query->orderBy($sortField, $sortDirection);
                }
            } else {
                $query->orderBy('institution_id')->orderBy('number');
            }
            $perPage = $request->get('per_page', self::ITEMS_PER_PAGE);
            $votingTables = $query->paginate($perPage)->withQueryString();
            $institutions = Institution::where('status', 'activo')
                                       ->orderBy('name')
                                       ->get();
            $electionTypes = ElectionType::where('active', true)
                                        ->orderBy('name')
                                        ->get();

            $statusOptions = VotingTable::getStatuses();

            return view('voting-tables.index', compact(
                'votingTables',
                'institutions',
                'electionTypes',
                'statusOptions',
                'sortField',
                'sortDirection'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading voting tables: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las mesas de votación.');
        }
    }

    public function create()
    {
        try {
            $institutions = Institution::where('status', 'activo')
                                       ->orderBy('name')
                                       ->get();
            $electionTypes = ElectionType::where('active', true)
                                         ->orderBy('name')
                                         ->get();
            $statusOptions = VotingTable::getStatuses();

            return view('voting-tables.create', compact('institutions', 'electionTypes', 'statusOptions'));
        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage());
            return redirect()->route('voting-tables.index')
                            ->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('=== STORE: Datos completos del formulario ===', $request->all());

            $rules = $this->validationRules(null, $request);
            $validated = $request->validate($rules, $this->validationMessages());

            Log::info('=== STORE: Datos validados ===', $validated);

            $existingTable = VotingTable::where('institution_id', $validated['institution_id'])
                ->where('number', $validated['number'])
                ->first();

            if ($existingTable) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['number' => 'Ya existe una mesa con este número en la institución seleccionada.']);
            }

            $institution = Institution::find($validated['institution_id']);

            // Generar códigos si están vacíos
            if (empty($validated['oep_code'])) {
                $validated['oep_code'] = $institution->code . '-' . $validated['number'];
            }

            if (empty($validated['internal_code'])) {
                $validated['internal_code'] = $institution->code . '-M' . str_pad($validated['number'], 2, '0', STR_PAD_LEFT);
            }

            $validated['municipality_id'] = $institution->municipality_id;

            // Valores por defecto
            $validated['expected_voters'] = $validated['expected_voters'] ?? 0;
            $validated['ballots_received'] = $validated['ballots_received'] ?? 0;
            $validated['ballots_spoiled'] = $validated['ballots_spoiled'] ?? 0;

            // Manejo de campos opcionales
            $validated['opening_time'] = $request->filled('opening_time') ? $request->opening_time : null;
            $validated['closing_time'] = $request->filled('closing_time') ? $request->closing_time : null;
            $validated['election_date'] = $request->filled('election_date') ? $request->election_date : null;
            $validated['acta_number'] = $request->filled('acta_number') ? $request->acta_number : null;
            $validated['observations'] = $request->filled('observations') ? $request->observations : null;

            DB::beginTransaction();

            try {
                $votingTable = VotingTable::create($validated);
                DB::commit();

                Log::info('=== STORE: Mesa creada exitosamente ===', ['id' => $votingTable->id]);

                return redirect()->route('voting-tables.index')
                    ->with('success', '✅ La mesa de votación fue creada con éxito.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('=== STORE: Error al crear en DB ===', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
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
                ->with('error', '❌ Error al crear la mesa: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $votingTable = VotingTable::with([
                'institution.locality.municipality.province.department',
                'electionType',
                'president',
                'secretary',
                'vocal1',
                'vocal2',
                'vocal3',
                'votes.candidate'
            ])->findOrFail($id);

            // Obtener estadísticas de votos por categoría
            $alcaldeCategory = ElectionCategory::where('code', 'ALC')->first();
            $concejalCategory = ElectionCategory::where('code', 'CON')->first();

            $votesByCategory = [
                'alcalde' => $votingTable->votes()
                    ->whereHas('candidate', function($q) use ($alcaldeCategory) {
                        $q->whereHas('electionCategory', function($sq) use ($alcaldeCategory) {
                            $sq->where('election_categories.id', $alcaldeCategory->id);
                        });
                    })
                    ->with('candidate')
                    ->get(),
                'concejal' => $votingTable->votes()
                    ->whereHas('candidate', function($q) use ($concejalCategory) {
                        $q->whereHas('electionCategory', function($sq) use ($concejalCategory) {
                            $sq->where('election_categories.id', $concejalCategory->id);
                        });
                    })
                    ->with('candidate')
                    ->get()
            ];

            return view('voting-tables.show', compact('votingTable', 'votesByCategory'));

        } catch (\Exception $e) {
            Log::error('Error showing voting table: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('voting-tables.index')
                ->with('error', 'Error al cargar los detalles de la mesa de votación.');
        }
    }

    public function edit($id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);
            $institutions = Institution::where('status', 'activo')
                                       ->orderBy('name')
                                       ->get();
            $electionTypes = ElectionType::where('active', true)
                                         ->orderBy('name')
                                         ->get();
            $statusOptions = VotingTable::getStatuses();

            return view('voting-tables.edit', compact('votingTable', 'institutions', 'electionTypes', 'statusOptions'));
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('voting-tables.index')
                ->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);
            $rules = $this->validationRules($id, $request);
            $validated = $request->validate($rules, $this->validationMessages());
            $existingTable = VotingTable::where('institution_id', $validated['institution_id'])
                ->where('number', $validated['number'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingTable) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['number' => 'Ya existe una mesa con este número en la institución seleccionada.']);
            }
            if ($votingTable->institution_id != $validated['institution_id']) {
                $institution = Institution::find($validated['institution_id']);
                $validated['municipality_id'] = $institution->municipality_id;
            }
            if (!empty($validated['opening_time']) && !empty($validated['closing_time'])) {
                if ($validated['closing_time'] <= $validated['opening_time']) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['closing_time' => 'La hora de cierre debe ser posterior a la hora de apertura.']);
                }
            }
            if ($validated['ballots_spoiled'] > $validated['ballots_received']) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['ballots_spoiled' => 'Las papeletas deterioradas no pueden ser mayores a las recibidas.']);
            }
            DB::beginTransaction();
            try {
                $votingTable->update($validated);
                DB::commit();
                return redirect()->route('voting-tables.show', $id)
                    ->with('success', '✅ La mesa de votación fue actualizada con éxito.');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating voting table: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $request->except('_token'),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error al actualizar la mesa de votación. ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);

            if ($votingTable->votes()->count() > 0) {
                return redirect()->back()
                    ->with('error', '❌ No se puede eliminar la mesa porque tiene votos registrados.');
            }

            DB::transaction(function () use ($votingTable) {
                $votingTable->delete();
            });

            return redirect()->route('voting-tables.index')
                ->with('success', '✅ La mesa de votación fue eliminada correctamente.');

        } catch (\Exception $e) {
            Log::error('Error deleting voting table: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', '❌ Error al eliminar la mesa de votación.');
        }
    }

    public function deleteMultiple(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:voting_tables,id'
            ]);

            $ids = $request->input('ids');
            $count = count($ids);

            $tablesWithVotes = VotingTable::whereIn('id', $ids)
                ->whereHas('votes')
                ->count();

            if ($tablesWithVotes > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar mesas que tienen votos registrados.'
                ], 422);
            }

            $deleted = VotingTable::whereIn('id', $ids)->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => "✅ Se eliminaron {$count} mesas de votación correctamente.",
                    'deleted_count' => $deleted
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudieron eliminar las mesas de votación.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error deleting multiple voting tables: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Ocurrió un error inesperado al eliminar las mesas.'
            ], 500);
        }
    }

    public function assignDelegatesForm($id)
    {
        try {
            $votingTable = VotingTable::with(['president', 'secretary', 'vocal1', 'vocal2', 'vocal3'])->findOrFail($id);
            $users = User::where('is_active', true)->orderBy('name')->get();

            return view('voting-tables.assign-delegates', compact('votingTable', 'users'));
        } catch (\Exception $e) {
            Log::error('Error loading assign delegates form: ' . $e->getMessage());
            return redirect()->route('voting-tables.show', $id)
                ->with('error', 'Error al cargar el formulario de asignación.');
        }
    }

    public function assignDelegates(Request $request, $id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);

            $validated = $request->validate([
                'president_id' => 'nullable|exists:users,id',
                'secretary_id' => 'nullable|exists:users,id',
                'vocal1_id' => 'nullable|exists:users,id',
                'vocal2_id' => 'nullable|exists:users,id',
                'vocal3_id' => 'nullable|exists:users,id',
                'vocal4_name' => 'nullable|string|max:255',
            ]);

            $votingTable->update($validated);

            return redirect()->route('voting-tables.show', $id)
                ->with('success', '✅ Delegados asignados correctamente.');

        } catch (\Exception $e) {
            Log::error('Error assigning delegates: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al asignar delegados: ' . $e->getMessage());
        }
    }

    public function exportAll(Request $request)
    {
        try {
            $filters = $request->only(['institution_id', 'status', 'election_type_id', 'search']);
            $export = new VotingTablesExport();
            $filePath = $export->export($filters);

            return response()->download(storage_path("app/{$filePath}"))
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error exporting all voting tables: ' . $e->getMessage());
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
                return redirect()->back()->with('error', '❌ No se seleccionaron mesas para exportar.');
            }

            $export = new VotingTablesExport();
            $filePath = $export->export(['selected_ids' => $selectedIds]);

            return response()->download(storage_path("app/{$filePath}"))
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error exporting selected voting tables: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al exportar las mesas seleccionadas: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            ]);
            $import = new VotingTablesImport();
            $result = $import->import($request->file('file'));
            if (!$result['success']) {
                return redirect()->route('voting-tables.index')
                    ->with('import_errors', $result['errors'])
                    ->with('error', '❌ Error durante la importación.');
            }
            if (!empty($result['errors']) && $result['success_count'] === 0) {
                return redirect()->route('voting-tables.index')
                    ->with('import_errors', $result['errors'])
                    ->with('error', '❌ No se pudo importar ninguna mesa.');
            } elseif (!empty($result['errors'])) {
                return redirect()->route('voting-tables.index')
                    ->with('import_errors', $result['errors'])
                    ->with('import_warnings', $result['warnings'] ?? [])
                    ->with('success_count', $result['success_count'])
                    ->with('warning', "⚠️ Se importaron {$result['success_count']} mesas. Algunas filas tuvieron errores.");
            }
            return redirect()->route('voting-tables.index')
                ->with('success', "✅ Se importaron {$result['success_count']} mesas correctamente.");
        } catch (\Exception $e) {
            Log::error('Error importing voting tables: ' . $e->getMessage());
            return redirect()->route('voting-tables.index')
                ->with('error', '❌ Error durante la importación: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $export = new VotingTablesExport();
            $filePath = $export->downloadTemplate();
            return response()->download(storage_path("app/{$filePath}"))
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al generar la plantilla: ' . $e->getMessage());
        }
    }

    public function getByInstitution($institutionId)
    {
        try {
            $votingTables = VotingTable::where('institution_id', $institutionId)
                ->select('id', 'number', 'oep_code', 'internal_code')
                ->orderBy('number')
                ->get();
            return response()->json($votingTables);
        } catch (\Exception $e) {
            Log::error('Error getting voting tables by institution: ' . $e->getMessage(), [
                'institution_id' => $institutionId
            ]);
            return response()->json(['error' => 'Error loading voting tables'], 500);
        }
    }

    private function generateTableCode($institution, $number)
    {
        return $institution->code . '-M' . str_pad($number, 2, '0', STR_PAD_LEFT);
    }
}
