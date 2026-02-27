<?php
// app/Http/Controllers/VotingTableVoteController.php

namespace App\Http\Controllers;

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
            // Parámetros de filtro
            $institutionId = $request->input('institution_id');
            $electionTypeId = $request->input('election_type_id');
            
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

            // Construir query de mesas
            $query = VotingTable::with([
                'institution:id,name,code',
                'votes' => function($q) use ($electionTypeId) {
                    $q->where('election_type_id', $electionTypeId)
                      ->with('candidate:id,name,party,color');
                }
            ]);

            if ($institutionId) {
                $query->where('institution_id', $institutionId);
            }

            // Solo mesas activas o pendientes (no cerradas)
            $query->whereIn('status', ['pendiente', 'en_proceso', 'activo']);

            $votingTables = $query->orderBy('institution_id')
                ->orderBy('number')
                ->get();

            // Obtener candidatos del tipo de elección
            $candidates = Candidate::where('election_type_id', $electionTypeId)
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'party', 'color', 'photo']);

            // Calcular totales
            $totals = $this->calculateTotals($votingTables, $candidates, $electionTypeId);

            return view('voting-table-votes.index', compact(
                'votingTables',
                'candidates',
                'institutions',
                'electionTypes',
                'institutionId',
                'electionTypeId',
                'totals'
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

                // Verificar si la mesa está cerrada
                if ($votingTable->status === 'cerrado') {
                    throw new \Exception('No se pueden modificar votos de una mesa cerrada');
                }

                // Validar candidatos
                $validCandidateIds = Candidate::where('election_type_id', $electionTypeId)
                    ->where('active', true)
                    ->pluck('id')
                    ->toArray();

                foreach (array_keys($votesData) as $candidateId) {
                    if (!in_array($candidateId, $validCandidateIds)) {
                        throw new \Exception("Candidato inválido para este tipo de elección");
                    }
                }

                // Validar total de votos
                $totalVotes = array_sum($votesData);
                if ($votingTable->registered_citizens && $totalVotes > $votingTable->registered_citizens) {
                    throw new \Exception("El total de votos ($totalVotes) excede los ciudadanos registrados ({$votingTable->registered_citizens})");
                }

                // Registrar votos
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

                // Actualizar estado de la mesa
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
}