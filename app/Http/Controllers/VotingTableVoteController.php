<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Candidate;
use App\Models\VotingTable;
use App\Models\VotingTableElection;
use App\Models\VotingTableCategoryResult;
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
        $this->middleware('permission:upload_acta')->only(['uploadActa']);
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

            $electionType = ElectionType::with('typeCategories.electionCategory')->find($electionTypeId);

            $categories = collect();
            $candidatesByCategory = [];
            $typeCategoryIds = [];

            if ($electionType) {
                // Get all type categories for this election
                $typeCategories = ElectionTypeCategory::where('election_type_id', $electionTypeId)
                    ->with('electionCategory')
                    ->orderBy('ballot_order')
                    ->get();

                foreach ($typeCategories as $typeCategory) {
                    $categoryCode = $typeCategory->electionCategory->code;
                    $typeCategoryIds[] = $typeCategory->id;

                    $candidatesByCategory[$categoryCode] = Candidate::where('election_type_category_id', $typeCategory->id)
                        ->where('active', true)
                        ->orderBy('list_order')
                        ->orderBy('name')
                        ->get();

                    $categories->push($typeCategory->electionCategory);
                }

                Log::info("Categorías para elección {$electionTypeId}:", [
                    'count' => $typeCategories->count(),
                    'candidates_by_category' => collect($candidatesByCategory)->map->count()
                ]);
            }

            // Institutions filter
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

            // Voting tables query
            $query = VotingTable::with([
                'institution:id,name,code',
                'tableElections' => function($q) use ($electionTypeId) {
                    $q->where('election_type_id', $electionTypeId);
                },
                'categoryResults' => function($q) use ($typeCategoryIds) {
                    $q->whereIn('election_type_category_id', $typeCategoryIds)
                      ->with('electionTypeCategory.electionCategory');
                },
                'observations' => function($q) {
                    $q->where('status', 'pending');
                },
                'president:id,name,last_name',
                'secretary:id,name,last_name',
                'vocal1:id,name,last_name',
                'vocal2:id,name,last_name',
                'vocal3:id,name,last_name',
                'vocal4:id,name,last_name',
            ])->withCount(['observations as observations_count' => function($q) {
                $q->where('status', 'pending');
            }]);

            $query = $this->applyScopeFilter($query);

            if ($institutionId) {
                $query->where('institution_id', $institutionId);
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

            // Apply status filter on table elections
            if ($status) {
                $query->whereHas('tableElections', function($q) use ($electionTypeId, $status) {
                    $q->where('election_type_id', $electionTypeId)
                      ->where('status', $status);
                });
            }

            // Apply min/max votes filters
            if ($minVotes) {
                $query->whereHas('tableElections', function($q) use ($electionTypeId, $minVotes) {
                    $q->where('election_type_id', $electionTypeId)
                      ->where('total_voters', '>=', $minVotes);
                });
            }

            if ($maxVotes) {
                $query->whereHas('tableElections', function($q) use ($electionTypeId, $maxVotes) {
                    $q->where('election_type_id', $electionTypeId)
                      ->where('total_voters', '<=', $maxVotes);
                });
            }

            if ($hasObservations === 'true') {
                $query->has('observations');
            } elseif ($hasObservations === 'false') {
                $query->doesntHave('observations');
            }

            // Sorting
            switch ($sortBy) {
                case 'number':
                    $query->orderBy('number', $sortDirection);
                    break;
                case 'expected_voters':
                    $query->orderBy('expected_voters', $sortDirection);
                    break;
                default:
                    $query->orderBy('institution_id')->orderBy('number', 'asc');
            }

            $votingTables = $query->paginate(10)->withQueryString();

            // Enhance tables with election data
            $votingTables->getCollection()->transform(function($table) use ($electionTypeId, $typeCategoryIds) {
                $tableElection = $table->tableElections->first();

                $table->current_status = $tableElection ? $tableElection->status : 'sin_configurar';
                $table->total_voters = $tableElection ? $tableElection->total_voters : 0;
                $table->ballots_received = $tableElection ? $tableElection->ballots_received : 0;
                $table->ballots_used = $tableElection ? $tableElection->ballots_used : 0;
                $table->ballots_leftover = $tableElection ? $tableElection->ballots_leftover : 0;
                $table->ballots_spoiled = $tableElection ? $tableElection->ballots_spoiled : 0;

                // Group results by category
                $resultsByCategory = [];
                foreach ($table->categoryResults as $result) {
                    $categoryCode = $result->electionTypeCategory->electionCategory->code;
                    $resultsByCategory[$categoryCode] = [
                        'valid_votes' => $result->valid_votes,
                        'blank_votes' => $result->blank_votes,
                        'null_votes' => $result->null_votes,
                        'total_votes' => $result->total_votes,
                        'is_consistent' => $result->is_consistent,
                    ];
                }
                $table->results_by_category = $resultsByCategory;

                return $table;
            });

            // Calculate totals
            $totals = $this->calculateTotals($votingTables);
            $totals['participation'] = $totals['expected'] > 0 ? round(($totals['total'] / $totals['expected']) * 100, 1) : 0;

            // Table statistics by status
            $tableStats = [
                'total' => $votingTables->total(),
                'configurada' => $this->countTablesByStatus($votingTables, 'configurada'),
                'en_espera' => $this->countTablesByStatus($votingTables, 'en_espera'),
                'votacion' => $this->countTablesByStatus($votingTables, 'votacion'),
                'en_escrutinio' => $this->countTablesByStatus($votingTables, 'en_escrutinio'),
                'cerrada' => $this->countTablesByStatus($votingTables, 'cerrada'),
                'observada' => $this->countTablesByStatus($votingTables, 'observada'),
                'escrutada' => $this->countTablesByStatus($votingTables, 'escrutada'),
                'transmitida' => $this->countTablesByStatus($votingTables, 'transmitida'),
                'anulada' => $this->countTablesByStatus($votingTables, 'anulada'),
            ];

            $statusLabels = VotingTableElection::getStatuses();

            $validationLabels = Vote::getVoteStatuses();

            $permissions = [
                'can_register' => $this->userHasPermission($user, 'register_votes'),
                'can_observe' => $this->userHasPermission($user, 'observe_votes'),
                'can_validate' => $this->userHasPermission($user, 'validate_votes'),
                'can_correct' => $this->userHasPermission($user, 'correct_votes'),
                'can_close' => $this->userHasPermission($user, 'close_table'),
                'can_reopen' => $this->userHasPermission($user, 'reopen_table'),
                'can_view' => $this->userHasPermission($user, 'view_votes'),
                'can_upload_acta' => $this->userHasPermission($user, 'upload_acta'),
                'can_review' => $this->userHasPermission($user, 'review_votes'),
            ];

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

    private function countTablesByStatus($votingTables, $status)
    {
        return $votingTables->filter(function($table) use ($status) {
            return $table->current_status === $status;
        })->count();
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

            DB::beginTransaction();

            try {
                $votingTable = VotingTable::lockForUpdate()->find($votingTableId);
                if (!$votingTable) {
                    throw new \Exception('Mesa no encontrada');
                }

                // Get or create table election record
                $tableElection = VotingTableElection::firstOrCreate(
                    [
                        'voting_table_id' => $votingTable->id,
                        'election_type_id' => $electionTypeId,
                    ],
                    [
                        'ballots_received' => 0,
                        'ballots_used' => 0,
                        'ballots_leftover' => 0,
                        'ballots_spoiled' => 0,
                        'total_voters' => 0,
                        'status' => 'configurada',
                        'election_date' => now(),
                    ]
                );

                Log::info('✅ Mesa encontrada', [
                    'id' => $votingTable->id,
                    'status' => $tableElection->status,
                    'expected_voters' => $votingTable->expected_voters
                ]);

                if (!$this->canAccessTable($votingTable, 'register_votes')) {
                    throw new \Exception('No tiene permisos para registrar votos en esta mesa');
                }

                if (in_array($tableElection->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                    throw new \Exception('No se pueden modificar votos de una mesa ' . $tableElection->status);
                }

                // Get all type categories for this election
                $typeCategories = ElectionTypeCategory::where('election_type_id', $electionTypeId)
                    ->with('electionCategory')
                    ->get();

                Log::info('✅ Categorías encontradas', ['count' => $typeCategories->count()]);

                // Get valid candidates
                $candidateIds = array_keys($votesData);
                Log::info('Buscando candidatos con IDs:', $candidateIds);

                $candidates = Candidate::whereIn('id', $candidateIds)
                    ->where('active', true)
                    ->with('electionTypeCategory.electionCategory')
                    ->get()
                    ->keyBy('id');

                Log::info('✅ Candidatos encontrados', ['count' => $candidates->count()]);

                // Verify all candidates are valid
                $invalidCandidates = [];
                foreach ($candidateIds as $candidateId) {
                    if (!isset($candidates[$candidateId])) {
                        $invalidCandidates[] = $candidateId;
                    }
                }

                if (!empty($invalidCandidates)) {
                    throw new \Exception("Candidatos no válidos: " . implode(', ', $invalidCandidates));
                }

                // Group votes by category
                $categoryTotals = [];
                foreach ($typeCategories as $typeCategory) {
                    $categoryCode = $typeCategory->electionCategory->code;
                    $categoryTotals[$categoryCode] = [
                        'total' => 0,
                        'blank' => 0,
                        'null' => 0,
                    ];
                }

                // Get or create category result records
                $categoryResults = [];
                foreach ($typeCategories as $typeCategory) {
                    $result = VotingTableCategoryResult::firstOrCreate(
                        [
                            'voting_table_id' => $votingTable->id,
                            'election_type_category_id' => $typeCategory->id,
                        ],
                        [
                            'valid_votes' => 0,
                            'blank_votes' => 0,
                            'null_votes' => 0,
                            'total_votes' => 0,
                            'status' => 'pending',
                        ]
                    );
                    $categoryResults[$typeCategory->electionCategory->code] = $result;
                }

                Log::info('Procesando votos individuales...');

                // Save votes
                foreach ($votesData as $candidateId => $quantity) {
                    $quantity = intval($quantity);
                    $candidate = $candidates[$candidateId];
                    $categoryCode = $candidate->electionTypeCategory->electionCategory->code;

                    Log::info("Procesando candidato {$candidateId}: {$quantity} votos (categoría {$categoryCode})");

                    if ($quantity > 0) {
                        // Save or update vote
                        $vote = Vote::updateOrCreate(
                            [
                                'voting_table_id' => $votingTable->id,
                                'candidate_id' => $candidateId,
                                'election_type_id' => $electionTypeId,
                                'election_type_category_id' => $candidate->election_type_category_id,
                            ],
                            [
                                'quantity' => $quantity,
                                'user_id' => $user->id,
                                'registered_at' => now(),
                                'vote_status' => Vote::VOTE_STATUS_PENDING_REVIEW,
                            ]
                        );

                        // Update category total
                        $categoryTotals[$categoryCode]['total'] += $quantity;

                        Log::info("✅ Voto guardado/actualizado", [
                            'vote_id' => $vote->id,
                            'candidate_id' => $candidateId,
                            'quantity' => $quantity
                        ]);
                    } else {
                        // Delete vote if quantity is 0
                        $deleted = Vote::where('voting_table_id', $votingTable->id)
                            ->where('candidate_id', $candidateId)
                            ->where('election_type_id', $electionTypeId)
                            ->delete();

                        Log::info("🗑️ Votos eliminados", ['candidate_id' => $candidateId, 'deleted' => $deleted]);
                    }
                }

                // Update category results
                $totalVoters = 0;
                foreach ($typeCategories as $typeCategory) {
                    $categoryCode = $typeCategory->electionCategory->code;
                    $result = $categoryResults[$categoryCode];

                    // Get blank and null votes from the category result
                    $blankVotes = $result->blank_votes; // These would come from UI or be set separately
                    $nullVotes = $result->null_votes;

                    $result->valid_votes = $categoryTotals[$categoryCode]['total'];
                    $result->total_votes = $categoryTotals[$categoryCode]['total'] + $blankVotes + $nullVotes;
                    $result->checkConsistency(); // This will validate and save
                }

                // All categories should have the same total voters
                $categoryTotalsList = array_column($categoryTotals, 'total');
                $firstTotal = !empty($categoryTotalsList) ? $categoryTotalsList[0] : 0;
                $totalVoters = $firstTotal;

                Log::info('✅ Votos procesados. Totales por categoría:', $categoryTotals);

                // Validate that all categories have the same total
                $inconsistencies = [];
                foreach ($categoryTotals as $code => $data) {
                    if ($data['total'] != $firstTotal) {
                        $inconsistencies[] = "$code: {$data['total']} votos (debería ser $firstTotal)";
                    }
                }

                if (!empty($inconsistencies)) {
                    throw new \Exception(
                        "Inconsistencia en el número de votantes por categoría:\n" .
                        implode("\n", $inconsistencies) .
                        "\n\nTodas las categorías deben tener el MISMO número de votantes."
                    );
                }

                // Validate against expected voters
                if ($votingTable->expected_voters && $firstTotal > $votingTable->expected_voters) {
                    throw new \Exception(
                        "Los votos registrados ({$firstTotal}) exceden " .
                        "los votantes habilitados ({$votingTable->expected_voters})"
                    );
                }

                // Update table election
                $tableElection->total_voters = $firstTotal;
                $tableElection->ballots_used = $firstTotal;

                if (in_array($tableElection->status, ['configurada', 'en_espera'])) {
                    $tableElection->status = 'votacion';
                }

                if ($closeTable) {
                    if (!$this->canAccessTable($votingTable, 'close_table')) {
                        throw new \Exception('No tiene permiso para cerrar esta mesa');
                    }

                    // Validate all category results are consistent
                    $allConsistent = true;
                    foreach ($categoryResults as $result) {
                        if (!$result->fresh()->is_consistent) {
                            $allConsistent = false;
                            break;
                        }
                    }

                    if (!$allConsistent) {
                        throw new \Exception('No se puede cerrar la mesa: hay categorías inconsistentes');
                    }

                    $tableElection->status = 'cerrada';
                    $tableElection->closing_time = now()->format('H:i:s');
                }

                $tableElection->save();
                DB::commit();

                // Verify votes were saved
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
                    'table_status' => $tableElection->status,
                    'category_totals' => $categoryTotals,
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

                        $tableElection = VotingTableElection::firstOrCreate(
                            [
                                'voting_table_id' => $votingTable->id,
                                'election_type_id' => $electionTypeId,
                            ]
                        );

                        if (!$this->canAccessTable($votingTable, 'register_votes')) {
                            $errors[] = "Mesa {$votingTable->internal_code}: Sin permiso";
                            continue;
                        }

                        if (in_array($tableElection->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                            continue;
                        }

                        // Process votes individually
                        foreach ($votesData as $candidateId => $quantity) {
                            $candidate = Candidate::find($candidateId);
                            if (!$candidate) continue;

                            if ($quantity > 0) {
                                Vote::updateOrCreate(
                                    [
                                        'voting_table_id' => $votingTable->id,
                                        'candidate_id' => $candidateId,
                                        'election_type_id' => $electionTypeId,
                                        'election_type_category_id' => $candidate->election_type_category_id,
                                    ],
                                    [
                                        'quantity' => $quantity,
                                        'user_id' => $user->id,
                                        'registered_at' => now(),
                                        'vote_status' => Vote::VOTE_STATUS_PENDING_REVIEW,
                                    ]
                                );
                            } else {
                                Vote::where('voting_table_id', $votingTable->id)
                                    ->where('candidate_id', $candidateId)
                                    ->where('election_type_id', $electionTypeId)
                                    ->delete();
                            }
                        }

                        // Update category results
                        $typeCategories = ElectionTypeCategory::where('election_type_id', $electionTypeId)->get();
                        foreach ($typeCategories as $typeCategory) {
                            $result = VotingTableCategoryResult::firstOrCreate(
                                [
                                    'voting_table_id' => $votingTable->id,
                                    'election_type_category_id' => $typeCategory->id,
                                ]
                            );
                            $result->checkConsistency();
                        }

                        if ($closeAll && !in_array($tableElection->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                            $tableElection->status = 'cerrada';
                            $tableElection->closing_time = now()->format('H:i:s');
                        }

                        $tableElection->save();
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
            $electionTypeId = $request->input('election_type_id');

            if (!$electionTypeId) {
                return response()->json(['success' => false, 'message' => 'Se requiere tipo de elección'], 422);
            }

            $tableElection = VotingTableElection::where('voting_table_id', $tableId)
                ->where('election_type_id', $electionTypeId)
                ->firstOrFail();

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
                'election_type_id' => $electionTypeId,
                'reviewed_by' => Auth::id(),
                'reviewer_role' => 'revisor',
            ]);

            $tableElection->status = 'observada';
            $tableElection->save();

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
                'election_type_id' => 'required|exists:election_types,id',
            ]);

            $votingTable = VotingTable::findOrFail($tableId);
            $tableElection = VotingTableElection::where('voting_table_id', $tableId)
                ->where('election_type_id', $validated['election_type_id'])
                ->firstOrFail();

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
                'validate' => Vote::VOTE_STATUS_VALIDATED,
                'approve' => Vote::VOTE_STATUS_APPROVED,
                'reject' => Vote::VOTE_STATUS_REJECTED,
                default => Vote::VOTE_STATUS_VALIDATED
            };

            $tableElection->status = $tableStatus;
            $tableElection->save();

            Vote::where('voting_table_id', $tableId)
                ->where('election_type_id', $validated['election_type_id'])
                ->update([
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
            $validated = $request->validate([
                'notes' => 'nullable|string|max:500',
                'election_type_id' => 'required|exists:election_types,id',
            ]);

            $votingTable = VotingTable::findOrFail($tableId);
            $tableElection = VotingTableElection::where('voting_table_id', $tableId)
                ->where('election_type_id', $validated['election_type_id'])
                ->firstOrFail();

            if (!$this->canAccessTable($votingTable, 'correct_votes')) {
                return response()->json(['success' => false, 'message' => 'No tiene permiso'], 403);
            }

            DB::beginTransaction();

            $tableElection->status = 'en_escrutinio';
            $tableElection->save();

            Vote::where('voting_table_id', $tableId)
                ->where('election_type_id', $validated['election_type_id'])
                ->where('vote_status', Vote::VOTE_STATUS_OBSERVED)
                ->update([
                    'vote_status' => Vote::VOTE_STATUS_CORRECTED,
                    'corrected_by' => Auth::id(),
                    'corrected_at' => now(),
                ]);

            Observation::where('voting_table_id', $tableId)
                ->where('election_type_id', $validated['election_type_id'])
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

    public function getTableVotes(Request $request, $tableId)
    {
        try {
            $electionTypeId = $request->query('election_type_id');

            $query = Vote::where('voting_table_id', $tableId)
                ->with(['candidate:id,name,party,color,party_logo,election_type_category_id']);

            if ($electionTypeId) {
                $query->where('election_type_id', $electionTypeId);
            }

            $votes = $query->get()
                ->map(function ($vote) {
                    return [
                        'candidate_id' => $vote->candidate_id,
                        'candidate_name' => $vote->candidate->name,
                        'candidate_party' => $vote->candidate->party,
                        'candidate_color' => $vote->candidate->color,
                        'candidate_logo' => $vote->candidate->party_logo_url,
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

    public function closeTable(Request $request, $tableId)
    {
        try {
            $electionTypeId = $request->input('election_type_id');

            if (!$electionTypeId) {
                return response()->json(['success' => false, 'message' => 'Se requiere tipo de elección'], 422);
            }

            DB::beginTransaction();

            $tableElection = VotingTableElection::where('voting_table_id', $tableId)
                ->where('election_type_id', $electionTypeId)
                ->firstOrFail();

            if (in_array($tableElection->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                throw new \Exception('La mesa ya está cerrada o finalizada');
            }

            // Check all category results are consistent
            $inconsistentResults = VotingTableCategoryResult::where('voting_table_id', $tableId)
                ->whereHas('electionTypeCategory', function($q) use ($electionTypeId) {
                    $q->where('election_type_id', $electionTypeId);
                })
                ->where('is_consistent', false)
                ->exists();

            if ($inconsistentResults) {
                throw new \Exception('No se puede cerrar la mesa: hay categorías inconsistentes');
            }

            $tableElection->status = 'cerrada';
            $tableElection->closing_time = now()->format('H:i:s');
            $tableElection->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Mesa cerrada correctamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error closing table: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cerrar: ' . $e->getMessage()], 500);
        }
    }

    public function reopenTable(Request $request, $tableId)
    {
        try {
            $electionTypeId = $request->input('election_type_id');

            if (!$electionTypeId) {
                return response()->json(['success' => false, 'message' => 'Se requiere tipo de elección'], 422);
            }

            DB::beginTransaction();

            $tableElection = VotingTableElection::where('voting_table_id', $tableId)
                ->where('election_type_id', $electionTypeId)
                ->firstOrFail();

            if (!in_array($tableElection->status, ['cerrada', 'observada'])) {
                throw new \Exception('Solo se pueden reabrir mesas cerradas u observadas');
            }

            $tableElection->status = 'en_escrutinio';
            $tableElection->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Mesa reabierta correctamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reopening table: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al reabrir'], 500);
        }
    }

    public function getTableStats(Request $request, $tableId)
    {
        try {
            $electionTypeId = $request->query('election_type_id');

            $votingTable = VotingTable::with([
                'institution:id,name,code',
                'tableElections' => function($q) use ($electionTypeId) {
                    if ($electionTypeId) {
                        $q->where('election_type_id', $electionTypeId);
                    }
                },
                'categoryResults.electionTypeCategory.electionCategory',
                'observations' => function($q) {
                    $q->where('status', 'pending');
                }
            ])->findOrFail($tableId);

            $tableElection = $votingTable->tableElections->first();

            $stats = [
                'table' => [
                    'id' => $votingTable->id,
                    'number' => $votingTable->number,
                    'code' => $votingTable->internal_code,
                    'oep_code' => $votingTable->oep_code,
                    'status' => $tableElection ? $tableElection->status : 'sin_configurar',
                    'institution' => $votingTable->institution->name,
                ],
                'voters' => [
                    'expected' => $votingTable->expected_voters,
                    'total' => $tableElection ? $tableElection->total_voters : 0,
                    'participation' => $votingTable->expected_voters > 0 && $tableElection
                        ? round(($tableElection->total_voters / $votingTable->expected_voters) * 100, 1)
                        : 0,
                ],
                'ballots' => [
                    'received' => $tableElection ? $tableElection->ballots_received : 0,
                    'used' => $tableElection ? $tableElection->ballots_used : 0,
                    'leftover' => $tableElection ? $tableElection->ballots_leftover : 0,
                    'spoiled' => $tableElection ? $tableElection->ballots_spoiled : 0,
                ],
                'observations_count' => $votingTable->observations->count(),
                'category_results' => $votingTable->categoryResults->map(function($result) {
                    return [
                        'category' => $result->electionTypeCategory->electionCategory->name,
                        'code' => $result->electionTypeCategory->electionCategory->code,
                        'valid_votes' => $result->valid_votes,
                        'blank_votes' => $result->blank_votes,
                        'null_votes' => $result->null_votes,
                        'total_votes' => $result->total_votes,
                        'is_consistent' => $result->is_consistent,
                    ];
                }),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error getting table stats: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar estadísticas'], 500);
        }
    }

    public function uploadActa(Request $request)
    {
        try {
            $validated = $request->validate([
                'voting_table_id' => 'required|exists:voting_tables,id',
                'election_type_id' => 'required|exists:election_types,id',
                'acta_number' => 'required|string|max:50',
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
                'pdf' => 'nullable|mimes:pdf|max:10240',
                'has_physical' => 'nullable|in:on,off,true,false,1,0',
            ]);

            DB::beginTransaction();

            $votingTable = VotingTable::findOrFail($validated['voting_table_id']);
            $tableElection = VotingTableElection::where('voting_table_id', $validated['voting_table_id'])
                ->where('election_type_id', $validated['election_type_id'])
                ->firstOrFail();

            $actaController = new ActaController();
            return $actaController->upload($request);

        } catch (\Exception $e) {
            Log::error('Error uploading acta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al subir el acta'], 500);
        }
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
        }

        return $totals;
    }
}
