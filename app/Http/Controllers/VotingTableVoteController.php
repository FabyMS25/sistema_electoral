<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Vote;
use App\Models\Candidate;
use App\Models\VotingTable;
use App\Models\Institution;
use App\Models\ElectionType;
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
        $this->middleware('permission:view_votes')->only(['index']);
    }

    public function index(Request $request)
    {
        try {
            $institutionId = $request->input('institution_id');
            $electionTypeId = $request->input('election_type_id');
            $status = $request->input('status');
            $tableNumber = $request->input('table_number');
            $tableCode = $request->input('table_code');
            $fromName = $request->input('from_name');
            $toName = $request->input('to_name');
            $tableType = $request->input('table_type');
            $minVotes = $request->input('min_votes');
            $maxVotes = $request->input('max_votes');
            $participation = $request->input('participation');
            $hasObservations = $request->input('has_observations');
            $sortBy = $request->input('sort_by', 'number');

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
                ->get(['id', 'name', 'type', 'election_date']);

            // Construir query de mesas con relaciones
            $query = VotingTable::with([
                'institution:id,name,code',
                'votes' => function($q) use ($electionTypeId) {
                    $q->where('election_type_id', $electionTypeId)
                    ->with('candidate:id,name,party,color');
                },
                'observations' => function($q) {
                    $q->where('status', 'pending');
                }
            ]);

            // Aplicar filtros
            if ($institutionId) {
                $query->where('institution_id', $institutionId);
            }

            if ($status) {
                $query->where('status', $status);
            } else {
                // Por defecto, excluir mesas cerradas si no se especifica filtro
                $query->whereIn('status', ['pendiente', 'en_proceso', 'activo']);
            }

            if ($tableNumber) {
                $query->where('number', $tableNumber);
            }

            if ($tableCode) {
                $query->where('code', 'ilike', "%{$tableCode}%");
            }

            if ($fromName) {
                $query->where('from_name', 'ilike', "%{$fromName}%");
            }

            if ($toName) {
                $query->where('to_name', 'ilike', "%{$toName}%");
            }

            if ($tableType) {
                $query->where('type', $tableType);
            }

            // Filtros por votos (requieren subquery)
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
                        $query->whereRaw('(voted_citizens * 100.0 / NULLIF(registered_citizens, 0)) >= 75');
                        break;
                    case 'media':
                        $query->whereRaw('(voted_citizens * 100.0 / NULLIF(registered_citizens, 0)) BETWEEN 50 AND 75');
                        break;
                    case 'baja':
                        $query->whereRaw('(voted_citizens * 100.0 / NULLIF(registered_citizens, 0)) < 50');
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
            switch ($sortBy) {
                case 'registered_citizens':
                    $query->orderBy('registered_citizens', 'desc');
                    break;
                case 'computed_records':
                    $query->orderBy('computed_records', 'desc');
                    break;
                case 'status':
                    $query->orderBy('status');
                    break;
                default:
                    $query->orderBy('institution_id')->orderBy('number');
            }

            $votingTables = $query->get();

            // Obtener candidatos del tipo de elección
            $candidates = Candidate::where('election_type_id', $electionTypeId)
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'party', 'color', 'photo']);

            // Calcular totales
            $totals = $this->calculateTotals($votingTables, $candidates, $electionTypeId);

            // Contar mesas por estado para estadísticas rápidas
            $tableStats = [
                'total' => $votingTables->count(),
                'pendiente' => $votingTables->where('status', 'pendiente')->count(),
                'en_proceso' => $votingTables->where('status', 'en_proceso')->count(),
                'activo' => $votingTables->where('status', 'activo')->count(),
                'cerrado' => $votingTables->where('status', 'cerrado')->count(),
                'observado' => $votingTables->where('status', 'observado')->count(),
            ];

            return view('voting-table-votes.index', compact(
                'votingTables',
                'candidates',
                'institutions',
                'electionTypes',
                'institutionId',
                'electionTypeId',
                'totals',
                'tableStats',
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
                if ($votingTable->status === 'cerrado') {
                    throw new \Exception('No se pueden modificar votos de una mesa cerrada');
                }
                $validCandidateIds = Candidate::where('election_type_id', $electionTypeId)
                    ->where('active', true)
                    ->pluck('id')
                    ->toArray();
                foreach (array_keys($votesData) as $candidateId) {
                    if (!in_array($candidateId, $validCandidateIds)) {
                        throw new \Exception("Candidato inválido para este tipo de elección");
                    }
                }
                $totalVotes = array_sum($votesData);
                if ($votingTable->registered_citizens && $totalVotes > $votingTable->registered_citizens) {
                    throw new \Exception("El total de votos ($totalVotes) excede los ciudadanos registrados ({$votingTable->registered_citizens})");
                }
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
                                'verified_at' => now(),
                                'vote_status' => 'verified'
                            ]
                        );
                    } else {
                        Vote::where('voting_table_id', $votingTable->id)
                            ->where('candidate_id', $candidateId)
                            ->where('election_type_id', $electionTypeId)
                            ->delete();
                    }
                }
                $votingTable->computed_records = Vote::where('voting_table_id', $votingTable->id)
                    ->where('election_type_id', $electionTypeId)
                    ->where('quantity', '>', 0)
                    ->count();

                if ($votingTable->status === 'pendiente') {
                    $votingTable->status = 'en_proceso';
                }

                if ($closeTable) {
                    $votingTable->status = 'cerrado';
                }

                $votingTable->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $closeTable ? 'Votos registrados y mesa cerrada exitosamente' : 'Votos registrados exitosamente',
                    'table_status' => $votingTable->status
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

                        if ($votingTable->status === 'cerrado' && !$closeAll) {
                            continue;
                        }

                        $totalVotes = array_sum($votesData);
                        if ($votingTable->registered_citizens && $totalVotes > $votingTable->registered_citizens) {
                            $errors[] = "Mesa {$votingTable->code}: votos exceden ciudadanos registrados";
                            continue;
                        }

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
                                        'verified_at' => now(),
                                        'vote_status' => 'verified'
                                    ]
                                );
                            } else {
                                Vote::where('voting_table_id', $votingTable->id)
                                    ->where('candidate_id', $candidateId)
                                    ->where('election_type_id', $electionTypeId)
                                    ->delete();
                            }
                        }

                        if ($votingTable->status === 'pendiente') {
                            $votingTable->status = 'en_proceso';
                        }

                        if ($closeAll && $votingTable->status !== 'cerrado') {
                            $votingTable->status = 'cerrado';
                        }

                        $votingTable->save();
                        $processedTables++;

                    } catch (\Exception $e) {
                        $errors[] = "Error en mesa {$tableId}: " . $e->getMessage();
                    }
                }

                DB::commit();

                $message = "Se procesaron $processedTables mesas";
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

    private function calculateTotals($votingTables, $candidates, $electionTypeId)
    {
        $totals = [
            'registered' => 0,
            'voted' => 0,
            'computed' => 0,
            'by_candidate' => []
        ];

        foreach ($candidates as $candidate) {
            $totals['by_candidate'][$candidate->id] = 0;
        }

        foreach ($votingTables as $table) {
            $totals['registered'] += $table->registered_citizens ?? 0;
            $totals['voted'] += $table->voted_citizens ?? 0;
            
            foreach ($table->votes as $vote) {
                if (isset($totals['by_candidate'][$vote->candidate_id])) {
                    $totals['by_candidate'][$vote->candidate_id] += $vote->quantity;
                    $totals['computed'] += $vote->quantity;
                }
            }
        }

        return $totals;
    }
    public function validateTable(Request $request, $tableId)
    {
        try {
            $validated = $request->validate([
                'action' => 'required|in:review,validate,approve,reject',
                'notes' => 'nullable|string|max:500',
            ]);

            $votingTable = VotingTable::findOrFail($tableId);

            DB::beginTransaction();

            switch ($validated['action']) {
                case 'review':
                    $votingTable->validation_status = 'reviewed';
                    $votingTable->verified_by = Auth::id();
                    $votingTable->verified_at = now();
                    break;
                    
                case 'validate':
                    $votingTable->validation_status = 'validated';
                    $votingTable->validated_by = Auth::id();
                    $votingTable->validated_at = now();
                    break;
                    
                case 'approve':
                    $votingTable->validation_status = 'approved';
                    $votingTable->approved_by = Auth::id();
                    $votingTable->approved_at = now();
                    break;
                    
                case 'reject':
                    $votingTable->validation_status = 'rejected';
                    $votingTable->rejected_by = Auth::id();
                    $votingTable->rejected_at = now();
                    $votingTable->status = 'observado';
                    break;
            }

            $votingTable->save();
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $validated['action'],
                'model_type' => 'VotingTable',
                'model_id' => $votingTable->id,
                'notes' => $validated['notes'],
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
}