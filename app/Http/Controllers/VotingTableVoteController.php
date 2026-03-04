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

        // Permisos para vistas y acciones - usando nombres de permisos de tu seeder
        $this->middleware('permission:view_votes')->only(['index', 'getTableVotes', 'getTableStats']);
        $this->middleware('permission:register_votes')->only(['registerVotes', 'registerAllVotes']);
        $this->middleware('permission:observe_votes')->only(['observeTable']);
        $this->middleware('permission:validate_votes')->only(['validateTable']);
        $this->middleware('permission:correct_votes')->only(['correctTable']);
        $this->middleware('permission:close_table')->only(['closeTable']);
        $this->middleware('permission:reopen_table')->only(['reopenTable']);
    }

    /**
     * Verificar si el usuario tiene acceso a una mesa específica según su ámbito
     */
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
            $minVotes = $request->input('min_votes');
            $maxVotes = $request->input('max_votes');
            $participation = $request->input('participation');
            $hasObservations = $request->input('has_observations');
            $validationStatus = $request->input('validation_status');

            // Si no hay tipo de elección, obtener el activo por defecto
            if (!$electionTypeId) {
                $defaultElectionType = ElectionType::where('active', true)->first();
                $electionTypeId = $defaultElectionType?->id;
            }

            // Obtener el tipo de elección actual
            $electionType = ElectionType::find($electionTypeId);

            // Obtener datos para filtros
            $institutionsQuery = Institution::where('status', 'activo');

            // Aplicar filtro de ámbito a instituciones
            $assignedInstitutionIds = $user->roles()
                ->wherePivot('scope', 'institution')
                ->wherePivot('institution_id', '!=', null)
                ->get()
                ->pluck('pivot.institution_id')
                ->toArray();

            if (!empty($assignedInstitutionIds)) {
                $institutionsQuery->whereIn('id', $assignedInstitutionIds);
            }

            $institutions = $institutionsQuery->orderBy('name')
                ->get(['id', 'name', 'code']);

            $electionTypes = ElectionType::where('active', true)
                ->orderBy('election_date', 'desc')
                ->get(['id', 'name', 'election_date']);

            // Obtener categorías electorales
            $alcaldeCategory = ElectionCategory::where('code', 'ALC')->first();
            $concejalCategory = ElectionCategory::where('code', 'CON')->first();

            // Obtener los election_type_category_ids para este tipo de elección
            $alcaldeTypeCategoryIds = [];
            $concejalTypeCategoryIds = [];

            if ($alcaldeCategory) {
                $alcaldeTypeCategoryIds = ElectionTypeCategory::where('election_type_id', $electionTypeId)
                    ->where('election_category_id', $alcaldeCategory->id)
                    ->pluck('id')
                    ->toArray();
            }

            if ($concejalCategory) {
                $concejalTypeCategoryIds = ElectionTypeCategory::where('election_type_id', $electionTypeId)
                    ->where('election_category_id', $concejalCategory->id)
                    ->pluck('id')
                    ->toArray();
            }

            // Construir query de mesas con relaciones
            $query = VotingTable::with([
                'institution:id,name,code',
                'votes' => function($q) use ($electionTypeId) {
                    $q->where('election_type_id', $electionTypeId)
                    ->with('candidate:id,name,party,color,election_type_category_id');
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

            // Aplicar filtro de ámbito del usuario
            $query = $this->applyScopeFilter($query);

            // Aplicar filtros adicionales
            if ($institutionId) {
                $query->where('institution_id', $institutionId);
            }

            if ($status) {
                $query->where('status', $status);
            } else {
                $query->whereIn('status', ['configurada', 'en_espera', 'votacion', 'en_escrutinio']);
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

            $votingTables = $query->paginate(10)->withQueryString();

            // Obtener candidatos
            $candidates = Candidate::with(['electionTypeCategory.electionCategory', 'electionTypeCategory.electionType'])
                ->whereHas('electionTypeCategory', function($q) use ($electionTypeId) {
                    $q->where('election_type_id', $electionTypeId);
                })
                ->where('active', true)
                ->orderBy('list_order')
                ->orderBy('name')
                ->get();

            // Separar candidatos por categoría
            $candidatesByCategory = [
                'alcalde' => collect(),
                'concejal' => collect(),
            ];

            foreach ($candidates as $candidate) {
                $categoryCode = $candidate->electionTypeCategory?->electionCategory?->code;
                if ($categoryCode === 'ALC') {
                    $candidatesByCategory['alcalde']->push($candidate);
                } elseif ($categoryCode === 'CON') {
                    $candidatesByCategory['concejal']->push($candidate);
                }
            }

            // Calcular totales
            $totals = $this->calculateTotals($votingTables);

            // Calcular participación total
            $totals['participation'] = $totals['expected'] > 0
                ? round(($totals['total'] / $totals['expected']) * 100, 1)
                : 0;

            // Contar mesas por estado
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

            // Labels para los estados
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

            // Labels para validación
            $validationLabels = [
                'pending' => 'Pendiente',
                'reviewed' => 'Revisado',
                'observed' => 'Observado',
                'corrected' => 'Corregido',
                'validated' => 'Validado',
                'approved' => 'Aprobado',
                'rejected' => 'Rechazado'
            ];

            // Determinar permisos del usuario para acciones
            $permissions = [
                'can_register' => $this->userHasPermission($user, 'register_votes'),
                'can_observe' => $this->userHasPermission($user, 'observe_votes'),
                'can_validate' => $this->userHasPermission($user, 'validate_votes'),
                'can_correct' => $this->userHasPermission($user, 'correct_votes'),
                'can_close' => $this->userHasPermission($user, 'close_table'),
                'can_reopen' => $this->userHasPermission($user, 'reopen_table'),
                'can_view' => $this->userHasPermission($user, 'view_votes'),
            ];

            return view('voting-table-votes.index', compact(
                'votingTables',
                'candidates',
                'candidatesByCategory',
                'institutions',
                'electionTypes',
                'electionType',       // ← AGREGADO
                'institutionId',
                'electionTypeId',
                'totals',
                'tableStats',
                'permissions',
                'statusLabels',       // ← AGREGADO
                'validationLabels',    // ← AGREGADO
                'request'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading voting table votes: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Error al cargar los datos de votación: ' . $e->getMessage());
        }
    }

    public function observeTable(Request $request, $tableId)
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $votingTable = VotingTable::findOrFail($tableId);

            // Verificar permisos
            if (!$this->canAccessTable($votingTable, 'observe_votes')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para observar esta mesa'
                ], 403);
            }

            DB::beginTransaction();

            // Crear observación
            $observation = Observation::create([
                'code' => Observation::generateCode(),
                'type' => 'inconsistencia_acta',
                'description' => $validated['notes'] ?? 'Mesa observada durante revisión',
                'severity' => 'warning',
                'status' => 'pending',
                'voting_table_id' => $votingTable->id,
                'election_type_id' => $votingTable->election_type_id,
                'reviewed_by' => Auth::id(),
                'reviewer_role' => 'revisor',
            ]);

            // Marcar mesa como observada
            $votingTable->status = 'observada';
            $votingTable->verified_by = Auth::id();
            $votingTable->verified_at = now();
            $votingTable->verification_notes = $validated['notes'] ?? null;
            $votingTable->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mesa observada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error observing table: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al observar la mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra los votos para una mesa específica
     */
    public function registerVotes(Request $request)
    {
        Log::info('========== REGISTRAR VOTOS ==========');

        try {
            $validated = $request->validate([
                'voting_table_id' => 'required|integer|exists:voting_tables,id',
                'election_type_id' => 'required|integer|exists:election_types,id',
                'votes' => 'required|array',
                'votes.*' => 'integer|min:0',
                'close' => 'boolean'
            ]);

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

                // Verificar permisos
                if (!$this->canAccessTable($votingTable, 'register_votes')) {
                    throw new \Exception('No tiene permisos para registrar votos en esta mesa');
                }

                if (in_array($votingTable->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                    throw new \Exception('No se pueden modificar votos de una mesa ' . $votingTable->status);
                }

                // Obtener todos los candidatos válidos para esta elección
                $candidateIds = array_keys($votesData);
                $candidates = Candidate::whereIn('id', $candidateIds)
                    ->whereHas('electionTypeCategory', function($q) use ($electionTypeId) {
                        $q->where('election_type_id', $electionTypeId);
                    })
                    ->where('active', true)
                    ->get()
                    ->keyBy('id');

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

                // Obtener IDs de categorías
                $alcaldeCategory = ElectionCategory::where('code', 'ALC')->first();
                $concejalCategory = ElectionCategory::where('code', 'CON')->first();

                $alcaldeTypeCategoryIds = $alcaldeCategory
                    ? ElectionTypeCategory::where('election_type_id', $electionTypeId)
                        ->where('election_category_id', $alcaldeCategory->id)
                        ->pluck('id')
                        ->toArray()
                    : [];

                $concejalTypeCategoryIds = $concejalCategory
                    ? ElectionTypeCategory::where('election_type_id', $electionTypeId)
                        ->where('election_category_id', $concejalCategory->id)
                        ->pluck('id')
                        ->toArray()
                    : [];

                // Calcular totales por separado
                $totalVotosAlcalde = 0;
                $totalVotosConcejal = 0;

                // Guardar o actualizar cada voto
                foreach ($votesData as $candidateId => $quantity) {
                    $quantity = intval($quantity);
                    $candidate = $candidates[$candidateId];

                    if ($quantity > 0) {
                        // Clasificar el voto para totales
                        if (in_array($candidate->election_type_category_id, $alcaldeTypeCategoryIds)) {
                            $totalVotosAlcalde += $quantity;
                        } elseif (in_array($candidate->election_type_category_id, $concejalTypeCategoryIds)) {
                            $totalVotosConcejal += $quantity;
                        }

                        // 🔴 CORREGIDO: Usar 'pending_review' en lugar de 'verified'
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
                                'vote_status' => 'pending_review',  // ← VALOR CORRECTO
                            ]
                        );
                    } else {
                        Vote::where('voting_table_id', $votingTable->id)
                            ->where('candidate_id', $candidateId)
                            ->where('election_type_id', $electionTypeId)
                            ->delete();
                    }
                }

                // Validación: Los votantes deben ser los mismos en ambas categorías
                if ($totalVotosAlcalde != $totalVotosConcejal) {
                    throw new \Exception(
                        "El número de votantes debe ser el mismo en ambas categorías.\n" .
                        "Alcaldes: {$totalVotosAlcalde} votantes\n" .
                        "Concejales: {$totalVotosConcejal} votantes"
                    );
                }

                // Validar contra votantes esperados
                if ($votingTable->expected_voters && $totalVotosAlcalde > $votingTable->expected_voters) {
                    throw new \Exception(
                        "Los votos registrados ({$totalVotosAlcalde}) exceden " .
                        "los votantes habilitados ({$votingTable->expected_voters})"
                    );
                }

                // Actualizar totales en la tabla
                $this->updateVotingTableTotals($votingTable, $electionTypeId);

                // Actualizar estado de la mesa
                if (in_array($votingTable->status, ['configurada', 'en_espera'])) {
                    $votingTable->status = 'votacion';
                }

                if ($closeTable) {
                    // Verificar permiso para cerrar
                    if (!$this->canAccessTable($votingTable, 'close_table')) {
                        throw new \Exception('No tiene permiso para cerrar esta mesa');
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
                    ->count();

                Log::info('Votos guardados exitosamente', [
                    'table_id' => $votingTable->id,
                    'total_votantes' => $totalVotosAlcalde,
                    'votos_guardados' => $savedVotes
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $closeTable ? '✅ Votos registrados y mesa cerrada exitosamente' : '✅ Votos registrados exitosamente',
                    'table_status' => $votingTable->status,
                    'totals' => [
                        'alcalde' => $totalVotosAlcalde,
                        'concejal' => $totalVotosConcejal,
                        'total' => $totalVotosAlcalde
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error registering votes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateVotingTableTotals($votingTable, $electionTypeId)
    {
        $alcaldeCategory = ElectionCategory::where('code', 'ALC')->first();
        $concejalCategory = ElectionCategory::where('code', 'CON')->first();

        $alcaldeTypeCategoryIds = $alcaldeCategory
            ? ElectionTypeCategory::where('election_type_id', $electionTypeId)
                ->where('election_category_id', $alcaldeCategory->id)
                ->pluck('id')
                ->toArray()
            : [];

        $concejalTypeCategoryIds = $concejalCategory
            ? ElectionTypeCategory::where('election_type_id', $electionTypeId)
                ->where('election_category_id', $concejalCategory->id)
                ->pluck('id')
                ->toArray()
            : [];

        // Calcular totales para Alcaldes
        $votingTable->valid_votes = Vote::where('voting_table_id', $votingTable->id)
            ->where('election_type_id', $electionTypeId)
            ->whereHas('candidate', function($q) use ($alcaldeTypeCategoryIds) {
                $q->whereIn('election_type_category_id', $alcaldeTypeCategoryIds)
                  ->whereNotIn('type', ['null_votes', 'blank_votes']);
            })
            ->sum('quantity');

        $votingTable->blank_votes = Vote::where('voting_table_id', $votingTable->id)
            ->where('election_type_id', $electionTypeId)
            ->whereHas('candidate', function($q) use ($alcaldeTypeCategoryIds) {
                $q->whereIn('election_type_category_id', $alcaldeTypeCategoryIds)
                  ->where('type', 'blank_votes');
            })
            ->sum('quantity');

        $votingTable->null_votes = Vote::where('voting_table_id', $votingTable->id)
            ->where('election_type_id', $electionTypeId)
            ->whereHas('candidate', function($q) use ($alcaldeTypeCategoryIds) {
                $q->whereIn('election_type_category_id', $alcaldeTypeCategoryIds)
                  ->where('type', 'null_votes');
            })
            ->sum('quantity');

        // Calcular totales para Concejales
        $votingTable->valid_votes_second = Vote::where('voting_table_id', $votingTable->id)
            ->where('election_type_id', $electionTypeId)
            ->whereHas('candidate', function($q) use ($concejalTypeCategoryIds) {
                $q->whereIn('election_type_category_id', $concejalTypeCategoryIds)
                  ->whereNotIn('type', ['null_votes', 'blank_votes']);
            })
            ->sum('quantity');

        $votingTable->blank_votes_second = Vote::where('voting_table_id', $votingTable->id)
            ->where('election_type_id', $electionTypeId)
            ->whereHas('candidate', function($q) use ($concejalTypeCategoryIds) {
                $q->whereIn('election_type_category_id', $concejalTypeCategoryIds)
                  ->where('type', 'blank_votes');
            })
            ->sum('quantity');

        $votingTable->null_votes_second = Vote::where('voting_table_id', $votingTable->id)
            ->where('election_type_id', $electionTypeId)
            ->whereHas('candidate', function($q) use ($concejalTypeCategoryIds) {
                $q->whereIn('election_type_category_id', $concejalTypeCategoryIds)
                  ->where('type', 'null_votes');
            })
            ->sum('quantity');

        // Calcular totales de votantes
        $totalAlcalde = $votingTable->valid_votes + $votingTable->blank_votes + $votingTable->null_votes;
        $totalConcejal = $votingTable->valid_votes_second + $votingTable->blank_votes_second + $votingTable->null_votes_second;

        $votingTable->total_voters = $totalAlcalde;
        $votingTable->total_voters_second = $totalConcejal;
        $votingTable->ballots_used = $totalAlcalde;
        $votingTable->ballots_leftover = ($votingTable->ballots_received ?? 0) - $totalAlcalde - ($votingTable->ballots_spoiled ?? 0);
    }

    private function calculateTotals($votingTables)
    {
        $totals = [
            'expected' => 0,
            'total' => 0,
            'alcalde' => 0,
            'concejal' => 0,
            'by_candidate' => []
        ];
        foreach ($votingTables as $table) {
            $totals['expected'] += $table->expected_voters ?? 0;
            $totals['total'] += $table->total_voters ?? 0;
            $totals['alcalde'] += $table->valid_votes ?? 0;
            $totals['concejal'] += $table->valid_votes_second ?? 0;
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

            // Verificar permiso básico para registrar votos
            if (!$this->userHasPermission($user, 'register_votes')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para registrar votos'
                ], 403);
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
                            $errors[] = "Mesa {$votingTable->internal_code}: No tiene permiso para registrar votos";
                            continue;
                        }
                        if (in_array($votingTable->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                            continue;
                        }
                        $candidateIds = array_keys($votesData);
                        $candidates = Candidate::whereIn('id', $candidateIds)
                            ->whereHas('electionTypeCategory', function($q) use ($electionTypeId) {
                                $q->where('election_type_id', $electionTypeId);
                            })
                            ->where('active', true)
                            ->get()
                            ->keyBy('id');

                        // Verificar candidatos inválidos
                        $invalidCandidates = [];
                        foreach ($candidateIds as $candidateId) {
                            if (!isset($candidates[$candidateId])) {
                                $invalidCandidates[] = $candidateId;
                            }
                        }

                        if (!empty($invalidCandidates)) {
                            $errors[] = "Mesa {$votingTable->internal_code}: Candidatos no válidos";
                            continue;
                        }

                        // Calcular totales por categoría
                        $alcaldeCategory = ElectionCategory::where('code', 'ALC')->first();
                        $concejalCategory = ElectionCategory::where('code', 'CON')->first();

                        $alcaldeTypeCategoryIds = $alcaldeCategory
                            ? ElectionTypeCategory::where('election_type_id', $electionTypeId)
                                ->where('election_category_id', $alcaldeCategory->id)
                                ->pluck('id')
                                ->toArray()
                            : [];

                        $concejalTypeCategoryIds = $concejalCategory
                            ? ElectionTypeCategory::where('election_type_id', $electionTypeId)
                                ->where('election_category_id', $concejalCategory->id)
                                ->pluck('id')
                                ->toArray()
                            : [];

                        $totalVotosAlcalde = 0;
                        $totalVotosConcejal = 0;

                        // Procesar votos
                        foreach ($votesData as $candidateId => $quantity) {
                            $quantity = intval($quantity);
                            $candidate = $candidates[$candidateId];

                            if ($quantity > 0) {
                                // Clasificar para totales
                                if (in_array($candidate->election_type_category_id, $alcaldeTypeCategoryIds)) {
                                    $totalVotosAlcalde += $quantity;
                                } elseif (in_array($candidate->election_type_category_id, $concejalTypeCategoryIds)) {
                                    $totalVotosConcejal += $quantity;
                                }

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
                                        'vote_status' => 'verified',
                                    ]
                                );
                            } else {
                                Vote::where('voting_table_id', $votingTable->id)
                                    ->where('candidate_id', $candidateId)
                                    ->where('election_type_id', $electionTypeId)
                                    ->delete();
                            }
                        }

                        // Validar consistencia
                        if ($totalVotosAlcalde != $totalVotosConcejal) {
                            $errors[] = "Mesa {$votingTable->internal_code}: Votantes inconsistentes (Alc: $totalVotosAlcalde, Con: $totalVotosConcejal)";
                            continue;
                        }

                        if ($votingTable->expected_voters && $totalVotosAlcalde > $votingTable->expected_voters) {
                            $errors[] = "Mesa {$votingTable->internal_code}: Votos exceden habilitados";
                            continue;
                        }

                        // Actualizar estado
                        if (in_array($votingTable->status, ['configurada', 'en_espera'])) {
                            $votingTable->status = 'votacion';
                        }

                        // Actualizar totales
                        $this->updateVotingTableTotals($votingTable, $electionTypeId);

                        if ($closeAll && !in_array($votingTable->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                            // Verificar permiso para cerrar
                            if (!$this->canAccessTable($votingTable, 'close_table')) {
                                $errors[] = "Mesa {$votingTable->internal_code}: No tiene permiso para cerrar";
                                continue;
                            }
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

                $message = "Se procesaron {$processedTables} mesas";
                if (!empty($errors)) {
                    $message .= " con " . count($errors) . " errores";
                }

                return response()->json([
                    'success' => empty($errors),
                    'message' => $message,
                    'processed' => $processedTables,
                    'errors' => $errors
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error registering all votes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar las mesas: ' . $e->getMessage()
            ], 500);
        }
    }
    public function reviewTable(Request $request, $tableId)
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $votingTable = VotingTable::findOrFail($tableId);
            $user = Auth::user();

            // Verificar permiso para revisar esta mesa
            if (!$this->canAccessTable($votingTable, 'review_votes')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para revisar esta mesa'
                ], 403);
            }

            DB::beginTransaction();

            // Actualizar mesa
            $votingTable->verified_by = Auth::id();
            $votingTable->verified_at = now();
            $votingTable->verification_notes = $validated['notes'] ?? null;
            $votingTable->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mesa revisada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reviewing table: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al revisar la mesa'
            ], 500);
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
            $user = Auth::user();

            // Verificar permiso para validar
            if (!$this->canAccessTable($votingTable, 'validate_votes')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para validar esta mesa'
                ], 403);
            }

            DB::beginTransaction();

            // Mapeo de estados
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

            // Actualizar mesa
            $votingTable->status = $tableStatus;
            $votingTable->validated_by = Auth::id();
            $votingTable->validated_at = now();
            $votingTable->validation_notes = $validated['notes'] ?? null;
            $votingTable->save();

            // Actualizar votos
            Vote::where('voting_table_id', $tableId)
                ->update([
                    'vote_status' => $voteStatus,
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                ]);

            DB::commit();

            $messages = [
                'validate' => 'Mesa validada correctamente',
                'approve' => 'Mesa aprobada correctamente',
                'reject' => 'Mesa rechazada correctamente'
            ];

            return response()->json([
                'success' => true,
                'message' => $messages[$validated['action']]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error validating table: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la acción: ' . $e->getMessage()
            ], 500);
        }
    }
    public function correctTable(Request $request, $tableId)
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $votingTable = VotingTable::findOrFail($tableId);
            $user = Auth::user();

            // Verificar permiso para corregir
            if (!$this->canAccessTable($votingTable, 'correct_votes')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para corregir esta mesa'
                ], 403);
            }

            DB::beginTransaction();

            // Actualizar mesa
            $votingTable->status = 'en_escrutinio';
            $votingTable->corrected_by = Auth::id();
            $votingTable->corrected_at = now();
            $votingTable->correction_notes = $validated['notes'] ?? null;
            $votingTable->save();

            // Actualizar votos observados a corregidos
            Vote::where('voting_table_id', $tableId)
                ->where('vote_status', 'observed')
                ->update([
                    'vote_status' => 'corrected',
                    'corrected_by' => Auth::id(),
                    'corrected_at' => now(),
                ]);

            // Cerrar observaciones pendientes
            Observation::where('voting_table_id', $tableId)
                ->where('status', 'pending')
                ->update([
                    'status' => 'resolved',
                    'resolved_by' => Auth::id(),
                    'resolved_at' => now(),
                    'resolution_notes' => $validated['notes'] ?? 'Corregido por modificador',
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mesa corregida correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error correcting table: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al corregir la mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTableVotes($tableId)
    {
        try {
            $votes = Vote::where('voting_table_id', $tableId)
                ->with('candidate:id,name,party,color')
                ->get()
                ->map(function ($vote) {
                    return [
                        'candidate_id' => $vote->candidate_id,
                        'candidate_name' => $vote->candidate->name,
                        'candidate_party' => $vote->candidate->party,
                        'candidate_color' => $vote->candidate->color,
                        'quantity' => $vote->quantity,
                        'percentage' => $vote->percentage,
                        'vote_status' => $vote->vote_status,
                        'validation_status' => $vote->validation_status,
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

            return response()->json([
                'success' => true,
                'message' => 'Mesa cerrada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error closing table: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar la mesa: ' . $e->getMessage()
            ], 500);
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

            return response()->json([
                'success' => true,
                'message' => 'Mesa reabierta correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reopening table: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al reabrir la mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTableStats($tableId)
    {
        try {
            $votingTable = VotingTable::with([
                'institution:id,name,code',
                'votes.candidate',
                'observations' => function($q) {
                    $q->orderBy('created_at', 'desc');
                }
            ])->findOrFail($tableId);

            $alcaldeCategory = ElectionCategory::where('code', 'ALC')->first();
            $concejalCategory = ElectionCategory::where('code', 'CON')->first();

            $stats = [
                'table' => [
                    'id' => $votingTable->id,
                    'number' => $votingTable->number,
                    'code' => $votingTable->internal_code,
                    'oep_code' => $votingTable->oep_code,
                    'status' => $votingTable->status,
                    'validation_status' => $votingTable->validation_status,
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
                'votes' => [
                    'alcalde' => [
                        'valid' => $votingTable->valid_votes,
                        'blank' => $votingTable->blank_votes,
                        'null' => $votingTable->null_votes,
                        'total' => $votingTable->total_voters,
                    ],
                    'concejal' => [
                        'valid' => $votingTable->valid_votes_second,
                        'blank' => $votingTable->blank_votes_second,
                        'null' => $votingTable->null_votes_second,
                        'total' => $votingTable->total_voters_second,
                    ],
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
