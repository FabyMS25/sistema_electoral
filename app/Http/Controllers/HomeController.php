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
use App\Models\Election;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function root(Request $request){
        $dashboard = Dashboard::first();
        if (!$dashboard->is_public && !Auth::check()) {
            return redirect()->route('login');
        }
        
        $electionTypeId = $request->get('election_type');
        $departmentId = $request->get('department', 2);
        $provinceId = $request->get('province', 14);
        $municipalityId = $request->get('municipality', 45);        
        $electionData = $this->getElectionData($electionTypeId, $departmentId, $provinceId, $municipalityId);        
        if (Auth::check()) {
            return view('index', array_merge(compact('dashboard'), $electionData));
        }
        return view('landing', array_merge(compact('dashboard'), $electionData));
    }

    public function index(Request $request){
        $dashboard = Dashboard::first();
        if (!$dashboard->is_public && !Auth::check()) {
            return redirect()->route('login');
        }        
        $electionTypeId = $request->get('election_type');
        $departmentId = $request->get('department', 2);
        $provinceId = $request->get('province', 14);
        $municipalityId = $request->get('municipality', 45);        
        $electionData = $this->getElectionData($electionTypeId, $departmentId, $provinceId, $municipalityId);
        if (Auth::check()) {
            if (view()->exists($request->path())) {
                return view($request->path(), array_merge(compact('dashboard'), $electionData));
            }
            return abort(404);
        }
        return view('landing', array_merge(compact('dashboard'), $electionData));
    }

    public function toggleDashboardVisibility(Request $request){
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $dashboard = Dashboard::first();
        $dashboard->is_public = !$dashboard->is_public;
        $dashboard->save();
        return response()->json([
            'success' => true,
            'is_public' => $dashboard->is_public,
            'message' => 'Dashboard visibility updated successfully'
        ]);
    }

