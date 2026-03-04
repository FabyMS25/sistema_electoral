<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Institution;
use App\Models\Observation;
use App\Models\RecintoDelegate;
use App\Models\Reviewer;
use App\Models\TableDelegate;
use App\Models\User;
use App\Models\ValidationHistory;
use App\Models\Vote;
use App\Models\VotingTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ObservationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_observations')->only(['index', 'show', 'getByTable']);
        $this->middleware('permission:create_observations')->only(['store']);
        // $this->middleware('permission:resolve_observations')->only(['resolve']);
        $this->middleware('permission:resolve_observations')->only(['resolve', 'escalate', 'reject']);
    }

    private function getUserRole()
    {
        $user = Auth::user();
        if ($user->hasRole('revisor')) return 'revisor';
        if ($user->hasRole('fiscal')) return 'fiscal';
        if ($user->hasRole('notario')) return 'notario';
        if ($user->hasRole('coordinador')) return 'coordinador';
        return 'revisor';
    }

    public static function getTypes(): array
    {
        return [
            Observation::TYPE_INCONSISTENCIA_ACTA => 'Inconsistencia en Acta',
            Observation::TYPE_ERROR_DATOS => 'Error en Datos',
            Observation::TYPE_FALTA_FIRMA => 'Falta Firma',
            Observation::TYPE_ACTA_ILEGIBLE => 'Acta Ilegible',
            Observation::TYPE_VOTOS_INCONSISTENTES => 'Votos Inconsistentes',
            Observation::TYPE_MESA_ANULADA => 'Mesa Anulada',
            Observation::TYPE_RECLAMO_PARTIDO => 'Reclamo de Partido',
            Observation::TYPE_DIFERENCIA_PAPELETAS => 'Diferencia de Papeletas',
            Observation::TYPE_CIERRE_ANTICIPADO => 'Cierre Anticipado',
            Observation::TYPE_OTRO => 'Otro',
        ];
    }

    private function getStatusBadge($status)
    {
        return match($status) {
            Observation::STATUS_PENDING => '<span class="badge bg-warning">Pendiente</span>',
            Observation::STATUS_IN_REVIEW => '<span class="badge bg-info">En Revisión</span>',
            Observation::STATUS_RESOLVED => '<span class="badge bg-success">Resuelto</span>',
            Observation::STATUS_REJECTED => '<span class="badge bg-danger">Rechazado</span>',
            Observation::STATUS_ESCALATED => '<span class="badge bg-primary">Escalado</span>',
            default => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }
    private function getSeverityBadge($severity)
    {
        return match($severity) {
            'info' => '<span class="badge bg-info">Info</span>',
            'warning' => '<span class="badge bg-warning">Advertencia</span>',
            'error' => '<span class="badge bg-danger">Error</span>',
            'critical' => '<span class="badge bg-dark">Crítico</span>',
            default => '<span class="badge bg-secondary">Desconocido</span>',
        };
    }

    public function index(Request $request)
    {
        $query = Observation::with(['votingTable.institution', 'reviewer', 'resolver']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('voting_table_id')) {
            $query->where('voting_table_id', $request->voting_table_id);
        }

        $observations = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('observations.index', compact('observations'));
    }


    private function canBeObserved($votingTable)
    {
        return in_array($votingTable->status, [
            VotingTable::STATUS_VOTACION,
            VotingTable::STATUS_CERRADA,
            VotingTable::STATUS_EN_ESCRUTINIO
        ]) && $votingTable->validation_status !== VotingTable::VALIDATION_APPROVED;
    }

    private function generateObservationCode(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastObservation = Observation::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastObservation && preg_match('/OBS-(\d{6})-(\d{4})/', $lastObservation->code, $matches)) {
            $lastNumber = intval($matches[2]);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "OBS-{$year}{$month}-{$newNumber}";
    }



    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'voting_table_id' => 'required|exists:voting_tables,id',
                'type' => 'required|in:' . implode(',', array_keys($this->getTypes())),
                'description' => 'required|string|max:1000',
                'severity' => 'required|in:info,warning,error,critical',
                'candidate_id' => 'nullable|exists:candidates,id',
                'vote_ids' => 'nullable|array',
                'vote_ids.*' => 'exists:votes,id',
                'evidence' => 'nullable|image|max:5120', // 5MB max
            ]);

            DB::beginTransaction();

            $votingTable = VotingTable::findOrFail($validated['voting_table_id']);

            // Verificar que la mesa pueda ser observada
            if (!$this->canBeObserved($votingTable)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta mesa no puede ser observada en su estado actual'
                ], 422);
            }

            // Subir evidencia si existe
            $evidencePath = null;
            if ($request->hasFile('evidence')) {
                $evidencePath = $request->file('evidence')->store('observations/' . date('Y/m/d'), 'public');
            }

            // Crear la observación
            $observation = Observation::create([
                'code' => $this->generateObservationCode(),
                'type' => $validated['type'],
                'description' => $validated['description'],
                'severity' => $validated['severity'],
                'status' => Observation::STATUS_PENDING,
                'voting_table_id' => $validated['voting_table_id'],
                'election_type_id' => $votingTable->election_type_id,
                'candidate_id' => $validated['candidate_id'] ?? null,
                'reviewed_by' => Auth::id(),
                'reviewer_role' => $this->getUserRole(),
                'evidence_photo' => $evidencePath,
            ]);

            // Asociar votos específicos si se seleccionaron
            if (!empty($validated['vote_ids'])) {
                foreach ($validated['vote_ids'] as $voteId) {
                    $vote = Vote::find($voteId);
                    if ($vote) {
                        $oldValues = [
                            'vote_status' => $vote->vote_status,
                            'validation_status' => $vote->validation_status,
                        ];

                        $vote->update([
                            'observation_id' => $observation->id,
                            'vote_status' => Vote::VOTE_STATUS_OBSERVED,
                            'validation_status' => Vote::VALIDATION_STATUS_OBSERVED,
                        ]);

                        // Registrar en historial
                        ValidationHistory::create([
                            'vote_id' => $vote->id,
                            'user_id' => Auth::id(),
                            'action' => ValidationHistory::ACTION_OBSERVE,
                            'notes' => 'Observado vía #' . $observation->code,
                            'previous_values' => $oldValues,
                            'new_values' => [
                                'vote_status' => Vote::VOTE_STATUS_OBSERVED,
                                'validation_status' => Vote::VALIDATION_STATUS_OBSERVED,
                            ],
                        ]);
                    }
                }
            }

            // Marcar la mesa como observada
            $votingTable->update([
                'status' => VotingTable::STATUS_OBSERVADA,
                'validation_status' => VotingTable::VALIDATION_OBSERVED,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'verification_notes' => 'Observación creada: ' . $observation->code,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Observación creada exitosamente',
                'observation' => [
                    'id' => $observation->id,
                    'code' => $observation->code,
                    'type' => $observation->type,
                    'type_label' => $this->getTypes()[$observation->type] ?? $observation->type,
                    'severity' => $observation->severity,
                    'description' => $observation->description,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating observation: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la observación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolver una observación
     */
    public function resolve(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'resolution_type' => 'required|in:correccion,anulacion,rechazo,escalamiento',
                'notes' => 'required|string|max:1000',
                'corrected_votes' => 'nullable|array',
                'corrected_votes.*' => 'integer|min:0',
                'escalated_to' => 'required_if:resolution_type,escalamiento|exists:users,id|nullable',
            ]);

            DB::beginTransaction();

            $observation = Observation::with('votingTable')->findOrFail($id);

            if ($observation->status !== Observation::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta observación ya no está pendiente'
                ], 422);
            }

            // Procesar según el tipo de resolución
            switch ($validated['resolution_type']) {
                case 'correccion':
                    $this->processCorrection($observation, $validated);
                    break;
                case 'anulacion':
                    $this->processAnnulment($observation, $validated);
                    break;
                case 'escalamiento':
                    $this->processEscalation($observation, $validated);
                    break;
                case 'rechazo':
                    $this->processRejection($observation, $validated);
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Observación resuelta exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error resolving observation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al resolver la observación: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processCorrection($observation, $validated)
    {
        $votingTable = $observation->votingTable;

        // Si hay votos corregidos
        if (!empty($validated['corrected_votes'])) {
            foreach ($validated['corrected_votes'] as $voteId => $newQuantity) {
                $vote = Vote::find($voteId);
                if ($vote && $vote->observation_id == $observation->id) {
                    $oldQuantity = $vote->quantity;

                    $vote->update([
                        'quantity' => $newQuantity,
                        'vote_status' => Vote::VOTE_STATUS_CORRECTED,
                        'validation_status' => Vote::VALIDATION_STATUS_CORRECTED,
                        'corrected_by' => Auth::id(),
                        'corrected_at' => now(),
                        'correction_notes' => $validated['notes'],
                        'observation_id' => null, // Desvincular observación
                    ]);

                    ValidationHistory::create([
                        'vote_id' => $vote->id,
                        'user_id' => Auth::id(),
                        'action' => ValidationHistory::ACTION_CORRECT,
                        'notes' => 'Corregido por observación #' . $observation->code,
                        'previous_values' => ['quantity' => $oldQuantity],
                        'new_values' => ['quantity' => $newQuantity],
                    ]);
                }
            }
        }

        // Marcar observación como resuelta
        $observation->update([
            'status' => Observation::STATUS_RESOLVED,
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
            'resolution_type' => $validated['resolution_type'],
            'resolution_notes' => $validated['notes'],
        ]);

        // Actualizar estado de la mesa
        $votingTable->update([
            'status' => VotingTable::STATUS_EN_ESCRUTINIO,
            'validation_status' => VotingTable::VALIDATION_CORRECTED,
            'corrected_by' => Auth::id(),
            'corrected_at' => now(),
        ]);
    }

    private function processAnnulment($observation, $validated)
    {
        $votingTable = $observation->votingTable;

        // Anular la mesa
        $votingTable->update([
            'status' => VotingTable::STATUS_ANULADA,
            'validation_status' => VotingTable::VALIDATION_REJECTED,
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        // Anular todos los votos de la mesa
        Vote::where('voting_table_id', $votingTable->id)
            ->update([
                'vote_status' => Vote::VOTE_STATUS_REJECTED,
                'validation_status' => Vote::VALIDATION_STATUS_REJECTED,
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);

        $observation->update([
            'status' => Observation::STATUS_RESOLVED,
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
            'resolution_type' => $validated['resolution_type'],
            'resolution_notes' => $validated['notes'],
        ]);
    }

    private function processEscalation($observation, $validated)
    {
        $observation->update([
            'status' => Observation::STATUS_ESCALATED,
            'is_escalated' => true,
            'escalated_to' => $validated['escalated_to'],
            'escalated_at' => now(),
            'resolution_notes' => $validated['notes'],
        ]);
    }

    private function processRejection($observation, $validated)
    {
        // Desvincular votos
        Vote::where('observation_id', $observation->id)
            ->update([
                'observation_id' => null,
                'vote_status' => Vote::VOTE_STATUS_PENDING,
                'validation_status' => Vote::VALIDATION_STATUS_PENDING,
            ]);

        $observation->update([
            'status' => Observation::STATUS_REJECTED,
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
            'resolution_type' => $validated['resolution_type'],
            'resolution_notes' => $validated['notes'],
        ]);

        // Actualizar estado de la mesa
        $observation->votingTable->updateValidationStatus();
    }

    /**
     * Obtener observaciones de una mesa
     */
    public function getTableObservations($tableId)
    {
        try {
            $observations = Observation::where('voting_table_id', $tableId)
                ->with(['reviewer', 'resolver', 'votes.candidate'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($obs) {
                    return [
                        'id' => $obs->id,
                        'code' => $obs->code,
                        'type' => $obs->type,
                        'type_label' => $this->getTypes()[$obs->type] ?? $obs->type,
                        'description' => $obs->description,
                        'severity' => $obs->severity,
                        'severity_badge' => $this->getSeverityBadge($obs->severity),
                        'status' => $obs->status,
                        'status_badge' => $this->getStatusBadge($obs->status),
                        'reviewer_name' => $obs->reviewer->name ?? 'N/A',
                        'reviewer_role' => $obs->reviewer_role,
                        'created_at' => $obs->created_at->format('d/m/Y H:i'),
                        'resolved_at' => $obs->resolved_at ? $obs->resolved_at->format('d/m/Y H:i') : null,
                        'resolver_name' => $obs->resolver->name ?? null,
                        'resolution_notes' => $obs->resolution_notes,
                        'evidence_url' => $obs->evidence_photo ? asset('storage/' . $obs->evidence_photo) : null,
                        'votes_count' => $obs->votes->count(),
                    ];
                });

            return response()->json($observations);

        } catch (\Exception $e) {
            Log::error('Error getting observations: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar observaciones'], 500);
        }
    }

    /**
     * Obtener estadísticas de observaciones
     */
    public function getStats()
    {
        try {
            $stats = [
                'total' => Observation::count(),
                'pending' => Observation::where('status', Observation::STATUS_PENDING)->count(),
                'in_review' => Observation::where('status', Observation::STATUS_IN_REVIEW)->count(),
                'resolved' => Observation::where('status', Observation::STATUS_RESOLVED)->count(),
                'rejected' => Observation::where('status', Observation::STATUS_REJECTED)->count(),
                'escalated' => Observation::where('is_escalated', true)->count(),
                'by_severity' => [
                    'info' => Observation::where('severity', Observation::SEVERITY_INFO)->count(),
                    'warning' => Observation::where('severity', Observation::SEVERITY_WARNING)->count(),
                    'error' => Observation::where('severity', Observation::SEVERITY_ERROR)->count(),
                    'critical' => Observation::where('severity', Observation::SEVERITY_CRITICAL)->count(),
                ],
                'by_type' => [],
            ];

            foreach ($this->getTypes() as $type => $label) {
                $stats['by_type'][$type] = Observation::where('type', $type)->count();
            }

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error getting observation stats: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar estadísticas'], 500);
        }
    }





    private function getElectionTypeId($votingTableId)
    {
        $votingTable = VotingTable::find($votingTableId);
        return $votingTable ? $votingTable->election_type_id : null;
    }

    public function show(Observation $observation)
    {
        $observation->load(['votingTable.institution', 'reviewer', 'resolver']);
        return view('observations.show', compact('observation'));
    }


    public function getByTable($tableId)
    {
        try {
            $observations = Observation::where('voting_table_id', $tableId)
                ->with(['reviewer', 'resolver'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($obs) {
                    return [
                        'id' => $obs->id,
                        'code' => $obs->code,
                        'type' => $obs->type,
                        'description' => $obs->description,
                        'severity' => $obs->severity,
                        'status' => $obs->status,
                        'created_at' => $obs->created_at->format('d/m/Y H:i'),
                        'reviewer_name' => $obs->reviewer->name ?? 'N/A',
                    ];
                });

            return response()->json($observations);

        } catch (\Exception $e) {
            Log::error('Error getting observations: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar observaciones'], 500);
        }
    }

    private function getSupervisorId($votingTableId = null)
    {
        $user = Auth::user();

        // Si el usuario tiene asignado un recinto, su supervisor es el coordinador municipal
        $recintoDelegate = RecintoDelegate::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($recintoDelegate) {
            // Buscar coordinador municipal del mismo municipio
            $municipalityId = $recintoDelegate->institution->municipality_id;

            $coordinator = User::whereHas('roles', function($q) {
                    $q->where('name', 'coordinador_municipal');
                })
                ->whereHas('reviewerAssignments', function($q) use ($municipalityId) {
                    $q->where('assignable_type', Institution::class)
                      ->whereHasMorph('assignable', [Institution::class], function($subq) use ($municipalityId) {
                          $subq->where('municipality_id', $municipalityId);
                      });
                })->first();

            if ($coordinator) return $coordinator->id;
        }

        // Si el usuario tiene asignada una mesa, su supervisor es el delegado del recinto
        $tableDelegate = TableDelegate::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($tableDelegate && $votingTableId) {
            $votingTable = VotingTable::find($votingTableId);
            if ($votingTable) {
                $recintoDelegate = RecintoDelegate::where('institution_id', $votingTable->institution_id)
                    ->where('is_active', true)
                    ->first();

                if ($recintoDelegate) return $recintoDelegate->user_id;
            }
        }

        // Si el usuario es revisor/modificador de un ámbito, escalar al coordinador correspondiente
        $reviewer = Reviewer::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($reviewer) {
            if ($reviewer->assignable_type === Institution::class) {
                // Es revisor de recinto, escalar a coordinador municipal
                $municipalityId = $reviewer->assignable->municipality_id;
                $coordinator = User::whereHas('roles', function($q) {
                        $q->where('name', 'coordinador_municipal');
                    })
                    ->whereHas('reviewerAssignments', function($q) use ($municipalityId) {
                        $q->where('assignable_type', Institution::class)
                          ->whereHasMorph('assignable', [Institution::class], function($subq) use ($municipalityId) {
                              $subq->where('municipality_id', $municipalityId);
                          });
                    })->first();

                if ($coordinator) return $coordinator->id;
            } else {
                // Es revisor de mesa, escalar a delegado de recinto
                $institutionId = $reviewer->assignable->institution_id;
                $recintoDelegate = RecintoDelegate::where('institution_id', $institutionId)
                    ->where('is_active', true)
                    ->first();

                if ($recintoDelegate) return $recintoDelegate->user_id;
            }
        }

        // Último recurso: buscar administrador
        $admin = User::whereHas('roles', function($q) {
            $q->where('name', 'administrador');
        })->first();

        return $admin?->id;
    }
}
