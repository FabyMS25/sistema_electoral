<?php
namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Observation;
use App\Models\User;
use App\Models\ValidationHistory;
use App\Models\Vote;
use App\Models\VotingTable;
use App\Models\VotingTableElection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ObservationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_observations')->only(['index', 'show', 'getByTable', 'getTableObservations', 'getStats']);
        $this->middleware('permission:create_observations')->only(['store']);
        $this->middleware('permission:resolve_observations')->only(['resolve', 'escalate', 'reject']);
    }

    private function getUserRole(): string
    {
        $user = Auth::user();
        $allowed = ["revisor","fiscal","notario","coordinador"];
        foreach ($allowed as $role) {
            if ($user->hasRole($role)) return $role;
        }
        return $user->hasRole("administrador") ? "coordinador" : "revisor";
    }

    private function tableCanBeObserved(VotingTable $table, ?int $electionTypeId): bool
    {
        if (!$electionTypeId) return false;

        $te = VotingTableElection::where('voting_table_id', $table->id)
            ->where('election_type_id', $electionTypeId)
            ->first();

        if (!$te) return false;

        return in_array($te->status, [
            VotingTableElection::STATUS_VOTACION,
            VotingTableElection::STATUS_CERRADA,
            VotingTableElection::STATUS_EN_ESCRUTINIO,
            VotingTableElection::STATUS_OBSERVADA,
        ]);
    }

    public function index(Request $request)
    {
        $query = Observation::with(['votingTable.institution', 'reviewer', 'resolver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('voting_table_id')) {
            $query->where('voting_table_id', $request->voting_table_id);
        }

        $observations = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('observations.index', compact('observations'));
    }

    public function show(Observation $observation)
    {
        $observation->load(['votingTable.institution', 'reviewer', 'resolver']);
        return view('observations.show', compact('observation'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'voting_table_id'  => 'required|exists:voting_tables,id',
                'election_type_id' => 'required|exists:election_types,id',
                'type'             => 'required|in:' . implode(',', array_keys(Observation::getTypes())),
                'description'      => 'required|string|max:1000',
                'severity'         => 'required|in:info,warning,error,critical',
                'candidate_id'     => 'nullable|exists:candidates,id',
                'vote_ids'         => 'nullable|array',
                'vote_ids.*'       => 'exists:votes,id',
                'evidence'         => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            DB::beginTransaction();

            $votingTable    = VotingTable::findOrFail($validated['voting_table_id']);
            $electionTypeId = (int) $validated['election_type_id'];

            if (!$this->tableCanBeObserved($votingTable, $electionTypeId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta mesa no puede ser observada en su estado actual',
                ], 422);
            }
            $evidencePath = null;
            if ($request->hasFile('evidence')) {
                $evidencePath = $request->file('evidence')
                    ->store('observations/' . date('Y/m/d'), 'public');
            }
            $observation = Observation::create([
                'code'             => Observation::generateCode(),
                'type'             => $validated['type'],
                'description'      => $validated['description'],
                'severity'         => $validated['severity'],
                'status'           => Observation::STATUS_PENDING,
                'voting_table_id'  => $validated['voting_table_id'],
                'election_type_id' => $electionTypeId,
                'candidate_id'     => $validated['candidate_id'] ?? null,
                'reviewed_by'      => Auth::id(),
                'reviewer_role'    => $this->getUserRole(),
                'evidence_photo'   => $evidencePath,
            ]);
            if (!empty($validated['vote_ids'])) {
                foreach ($validated['vote_ids'] as $voteId) {
                    $vote = Vote::find($voteId);
                    if ($vote && $vote->voting_table_id == $validated['voting_table_id']) {
                        $vote->markAsObserved(Auth::id(), $observation->id, $validated['description']);
                    }
                }
            }
            VotingTableElection::where('voting_table_id', $validated['voting_table_id'])
                ->where('election_type_id', $electionTypeId)
                ->first()
                ?->markAsObserved(Auth::id(), $validated['description']);

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Observación creada exitosamente',
                'observation' => [
                    'id'         => $observation->id,
                    'code'       => $observation->code,
                    'type'       => $observation->type,
                    'type_label' => Observation::getTypes()[$observation->type] ?? $observation->type,
                    'severity'   => $observation->severity,
                    'description'=> $observation->description,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ObservationController@store: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la observación: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function resolve(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'resolution_type' => 'required|in:correccion,anulacion,rechazo,escalamiento',
                'notes'           => 'required|string|max:1000',
                'corrected_votes' => 'nullable|array',
                'corrected_votes.*'=> 'integer|min:0',
                'escalated_to'    => 'required_if:resolution_type,escalamiento|nullable|exists:users,id',
            ]);

            DB::beginTransaction();

            $observation = Observation::with('votingTable')->findOrFail($id);

            if ($observation->status !== Observation::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta observación ya no está pendiente',
                ], 422);
            }

            match ($validated['resolution_type']) {
                'correccion'   => $this->processCorrection($observation, $validated),
                'anulacion'    => $observation->resolve(Auth::id(), Observation::RESOLUTION_ANULACION, $validated['notes']),
                'escalamiento' => $observation->escalate(Auth::id(), $validated['escalated_to'], $validated['notes']),
                'rechazo'      => $this->processRejection($observation, $validated),
            };

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Observación resuelta exitosamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ObservationController@resolve: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al resolver: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function processCorrection(Observation $observation, array $validated): void
    {
        if (!empty($validated['corrected_votes'])) {
            foreach ($validated['corrected_votes'] as $voteId => $newQty) {
                $vote = Vote::find($voteId);
                if ($vote && $vote->observation_id == $observation->id) {
                    $vote->markAsCorrected(Auth::id(), max(0, (int) $newQty), $validated['notes']);
                }
            }
        }
        $observation->resolve(Auth::id(), Observation::RESOLUTION_CORRECCION, $validated['notes']);
    }

    private function processRejection(Observation $observation, array $validated): void
    {
        Vote::where('observation_id', $observation->id)
            ->update([
                'observation_id' => null,
                'vote_status'    => Vote::VOTE_STATUS_PENDING_REVIEW,
            ]);

        $observation->reject(Auth::id(), $validated['notes']);
    }

    public function getByTable(int $tableId)
    {
        try {
            $observations = Observation::where('voting_table_id', $tableId)
                ->with(['reviewer', 'resolver'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($obs) => [
                    'id'            => $obs->id,
                    'code'          => $obs->code,
                    'type'          => $obs->type,
                    'type_label'    => Observation::getTypes()[$obs->type] ?? $obs->type,
                    'description'   => $obs->description,
                    'severity'      => $obs->severity,
                    'severity_badge'=> $obs->severity_badge,
                    'status'        => $obs->status,
                    'status_badge'  => $obs->status_badge,
                    'reviewer_name' => $obs->reviewer?->name ?? 'N/A',
                    'reviewer_role' => $obs->reviewer_role,
                    'created_at'    => $obs->created_at->format('d/m/Y H:i'),
                    'resolved_at'   => $obs->resolved_at?->format('d/m/Y H:i'),
                    'resolver_name' => $obs->resolver?->name,
                    'resolution_notes' => $obs->resolution_notes,
                    'evidence_url'  => $obs->evidence_photo
                        ? asset('storage/' . $obs->evidence_photo)
                        : null,
                    'votes_count'   => $obs->votes()->count(),
                ]);

            return response()->json($observations);

        } catch (\Exception $e) {
            Log::error('ObservationController@getByTable: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar observaciones'], 500);
        }
    }

    public function getTableObservations(int $tableId)
    {
        return $this->getByTable($tableId);
    }
    public function getStats()
    {
        try {
            $stats = [
                'total'      => Observation::count(),
                'pending'    => Observation::where('status', Observation::STATUS_PENDING)->count(),
                'in_review'  => Observation::where('status', Observation::STATUS_IN_REVIEW)->count(),
                'resolved'   => Observation::where('status', Observation::STATUS_RESOLVED)->count(),
                'rejected'   => Observation::where('status', Observation::STATUS_REJECTED)->count(),
                'escalated'  => Observation::where('is_escalated', true)->count(),
                'by_severity'=> [
                    'info'     => Observation::where('severity', Observation::SEVERITY_INFO)->count(),
                    'warning'  => Observation::where('severity', Observation::SEVERITY_WARNING)->count(),
                    'error'    => Observation::where('severity', Observation::SEVERITY_ERROR)->count(),
                    'critical' => Observation::where('severity', Observation::SEVERITY_CRITICAL)->count(),
                ],
                'by_type' => [],
            ];

            foreach (Observation::getTypes() as $type => $label) {
                $stats['by_type'][$type] = Observation::where('type', $type)->count();
            }

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('ObservationController@getStats: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar estadísticas'], 500);
        }
    }
}
