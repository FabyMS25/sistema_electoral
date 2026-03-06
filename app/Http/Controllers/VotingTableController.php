<?php

namespace App\Http\Controllers;

use App\Models\VotingTable;
use App\Models\Institution;
use App\Models\ElectionType;
use App\Models\ElectionCategory;
use App\Models\ElectionTypeCategory;
use App\Models\VotingTableElection;
use App\Models\VotingTableCategoryResult;
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
            'number.required' => 'El número de mesa es obligatorio.',
            'number.integer' => 'El número de mesa debe ser un valor numérico.',
            'number.min' => 'El número de mesa debe ser mayor o igual a 1.',
            'oep_code.unique' => 'El código OEP ya está siendo utilizado por otra mesa.',
            'oep_code.max' => 'El código OEP no puede exceder los 20 caracteres.',
            'internal_code.unique' => 'El código interno ya está siendo utilizado por otra mesa.',
            'internal_code.max' => 'El código interno no puede exceder los 20 caracteres.',
            'institution_id.required' => 'Debe seleccionar una institución/recinto.',
            'institution_id.exists' => 'La institución seleccionada no existe en el sistema.',
            'letter.max' => 'La letra debe tener máximo 1 carácter.',
            'type.in' => 'El tipo de mesa debe ser: mixta, masculina o femenina.',
            'voter_range_start_name.max' => 'El apellido inicial no puede exceder los 255 caracteres.',
            'voter_range_end_name.max' => 'El apellido final no puede exceder los 255 caracteres.',
            'expected_voters.integer' => 'Los votantes esperados deben ser un valor numérico.',
            'expected_voters.min' => 'Los votantes esperados deben ser mayor o igual a 0.',
            'observations.string' => 'Las observaciones deben ser texto.',
        ];
    }

    private function validationRules($id = null)
    {
        return [
            'oep_code' => 'nullable|string|max:20|unique:voting_tables,oep_code' . ($id ? ',' . $id : ''),
            'internal_code' => 'nullable|string|max:20|unique:voting_tables,internal_code' . ($id ? ',' . $id : ''),
            'number' => 'required|integer|min:1',
            'letter' => 'nullable|string|max:1',
            'type' => 'nullable|in:mixta,masculina,femenina',

            'voter_range_start_name' => 'nullable|string|max:255',
            'voter_range_end_name' => 'nullable|string|max:255',

            'expected_voters' => 'nullable|integer|min:0',

            'institution_id' => 'required|exists:institutions,id',

            'president_id' => 'nullable|exists:users,id',
            'secretary_id' => 'nullable|exists:users,id',
            'vocal1_id' => 'nullable|exists:users,id',
            'vocal2_id' => 'nullable|exists:users,id',
            'vocal3_id' => 'nullable|exists:users,id',
            'vocal4_id' => 'nullable|exists:users,id',

            'observations' => 'nullable|string',
        ];
    }

    public function index(Request $request)
    {
        try {
            $query = VotingTable::with([
                'institution.locality.municipality.province.department',
                'tableElections.electionType',
                'president',
                'secretary',
                'vocal1',
                'vocal2',
                'vocal3',
                'vocal4',
            ]);

            if ($request->filled('institution_id')) {
                $query->where('institution_id', $request->institution_id);
            }

            if ($request->filled('status')) {
                $query->whereHas('tableElections', function($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }

            if ($request->filled('election_type_id')) {
                $query->whereHas('tableElections', function($q) use ($request) {
                    $q->where('election_type_id', $request->election_type_id);
                });
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

            $sortField = $request->get('sort', 'institution_id');
            $sortDirection = $request->get('direction', 'asc');

            $allowedSortFields = [
                'institution_id', 'number', 'oep_code', 'internal_code', 'expected_voters',
                'institution_name'
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

            // Add computed properties for each table
            $votingTables->getCollection()->transform(function($table) {
                $latestElection = $table->tableElections()->latest()->first();
                if ($latestElection) {
                    $table->current_status = $latestElection->status;
                    $table->total_voters = $latestElection->total_voters ?? 0;
                    $table->election_type_name = $latestElection->electionType->name ?? 'N/A';
                } else {
                    $table->current_status = 'sin_configurar';
                    $table->total_voters = 0;
                    $table->election_type_name = 'Sin elección';
                }
                return $table;
            });

            $institutions = Institution::where('status', 'activo')
                                       ->orderBy('name')
                                       ->get();

            $electionTypes = ElectionType::where('active', true)
                                        ->orderBy('name')
                                        ->get();

            $statusOptions = [
                'configurada' => 'Configurada',
                'en_espera' => 'En Espera',
                'votacion' => 'Votación',
                'cerrada' => 'Cerrada',
                'en_escrutinio' => 'En Escrutinio',
                'escrutada' => 'Escrutada',
                'observada' => 'Observada',
                'transmitida' => 'Transmitida',
                'anulada' => 'Anulada'
            ];

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
            $users = User::where('is_active', true)->orderBy('name')->get();

            return view('voting-tables.create', compact('institutions', 'electionTypes', 'users'));
        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage());
            return redirect()->route('voting-tables.index')
                            ->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = $this->validationRules(null);
            $validated = $request->validate($rules, $this->validationMessages());

            // Check for existing table with same number in institution
            $existingTable = VotingTable::where('institution_id', $validated['institution_id'])
                ->where('number', $validated['number'])
                ->first();

            if ($existingTable) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['number' => 'Ya existe una mesa con este número en la institución seleccionada.']);
            }

            // Generate codes if not provided
            $institution = Institution::find($validated['institution_id']);

            if (empty($validated['oep_code'])) {
                $validated['oep_code'] = $institution->code . '-' . $validated['number'];
            }

            if (empty($validated['internal_code'])) {
                $validated['internal_code'] = $institution->code . '-M' . str_pad($validated['number'], 2, '0', STR_PAD_LEFT);
            }

            DB::beginTransaction();
            try {
                $votingTable = VotingTable::create($validated);

                // Create table election records for all active election types
                $activeElectionTypes = ElectionType::where('active', true)->get();

                foreach ($activeElectionTypes as $electionType) {
                    VotingTableElection::create([
                        'voting_table_id' => $votingTable->id,
                        'election_type_id' => $electionType->id,
                        'ballots_received' => 0,
                        'ballots_used' => 0,
                        'ballots_leftover' => 0,
                        'ballots_spoiled' => 0,
                        'total_voters' => 0,
                        'status' => 'configurada',
                        'election_date' => $electionType->election_date,
                    ]);
                }

                DB::commit();

                return redirect()->route('voting-tables.show', $votingTable->id)
                    ->with('success', '✅ La mesa de votación fue creada con éxito.');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating voting table: ' . $e->getMessage(), [
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
                'tableElections.electionType.categories',
                'president',
                'secretary',
                'vocal1',
                'vocal2',
                'vocal3',
                'vocal4',
                'categoryResults.electionTypeCategory.electionCategory',
                'categoryResults.electionTypeCategory.electionType',
            ])->findOrFail($id);

            // Get active election types with their categories
            $electionTypes = ElectionType::with(['typeCategories.electionCategory'])
                ->where('active', true)
                ->orderBy('election_date', 'desc')
                ->get();

            // Get results grouped by election type
            $resultsByElection = [];
            foreach ($votingTable->tableElections as $tableElection) {
                $electionTypeId = $tableElection->election_type_id;
                $resultsByElection[$electionTypeId] = [
                    'election' => $tableElection->electionType,
                    'status' => $tableElection->status,
                    'ballots_received' => $tableElection->ballots_received,
                    'ballots_used' => $tableElection->ballots_used,
                    'ballots_leftover' => $tableElection->ballots_leftover,
                    'ballots_spoiled' => $tableElection->ballots_spoiled,
                    'total_voters' => $tableElection->total_voters,
                    'results' => $votingTable->categoryResults()
                        ->whereHas('electionTypeCategory', function($q) use ($electionTypeId) {
                            $q->where('election_type_id', $electionTypeId);
                        })
                        ->with('electionTypeCategory.electionCategory')
                        ->get()
                        ->map(function($result) {
                            return [
                                'category' => $result->electionTypeCategory->electionCategory->name,
                                'code' => $result->electionTypeCategory->electionCategory->code,
                                'ballot_order' => $result->electionTypeCategory->ballot_order,
                                'valid_votes' => $result->valid_votes,
                                'blank_votes' => $result->blank_votes,
                                'null_votes' => $result->null_votes,
                                'total_votes' => $result->total_votes,
                                'is_consistent' => $result->is_consistent,
                            ];
                        }),
                ];
            }

            return view('voting-tables.show', compact('votingTable', 'electionTypes', 'resultsByElection'));

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
            $users = User::where('is_active', true)->orderBy('name')->get();

            return view('voting-tables.edit', compact('votingTable', 'institutions', 'users'));
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
            $rules = $this->validationRules($id);
            $validated = $request->validate($rules, $this->validationMessages());

            // Check for existing table with same number in institution
            $existingTable = VotingTable::where('institution_id', $validated['institution_id'])
                ->where('number', $validated['number'])
                ->where('id', '!=', $id)
                ->first();

            if ($existingTable) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['number' => 'Ya existe una mesa con este número en la institución seleccionada.']);
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
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error al actualizar la mesa de votación.');
        }
    }

    public function destroy($id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);

            // Check if there are any results
            if ($votingTable->categoryResults()->count() > 0) {
                return redirect()->back()
                    ->with('error', '❌ No se puede eliminar la mesa porque tiene resultados registrados.');
            }

            DB::transaction(function () use ($votingTable) {
                // Delete related records
                $votingTable->tableElections()->delete();
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

            // Check for tables with results
            $tablesWithResults = VotingTable::whereIn('id', $ids)
                ->whereHas('categoryResults')
                ->count();

            if ($tablesWithResults > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar mesas que tienen resultados registrados.'
                ], 422);
            }

            DB::transaction(function () use ($ids) {
                // Delete table elections first
                VotingTableElection::whereIn('voting_table_id', $ids)->delete();
                // Delete tables
                VotingTable::whereIn('id', $ids)->delete();
            });

            return response()->json([
                'success' => true,
                'message' => "✅ Se eliminaron " . count($ids) . " mesas de votación correctamente."
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting multiple voting tables: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Ocurrió un error inesperado al eliminar las mesas.'
            ], 500);
        }
    }

    public function electionConfig($id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);
            $electionTypes = ElectionType::where('active', true)->orderBy('name')->get();

            return view('voting-tables.election-config', compact('votingTable', 'electionTypes'));

        } catch (\Exception $e) {
            Log::error('Error loading election config form: ' . $e->getMessage());
            return redirect()->route('voting-tables.show', $id)
                ->with('error', 'Error al cargar el formulario de configuración.');
        }
    }

    public function updateElectionConfig(Request $request, $id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);

            $validated = $request->validate([
                'election_type_id' => 'required|exists:election_types,id',
                'ballots_received' => 'required|integer|min:0',
                'ballots_used' => 'required|integer|min:0',
                'ballots_leftover' => 'required|integer|min:0',
                'ballots_spoiled' => 'required|integer|min:0',
                'total_voters' => 'required|integer|min:0',
                'status' => 'required|in:configurada,en_espera,votacion,cerrada,en_escrutinio,escrutada,observada,transmitida,anulada',
                'opening_time' => 'nullable',
                'closing_time' => 'nullable',
            ]);

            // Validation logic
            if ($validated['ballots_received'] < $validated['total_voters']) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['ballots_received' => 'Las papeletas recibidas no pueden ser menores al total de votantes.']);
            }

            if ($validated['ballots_spoiled'] > $validated['ballots_received']) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['ballots_spoiled' => 'Las papeletas deterioradas no pueden ser mayores a las recibidas.']);
            }

            $totalCalculated = $validated['ballots_used'] + $validated['ballots_leftover'] + $validated['ballots_spoiled'];
            if ($totalCalculated != $validated['ballots_received']) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['ballots_used' => 'La suma de usadas + sobrantes + deterioradas debe igualar las recibidas.']);
            }

            DB::beginTransaction();
            try {
                $tableElection = VotingTableElection::updateOrCreate(
                    [
                        'voting_table_id' => $votingTable->id,
                        'election_type_id' => $validated['election_type_id'],
                    ],
                    [
                        'ballots_received' => $validated['ballots_received'],
                        'ballots_used' => $validated['ballots_used'],
                        'ballots_leftover' => $validated['ballots_leftover'],
                        'ballots_spoiled' => $validated['ballots_spoiled'],
                        'total_voters' => $validated['total_voters'],
                        'status' => $validated['status'],
                        'opening_time' => $validated['opening_time'],
                        'closing_time' => $validated['closing_time'],
                        'election_date' => now(),
                    ]
                );

                DB::commit();

                return redirect()->route('voting-tables.show', $id)
                    ->with('success', '✅ Configuración de elección actualizada correctamente.');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating election config: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error al actualizar la configuración: ' . $e->getMessage());
        }
    }

    public function assignDelegatesForm($id)
    {
        try {
            $votingTable = VotingTable::with(['president', 'secretary', 'vocal1', 'vocal2', 'vocal3', 'vocal4'])
                ->findOrFail($id);
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
                'vocal4_id' => 'nullable|exists:users,id',
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
            return redirect()->back()->with('error', '❌ Error al exportar las mesas seleccionadas.');
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
            return redirect()->back()->with('error', '❌ Error al generar la plantilla.');
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
