<?php
// app/Http/Controllers/VotingTableVoteController.php

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
        $this->middleware('permission:register_votes')->only(['index', 'registerVotes', 'registerAllVotes']);
        $this->middleware('permission:view_votes')->only(['index', 'getTableVotes']);
        $this->middleware('permission:review_votes')->only(['reviewTable']);
        $this->middleware('permission:validate_votes')->only(['validateTable']);
        $this->middleware('permission:correct_votes')->only(['correctTable']);
    }

    public function index(Request $request)
    {
        try {
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

            // Obtener datos para filtros
            $institutions = Institution::where('status', 'activo')
                ->orderBy('name')
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

            // Aplicar filtros (mismo código que antes...)
            if ($institutionId) {
                $query->where('institution_id', $institutionId);
            }

            if ($status) {
                $query->where('status', $status);
            } else {
                $query->whereIn('status', ['configurada', 'en_espera', 'votacion', 'en_escrutinio']);
            }

            if ($validationStatus) {
                $query->where('validation_status', $validationStatus);
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

            // Filtros por votos
            if ($minVotes || $maxVotes) {
                $query->whereHas('votes', function($q) use ($minVotes, $maxVotes, $electionTypeId) {
                    $q->where('election_type_id', $electionTypeId);
                    if ($minVotes) {
                        $q->havingRaw('SUM(quantity) >= ?', [$minVotes]);
                    }
                    if ($maxVotes) {
                        $q->havingRaw('SUM(quantity) <= ?', [$maxVotes]);
                    }
                });
            }

            // Filtro por participación
            if ($participation) {
                switch ($participation) {
                    case 'alta':
                        $query->whereRaw('(total_voters * 100.0 / NULLIF(expected_voters, 0)) >= 75');
                        break;
                    case 'media':
                        $query->whereRaw('(total_voters * 100.0 / NULLIF(expected_voters, 0)) BETWEEN 50 AND 75');
                        break;
                    case 'baja':
                        $query->whereRaw('(total_voters * 100.0 / NULLIF(expected_voters, 0)) < 50');
                        break;
                }
            }

            // Filtro por observaciones
            if ($hasObservations !== null) {
                if ($hasObservations == '1') {
                    $query->has('observations');
                } else {
                    $query->doesntHave('observations');
                }
            }

            // Ordenamiento
            $sortBy = $request->input('sort_by', 'number');
            $sortDirection = $request->input('sort_direction', 'asc');

            switch ($sortBy) {
                case 'expected_voters':
                    $query->orderBy('expected_voters', $sortDirection);
                    break;
                case 'total_voters':
                    $query->orderBy('total_voters', $sortDirection);
                    break;
                case 'status':
                    $query->orderBy('status', $sortDirection);
                    break;
                case 'participation':
                    $query->orderByRaw('(total_voters * 100.0 / NULLIF(expected_voters, 0)) ' . $sortDirection);
                    break;
                default:
                    $query->orderBy('institution_id')->orderBy('number', $sortDirection);
            }

            $votingTables = $query->get();

            // Obtener candidatos a través de election_type_category
            $candidates = Candidate::with(['electionTypeCategory.electionCategory', 'electionTypeCategory.electionType'])
                ->whereHas('electionTypeCategory', function($q) use ($electionTypeId) {
                    $q->where('election_type_id', $electionTypeId);
                })
                ->where('active', true)
                ->orderBy('list_order')
                ->orderBy('name')
                ->get();

            // Separar candidatos por categoría para la vista
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
            $totals = $this->calculateTotals($votingTables, $candidates, $alcaldeTypeCategoryIds, $concejalTypeCategoryIds);

            // Contar mesas por estado
            $tableStats = [
                'total' => $votingTables->count(),
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

            // Preparar labels para los badges de estado
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

            return view('voting-table-votes.index', compact(
                'votingTables',
                'candidates',
                'candidatesByCategory',
                'institutions',
                'electionTypes',
                'institutionId',
                'electionTypeId',
                'totals',
                'tableStats',
                'statusLabels',
                'validationLabels',
                'request'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading voting table votes: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Error al cargar los datos de votación: ' . $e->getMessage());
        }
    }

    public function registerVotes(Request $request)
    {
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

                if (in_array($votingTable->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                    throw new \Exception('No se pueden modificar votos de una mesa ' . $votingTable->status);
                }

                // Validar que los candidatos existan
                $candidateIds = array_keys($votesData);
                $candidates = Candidate::whereIn('id', $candidateIds)
                    ->whereHas('electionTypeCategory', function($q) use ($electionTypeId) {
                        $q->where('election_type_id', $electionTypeId);
                    })
                    ->where('active', true)
                    ->get()
                    ->keyBy('id');

                foreach ($candidateIds as $candidateId) {
                    if (!isset($candidates[$candidateId])) {
                        throw new \Exception("Candidato ID {$candidateId} no válido para este tipo de elección");
                    }
                }

                // Obtener categorías
                $alcaldeCategory = ElectionCategory::where('code', 'ALC')->first();
                $concejalCategory = ElectionCategory::where('code', 'CON')->first();

                // Obtener los election_type_category_ids para cada categoría
                $alcaldeTypeCategoryIds = $alcaldeCategory ? ElectionTypeCategory::where('election_type_id', $electionTypeId)
                    ->where('election_category_id', $alcaldeCategory->id)
                    ->pluck('id')
                    ->toArray() : [];

                $concejalTypeCategoryIds = $concejalCategory ? ElectionTypeCategory::where('election_type_id', $electionTypeId)
                    ->where('election_category_id', $concejalCategory->id)
                    ->pluck('id')
                    ->toArray() : [];

                // Calcular totales por categoría (solo para información, no para validación)
                $totalAlcalde = 0;
                $totalConcejal = 0;
                $totalVotos = 0;

                foreach ($votesData as $candidateId => $quantity) {
                    if ($quantity > 0) {
                        $candidate = $candidates[$candidateId];
                        $totalVotos += $quantity;

                        if (in_array($candidate->election_type_category_id, $alcaldeTypeCategoryIds)) {
                            $totalAlcalde += $quantity;
                        } elseif (in_array($candidate->election_type_category_id, $concejalTypeCategoryIds)) {
                            $totalConcejal += $quantity;
                        }
                    }
                }

                // ❌ ELIMINADA LA VALIDACIÓN DE IGUALDAD ENTRE ALCALDE Y CONCEJAL
                // if ($totalAlcalde != $totalConcejal) {
                //     throw new \Exception("El total de votos para Alcalde ({$totalAlcalde}) debe ser igual al de Concejales ({$totalConcejal})");
                // }

                // Validar contra votantes esperados (solo como tope máximo)
                if ($votingTable->expected_voters && $totalVotos > $votingTable->expected_voters) {
                    throw new \Exception("El total de votos ({$totalVotos}) excede los votantes esperados ({$votingTable->expected_voters})");
                }

                // Validar contra papeletas recibidas
                if ($votingTable->ballots_received && $totalVotos > $votingTable->ballots_received) {
                    throw new \Exception("El total de votos ({$totalVotos}) excede las papeletas recibidas ({$votingTable->ballots_received})");
                }

                // Procesar votos
                foreach ($votesData as $candidateId => $quantity) {
                    if ($quantity > 0) {
                        $percentage = $totalVotos > 0 ? round(($quantity / $totalVotos) * 100, 2) : 0;

                        Vote::updateOrCreate(
                            [
                                'voting_table_id' => $votingTable->id,
                                'candidate_id' => $candidateId,
                                'election_type_id' => $electionTypeId
                            ],
                            [
                                'quantity' => $quantity,
                                'percentage' => $percentage,
                                'user_id' => $user->id,
                                'registered_at' => now(),
                                'vote_status' => Vote::VOTE_STATUS_VERIFIED,
                                'validation_status' => Vote::VALIDATION_STATUS_PENDING,
                            ]
                        );
                    } else {
                        Vote::where('voting_table_id', $votingTable->id)
                            ->where('candidate_id', $candidateId)
                            ->where('election_type_id', $electionTypeId)
                            ->delete();
                    }
                }

                // Actualizar totales en la mesa
                $votingTable->valid_votes = Vote::where('voting_table_id', $votingTable->id)
                    ->where('election_type_id', $electionTypeId)
                    ->whereHas('candidate', function($q) use ($alcaldeTypeCategoryIds) {
                        $q->whereIn('election_type_category_id', $alcaldeTypeCategoryIds);
                    })
                    ->sum('quantity');

                $votingTable->valid_votes_second = Vote::where('voting_table_id', $votingTable->id)
                    ->where('election_type_id', $electionTypeId)
                    ->whereHas('candidate', function($q) use ($concejalTypeCategoryIds) {
                        $q->whereIn('election_type_category_id', $concejalTypeCategoryIds);
                    })
                    ->sum('quantity');

                $votingTable->total_voters = $totalAlcalde;
                $votingTable->total_voters_second = $totalConcejal;
                $votingTable->ballots_used = $totalVotos;
                $votingTable->ballots_leftover = $votingTable->ballots_received - $totalVotos - ($votingTable->ballots_spoiled ?? 0);

                // Actualizar estado
                if (in_array($votingTable->status, ['configurada', 'en_espera'])) {
                    $votingTable->status = 'votacion';
                }

                if ($closeTable) {
                    $votingTable->status = 'cerrada';
                    $votingTable->closing_time = now();
                }

                $votingTable->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $closeTable ? 'Votos registrados y mesa cerrada exitosamente' : 'Votos registrados exitosamente',
                    'table_status' => $votingTable->status,
                    'totals' => [
                        'alcalde' => $totalAlcalde,
                        'concejal' => $totalConcejal,
                        'total' => $totalVotos
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error registering votes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateTotals($votingTables, $candidates, $alcaldeTypeCategoryIds, $concejalTypeCategoryIds)
    {
        $totals = [
            'expected' => 0,
            'total' => 0,
            'alcalde' => 0,
            'concejal' => 0,
            'by_candidate' => []
        ];

        foreach ($candidates as $candidate) {
            $totals['by_candidate'][$candidate->id] = 0;
        }

        foreach ($votingTables as $table) {
            $totals['expected'] += $table->expected_voters ?? 0;
            $totals['total'] += $table->total_voters ?? 0;
            $totals['alcalde'] += $table->valid_votes ?? 0;
            $totals['concejal'] += $table->valid_votes_second ?? 0;

            foreach ($table->votes as $vote) {
                if (isset($totals['by_candidate'][$vote->candidate_id])) {
                    $totals['by_candidate'][$vote->candidate_id] += $vote->quantity;
                }
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

                        if (in_array($votingTable->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                            continue;
                        }

                        // Validar total de votos
                        $totalVotes = array_sum($votesData);

                        if ($votingTable->expected_voters && $totalVotes > $votingTable->expected_voters) {
                            $errors[] = "Mesa {$votingTable->internal_code}: votos exceden votantes esperados";
                            continue;
                        }

                        // Procesar votos
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
                                        'vote_status' => Vote::VOTE_STATUS_VERIFIED,
                                        'validation_status' => Vote::VALIDATION_STATUS_PENDING
                                    ]
                                );
                            } else {
                                Vote::where('voting_table_id', $votingTable->id)
                                    ->where('candidate_id', $candidateId)
                                    ->where('election_type_id', $electionTypeId)
                                    ->delete();
                            }
                        }

                        if (in_array($votingTable->status, ['configurada', 'en_espera'])) {
                            $votingTable->status = 'votacion';
                        }

                        if ($closeAll && !in_array($votingTable->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])) {
                            $votingTable->status = 'cerrada';
                            $votingTable->closing_time = now();
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

            DB::beginTransaction();

            $votingTable->validation_status = Vote::VALIDATION_STATUS_REVIEWED;
            $votingTable->verified_by = Auth::id();
            $votingTable->verified_at = now();
            $votingTable->verification_notes = $validated['notes'] ?? null;
            $votingTable->save();

            // Actualizar todos los votos de la mesa
            Vote::where('voting_table_id', $tableId)
                ->update([
                    'validation_status' => Vote::VALIDATION_STATUS_REVIEWED,
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                ]);

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

            DB::beginTransaction();

            $statusMap = [
                'validate' => Vote::VALIDATION_STATUS_VALIDATED,
                'approve' => Vote::VALIDATION_STATUS_APPROVED,
                'reject' => Vote::VALIDATION_STATUS_REJECTED
            ];

            $newStatus = $statusMap[$validated['action']];

            $votingTable->validation_status = $newStatus;
            $votingTable->validated_by = Auth::id();
            $votingTable->validated_at = now();
            $votingTable->validation_notes = $validated['notes'] ?? null;

            if ($validated['action'] === 'reject') {
                $votingTable->status = 'observada';
            } elseif ($validated['action'] === 'approve') {
                $votingTable->status = 'escrutada';
            }

            $votingTable->save();

            // Actualizar todos los votos de la mesa
            Vote::where('voting_table_id', $tableId)
                ->update([
                    'validation_status' => $newStatus,
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acción completada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error validating table: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la acción'
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

            DB::beginTransaction();

            $votingTable->validation_status = Vote::VALIDATION_STATUS_CORRECTED;
            $votingTable->corrected_by = Auth::id();
            $votingTable->corrected_at = now();
            $votingTable->correction_notes = $validated['notes'] ?? null;
            $votingTable->status = 'en_escrutinio';
            $votingTable->save();

            // Actualizar todos los votos de la mesa
            Vote::where('voting_table_id', $tableId)
                ->update([
                    'validation_status' => Vote::VALIDATION_STATUS_CORRECTED,
                    'corrected_by' => Auth::id(),
                    'corrected_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mesa marcada para corrección'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error correcting table: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al corregir la mesa'
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
