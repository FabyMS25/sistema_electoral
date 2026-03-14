<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\Locality;
use App\Models\Institution;
use App\Models\VotingTable;
use App\Models\VotingTableCategoryResult;
use App\Models\Candidate;
use App\Models\Vote;
use App\Models\Dashboard;
use App\Models\ElectionType;
use App\Models\ElectionCategory;
use App\Models\ElectionTypeCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function root(Request $request)
    {
        $dashboard = Dashboard::first();
        if (!$dashboard?->is_public && !Auth::check()) {
            return redirect()->route('login');
        }
        $data = $this->buildDashboardData($request, $dashboard);
        return Auth::check() ? view('index', $data) : view('landing', $data);
    }

    public function index(Request $request)
    {
        $dashboard = Dashboard::first();
        if (!$dashboard?->is_public && !Auth::check()) {
            return redirect()->route('login');
        }
        $data = $this->buildDashboardData($request, $dashboard);
        if (Auth::check()) {
            if (view()->exists($request->path())) {
                return view($request->path(), $data);
            }
            return abort(404);
        }
        return view('landing', $data);
    }

    public function toggleDashboardVisibility(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $dashboard = Dashboard::first();
        $dashboard->is_public = !$dashboard->is_public;
        $dashboard->save();
        return response()->json([
            'success'   => true,
            'is_public' => $dashboard->is_public,
            'message'   => 'Estado del dashboard actualizado correctamente.',
        ]);
    }

    /**
     * Called by the AJAX refresh button — returns only the data needed to
     * update counters and charts without a full page reload.
     */
    public function refreshDashboard(Request $request)
    {
        $dashboard = Dashboard::first();
        if (!$dashboard?->is_public && !Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $data = $this->buildDashboardData($request, $dashboard);
        return response()->json([
            'success'            => true,
            'last_updated'       => now()->format('d/m/Y H:i:s'),
            'totalVotes'         => $data['totalVotes'],
            'reportedTables'     => $data['reportedTables'],
            'totalTables'        => $data['totalTables'],
            'progressPercentage' => $data['progressPercentage'],
            'totalBlankVotes'    => $data['totalBlankVotes'],
            'totalNullVotes'     => $data['totalNullVotes'],
            'candidateStats'     => $data['candidateStats'],
        ]);
    }

    public function getDashboardData(Request $request)
    {
        $dashboard = Dashboard::first();
        $data      = $this->buildDashboardData($request, $dashboard);
        return response()->json(array_merge(
            ['success' => true, 'last_updated' => now()->toDateTimeString()],
            $data
        ));
    }


    private function buildDashboardData(Request $request, ?Dashboard $dashboard): array
    {
        $electionTypes       = ElectionType::where('active', true)->get();
        $departments         = Department::all();
        $defaultElectionType = $dashboard?->defaultElectionType ?? $electionTypes->first();
        $defaultDeptId = $dashboard?->default_department_id
            ?? $departments->first()?->id;
        $defaultProvId = $dashboard?->default_province_id
            ?? ($defaultDeptId ? Province::where('department_id', $defaultDeptId)->value('id') : null);
        $defaultMuniId = $dashboard?->default_municipality_id
            ?? ($defaultProvId ? Municipality::where('province_id', $defaultProvId)->value('id') : null);

        $electionTypeId = $request->get('election_type', $defaultElectionType?->id);
        $departmentId   = (int) $request->get('department',   $defaultDeptId);
        $provinceId     = (int) $request->get('province',     $defaultProvId);
        $municipalityId = (int) $request->get('municipality', $defaultMuniId);

        if (!$municipalityId) {
            return $this->emptyData(
                $dashboard, $electionTypes, $departments,
                Province::where('department_id', $departmentId)->get(),
                collect(),
                $departmentId, $provinceId, null
            );
        }

        $municipalityId = (int) $municipalityId;
        $departmentId   = (int) $departmentId;
        $provinceId     = (int) $provinceId;
        $provinces      = Province::where('department_id', $departmentId)->get();
        $municipalities = Municipality::where('province_id', $provinceId)->get();

        $selectedElectionType = ElectionType::find($electionTypeId);
        if (!$selectedElectionType) {
            return $this->emptyData(
                $dashboard, $electionTypes, $departments,
                $provinces, $municipalities,
                $departmentId, $provinceId, $municipalityId
            );
        }

        // ── Voting tables in this municipality for this election ──────────────
        $tableIds = VotingTable::whereHas('institution', function ($q) use ($municipalityId) {
            $q->whereHas('locality', fn($q2) => $q2->where('municipality_id', $municipalityId));
        })->pluck('id');

        $totalTables = $tableIds->count();

        // ── reportedTables: tables that have FINISHED counting (escrutada/transmitida)
        //    for THIS election type ──────────────────────────────────────────────
        $reportedTables = \App\Models\VotingTableElection::whereIn('voting_table_id', $tableIds)
            ->where('election_type_id', $selectedElectionType->id)
            ->whereIn('status', [
                \App\Models\VotingTableElection::STATUS_ESCRUTADA,
                \App\Models\VotingTableElection::STATUS_TRANSMITIDA,
            ])
            ->count();

        $progressPercentage = $totalTables > 0
            ? round(($reportedTables / $totalTables) * 100, 2)
            : 0;

        // ── Per-category stats ────────────────────────────────────────────────
        $typeCategories = ElectionTypeCategory::where('election_type_id', $selectedElectionType->id)
            ->with('electionCategory')
            ->orderBy('ballot_order')
            ->get();

        $categoryStats   = [];
        $totalBlankVotes = 0;
        $totalNullVotes  = 0;

        foreach ($typeCategories as $tc) {
            $cat  = $tc->electionCategory;
            $code = $cat->code;
            $tcId = $tc->id;

            $candidates = Candidate::where('election_type_category_id', $tcId)
                ->where('active', true)
                ->orderBy('list_order')
                ->get();

            // Use Vote table for candidate-level counts
            $votes = Vote::select('candidate_id', DB::raw('SUM(quantity) as total_votes'))
                ->where('election_type_id', $selectedElectionType->id)
                ->where('election_type_category_id', $tcId)
                ->whereIn('voting_table_id', $tableIds)
                ->groupBy('candidate_id')
                ->with('candidate')
                ->orderByDesc('total_votes')
                ->get();

            $totalValidVotes = (int) $votes->sum('total_votes');

            // Blank/null come from VotingTableCategoryResult (entered per acta)
            $specialVotes = VotingTableCategoryResult::where('election_type_category_id', $tcId)
                ->whereIn('voting_table_id', $tableIds)
                ->selectRaw('COALESCE(SUM(blank_votes), 0) as blank, COALESCE(SUM(null_votes), 0) as null_v')
                ->first();

            $catBlank = (int) ($specialVotes->blank  ?? 0);
            $catNull  = (int) ($specialVotes->null_v ?? 0);

            // Total for percentage calculation = valid + blank + null (= papeletas en ánfora)
            $catTotal = $totalValidVotes + $catBlank + $catNull;

            $totalBlankVotes += $catBlank;
            $totalNullVotes  += $catNull;

            $categoryStats[$code] = [
                'category'       => $cat,
                'typeCategoryId' => $tcId,
                'candidates'     => $candidates,
                'stats'          => $this->calculateStats($votes, $catTotal),
                'totalVotes'     => $totalValidVotes,
                'totalBallots'   => $catTotal,   // valid + blank + null = ánfora
                'blankVotes'     => $catBlank,
                'nullVotes'      => $catNull,
            ];
        }

        // ── Active category for display ───────────────────────────────────────
        $defaultCategoryCode = $dashboard?->defaultCategory?->code ?? array_key_first($categoryStats);
        $activeCategoryCode  = $request->get('category', $defaultCategoryCode);
        if (!isset($categoryStats[$activeCategoryCode])) {
            $activeCategoryCode = array_key_first($categoryStats);
        }

        // ── totalVotes = ánfora ballots for the active category ───────────────
        // (all categories share the same ánfora, so any category total is equivalent)
        $totalVotes = $categoryStats[$activeCategoryCode]['totalBallots'] ?? 0;

        // ── Locality breakdown ────────────────────────────────────────────────
        $localityResults = $this->getLocalityResults(
            $selectedElectionType->id, $municipalityId, $typeCategories
        );
        $localityStats = $this->getLocalityStats($municipalityId, $selectedElectionType->id);

        return [
            'dashboard'            => $dashboard,
            'electionTypes'        => $electionTypes,
            'departments'          => $departments,
            'provinces'            => $provinces,
            'municipalities'       => $municipalities,
            'selectedDepartment'   => $departmentId,
            'selectedProvince'     => $provinceId,
            'selectedMunicipality' => $municipalityId,
            'selectedElectionType' => $selectedElectionType,
            'typeCategories'       => $typeCategories,
            'categoryStats'        => $categoryStats,
            'activeCategoryCode'   => $activeCategoryCode,
            'totalTables'          => $totalTables,
            'reportedTables'       => $reportedTables,
            'progressPercentage'   => $progressPercentage,
            'localityResults'      => $localityResults,
            'localityStats'        => $localityStats,
            // Convenience shortcuts kept for backwards-compat with existing partials
            'alcaldeCandidates'    => $categoryStats['ALC']['candidates']  ?? collect(),
            'alcaldeStats'         => $categoryStats['ALC']['stats']        ?? [],
            'concejalCandidates'   => $categoryStats['CON']['candidates']  ?? collect(),
            'concejalStats'        => $categoryStats['CON']['stats']        ?? [],
            'totalVotesAlcalde'    => $categoryStats['ALC']['totalBallots'] ?? 0,
            'totalVotesConcejal'   => $categoryStats['CON']['totalBallots'] ?? 0,
            'totalVotes'           => $totalVotes,
            'candidateStats'       => $categoryStats[$activeCategoryCode]['stats']      ?? [],
            'candidates'           => $categoryStats[$activeCategoryCode]['candidates'] ?? collect(),
            'totalBlankVotes'      => $totalBlankVotes,
            'totalNullVotes'       => $totalNullVotes,
        ];
    }

    // ── Also fix getLocalityStats to accept electionTypeId ────────────────────
    private function getLocalityStats(int $municipalityId, int $electionTypeId = 0)
    {
        return Locality::where('municipality_id', $municipalityId)
            ->withCount([
                'institutions as total_institutions',
                'institutions as total_tables' => fn($q) =>
                    $q->select(DB::raw('COALESCE(SUM(institutions.total_voting_tables), 0)')),
            ])
            ->get()
            ->map(function ($locality) use ($electionTypeId) {
                // Count tables that are escrutada/transmitida for this election type
                $locality->reported_tables = DB::table('voting_table_elections as vte')
                    ->join('voting_tables as vt', 'vte.voting_table_id', '=', 'vt.id')
                    ->join('institutions as inst', 'vt.institution_id', '=', 'inst.id')
                    ->where('inst.locality_id', $locality->id)
                    ->when($electionTypeId, fn($q) => $q->where('vte.election_type_id', $electionTypeId))
                    ->whereIn('vte.status', ['escrutada', 'transmitida'])
                    ->count();

                return $locality;
            });
    }
    private function calculateStats($votes, int $totalVotes): array
    {
        $stats = [];
        $rank  = 1;
        foreach ($votes as $vote) {
            $pct = $totalVotes > 0 ? ($vote->total_votes / $totalVotes) * 100 : 0;
            $stats[$vote->candidate_id] = [
                'votes'      => (int) $vote->total_votes,
                'percentage' => round($pct, 1),
                'rank'       => $rank++,
                'candidate'  => $vote->candidate,
            ];
        }
        uasort($stats, fn($a, $b) => $b['votes'] - $a['votes']);
        return $stats;
    }

    private function getLocalityResults(int $electionTypeId, int $municipalityId, $typeCategories): array
    {
        $localities = Locality::where('municipality_id', $municipalityId)->get();
        $results    = [];

        foreach ($localities as $locality) {
            $tableIds = VotingTable::whereHas('institution', fn($q) =>
                $q->where('locality_id', $locality->id)
            )->pluck('id');
            $specialVotes = VotingTableCategoryResult::whereIn('voting_table_id', $tableIds)
                ->selectRaw('COALESCE(SUM(blank_votes), 0) as blank, COALESCE(SUM(null_votes), 0) as null_v')
                ->first();
            $results[$locality->id] = [
                'name'        => $locality->name,
                'latitude'    => $locality->latitude,
                'longitude'   => $locality->longitude,
                'total_votes' => 0,
                'blank_votes' => (int) ($specialVotes->blank  ?? 0),
                'null_votes'  => (int) ($specialVotes->null_v ?? 0),
                'categories'  => [],
                'total_votes_alcalde'  => 0,
                'total_votes_concejal' => 0,
                'alcalde'              => [],
                'concejal'             => [],
            ];

            foreach ($typeCategories as $tc) {
                $code    = $tc->electionCategory?->code ?? 'UNK';
                $catName = $tc->electionCategory?->name ?? $code;

                $votes = Vote::whereIn('voting_table_id', $tableIds)
                    ->where('election_type_id', $electionTypeId)
                    ->where('election_type_category_id', $tc->id)
                    ->select('candidate_id', DB::raw('SUM(quantity) as total'))
                    ->groupBy('candidate_id')
                    ->with('candidate')
                    ->orderByDesc(DB::raw('SUM(quantity)'))
                    ->get();
                $catTotal = (int) $votes->sum('total');
                $results[$locality->id]['total_votes'] += $catTotal;
                $candidateList = $votes->map(fn($v) => [
                    'id'             => $v->candidate_id,
                    'candidate_name' => $v->candidate?->name ?? '—',
                    'name'           => $v->candidate?->name ?? '—',
                    'party'          => $v->candidate?->party ?? '—',
                    'color'          => $v->candidate?->color ?? '#888',
                    'party_logo'     => $v->candidate?->party_logo,
                    'votes'          => (int) $v->total,
                    'percentage'     => $catTotal > 0 ? round(($v->total / $catTotal) * 100, 1) : 0,
                ])->values()->toArray();
                $results[$locality->id]['categories'][$code] = [
                    'label'       => $catName,
                    'total_votes' => $catTotal,
                    'candidates'  => $candidateList,
                ];
                if ($code === 'ALC') {
                    $results[$locality->id]['total_votes_alcalde'] = $catTotal;
                    $results[$locality->id]['alcalde']             = $candidateList;
                }
                if ($code === 'CON') {
                    $results[$locality->id]['total_votes_concejal'] = $catTotal;
                    $results[$locality->id]['concejal']             = $candidateList;
                }
            }
        }

        return $results;
    }

    private function emptyData($dashboard, $electionTypes, $departments, $provinces, $municipalities, $deptId, $provId, $muniId): array
    {
        return [
            'dashboard'            => $dashboard,
            'electionTypes'        => $electionTypes,
            'departments'          => $departments,
            'provinces'            => $provinces,
            'municipalities'       => $municipalities,
            'selectedDepartment'   => $deptId,
            'selectedProvince'     => $provId,
            'selectedMunicipality' => $muniId,
            'selectedElectionType' => null,
            'typeCategories'       => collect(),
            'categoryStats'        => [],
            'activeCategoryCode'   => null,
            'totalTables'          => 0,
            'reportedTables'       => 0,
            'progressPercentage'   => 0,
            'localityResults'      => [],
            'localityStats'        => collect(),
            'alcaldeCandidates'    => collect(),
            'alcaldeStats'         => [],
            'concejalCandidates'   => collect(),
            'concejalStats'        => [],
            'totalVotesAlcalde'    => 0,
            'totalVotesConcejal'   => 0,
            'totalVotes'           => 0,
            'candidateStats'       => [],
            'candidates'           => collect(),
            'totalBlankVotes'      => 0,
            'totalNullVotes'       => 0,
        ];
    }

    public function getProvinces($departmentId)
    {
        return response()->json(Province::where('department_id', $departmentId)->get());
    }
    public function getMunicipalities($provinceId)
    {
        return response()->json(Municipality::where('province_id', $provinceId)->get());
    }

    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        }
        return redirect()->back();
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'string', 'email', 'unique:users,email,' . $id],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);
        $user = User::findOrFail($id);
        $user->name  = $request->name;
        $user->email = $request->email;
        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $name   = time() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('/images/'), $name);
            $user->avatar = $name;
        }
        $user->save();
        return redirect()->back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return redirect()->back()->with('error', 'Su contraseña actual no coincide.');
        }
        User::findOrFail($id)->update(['password' => Hash::make($request->password)]);
        return redirect()->back()->with('success', 'Contraseña actualizada correctamente.');
    }
}
