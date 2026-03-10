<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\Locality;
use App\Models\Institution;
use App\Models\VotingTable;
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
        if (! $dashboard?->is_public && ! Auth::check()) {
            return redirect()->route('login');
        }

        $data = $this->buildDashboardData($request, $dashboard);

        return Auth::check()
            ? view('index',   $data)
            : view('landing', $data);
    }

    public function index(Request $request)
    {
        $dashboard = Dashboard::first();
        if (! $dashboard?->is_public && ! Auth::check()) {
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
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $dashboard = Dashboard::first();
        $dashboard->is_public = ! $dashboard->is_public;
        $dashboard->save();

        return response()->json([
            'success'   => true,
            'is_public' => $dashboard->is_public,
            'message'   => 'Estado del dashboard actualizado correctamente.',
        ]);
    }

    public function getDashboardData(Request $request)
    {
        $dashboard = Dashboard::first();
        $data      = $this->buildDashboardData($request, $dashboard);
        return response()->json(array_merge(['success' => true, 'last_updated' => now()->toDateTimeString()], $data));
    }

    public function refreshDashboard(Request $request)
    {
        $dashboard = Dashboard::first();
        if (!$dashboard?->is_public && !Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $data = $this->buildDashboardData($request, $dashboard);
        return response()->json([
            'success'      => true,
            'last_updated' => now()->format('d/m/Y H:i:s'),
            'totalVotes'        => $data['totalVotes'],
            'reportedTables'    => $data['reportedTables'],
            'totalTables'       => $data['totalTables'],
            'progressPercentage'=> $data['progressPercentage'],
            'candidateStats'    => $data['candidateStats'],
            'categoryStats'     => $data['categoryStats'],
        ]);
    }

    private function buildDashboardData(Request $request, ?Dashboard $dashboard): array
    {
        $electionTypes  = ElectionType::where('active', true)->get();
        $departments    = Department::all();
        $defaultElectionType = $dashboard?->defaultElectionType
            ?? $electionTypes->first();
        $electionTypeId  = $request->get('election_type', $defaultElectionType?->id);
        $departmentId    = $request->get('department',  2);
        $provinceId      = $request->get('province',   14);
        $municipalityId  = $request->get('municipality', 45);
        $provinces     = Province::where('department_id', $departmentId)->get();
        $municipalities = Municipality::where('province_id', $provinceId)->get();
        $selectedElectionType = ElectionType::find($electionTypeId);
        if (! $selectedElectionType) {
            return $this->emptyData($dashboard, $electionTypes, $departments, $provinces, $municipalities, $departmentId, $provinceId, $municipalityId);
        }
        $typeCategories = ElectionTypeCategory::where('election_type_id', $selectedElectionType->id)
            ->with('electionCategory')
            ->orderBy('ballot_order')
            ->get();
        $categoryStats = [];
        foreach ($typeCategories as $tc) {
            $cat     = $tc->electionCategory;
            $code    = $cat->code;
            $tcId    = $tc->id;
            $candidates = Candidate::where('election_type_category_id', $tcId)
                ->where('active', true)
                ->orderBy('list_order')
                ->get();
            $votes = Vote::select('candidate_id', DB::raw('SUM(quantity) as total_votes'))
                ->where('election_type_id', $selectedElectionType->id)
                ->where('election_type_category_id', $tcId)
                ->whereHas('votingTable.institution.locality', function ($q) use ($municipalityId) {
                    $q->where('municipality_id', $municipalityId);
                })
                ->groupBy('candidate_id')
                ->with('candidate')
                ->orderByDesc('total_votes')
                ->get();
            $totalVotes = (int) $votes->sum('total_votes');
            $categoryStats[$code] = [
                'category'       => $cat,
                'typeCategoryId' => $tcId,
                'candidates'     => $candidates,
                'stats'          => $this->calculateStats($votes, $totalVotes),
                'totalVotes'     => $totalVotes,
            ];
        }
        $defaultCategoryCode = $dashboard?->defaultCategory?->code
            ?? array_key_first($categoryStats);
        $activeCategoryCode = $request->get('category', $defaultCategoryCode);
        if (! isset($categoryStats[$activeCategoryCode])) {
            $activeCategoryCode = array_key_first($categoryStats);
        }
        $totalTables = VotingTable::whereHas('institution.locality', function ($q) use ($municipalityId) {
            $q->where('municipality_id', $municipalityId);
        })->count();
        $reportedTables = Vote::where('election_type_id', $selectedElectionType->id)
            ->whereHas('votingTable.institution.locality', function ($q) use ($municipalityId) {
                $q->where('municipality_id', $municipalityId);
            })
            ->distinct('voting_table_id')
            ->count('voting_table_id');
        $progressPercentage = $totalTables > 0 ? round(($reportedTables / $totalTables) * 100, 2) : 0;
        $localityResults = $this->getLocalityResults($selectedElectionType->id, $municipalityId, $typeCategories);
        $localityStats   = $this->getLocalityStats($municipalityId);
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
            'alcaldeCandidates'    => $categoryStats['ALC']['candidates']  ?? collect(),
            'alcaldeStats'         => $categoryStats['ALC']['stats']        ?? [],
            'concejalCandidates'   => $categoryStats['CON']['candidates']  ?? collect(),
            'concejalStats'        => $categoryStats['CON']['stats']        ?? [],
            'totalVotesAlcalde'    => $categoryStats['ALC']['totalVotes']   ?? 0,
            'totalVotesConcejal'   => $categoryStats['CON']['totalVotes']   ?? 0,
            'totalVotes'           => $categoryStats[$activeCategoryCode]['totalVotes'] ?? 0,
            'candidateStats'       => $categoryStats[$activeCategoryCode]['stats']      ?? [],
            'candidates'           => $categoryStats[$activeCategoryCode]['candidates'] ?? collect(),
            'totalBlankVotes'      => 0,
            'totalNullVotes'       => 0,
        ];
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
        uasort($stats, fn ($a, $b) => $b['votes'] - $a['votes']);
        return $stats;
    }

    private function getLocalityResults(int $electionTypeId, int $municipalityId, $typeCategories): array
    {
        $localities = Locality::where('municipality_id', $municipalityId)->get();
        $results    = [];
        foreach ($localities as $locality) {
            $tableIds = VotingTable::whereHas('institution', function ($q) use ($locality) {
                $q->where('locality_id', $locality->id);
            })->pluck('id');
            $results[$locality->id] = [
                'name'        => $locality->name,
                'latitude'    => $locality->latitude,
                'longitude'   => $locality->longitude,
                'total_votes' => 0,
                'categories'  => [],
            ];
            foreach ($typeCategories as $tc) {
                $code = $tc->electionCategory?->code ?? 'UNK';
                $votes = Vote::whereIn('voting_table_id', $tableIds)
                    ->where('election_type_id', $electionTypeId)
                    ->where('election_type_category_id', $tc->id)
                    ->select('candidate_id', DB::raw('SUM(quantity) as total'))
                    ->groupBy('candidate_id')
                    ->with('candidate')
                    ->get();
                $catTotal = (int) $votes->sum('total');
                $results[$locality->id]['total_votes'] += $catTotal;
                $results[$locality->id]['categories'][$code] = [
                    'label'       => $tc->electionCategory?->name ?? $code,
                    'total_votes' => $catTotal,
                    'candidates'  => $votes->map(fn ($v) => [
                        'id'         => $v->candidate_id,
                        'name'       => $v->candidate?->name ?? '—',
                        'party'      => $v->candidate?->party ?? '—',
                        'color'      => $v->candidate?->color ?? '#888',
                        'party_logo' => $v->candidate?->party_logo,
                        'votes'      => (int) $v->total,
                        'percentage' => $catTotal > 0 ? round(($v->total / $catTotal) * 100, 1) : 0,
                    ])->sortByDesc('votes')->values()->toArray(),
                ];
            }
        }
        return $results;
    }

    private function getLocalityStats(int $municipalityId)
    {
        return Locality::where('municipality_id', $municipalityId)
            ->withCount([
                'institutions as total_institutions',
                'institutions as total_tables' => fn ($q) =>
                    $q->select(DB::raw('COALESCE(SUM(institutions.total_voting_tables), 0)')),
            ])
            ->get()
            ->map(function ($locality) {
                $locality->reported_tables = DB::table('voting_tables')
                    ->join('institutions', 'voting_tables.institution_id', '=', 'institutions.id')
                    ->where('institutions.locality_id', $locality->id)
                    ->whereExists(fn ($q) =>
                        $q->select(DB::raw(1))->from('votes')
                          ->whereRaw('votes.voting_table_id = voting_tables.id')
                    )
                    ->count();
                return $locality;
            });
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
        if (! Hash::check($request->current_password, Auth::user()->password)) {
            return redirect()->back()->with('error', 'Su contraseña actual no coincide.');
        }
        User::findOrFail($id)->update(['password' => Hash::make($request->password)]);
        return redirect()->back()->with('success', 'Contraseña actualizada correctamente.');
    }
}
