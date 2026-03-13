<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Candidate;
use App\Models\Dashboard;
use App\Models\VotingTable;
use App\Models\VotingTableElection;
use App\Models\VotingTableCategoryResult;
use App\Models\ElectionType;
use App\Models\ElectionTypeCategory;
use App\Models\Institution;
use App\Models\Observation;
use App\Models\ValidationHistory;
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
    }

    private function resolvePermissions(?VotingTable $table = null): array
    {
        $user    = Auth::user();
        $isAdmin = $user->roles()->where('name', 'administrador')->exists();

        if ($isAdmin) {
            return array_fill_keys([
                'can_view', 'can_register', 'can_observe', 'can_review',
                'can_correct', 'can_validate', 'can_close', 'can_reopen',
                'can_upload_acta',
            ], true);
        }

        $check = fn(string $perm) => $this->userCanOnTable($user, $perm, $table);

        return [
            'can_view'        => $check('view_votes'),
            'can_register'    => $check('register_votes'),
            'can_observe'     => $check('observe_votes'),
            'can_review'      => $check('review_votes'),
            'can_correct'     => $check('correct_votes'),
            'can_validate'    => $check('validate_votes'),
            'can_close'       => $check('close_table'),
            'can_reopen'      => $check('reopen_table'),
            'can_upload_acta' => $check('upload_acta'),
        ];
    }

    private function userCanOnTable($user, string $permission, ?VotingTable $table): bool
    {
        if ($user->permissions()
            ->where('name', $permission)
            ->where('scope', 'global')
            ->exists()) {
            return true;
        }

        $roles = $user->roles()->with('permissions')->get();

        foreach ($roles as $role) {
            if (! $role->permissions->contains('name', $permission)) {
                continue;
            }
            $pivot = $role->pivot;
            if ($pivot->scope === 'global') {
                return true;
            }
            if ($table === null) {
                return true;
            }
            if ($pivot->scope === 'recinto'
                && (int) $pivot->institution_id === (int) $table->institution_id) {
                return true;
            }
            if ($pivot->scope === 'mesa'
                && (int) $pivot->voting_table_id === (int) $table->id) {
                return true;
            }
        }

        return false;
    }

    private function scopedTableQuery()
    {
        $user    = Auth::user();
        $isAdmin = $user->roles()->where('name', 'administrador')->exists();

        if ($isAdmin) {
            return VotingTable::query();
        }

        $roles = $user->roles()->get();

        $institutionIds = $roles
            ->where('pivot.scope', 'recinto')
            ->pluck('pivot.institution_id')
            ->filter()->values()->toArray();

        $tableIds = $roles
            ->where('pivot.scope', 'mesa')
            ->pluck('pivot.voting_table_id')
            ->filter()->values()->toArray();

        if (! empty($tableIds)) {
            return VotingTable::whereIn('id', $tableIds);
        }
        if (! empty($institutionIds)) {
            return VotingTable::whereIn('institution_id', $institutionIds);
        }

        return VotingTable::whereRaw('1 = 0');
    }

    private function resolveElectionTypeId(?int $requested): ?int
    {
        if ($requested) {
            return $requested;
        }
        $dashboard = Dashboard::find(1);
        if ($dashboard?->default_election_type_id) {
            return $dashboard->default_election_type_id;
        }
        return ElectionType::where('active', true)
            ->orderBy('election_date', 'desc')
            ->value('id');
    }

    public function index(Request $request)
    {
        try {
            $user    = Auth::user();
            $isAdmin = $user->roles()->where('name', 'administrador')->exists();
            if (! $isAdmin && ! $this->userCanOnTable($user, 'view_votes', null)) {
                abort(403, 'No tiene permiso para ver votos');
            }
            $electionTypeId = $this->resolveElectionTypeId(
                $request->filled('election_type_id') ? (int) $request->input('election_type_id') : null
            );
            $electionType = ElectionType::with('typeCategories.electionCategory')
                ->find($electionTypeId);
            $typeCategories       = collect();
            $candidatesByCategory = [];
            $typeCategoryIds      = [];
            if ($electionType) {
                $typeCategories = ElectionTypeCategory::where('election_type_id', $electionTypeId)
                    ->with('electionCategory')
                    ->orderBy('ballot_order')
                    ->get();
                foreach ($typeCategories as $tc) {
                    $code                        = $tc->electionCategory->code;
                    $typeCategoryIds[]           = $tc->id;
                    $candidatesByCategory[$code] = Candidate::where('election_type_category_id', $tc->id)
                        ->where('active', true)
                        ->orderBy('list_order')
                        ->orderBy('name')
                        ->get();
                }
            }
            $institutionId   = $request->input('institution_id');
            $status          = $request->input('status');
            $tableNumber     = $request->input('table_number');
            $tableCode       = $request->input('table_code');
            $tableType       = $request->input('table_type');
            $fromName        = $request->input('from_name');
            $toName          = $request->input('to_name');
            $minVotes        = $request->input('min_votes');
            $maxVotes        = $request->input('max_votes');
            $hasObservations = $request->input('has_observations');
            $sortBy          = $request->input('sort_by', 'number');
            $sortDir         = $request->input('sort_direction', 'asc');
            $query = $this->scopedTableQuery()->with([
                'institution:id,name,code',
                'elections'       => fn($q) => $q->where('election_type_id', $electionTypeId),
                'categoryResults' => fn($q) => $q
                    ->whereIn('election_type_category_id', $typeCategoryIds)
                    ->with('electionTypeCategory.electionCategory'),
                'votes'           => fn($q) => $q->where('election_type_id', $electionTypeId),
            ])->withCount([
                'observations as observations_count' => fn($q) => $q->where('status', 'pending'),
            ]);
            if ($institutionId) {
                $query->where('institution_id', $institutionId);
            }
            if ($tableNumber) {
                $query->where('number', $tableNumber);
            }
            if ($tableCode) {
                $query->where(fn($q) => $q
                    ->where('oep_code', 'ilike', "%{$tableCode}%")
                    ->orWhere('internal_code', 'ilike', "%{$tableCode}%")
                );
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
            if ($status) {
                $query->whereHas('elections', fn($q) => $q
                    ->where('election_type_id', $electionTypeId)
                    ->where('status', $status)
                );
            }
            if ($minVotes) {
                $query->whereHas('elections', fn($q) => $q
                    ->where('election_type_id', $electionTypeId)
                    ->where('total_voters', '>=', $minVotes)
                );
            }
            if ($maxVotes) {
                $query->whereHas('elections', fn($q) => $q
                    ->where('election_type_id', $electionTypeId)
                    ->where('total_voters', '<=', $maxVotes)
                );
            }
            if ($hasObservations === 'true' || $hasObservations === '1') {
                $query->has('observations');
            } elseif ($hasObservations === 'false' || $hasObservations === '0') {
                $query->doesntHave('observations');
            }

            match ($sortBy) {
                'expected_voters' => $query->orderBy('expected_voters', $sortDir),
                'institution'     => $query->orderBy('institution_id', $sortDir)->orderBy('number'),
                'status'          => $query->orderBy('institution_id')->orderBy('number'),
                default           => $query->orderBy('institution_id')->orderBy('number', $sortDir),
            };
            $votingTables = $query->paginate(20)->withQueryString();
            $votingTables->getCollection()->transform(function (VotingTable $table) {
                $te = $table->elections->first();
                $table->current_status   = $te?->status ?? 'sin_configurar';
                $table->total_voters     = $te?->total_voters ?? 0;
                $table->ballots_received = $te?->ballots_received ?? 0;
                $table->ballots_used     = $te?->ballots_used ?? 0;
                $table->ballots_leftover = $te?->ballots_leftover ?? 0;
                $table->ballots_spoiled  = $te?->ballots_spoiled ?? 0;
                $byCategory = [];
                foreach ($table->categoryResults as $r) {
                    $code             = $r->electionTypeCategory->electionCategory->code;
                    $byCategory[$code] = [
                        'valid_votes'   => $r->valid_votes,
                        'blank_votes'   => $r->blank_votes,
                        'null_votes'    => $r->null_votes,
                        'total_votes'   => $r->total_votes,
                        'is_consistent' => (bool) $r->is_consistent,
                        'status'        => $r->status,
                    ];
                }
                $table->results_by_category = $byCategory;
                return $table;
            });
            $user    = Auth::user();
            $isAdmin = $user->roles()->where('name', 'administrador')->exists();
            $institutionsQuery = Institution::where('status', 'activo');
            if (! $isAdmin) {
                $assignedIds = $user->roles()
                    ->wherePivot('scope', 'recinto')
                    ->get()
                    ->pluck('pivot.institution_id')
                    ->filter()->values()->toArray();
                if (! empty($assignedIds)) {
                    $institutionsQuery->whereIn('id', $assignedIds);
                }
            }
            $institutions = $institutionsQuery->orderBy('name')->get(['id', 'name', 'code']);
            $electionTypes = ElectionType::where('active', true)
                ->orderBy('election_date', 'desc')
                ->get(['id', 'name', 'election_date']);
            $dashboard = Dashboard::find(1);
            $totals = [
                'expected'      => $votingTables->sum('expected_voters'),
                'total'         => $votingTables->sum('total_voters'),
                'participation' => 0,
            ];
            if ($totals['expected'] > 0) {
                $totals['participation'] = round(($totals['total'] / $totals['expected']) * 100, 1);
            }
            foreach ($typeCategories as $tc) {
                $code                  = $tc->electionCategory->code;
                $totals['by_category'][$code] = $votingTables->sum(
                    fn($t) => $t->results_by_category[$code]['valid_votes'] ?? 0
                );
            }
            $allStatuses = array_keys(VotingTableElection::getStatuses());
            $tableStats  = ['total' => $votingTables->total()];
            foreach ($allStatuses as $s) {
                $tableStats[$s] = $votingTables->getCollection()
                    ->where('current_status', $s)->count();
            }
            $permissions      = $this->resolvePermissions(null);
            $statusLabels     = VotingTableElection::getStatuses();
            $validationLabels = Vote::getVoteStatuses();
            return view('voting-table-votes.index', compact(
                'votingTables',
                'typeCategories',
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
                'dashboard',
                'request'
            ));
        } catch (\Exception $e) {
            Log::error('VotingTableVoteController@index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Error al cargar los datos de votación: ' . $e->getMessage());
        }
    }

    public function registerVotes(Request $request)
    {
        try {
            $validated = $request->validate([
                'voting_table_id'   => 'required|integer|exists:voting_tables,id',
                'election_type_id'  => 'required|integer|exists:election_types,id',
                'votes'             => 'required|array',
                'votes.*'           => 'integer|min:0',
                'blank_votes'       => 'nullable|array',
                'blank_votes.*'     => 'integer|min:0',
                'null_votes'        => 'nullable|array',
                'null_votes.*'      => 'integer|min:0',
                // ── Ballot / ánfora data (from the physical acta) ──
                'ballots_received'  => 'nullable|integer|min:0',  // papeletas recibidas
                'ballots_leftover'  => 'nullable|integer|min:0',  // papeletas no utilizadas
                'ballots_spoiled'   => 'nullable|integer|min:0',  // papeletas deterioradas
            ]);

            $user            = Auth::user();
            $tableId         = $validated['voting_table_id'];
            $electionTypeId  = $validated['election_type_id'];
            $votesData       = $validated['votes'];
            $blankData       = $validated['blank_votes'] ?? [];
            $nullData        = $validated['null_votes']  ?? [];
            $ballotsReceived = isset($validated['ballots_received']) ? (int) $validated['ballots_received'] : null;
            $ballotsLeftover = isset($validated['ballots_leftover']) ? (int) $validated['ballots_leftover'] : null;
            $ballotsSpoiled  = isset($validated['ballots_spoiled'])  ? (int) $validated['ballots_spoiled']  : null;

            DB::beginTransaction();

            $votingTable  = VotingTable::lockForUpdate()->findOrFail($tableId);
            $perms        = $this->resolvePermissions($votingTable);

            if (! $perms['can_register']) {
                throw new \Exception('No tiene permiso para registrar votos en esta mesa');
            }

            $tableElection = VotingTableElection::firstOrCreate(
                ['voting_table_id' => $tableId, 'election_type_id' => $electionTypeId],
                [
                    'ballots_received' => 0, 'ballots_used' => 0,
                    'ballots_leftover' => 0, 'ballots_spoiled' => 0,
                    'total_voters'     => 0,
                    'status'           => VotingTableElection::STATUS_CONFIGURADA,
                    'election_date'    => now()->toDateString(),
                ]
            );

            $blocked = [
                VotingTableElection::STATUS_ESCRUTADA,
                VotingTableElection::STATUS_TRANSMITIDA,
                VotingTableElection::STATUS_ANULADA,
            ];
            if (in_array($tableElection->status, $blocked)) {
                throw new \Exception("No se pueden modificar votos de una mesa {$tableElection->status}");
            }

            $typeCategories = ElectionTypeCategory::where('election_type_id', $electionTypeId)
                ->with('electionCategory')
                ->get()
                ->keyBy(fn($tc) => $tc->electionCategory->code);

            $candidateIds = array_map('intval', array_keys($votesData));
            $candidates   = Candidate::whereIn('id', $candidateIds)
                ->where('active', true)
                ->with('electionTypeCategory.electionCategory')
                ->get()
                ->keyBy('id');

            $missing = array_diff($candidateIds, $candidates->keys()->toArray());
            if (! empty($missing)) {
                throw new \Exception('Candidatos no válidos: ' . implode(', ', $missing));
            }

            // ── Save individual candidate votes ───────────────────────────
            foreach ($votesData as $candidateId => $quantity) {
                $quantity  = max(0, (int) $quantity);
                $candidate = $candidates[(int) $candidateId];

                if ($quantity > 0) {
                    Vote::updateOrCreate(
                        [
                            'voting_table_id'           => $tableId,
                            'candidate_id'              => $candidateId,
                            'election_type_id'          => $electionTypeId,
                            'election_type_category_id' => $candidate->election_type_category_id,
                        ],
                        [
                            'quantity'      => $quantity,
                            'user_id'       => $user->id,
                            'registered_at' => now(),
                            'vote_status'   => Vote::VOTE_STATUS_PENDING_REVIEW,
                        ]
                    );
                } else {
                    Vote::where('voting_table_id', $tableId)
                        ->where('candidate_id', $candidateId)
                        ->where('election_type_id', $electionTypeId)
                        ->delete();
                }
            }

            // ── Compute per-category totals and save category results ─────
            $categoryTotals = [];
            foreach ($typeCategories as $code => $tc) {
                $validVotes = Vote::where('voting_table_id', $tableId)
                    ->where('election_type_category_id', $tc->id)
                    ->where('election_type_id', $electionTypeId)
                    ->sum('quantity');

                $blankVotes = max(0, (int) ($blankData[$code] ?? 0));
                $nullVotes  = max(0, (int) ($nullData[$code]  ?? 0));
                $totalVotes = $validVotes + $blankVotes + $nullVotes;

                $result = VotingTableCategoryResult::firstOrCreate(
                    ['voting_table_id' => $tableId, 'election_type_category_id' => $tc->id],
                    ['status' => 'pending']
                );
                $result->valid_votes = $validVotes;
                $result->blank_votes = $blankVotes;
                $result->null_votes  = $nullVotes;
                $result->total_votes = $totalVotes;
                $result->entered_by  = $user->id;
                $result->entered_at  = now();
                $result->status      = VotingTableCategoryResult::STATUS_ENTERED;
                $result->save();
                $result->checkConsistency();

                $categoryTotals[$code] = $totalVotes;
            }

            // ── Cross-category consistency: all categories must have the
            //    same total (each voter casts one ballot per category) ──────
            $counts = array_values($categoryTotals);
            $first  = $counts[0] ?? 0;   // papeletas en ánfora / total voters
            foreach ($categoryTotals as $code => $total) {
                if ($total !== $first) {
                    throw new \Exception(
                        "Inconsistencia entre categorías: {$code} tiene {$total} votos " .
                        "pero se esperaban {$first}. Todas las categorías deben tener el mismo total."
                    );
                }
            }

            // ── Validate against registered voters (ceiling check) ────────
            // "Papeletas en ánfora" can be LESS than "electores habilitados"
            // (some registered voters may not have voted), but NEVER more.
            $expectedVoters = $votingTable->expected_voters ?? null;
            if ($expectedVoters && $first > $expectedVoters) {
                throw new \Exception(
                    "Las papeletas en ánfora ({$first}) exceden los electores habilitados ({$expectedVoters}). " .
                    "Verifique los votos ingresados."
                );
            }

            // ── Validate ballot accounting (if ballot data was provided) ──
            //    papeletas en ánfora + no utilizadas + deterioradas = recibidas
            if ($ballotsReceived !== null) {
                $ballotsAccounted = $first
                    + ($ballotsLeftover ?? 0)
                    + ($ballotsSpoiled  ?? 0);

                if ($ballotsAccounted !== $ballotsReceived) {
                    throw new \Exception(
                        "Las papeletas no cuadran: " .
                        "en ánfora ({$first}) + no utilizadas (" . ($ballotsLeftover ?? 0) . ") " .
                        "+ deterioradas (" . ($ballotsSpoiled ?? 0) . ") = {$ballotsAccounted} " .
                        "≠ recibidas ({$ballotsReceived})."
                    );
                }

                // Also ensure received ≤ expected (received can't exceed the roll)
                if ($expectedVoters && $ballotsReceived > $expectedVoters) {
                    throw new \Exception(
                        "Las papeletas recibidas ({$ballotsReceived}) exceden los electores habilitados ({$expectedVoters})."
                    );
                }
            }

            // ── Update VotingTableElection ────────────────────────────────
            if (in_array($tableElection->status, [
                VotingTableElection::STATUS_CONFIGURADA,
                VotingTableElection::STATUS_EN_ESPERA,
            ])) {
                $tableElection->status = VotingTableElection::STATUS_VOTACION;
            }

            $tableElection->total_voters = $first;          // papeletas en ánfora
            $tableElection->ballots_used = $first;          // same as ánfora

            if ($ballotsReceived !== null) {
                $tableElection->ballots_received = $ballotsReceived;
            }
            if ($ballotsLeftover !== null) {
                $tableElection->ballots_leftover = $ballotsLeftover;
            }
            if ($ballotsSpoiled !== null) {
                $tableElection->ballots_spoiled = $ballotsSpoiled;
            }

            $tableElection->save();

            DB::commit();

            return response()->json([
                'success'          => true,
                'message'          => '✅ Votos registrados exitosamente',
                'table_status'     => $tableElection->status,
                'category_totals'  => $categoryTotals,
                'total_voters'     => $first,               // papeletas en ánfora
                'ballots_received' => $tableElection->ballots_received,
                'ballots_leftover' => $tableElection->ballots_leftover,
                'ballots_spoiled'  => $tableElection->ballots_spoiled,
            ]);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('registerVotes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reviewTable(Request $request, int $tableId)
    {
        try {
            $validated = $request->validate([
                'election_type_id'    => 'required|exists:election_types,id',
                'observed_vote_ids'   => 'nullable|array',
                'observed_vote_ids.*' => 'integer|exists:votes,id',
                'observation_type'    => 'nullable|string',
                'observation_notes'   => 'nullable|string|max:1000',
                'general_observation' => 'nullable|string|max:1000',
            ]);
            $table = VotingTable::findOrFail($tableId);
            $perms = $this->resolvePermissions($table);
            if (! $perms['can_review']) {
                return response()->json(['success' => false, 'message' => 'Sin permiso para revisar esta mesa'], 403);
            }
            $te = VotingTableElection::where('voting_table_id', $tableId)
                ->where('election_type_id', $validated['election_type_id'])
                ->firstOrFail();
            DB::beginTransaction();
            $user           = Auth::user();
            $electionTypeId = (int) $validated['election_type_id'];
            $observedIds    = $validated['observed_vote_ids'] ?? [];
            $hasObserved    = ! empty($observedIds) || ! empty($validated['general_observation']);
            if ($hasObserved) {
                $observation = Observation::create([
                    'code'             => Observation::generateCode(),
                    'type'             => $validated['observation_type'] ?? Observation::TYPE_VOTOS_INCONSISTENTES,
                    'description'      => $validated['observation_notes'] ?? $validated['general_observation'] ?? 'Revisión con observaciones',
                    'severity'         => Observation::SEVERITY_WARNING,
                    'status'           => Observation::STATUS_PENDING,
                    'voting_table_id'  => $tableId,
                    'election_type_id' => $electionTypeId,
                    'reviewed_by'      => $user->id,
                    'reviewer_role'    => $this->safeReviewerRole($user),
                ]);
                foreach ($observedIds as $voteId) {
                    $vote = Vote::find($voteId);
                    if (! $vote || $vote->voting_table_id != $tableId) {
                        continue;
                    }
                    $vote->markAsObserved($user->id, $observation->id, $validated['observation_notes'] ?? null);
                }
                $te->markAsObserved($user->id, $validated['observation_notes'] ?? $validated['general_observation'] ?? null);
            } else {
                $votes = Vote::where('voting_table_id', $tableId)
                    ->where('election_type_id', $electionTypeId)
                    ->where('vote_status', Vote::VOTE_STATUS_PENDING_REVIEW)
                    ->get();
                foreach ($votes as $vote) {
                    $vote->markAsReviewed($user->id);
                }
                VotingTableCategoryResult::where('voting_table_id', $tableId)
                    ->whereHas('electionTypeCategory', fn($q) => $q->where('election_type_id', $electionTypeId))
                    ->where('status', VotingTableCategoryResult::STATUS_ENTERED)
                    ->update(['status' => VotingTableCategoryResult::STATUS_REVIEWED]);
                $te->startEscrutinio($user->id);
            }
            DB::commit();
            return response()->json([
                'success'          => true,
                'message'          => $hasObserved
                    ? '⚠️ Mesa marcada como observada. Se creó la observación #' . ($observation->code ?? '')
                    : '✅ Mesa revisada correctamente. Pasó a estado En Escrutinio.',
                'table_status'     => $te->fresh()->status,
                'has_observations' => $hasObserved,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('reviewTable: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function correctTable(Request $request, int $tableId)
    {
        try {
            $validated = $request->validate([
                'election_type_id' => 'required|exists:election_types,id',
                'corrections'      => 'required|array',
                'corrections.*'    => 'integer|min:0',
                'blank_votes'      => 'nullable|array',
                'blank_votes.*'    => 'integer|min:0',
                'null_votes'       => 'nullable|array',
                'null_votes.*'     => 'integer|min:0',
                'notes'            => 'required|string|max:1000',
            ]);
            $table = VotingTable::findOrFail($tableId);
            $perms = $this->resolvePermissions($table);
            if (! $perms['can_correct']) {
                return response()->json(['success' => false, 'message' => 'Sin permiso para corregir esta mesa'], 403);
            }
            $te = VotingTableElection::where('voting_table_id', $tableId)
                ->where('election_type_id', $validated['election_type_id'])
                ->firstOrFail();
            $correctable = [
                VotingTableElection::STATUS_OBSERVADA,
                VotingTableElection::STATUS_EN_ESCRUTINIO,
            ];
            if (! in_array($te->status, $correctable)) {
                return response()->json([
                    'success' => false,
                    'message' => "La mesa no puede ser corregida en estado '{$te->status}'",
                ], 422);
            }
            DB::beginTransaction();
            $user           = Auth::user();
            $electionTypeId = (int) $validated['election_type_id'];
            foreach ($validated['corrections'] as $voteId => $newQty) {
                $vote = Vote::find((int) $voteId);
                if (! $vote || $vote->voting_table_id != $tableId) {
                    continue;
                }
                $vote->markAsCorrected($user->id, max(0, (int) $newQty), $validated['notes']);
            }
            $typeCategories = ElectionTypeCategory::where('election_type_id', $electionTypeId)
                ->with('electionCategory')
                ->get();
            foreach ($typeCategories as $tc) {
                $code   = $tc->electionCategory->code;
                $result = VotingTableCategoryResult::where('voting_table_id', $tableId)
                    ->where('election_type_category_id', $tc->id)
                    ->first();
                if (! $result) {
                    continue;
                }
                if (array_key_exists($code, $validated['blank_votes'] ?? [])) {
                    $result->blank_votes = max(0, (int) $validated['blank_votes'][$code]);
                }
                if (array_key_exists($code, $validated['null_votes'] ?? [])) {
                    $result->null_votes = max(0, (int) $validated['null_votes'][$code]);
                }
                $result->valid_votes = Vote::where('voting_table_id', $tableId)
                    ->where('election_type_category_id', $tc->id)
                    ->sum('quantity');
                $result->total_votes = $result->valid_votes + $result->blank_votes + $result->null_votes;
                $result->status      = VotingTableCategoryResult::STATUS_CORRECTED;
                $result->save();
                $result->checkConsistency();
            }
            Observation::where('voting_table_id', $tableId)
                ->where('election_type_id', $electionTypeId)
                ->where('status', Observation::STATUS_PENDING)
                ->get()
                ->each(fn($obs) => $obs->resolve($user->id, Observation::RESOLUTION_CORRECCION, $validated['notes']));
            $te->markAsCorrected($user->id, $validated['notes']);
            DB::commit();
            return response()->json(['success' => true, 'message' => '✅ Correcciones aplicadas. Mesa pasó a En Escrutinio.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('correctTable: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function validateTable(Request $request, int $tableId)
    {
        try {
            $validated = $request->validate([
                'election_type_id' => 'required|exists:election_types,id',
                'action'           => 'required|in:validate,escrutar,reject',
                'notes'            => 'nullable|string|max:500',
            ]);

            $table = VotingTable::findOrFail($tableId);
            $perms = $this->resolvePermissions($table);
            if (! $perms['can_validate']) {
                return response()->json(['success' => false, 'message' => 'Sin permiso para validar esta mesa'], 403);
            }
            $te = VotingTableElection::where('voting_table_id', $tableId)
                ->where('election_type_id', $validated['election_type_id'])
                ->firstOrFail();
            $allowedStatuses = match ($validated['action']) {
                'validate' => [
                    VotingTableElection::STATUS_VOTACION,
                    VotingTableElection::STATUS_OBSERVADA,
                ],
                'escrutar' => [
                    VotingTableElection::STATUS_EN_ESCRUTINIO,
                ],
                'reject'   => [
                    VotingTableElection::STATUS_VOTACION,
                    VotingTableElection::STATUS_EN_ESCRUTINIO,
                    VotingTableElection::STATUS_OBSERVADA,
                ],
            };

            if (! in_array($te->status, $allowedStatuses)) {
                $label = $te->getStatusLabelAttribute();
                return response()->json([
                    'success' => false,
                    'message' => "La acción '{$validated['action']}' no está disponible en estado '{$label}'",
                ], 422);
            }
            if ($validated['action'] !== 'reject') {
                $pendingObs = Observation::where('voting_table_id', $tableId)
                    ->where('election_type_id', $validated['election_type_id'])
                    ->where('status', Observation::STATUS_PENDING)
                    ->count();

                if ($pendingObs > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "Hay {$pendingObs} observación(es) pendiente(s). Resuélvalas antes de continuar.",
                    ], 422);
                }
            }
            DB::beginTransaction();
            $user   = Auth::user();
            $action = $validated['action'];
            $notes  = $validated['notes'] ?? null;
            $votesToUpdate = Vote::where('voting_table_id', $tableId)
                ->where('election_type_id', $validated['election_type_id'])
                ->whereNotIn('vote_status', [Vote::VOTE_STATUS_OBSERVED, Vote::VOTE_STATUS_REJECTED])
                ->get();

            foreach ($votesToUpdate as $vote) {
                match ($action) {
                    'validate' => $vote->markAsValidated($user->id, $notes),
                    'escrutar' => $vote->markAsApproved($user->id, $notes),
                    'reject'   => $vote->markAsRejected($user->id, $notes),
                };
            }
            $resultStatus = match ($action) {
                'validate' => VotingTableCategoryResult::STATUS_VALIDATED,
                'escrutar' => VotingTableCategoryResult::STATUS_CLOSED,
                'reject'   => VotingTableCategoryResult::STATUS_OBSERVED,
            };
            VotingTableCategoryResult::where('voting_table_id', $tableId)
                ->whereHas('electionTypeCategory', fn($q) => $q
                    ->where('election_type_id', $validated['election_type_id'])
                )
                ->update(['status' => $resultStatus]);
            match ($action) {
                'validate' => $te->startEscrutinio($user->id),
                'escrutar' => $te->markAsEscrutada($user->id),
                'reject'   => $te->markAsObserved($user->id, $notes),
            };
            DB::commit();
            $messages = [
                'validate' => '✅ Votos validados. Mesa en Escrutinio — lista para escrutar.',
                'escrutar' => '✅ Mesa escrutada exitosamente.',
                'reject'   => '⚠️ Mesa marcada como Observada.',
            ];
            return response()->json([
                'success'      => true,
                'message'      => $messages[$action],
                'table_status' => $te->fresh()->status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('validateTable: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function observeTable(Request $request, int $tableId)
    {
        try {
            $validated = $request->validate([
                'election_type_id' => 'required|exists:election_types,id',
                'type'             => 'nullable|string',
                'notes'            => 'required|string|max:1000',
                'severity'         => 'nullable|in:info,warning,error,critical',
            ]);
            $table = VotingTable::findOrFail($tableId);
            $perms = $this->resolvePermissions($table);
            if (! $perms['can_observe']) {
                return response()->json(['success' => false, 'message' => 'Sin permiso para observar esta mesa'], 403);
            }
            DB::beginTransaction();
            $user = Auth::user();
            $observation = Observation::create([
                'code'             => Observation::generateCode(),
                'type'             => $validated['type'] ?? Observation::TYPE_OTRO,
                'description'      => $validated['notes'],
                'severity'         => $validated['severity'] ?? Observation::SEVERITY_WARNING,
                'status'           => Observation::STATUS_PENDING,
                'voting_table_id'  => $tableId,
                'election_type_id' => (int) $validated['election_type_id'],
                'reviewed_by'      => $user->id,
                'reviewer_role'    => $this->safeReviewerRole($user),
            ]);
            VotingTableElection::where('voting_table_id', $tableId)
                ->where('election_type_id', $validated['election_type_id'])
                ->first()
                ?->markAsObserved($user->id, $validated['notes']);
            DB::commit();
            return response()->json([
                'success'     => true,
                'message'     => '✅ Observación registrada',
                'observation' => ['id' => $observation->id, 'code' => $observation->code],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('observeTable: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function reopenTable(Request $request, int $tableId)
    {
        try {
            $electionTypeId = $request->input('election_type_id');
            if (! $electionTypeId) {
                return response()->json(['success' => false, 'message' => 'Se requiere election_type_id'], 422);
            }
            $table = VotingTable::findOrFail($tableId);
            $perms = $this->resolvePermissions($table);
            if (! $perms['can_reopen']) {
                return response()->json(['success' => false, 'message' => 'Sin permiso para reabrir esta mesa'], 403);
            }
            $te = VotingTableElection::where('voting_table_id', $tableId)
                ->where('election_type_id', $electionTypeId)
                ->firstOrFail();
            if (! in_array($te->status, [
                VotingTableElection::STATUS_OBSERVADA,
                VotingTableElection::STATUS_EN_ESCRUTINIO,
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden reabrir mesas observadas o en escrutinio',
                ], 422);
            }
            DB::beginTransaction();
            $te->reopen(Auth::id());
            DB::commit();
            return response()->json(['success' => true, 'message' => '✅ Mesa reabierta. Pasó a estado Votación.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('reopenTable: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getTableVotes(Request $request, int $tableId)
    {
        try {
            $electionTypeId = $request->query('election_type_id');
            $query = Vote::where('voting_table_id', $tableId)
                ->with(['candidate:id,name,party,color,party_logo,election_type_category_id,photo',
                        'electionTypeCategory.electionCategory']);
            if ($electionTypeId) {
                $query->where('election_type_id', $electionTypeId);
            }
            $votes = $query->get()->map(fn(Vote $v) => [
                'id'              => $v->id,
                'candidate_id'    => $v->candidate_id,
                'candidate_name'  => $v->candidate->name,
                'candidate_party' => $v->candidate->party,
                'candidate_color' => $v->candidate->color,
                'candidate_logo'  => $v->candidate->party_logo_url ?? null,
                'quantity'        => $v->quantity,
                'vote_status'     => $v->vote_status,
                'category_id'     => $v->election_type_category_id,
                'category_code'   => $v->electionTypeCategory?->electionCategory?->code ?? '',
                'category_name'   => $v->electionTypeCategory?->electionCategory?->name ?? '',
            ]);
            return response()->json($votes);
        } catch (\Exception $e) {
            Log::error('getTableVotes: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar votos'], 500);
        }
    }

    public function getTableStats(Request $request, int $tableId)
    {
        try {
            $electionTypeId = $request->query('election_type_id');
            $table = VotingTable::with([
                'institution:id,name,code',
                'elections'      => fn($q) => $q->when($electionTypeId,
                    fn($q2) => $q2->where('election_type_id', $electionTypeId)
                ),
                'categoryResults.electionTypeCategory.electionCategory',
                'observations'   => fn($q) => $q->where('status', 'pending'),
            ])->findOrFail($tableId);
            $te = $table->elections->first();
            return response()->json([
                'table'   => [
                    'id'          => $table->id,
                    'number'      => $table->number,
                    'code'        => $table->full_code,
                    'status'      => $te?->status ?? 'sin_configurar',
                    'institution' => $table->institution->name,
                ],
                'voters'  => [
                    'expected'      => $table->expected_voters,
                    'total'         => $te?->total_voters ?? 0,
                    'participation' => $table->expected_voters > 0 && $te
                        ? round(($te->total_voters / $table->expected_voters) * 100, 1)
                        : 0,
                ],
                'ballots' => [
                    'received' => $te?->ballots_received ?? 0,
                    'used'     => $te?->ballots_used ?? 0,
                    'leftover' => $te?->ballots_leftover ?? 0,
                    'spoiled'  => $te?->ballots_spoiled ?? 0,
                ],
                'observations_count' => $table->observations->count(),
                'category_results'   => $table->categoryResults->map(fn($r) => [
                    'category'      => $r->electionTypeCategory->electionCategory->name,
                    'code'          => $r->electionTypeCategory->electionCategory->code,
                    'valid_votes'   => $r->valid_votes,
                    'blank_votes'   => $r->blank_votes,
                    'null_votes'    => $r->null_votes,
                    'total_votes'   => $r->total_votes,
                    'is_consistent' => (bool) $r->is_consistent,
                    'status'        => $r->status,
                ]),
            ]);

        } catch (\Exception $e) {
            Log::error('getTableStats: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar estadísticas'], 500);
        }
    }

    private function safeReviewerRole($user): string
    {
        foreach (['revisor', 'fiscal', 'notario', 'coordinador'] as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }
        return 'coordinador';
    }
}
