<?php
namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Candidate;
use App\Models\VotingTable;
use App\Models\Institution;
use App\Models\ElectionType;
use App\Models\ElectionCategory;
use App\Models\ElectionTypeCategory;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VotingTableVoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('permission:view_votes')->only(['index', 'getTableVotes', 'getTableStats']);
        $this->middleware('permission:register_votes')->only(['registerVotes', 'registerAllVotes']);
        $this->middleware('permission:observe_votes')->only(['observeTable']);
        $this->middleware('permission:validate_votes')->only(['validateTable']);
        $this->middleware('permission:correct_votes')->only(['correctTable']);
        $this->middleware('permission:close_table')->only(['closeTable']);
        $this->middleware('permission:reopen_table')->only(['reopenTable']);
        $this->middleware('permission:upload_acta')->only(['uploadActa']); // ← Agregado
    }

    private function canAccessTable($votingTable, $permission = null)
    {
        $user = Auth::user();
        if ($user->roles()->where('name', 'administrador')->exists()) {
            return true;
        }
        if ($permission) {
            $scope = 'global';
            $scopeId = null;
            $roleAssignment = $user->roles()
                ->wherePivot('scope', 'institution')
                ->wherePivot('institution_id', $votingTable->institution_id)
                ->first();
            if ($roleAssignment) {
                $scope = 'institution';
                $scopeId = $votingTable->institution_id;
            }
            $tableAssignment = $user->roles()
                ->wherePivot('scope', 'voting_table')
                ->wherePivot('voting_table_id', $votingTable->id)
                ->first();
            if ($tableAssignment) {
                $scope = 'voting_table';
                $scopeId = $votingTable->id;
            }
            if (!$this->userHasPermission($user, $permission, $scope, $scopeId)) {
                return false;
            }
        }
        return true;
    }

    private function userHasPermission($user, $permissionName, $scope = null, $scopeId = null)
    {
        if ($user->roles()->where('name', 'administrador')->exists()) {
            return true;
        }
        $hasDirectPermission = $user->permissions()
            ->where('name', $permissionName)
            ->where(function($q) use ($scope, $scopeId) {
                $q->where('scope', 'global');
                if ($scope && $scopeId) {
                    $q->orWhere(function($q2) use ($scope, $scopeId) {
                        $q2->where('scope', $scope)
                           ->where('scope_id', $scopeId);
                    });
                }
            })->exists();
        if ($hasDirectPermission) {
            return true;
        }
        foreach ($user->roles as $role) {
            $hasRolePermission = $role->permissions()
                ->where('name', $permissionName)
                ->exists();
            if ($hasRolePermission) {
                if ($role->pivot->scope === 'global') {
                    return true;
                }
                if ($scope === 'institution' && $role->pivot->scope === 'institution' && $role->pivot->institution_id == $scopeId) {
                    return true;
                }
                if ($scope === 'voting_table' && $role->pivot->scope === 'voting_table' && $role->pivot->voting_table_id == $scopeId) {
                    return true;
                }
            }
        }
        return false;
    }

    private function applyScopeFilter($query)
    {
        $user = Auth::user();
        if ($user->roles()->where('name', 'administrador')->exists()) {
            return $query;
        }
        $institutionIds = $user->roles()
            ->wherePivot('scope', 'institution')
            ->wherePivot('institution_id', '!=', null)
            ->get()
            ->pluck('pivot.institution_id')
            ->toArray();
        $tableIds = $user->roles()
            ->wherePivot('scope', 'voting_table')
            ->wherePivot('voting_table_id', '!=', null)
            ->get()
            ->pluck('pivot.voting_table_id')
            ->toArray();
        if (!empty($tableIds)) {
            return $query->whereIn('id', $tableIds);
        }
        if (!empty($institutionIds)) {
            return $query->whereIn('institution_id', $institutionIds);
        }
        return $query->whereRaw('1 = 0');
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$this->userHasPermission($user, 'view_votes')) {
                abort(403, 'No tiene permiso para ver votos');
            }

            $institutionId = $request->input('institution_id');
            $electionTypeId = $request->input('election_type_id');
            $status = $request->input('status');
            $tableNumber = $request->input('table_number');
            $tableCode = $request->input('table_code');
            $tableType = $request->input('table_type');
            $fromName = $request->input('from_name');
            $toName = $request->input('to_name');
            $minVotes = $request->input('min_votes');
            $maxVotes = $request->input('max_votes');
            $participation = $request->input('participation');
            $hasObservations = $request->input('has_observations');
            $sortBy = $request->input('sort_by', 'number');
            $sortDirection = $request->input('sort_direction', 'asc');

            if (!$electionTypeId) {
                $defaultElectionType = ElectionType::where('active', true)->first();
                $electionTypeId = $defaultElectionType?->id;
            }

            $electionType = ElectionType::find($electionTypeId);

            // 🔴 CORRECCIÓN: Inicializar arrays vacíos por defecto
            $categories = collect();
            $candidatesByCategory = [];

            if ($electionType) {
                // Obtener todas las categorías para este tipo de elección
                $categories = ElectionCategory::whereHas('typeCategories', function($q) use ($electionTypeId) {
                    $q->where('election_type_id', $electionTypeId);
                })->with(['typeCategories' => function($q) use ($electionTypeId) {
                    $q->where('election_type_id', $electionTypeId);
                }])->orderBy('order')->get();

                // Obtener candidates agrupados por categoría
                foreach ($categories as $category) {
                    $typeCategoryIds = ElectionTypeCategory::where('election_type_id', $electionTypeId)
                        ->where('election_category_id', $category->id)
                        ->pluck('id')
                        ->toArray();

                    $candidatesByCategory[$category->code] = Candidate::whereIn('election_type_category_id', $typeCategoryIds)
                        ->where('active', true)
                        ->orderBy('list_order')
                        ->orderBy('name')
                        ->get();

                    // 🔴 DEBUG: Verificar si hay candidatos
                    Log::info("Categoría {$category->code}: " . $candidatesByCategory[$category->code]->count() . " candidatos");
                }
            }

            // Filtros de instituciones
            $institutionsQuery = Institution::where('status', 'activo');
            $assignedInstitutionIds = $user->roles()
                ->wherePivot('scope', 'institution')
                ->wherePivot('institution_id', '!=', null)
                ->get()
                ->pluck('pivot.institution_id')
                ->toArray();

            if (!empty($assignedInstitutionIds)) {
                $institutionsQuery->whereIn('id', $assignedInstitutionIds);
            }

            $institutions = $institutionsQuery->orderBy('name')->get(['id', 'name', 'code']);
            $electionTypes = ElectionType::where('active', true)->orderBy('election_date', 'desc')->get(['id', 'name', 'election_date']);

            // Query de mesas
            $query = VotingTable::with([
                'institution:id,name,code',
                'votes' => function($q) use ($electionTypeId) {
                    $q->where('election_type_id', $electionTypeId)
                      ->with('candidate:id,name,party,color,election_type_category_id,type,photo,party_logo');
                },
                'observations' => function($q) {
                    $q->where('status', 'pending');
                },
                'president:id,name,last_name',
                'secretary:id,name,last_name',
                'vocal1:id,name,last_name',
                'vocal2:id,name,last_name',
                'vocal3:id,name,last_name',
            ])->withCount(['observations as observations_count' => function($q) {
                $q->where('status', 'pending');
            }])->withCount(['votes as votes_count' => function($q) use ($electionTypeId) {
                $q->where('election_type_id', $electionTypeId);
            }]);

            $query = $this->applyScopeFilter($query);

            if ($institutionId) {
                $query->where('institution_id', $institutionId);
            }
            if ($status) {
                $query->where('status', $status);
            }
            if ($tableNumber) {
                $query->where('number', $tableNumber);
            }
            if ($tableCode) {
                $query->where(function($q) use ($tableCode) {
                    $q->where('oep_code', 'ilike', "%{$tableCode}%")
                      ->orWhere('internal_code', 'ilike', "%{$tableCode}%");
                });
            }
            if ($tableType) {
                $query->where('type', $tableType);
            }
            if ($fromName) {
                $query->where('voter_range_start_name', 'ilike', "%{$fromName}%");
            }
            if ($toName) {
                $query->where('voter_range_end_name', 'ilike', "%{$toName}%");
            }

            // Ordenamiento
            switch ($sortBy) {
                case 'number':
                    $query->orderBy('number', $sortDirection);
                    break;
                case 'expected_voters':
                    $query->orderBy('expected_voters', $sortDirection);
                    break;
                case 'status':
                    $query->orderBy('status', $sortDirection);
                    break;
                default:
                    $query->orderBy('institution_id')->orderBy('number', 'asc');
            }

            $votingTables = $query->paginate(10)->withQueryString();

            // Calcular totales
            $totals = $this->calculateTotals($votingTables);
            $totals['participation'] = $totals['expected'] > 0 ? round(($totals['total'] / $totals['expected']) * 100, 1) : 0;

            // Estadísticas de mesas
            $tableStats = [
                'total' => $votingTables->total(),
                'configurada' => $votingTables->where('status', 'configurada')->count(),
                'en_espera' => $votingTables->where('status', 'en_espera')->count(),
                'votacion' => $votingTables->where('status', 'votacion')->count(),
                'en_escrutinio' => $votingTables->where('status', 'en_escrutinio')->count(),
                'cerrada' => $votingTables->where('status', 'cerrada')->count(),
                'observada' => $votingTables->where('status', 'observada')->count(),
                'escrutada' => $votingTables->where('status', 'escrutada')->count(),
                'transmitida' => $votingTables->where('status', 'transmitida')->count(),
                'anulada' => $votingTables->where('status', 'anulada')->count(),
            ];

            $statusLabels = [
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

            $validationLabels = [
                'pending' => 'Pendiente',
                'reviewed' => 'Revisado',
                'observed' => 'Observado',
                'corrected' => 'Corregido',
                'validated' => 'Validado',
                'approved' => 'Aprobado',
                'rejected' => 'Rechazado'
            ];

            // 🔴 CORRECCIÓN: Agregar todos los permisos necesarios
            $permissions = [
                'can_register' => $this->userHasPermission($user, 'register_votes'),
                'can_observe' => $this->userHasPermission($user, 'observe_votes'),
                'can_validate' => $this->userHasPermission($user, 'validate_votes'),
                'can_correct' => $this->userHasPermission($user, 'correct_votes'),
                'can_close' => $this->userHasPermission($user, 'close_table'),
                'can_reopen' => $this->userHasPermission($user, 'reopen_table'),
                'can_view' => $this->userHasPermission($user, 'view_votes'),
                'can_upload_acta' => $this->userHasPermission($user, 'upload_acta'), // ← IMPORTANTE
                'can_review' => $this->userHasPermission($user, 'review_votes'),
            ];

            // 🔴 DEBUG: Verificar que los datos llegan a la vista
            Log::info('Datos enviados a la vista:', [
                'categories_count' => $categories->count(),
                'candidates_count' => collect($candidatesByCategory)->map->count(),
                'permissions' => $permissions
            ]);

            return view('voting-table-votes.index', compact(
                'votingTables',
                'categories',
                'candidatesByCategory',
                'institutions',
                'electionTypes',
                'electionType',
                'institutionId',
                'electionTypeId',
                'totals',
                'tableStats',
                'permissions',
                'statusLabels',
                'validationLabels',
                'request'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading voting table votes: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los datos de votación: ' . $e->getMessage());
        }
    }


public function registerVotes(Request $request)
{
    Log::info('========== REGISTRAR VOTOS ==========');
    Log::info('Request completo:', $request->all());

    try {
        $validated = $request->validate([
            'voting_table_id' => 'required|integer|exists:voting_tables,id',
            'election_type_id' => 'required|integer|exists:election_types,id',
            'votes' => 'required|array',
            'votes.*' => 'integer|min:0',
            'close' => 'boolean'
        ]);

        Log::info('✅ Validación pasada', $validated);

        $user = Auth::user();
        $votingTableId = $validated['voting_table_id'];
        $electionTypeId = $validated['election_type_id'];
        $votesData = $validated['votes'];
        $closeTable = $validated['close'] ?? false;

        Log::info('Datos procesados:', [
            'user_id' => $user->id,
            'voting_table_id' => $votingTableId,
            'election_type_id' => $electionTypeId,
            'votes_count' => count($votesData),
            'close' => $closeTable
        ]);

        DB::beginTransaction();

        try {
            $votingTable = VotingTable::lockForUpdate()->find($votingTableId);
            if (!$votingTable) {
                throw new \Exception('Mesa no encontrada');
            }

            Log::info('✅ Mesa encontrada', [
                'id' => $votingTable->id,
                'status' => $votingTable->status,
                'expected_voters' => $votingTable->expected_voters
            ]);

            if (!$this->canAccessTable($votingTable, 'register_votes')) {
                throw new \Exception('No tiene permisos para registrar votos en esta mesa');
            }

            if (in_array($votingTable->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                throw new \Exception('No se pueden modificar votos de una mesa ' . $votingTable->status);
            }

            // Obtener todas las categorías
            $categories = ElectionCategory::whereHas('typeCategories', function($q) use ($electionTypeId) {
                $q->where('election_type_id', $electionTypeId);
            })->get();

            Log::info('✅ Categorías encontradas', ['count' => $categories->count()]);

            // Obtener candidatos válidos
            $candidateIds = array_keys($votesData);
            Log::info('Buscando candidatos con IDs:', $candidateIds);

            $candidates = Candidate::whereIn('id', $candidateIds)
                ->whereHas('electionTypeCategory', function($q) use ($electionTypeId) {
                    $q->where('election_type_id', $electionTypeId);
                })
                ->where('active', true)
                ->with('electionTypeCategory.electionCategory')
                ->get()
                ->keyBy('id');

            Log::info('✅ Candidatos encontrados', ['count' => $candidates->count()]);

            // Verificar candidatos inválidos
            $invalidCandidates = [];
            foreach ($candidateIds as $candidateId) {
                if (!isset($candidates[$candidateId])) {
                    $invalidCandidates[] = $candidateId;
                }
            }

            if (!empty($invalidCandidates)) {
                throw new \Exception("Candidatos no válidos: " . implode(', ', $invalidCandidates));
            }

            // Agrupar votos por categoría
            $categoryVotes = [];
            foreach ($categories as $category) {
                $categoryVotes[$category->code] = 0;
            }

            Log::info('Procesando votos individuales...');

            // Guardar votos
            foreach ($votesData as $candidateId => $quantity) {
                $quantity = intval($quantity);
                $candidate = $candidates[$candidateId];

                Log::info("Procesando candidato {$candidateId}: {$quantity} votos");

                if ($quantity > 0) {
                    $categoryCode = $candidate->electionTypeCategory->electionCategory->code;
                    if (isset($categoryVotes[$categoryCode])) {
                        $categoryVotes[$categoryCode] += $quantity;
                    }

                    // 🔴 IMPORTANTE: Verificar que se está guardando
                    $vote = Vote::updateOrCreate(
                        [
                            'voting_table_id' => $votingTable->id,
                            'candidate_id' => $candidateId,
                            'election_type_id' => $electionTypeId
                        ],
                        [
                            'quantity' => $quantity,
                            'user_id' => $user->id,
                            'registered_at' => now(),
                            'vote_status' => 'pending_review',
                        ]
                    );

                    Log::info("✅ Voto guardado/actualizado", [
                        'vote_id' => $vote->id,
                        'candidate_id' => $candidateId,
                        'quantity' => $quantity
                    ]);
                } else {
                    $deleted = Vote::where('voting_table_id', $votingTable->id)
                        ->where('candidate_id', $candidateId)
                        ->where('election_type_id', $electionTypeId)
                        ->delete();

                    Log::info("🗑️ Votos eliminados", ['candidate_id' => $candidateId, 'deleted' => $deleted]);
                }
            }

            Log::info('✅ Votos procesados. Totales por categoría:', $categoryVotes);

            // Validar que todas las categorías tengan el mismo total
            $firstTotal = null;
            $inconsistencies = [];

            foreach ($categoryVotes as $code => $total) {
                if ($firstTotal === null) {
                    $firstTotal = $total;
                } elseif ($total != $firstTotal) {
                    $inconsistencies[] = "$code: $total votos (debería ser $firstTotal)";
                }
            }

            if (!empty($inconsistencies)) {
                throw new \Exception(
                    "Inconsistencia en el número de votantes por categoría:\n" .
                    implode("\n", $inconsistencies) .
                    "\n\nTodas las categorías deben tener el MISMO número de votantes."
                );
            }

            // Validar contra votantes esperados
            if ($votingTable->expected_voters && $firstTotal > $votingTable->expected_voters) {
                throw new \Exception(
                    "Los votos registrados ({$firstTotal}) exceden " .
                    "los votantes habilitados ({$votingTable->expected_voters})"
                );
            }

            // Actualizar totales en la tabla voting_tables
            $this->updateVotingTableTotals($votingTable, $electionTypeId, $categories);

            // Actualizar estado de la mesa
            if (in_array($votingTable->status, ['configurada', 'en_espera'])) {
                $votingTable->status = 'votacion';
            }

            if ($closeTable) {
                if (!$this->canAccessTable($votingTable, 'close_table')) {
                    throw new \Exception('No tiene permiso para cerrar esta mesa');
                }

                $errors = $votingTable->validateResults();
                if (!empty($errors)) {
                    throw new \Exception('No se puede cerrar la mesa: ' . implode(', ', $errors));
                }

                $votingTable->status = 'cerrada';
                $votingTable->closing_time = now();
                $votingTable->closed_by = $user->id;
                $votingTable->closed_at = now();
            }

            $votingTable->save();
            DB::commit();

            // Verificar que los votos se guardaron
            $savedVotes = Vote::where('voting_table_id', $votingTable->id)
                ->where('election_type_id', $electionTypeId)
                ->get();

            Log::info('✅ Transacción completada. Votos guardados:', [
                'total' => $savedVotes->sum('quantity'),
                'count' => $savedVotes->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => $closeTable ? '✅ Votos registrados y mesa cerrada exitosamente' : '✅ Votos registrados exitosamente',
                'table_status' => $votingTable->status,
                'category_totals' => $categoryVotes,
                'total_voters' => $firstTotal
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error en transacción: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }

    } catch (ValidationException $e) {
        Log::error('❌ Error de validación:', $e->errors());
        return response()->json([
            'success' => false,
            'message' => '❌ Datos inválidos',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('❌ Error general: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => '❌ ' . $e->getMessage()
        ], 500);
    }
}
    private function updateVotingTableTotals($votingTable, $electionTypeId, $categories)
    {
        // Resetear campos
        $votingTable->valid_votes = 0;
        $votingTable->blank_votes = 0;
        $votingTable->null_votes = 0;
        $votingTable->valid_votes_second = 0;
        $votingTable->blank_votes_second = 0;
        $votingTable->null_votes_second = 0;

        foreach ($categories as $index => $category) {
            $suffix = $index === 0 ? '' : '_second';

            // Votos válidos (no nulos ni blancos)
            $votingTable->{'valid_votes' . $suffix} = Vote::where('voting_table_id', $votingTable->id)
                ->where('election_type_id', $electionTypeId)
                ->whereHas('candidate', function($q) use ($category) {
                    $q->whereHas('electionTypeCategory', function($q2) use ($category) {
                        $q2->where('election_category_id', $category->id);
                    })->whereNotIn('type', ['null_votes', 'blank_votes']);
                })
                ->sum('quantity');

            // Votos en blanco
            $votingTable->{'blank_votes' . $suffix} = Vote::where('voting_table_id', $votingTable->id)
                ->where('election_type_id', $electionTypeId)
                ->whereHas('candidate', function($q) use ($category) {
                    $q->whereHas('electionTypeCategory', function($q2) use ($category) {
                        $q2->where('election_category_id', $category->id);
                    })->where('type', 'blank_votes');
                })
                ->sum('quantity');

            // Votos nulos
            $votingTable->{'null_votes' . $suffix} = Vote::where('voting_table_id', $votingTable->id)
                ->where('election_type_id', $electionTypeId)
                ->whereHas('candidate', function($q) use ($category) {
                    $q->whereHas('electionTypeCategory', function($q2) use ($category) {
                        $q2->where('election_category_id', $category->id);
                    })->where('type', 'null_votes');
                })
                ->sum('quantity');
        }

        // Calcular totales
        $votingTable->total_voters = $votingTable->valid_votes + $votingTable->blank_votes + $votingTable->null_votes;
        $votingTable->total_voters_second = $votingTable->valid_votes_second + $votingTable->blank_votes_second + $votingTable->null_votes_second;
        $votingTable->ballots_used = $votingTable->total_voters;
        $votingTable->ballots_leftover = ($votingTable->ballots_received ?? 0) - $votingTable->ballots_used - ($votingTable->ballots_spoiled ?? 0);
    }

    private function calculateTotals($votingTables)
    {
        $totals = [
            'expected' => 0,
            'total' => 0,
            'by_candidate' => []
        ];

        foreach ($votingTables as $table) {
            $totals['expected'] += $table->expected_voters ?? 0;
            $totals['total'] += $table->total_voters ?? 0;

            foreach ($table->votes as $vote) {
                if (!isset($totals['by_candidate'][$vote->candidate_id])) {
                    $totals['by_candidate'][$vote->candidate_id] = 0;
                }
                $totals['by_candidate'][$vote->candidate_id] += $vote->quantity;
            }
        }

        return $totals;
    }

    public function registerAllVotes(Request $request)
    {
        try {
            $validated = $request->validate([
                'election_type_id' => 'required|integer|exists:election_types,id',
                'tables' => 'required|array',
                'tables.*' => 'required|array',
                'tables.*.*' => 'integer|min:0',
                'close_all' => 'boolean'
            ]);

            $user = Auth::user();
            if (!$this->userHasPermission($user, 'register_votes')) {
                return response()->json(['success' => false, 'message' => 'No tiene permiso para registrar votos'], 403);
            }

            $electionTypeId = $validated['election_type_id'];
            $tablesData = $validated['tables'];
            $closeAll = $validated['close_all'] ?? false;

            DB::beginTransaction();

            try {
                $processedTables = 0;
                $errors = [];

                foreach ($tablesData as $tableId => $votesData) {
                    try {
                        $votingTable = VotingTable::lockForUpdate()->find($tableId);
                        if (!$votingTable) {
                            $errors[] = "Mesa ID $tableId no encontrada";
                            continue;
                        }

                        if (!$this->canAccessTable($votingTable, 'register_votes')) {
                            $errors[] = "Mesa {$votingTable->internal_code}: Sin permiso";
                            continue;
                        }

                        if (in_array($votingTable->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                            continue;
                        }

                        // Procesar votos individualmente
                        foreach ($votesData as $candidateId => $quantity) {
                            if ($quantity > 0) {
                                Vote::updateOrCreate(
                                    [
                                        'voting_table_id' => $votingTable->id,
                                        'candidate_id' => $candidateId,
                                        'election_type_id' => $electionTypeId
                                    ],
                                    [
                                        'quantity' => $quantity,
                                        'user_id' => $user->id,
                                        'registered_at' => now(),
                                        'vote_status' => 'pending_review',
                                    ]
                                );
                            } else {
                                Vote::where('voting_table_id', $votingTable->id)
                                    ->where('candidate_id', $candidateId)
                                    ->where('election_type_id', $electionTypeId)
                                    ->delete();
                            }
                        }

                        if ($closeAll && !in_array($votingTable->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                            $votingTable->status = 'cerrada';
                            $votingTable->closing_time = now();
                            $votingTable->closed_by = $user->id;
                            $votingTable->closed_at = now();
                        }

                        $votingTable->save();
                        $processedTables++;

                    } catch (\Exception $e) {
                        $errors[] = "Error en mesa {$tableId}: " . $e->getMessage();
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => empty($errors),
                    'message' => "Se procesaron {$processedTables} mesas",
                    'processed' => $processedTables,
                    'errors' => $errors
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error registering all votes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al procesar las mesas'], 500);
        }
    }

    public function observeTable(Request $request, $tableId)
    {
        try {
            $validated = $request->validate(['notes' => 'nullable|string|max:500']);
            $votingTable = VotingTable::findOrFail($tableId);

            if (!$this->canAccessTable($votingTable, 'observe_votes')) {
                return response()->json(['success' => false, 'message' => 'No tiene permiso'], 403);
            }

            DB::beginTransaction();

            $observation = Observation::create([
                'code' => Observation::generateCode(),
                'type' => 'inconsistencia_acta',
                'description' => $validated['notes'] ?? 'Mesa observada',
                'severity' => 'warning',
                'status' => 'pending',
                'voting_table_id' => $votingTable->id,
                'election_type_id' => $votingTable->election_type_id,
                'reviewed_by' => Auth::id(),
                'reviewer_role' => 'revisor',
            ]);

            $votingTable->status = 'observada';
            $votingTable->verified_by = Auth::id();
            $votingTable->verified_at = now();
            $votingTable->verification_notes = $validated['notes'] ?? null;
            $votingTable->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Mesa observada correctamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error observing table: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al observar'], 500);
        }
    }

    public function validateTable(Request $request, $tableId)
    {
        try {
            $validated = $request->validate([
                'action' => 'required|in:validate,approve,reject',
                'notes' => 'nullable|string|max:500',
            ]);

            $votingTable = VotingTable::findOrFail($tableId);

            if (!$this->canAccessTable($votingTable, 'validate_votes')) {
                return response()->json(['success' => false, 'message' => 'No tiene permiso'], 403);
            }

            DB::beginTransaction();

            $tableStatus = match($validated['action']) {
                'validate' => 'en_escrutinio',
                'approve' => 'escrutada',
                'reject' => 'observada',
                default => 'en_escrutinio'
            };

            $voteStatus = match($validated['action']) {
                'validate' => 'validated',
                'approve' => 'approved',
                'reject' => 'rejected',
                default => 'validated'
            };

            $votingTable->status = $tableStatus;
            $votingTable->validated_by = Auth::id();
            $votingTable->validated_at = now();
            $votingTable->validation_notes = $validated['notes'] ?? null;
            $votingTable->save();

            Vote::where('voting_table_id', $tableId)->update([
                'vote_status' => $voteStatus,
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);

            DB::commit();

            $messages = ['validate' => 'Validada', 'approve' => 'Aprobada', 'reject' => 'Rechazada'];
            return response()->json(['success' => true, 'message' => "Mesa {$messages[$validated['action']]} correctamente"]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error validating table: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al validar'], 500);
        }
    }

    public function correctTable(Request $request, $tableId)
    {
        try {
            $validated = $request->validate(['notes' => 'nullable|string|max:500']);
            $votingTable = VotingTable::findOrFail($tableId);

            if (!$this->canAccessTable($votingTable, 'correct_votes')) {
                return response()->json(['success' => false, 'message' => 'No tiene permiso'], 403);
            }

            DB::beginTransaction();

            $votingTable->status = 'en_escrutinio';
            $votingTable->corrected_by = Auth::id();
            $votingTable->corrected_at = now();
            $votingTable->correction_notes = $validated['notes'] ?? null;
            $votingTable->save();

            Vote::where('voting_table_id', $tableId)
                ->where('vote_status', 'observed')
                ->update([
                    'vote_status' => 'corrected',
                    'corrected_by' => Auth::id(),
                    'corrected_at' => now(),
                ]);

            Observation::where('voting_table_id', $tableId)
                ->where('status', 'pending')
                ->update([
                    'status' => 'resolved',
                    'resolved_by' => Auth::id(),
                    'resolved_at' => now(),
                    'resolution_notes' => $validated['notes'] ?? 'Corregido',
                ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Mesa corregida correctamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error correcting table: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al corregir'], 500);
        }
    }

    public function getTableVotes($tableId)
    {
        try {
            $votes = Vote::where('voting_table_id', $tableId)
                ->with('candidate:id,name,party,color,type')
                ->get()
                ->map(function ($vote) {
                    return [
                        'candidate_id' => $vote->candidate_id,
                        'candidate_name' => $vote->candidate->name,
                        'candidate_party' => $vote->candidate->party,
                        'candidate_color' => $vote->candidate->color,
                        'candidate_type' => $vote->candidate->type,
                        'quantity' => $vote->quantity,
                        'vote_status' => $vote->vote_status,
                    ];
                });

            return response()->json($votes);

        } catch (\Exception $e) {
            Log::error('Error getting table votes: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar votos'], 500);
        }
    }

    public function closeTable($tableId)
    {
        try {
            DB::beginTransaction();

            $votingTable = VotingTable::findOrFail($tableId);

            if (in_array($votingTable->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                throw new \Exception('La mesa ya está cerrada o finalizada');
            }

            $votingTable->status = 'cerrada';
            $votingTable->closing_time = now();
            $votingTable->closed_by = Auth::id();
            $votingTable->closed_at = now();
            $votingTable->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Mesa cerrada correctamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error closing table: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cerrar'], 500);
        }
    }

    public function reopenTable($tableId)
    {
        try {
            DB::beginTransaction();

            $votingTable = VotingTable::findOrFail($tableId);

            if (!in_array($votingTable->status, ['cerrada', 'observada'])) {
                throw new \Exception('Solo se pueden reabrir mesas cerradas u observadas');
            }

            $votingTable->status = 'en_escrutinio';
            $votingTable->reopened_by = Auth::id();
            $votingTable->reopened_at = now();
            $votingTable->reopen_count = ($votingTable->reopen_count ?? 0) + 1;
            $votingTable->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Mesa reabierta correctamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reopening table: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al reabrir'], 500);
        }
    }

    public function getTableStats($tableId)
    {
        try {
            $votingTable = VotingTable::with([
                'institution:id,name,code',
                'votes.candidate',
                'observations'
            ])->findOrFail($tableId);

            $stats = [
                'table' => [
                    'id' => $votingTable->id,
                    'number' => $votingTable->number,
                    'code' => $votingTable->internal_code,
                    'oep_code' => $votingTable->oep_code,
                    'status' => $votingTable->status,
                    'institution' => $votingTable->institution->name,
                ],
                'voters' => [
                    'expected' => $votingTable->expected_voters,
                    'total' => $votingTable->total_voters,
                    'participation' => $votingTable->expected_voters > 0
                        ? round(($votingTable->total_voters / $votingTable->expected_voters) * 100, 1)
                        : 0,
                ],
                'ballots' => [
                    'received' => $votingTable->ballots_received,
                    'used' => $votingTable->ballots_used,
                    'leftover' => $votingTable->ballots_leftover,
                    'spoiled' => $votingTable->ballots_spoiled,
                ],
                'observations_count' => $votingTable->observations->count(),
                'votes_count' => $votingTable->votes->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error getting table stats: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar estadísticas'], 500);
        }
    }
}