private function getElectionData($electionTypeId = null, $departmentId = 2, $provinceId = 14, $municipalityId = 45) {
    $electionTypes = ElectionType::where('active', true)->get();
    $departments = Department::all();
    $provinces = Province::where('department_id', $departmentId)->get();
    $municipalities = Municipality::where('province_id', $provinceId)->get();    
    if (!$electionTypeId && $electionTypes->count() > 0) {
        $electionTypeId = $electionTypes->first()->id;
    }    
    $selectedElectionType = ElectionType::find($electionTypeId);        
    if (!$selectedElectionType) {
        return [
            'electionTypes' => $electionTypes,
            'departments' => $departments,
            'provinces' => $provinces,
            'municipalities' => $municipalities,
            'selectedDepartment' => $departmentId,
            'selectedProvince' => $provinceId,
            'selectedMunicipality' => $municipalityId,
            'selectedElectionType' => null,
            'candidates' => collect(),
            'candidateStats' => [],
            'totalVotes' => 0,
            'progressPercentage' => 0,
            'totalTables' => 0,
            'reportedTables' => 0,
            'localityResults' => [],
            'localityStats' => []
        ];
    }

    $candidates = Candidate::where('election_type_id', $selectedElectionType->id)
        ->where('active', true)
        ->get();    
    $candidateVotes = Vote::select('candidate_id', DB::raw('SUM(quantity) as total_votes'))
        ->where('election_type_id', $selectedElectionType->id)
        ->whereHas('votingTable.institution.locality', function($query) use ($municipalityId) {
            $query->where('municipality_id', $municipalityId);
        })
        ->groupBy('candidate_id')
        ->with('candidate')
        ->orderByDesc('total_votes')
        ->get();            
    
    $totalVotes = $candidateVotes->sum('total_votes');
    $candidateStats = [];
    $rank = 1;       
    
    foreach ($candidateVotes as $cv) {
        $percentage = $totalVotes > 0 ? ($cv->total_votes / $totalVotes) * 100 : 0;
        $trend = $percentage >= 15 ? 'up' : ($percentage < 5 ? 'down' : 'neutral');
        $candidateStats[$cv->candidate_id] = [
            'votes' => (int)$cv->total_votes,
            'percentage' => round($percentage, 1),
            'trend' => $trend,
            'rank' => $rank++,
            'candidate' => $cv->candidate
        ];
    }
    uasort($candidateStats, function($a, $b) {
        return $b['votes'] - $a['votes'];
    });

    $totalTables = VotingTable::whereHas('institution.locality', function($query) use ($municipalityId) {
            $query->where('municipality_id', $municipalityId);
        })->count();            
    
    $reportedTables = Vote::where('election_type_id', $selectedElectionType->id)
        ->whereHas('votingTable.institution.locality', function($query) use ($municipalityId) {
            $query->where('municipality_id', $municipalityId);
        })
        ->distinct('voting_table_id')
        ->count('voting_table_id');            
    
    $progressPercentage = $totalTables > 0 ? round(($reportedTables / $totalTables) * 100, 2) : 0;

    $localityResults = $this->getLocalityResults($selectedElectionType->id, $municipalityId);
    $localityStats = $this->getLocalityStats($municipalityId);
    
    return [
        'electionTypes' => $electionTypes,
        'departments' => $departments,
        'provinces' => $provinces,
        'municipalities' => $municipalities,
        'selectedDepartment' => $departmentId,
        'selectedProvince' => $provinceId,
        'selectedMunicipality' => $municipalityId,
        'selectedElectionType' => $selectedElectionType,
        'candidates' => $candidates,
        'candidateStats' => $candidateStats,
        'totalVotes' => $totalVotes,
        'progressPercentage' => $progressPercentage,
        'totalTables' => $totalTables,
        'reportedTables' => $reportedTables,
        'localityResults' => $localityResults,
        'localityStats' => $localityStats
    ];
}

    private function getLocalityResults($electionTypeId, $municipalityId) {
        $localityVotes = DB::table('localities')
            ->select(
                'localities.id as locality_id',
                'localities.name as locality_name',
                'localities.latitude',
                'localities.longitude',
                'municipalities.name as municipality_name',
                'candidates.id as candidate_id',
                'candidates.name as candidate_name',
                'candidates.party',
                DB::raw('COALESCE(SUM(votes.quantity), 0) as total_votes')
            )
            ->leftJoin('municipalities', 'localities.municipality_id', '=', 'municipalities.id')
            ->leftJoin('institutions', 'localities.id', '=', 'institutions.locality_id')
            ->leftJoin('voting_tables', 'institutions.id', '=', 'voting_tables.institution_id')
            ->leftJoin('votes', function($join) use ($electionTypeId) {
                $join->on('voting_tables.id', '=', 'votes.voting_table_id')
                     ->where('votes.election_type_id', '=', $electionTypeId);
            })
            ->leftJoin('candidates', function($join) use ($electionTypeId) {
                $join->on('votes.candidate_id', '=', 'candidates.id')
                     ->where('candidates.election_type_id', '=', $electionTypeId);
            })
            ->where('localities.municipality_id', $municipalityId) // Filter by municipality
            ->groupBy(
                'localities.id',
                'localities.name',
                'localities.latitude',
                'localities.longitude',
                'municipalities.name',
                'candidates.id',
                'candidates.name',
                'candidates.party'
            )
            ->orderBy('municipalities.name')
            ->orderBy('localities.name')
            ->orderByDesc('total_votes')
            ->get()
            ->toArray();
        $localityResults = [];
        foreach ($localityVotes as $lv) {
            $totalVotesInt = (int)$lv->total_votes;
            if (!isset($localityResults[$lv->locality_id])) {
                $localityResults[$lv->locality_id] = [
                    'name' => $lv->locality_name,
                    'municipality' => $lv->municipality_name,
                    'latitude' => $lv->latitude,
                    'longitude' => $lv->longitude,
                    'total_votes' => 0,
                    'candidates' => []
                ];
            }
            if ($lv->candidate_id !== null) {
                $localityResults[$lv->locality_id]['candidates'][] = [
                    'id' => $lv->candidate_id,
                    'name' => $lv->candidate_name,
                    'party' => $lv->party,
                    'votes' => $totalVotesInt
                ];
                $localityResults[$lv->locality_id]['total_votes'] += $totalVotesInt;
            }
        }
        
        foreach ($localityResults as &$locality) {
            foreach ($locality['candidates'] as &$candidate) {
                $candidate['percentage'] = $locality['total_votes'] > 0
                    ? round(($candidate['votes'] / $locality['total_votes']) * 100, 1)
                    : 0;
            }
        }        
        return $localityResults;
    }

    private function getLocalityStats($municipalityId) {
        return DB::table('localities')
            ->select(
                'localities.id',
                'localities.name',
                'municipalities.name as municipality_name',
                DB::raw('COUNT(DISTINCT institutions.id) as total_institutions'),
                DB::raw('COUNT(DISTINCT voting_tables.id) as total_tables'),
                DB::raw('COUNT(DISTINCT CASE WHEN votes.id IS NOT NULL THEN voting_tables.id END) as reported_tables')
            )
            ->leftJoin('municipalities', 'localities.municipality_id', '=', 'municipalities.id')
            ->leftJoin('institutions', 'localities.id', '=', 'institutions.locality_id')
            ->leftJoin('voting_tables', 'institutions.id', '=', 'voting_tables.institution_id')
            ->leftJoin('votes', 'voting_tables.id', '=', 'votes.voting_table_id')
            ->where('localities.municipality_id', $municipalityId)
            ->groupBy('localities.id', 'localities.name', 'municipalities.name')
            ->get();
    }

    public function getProvinces($departmentId)
    {
        $provinces = Province::where('department_id', $departmentId)->get();
        return response()->json($provinces);
    }
    public function getMunicipalities($provinceId)
    {
        $municipalities = Municipality::where('province_id', $provinceId)->get();
        return response()->json($municipalities);
    }
    
    public function getDashboardData(Request $request)
    {
        $electionTypeId = $request->get('election_type');
        $departmentId = $request->get('department', 2);
        $provinceId = $request->get('province', 14);
        $municipalityId = $request->get('municipality', 45);
        
        $electionData = $this->getElectionData($electionTypeId, $departmentId, $provinceId, $municipalityId);
        
        return response()->json([
            'success' => true,
            'data' => $electionData,
            'last_updated' => now()->toDateTimeString()
        ]);
    }

    /*Language Translation*/
    public function lang($locale){
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users,email,' . $id],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->get('name');
        $user->email = $request->get('email');
        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar = $avatarName;
        }
        $user->save();
        return redirect()->back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return redirect()->back()->with('error', 'Su contraseña actual no coincide.');
        }
        $user = User::findOrFail($id);
        $user->password = Hash::make($request->get('password'));
        $user->save();
        return redirect()->back()->with('success', 'Contraseña actualizada correctamente.');
    }
}