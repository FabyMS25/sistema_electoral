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

        // 🔴 CORRECCIÓN: Si no hay tipo de elección seleccionado, devolver valores por defecto
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
                'alcaldeCandidates' => collect(),
                'alcaldeStats' => [],
                'concejalCandidates' => collect(),
                'concejalStats' => [],
                'totalVotesAlcalde' => 0,
                'totalVotesConcejal' => 0,
                'progressPercentage' => 0,
                'totalTables' => 0,
                'reportedTables' => 0,
                'localityResults' => [],
                'localityStats' => collect(),
                // Variables para compatibilidad con el JS
                'totalVotes' => 0,
                'candidateStats' => [],
                'candidates' => collect(),
            ];
        }

        // Obtener las categorías para este tipo de elección
        $alcaldeCategory = ElectionCategory::where('code', 'ALC')->first();
        $concejalCategory = ElectionCategory::where('code', 'CON')->first();

        // Obtener los election_type_category_ids para alcalde y concejal
        $alcaldeTypeCategoryIds = $alcaldeCategory
            ? ElectionTypeCategory::where('election_type_id', $selectedElectionType->id)
                ->where('election_category_id', $alcaldeCategory->id)
                ->pluck('id')
                ->toArray()
            : [];

        $concejalTypeCategoryIds = $concejalCategory
            ? ElectionTypeCategory::where('election_type_id', $selectedElectionType->id)
                ->where('election_category_id', $concejalCategory->id)
                ->pluck('id')
                ->toArray()
            : [];

        // Candidatos a Alcalde
        $alcaldeCandidates = Candidate::whereIn('election_type_category_id', $alcaldeTypeCategoryIds)
            ->where('active', true)
            ->orderBy('list_order')
            ->get();

        // Candidatos a Concejal
        $concejalCandidates = Candidate::whereIn('election_type_category_id', $concejalTypeCategoryIds)
            ->where('active', true)
            ->orderBy('list_order')
            ->get();

        // Votos para Alcalde
        $alcaldeVotes = Vote::select('candidate_id', DB::raw('SUM(quantity) as total_votes'))
            ->where('election_type_id', $selectedElectionType->id)
            ->whereHas('candidate', function($q) use ($alcaldeTypeCategoryIds) {
                $q->whereIn('election_type_category_id', $alcaldeTypeCategoryIds);
            })
            ->whereHas('votingTable.institution.locality', function($query) use ($municipalityId) {
                $query->where('municipality_id', $municipalityId);
            })
            ->groupBy('candidate_id')
            ->with('candidate')
            ->orderByDesc('total_votes')
            ->get();

        // Votos para Concejal
        $concejalVotes = Vote::select('candidate_id', DB::raw('SUM(quantity) as total_votes'))
            ->where('election_type_id', $selectedElectionType->id)
            ->whereHas('candidate', function($q) use ($concejalTypeCategoryIds) {
                $q->whereIn('election_type_category_id', $concejalTypeCategoryIds);
            })
            ->whereHas('votingTable.institution.locality', function($query) use ($municipalityId) {
                $query->where('municipality_id', $municipalityId);
            })
            ->groupBy('candidate_id')
            ->with('candidate')
            ->orderByDesc('total_votes')
            ->get();

        $totalVotesAlcalde = $alcaldeVotes->sum('total_votes');
        $totalVotesConcejal = $concejalVotes->sum('total_votes');

        // Estadísticas Alcalde
        $alcaldeStats = $this->calculateStats($alcaldeVotes, $totalVotesAlcalde);

        // Estadísticas Concejal
        $concejalStats = $this->calculateStats($concejalVotes, $totalVotesConcejal);

        // Totales de mesas
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

        $localityResults = $this->getLocalityResults($selectedElectionType->id, $municipalityId, $alcaldeTypeCategoryIds, $concejalTypeCategoryIds);
        $localityStats = $this->getLocalityStats($municipalityId);

        // Preparar datos para el dashboard (simplificado para mostrar solo Alcaldes por defecto)
        $candidateStats = $alcaldeStats; // Por defecto mostramos Alcaldes
        $candidates = $alcaldeCandidates;
        $totalVotes = $totalVotesAlcalde;

        return [
            'electionTypes' => $electionTypes,
            'departments' => $departments,
            'provinces' => $provinces,
            'municipalities' => $municipalities,
            'selectedDepartment' => $departmentId,
            'selectedProvince' => $provinceId,
            'selectedMunicipality' => $municipalityId,
            'selectedElectionType' => $selectedElectionType,
            'alcaldeCandidates' => $alcaldeCandidates,
            'alcaldeStats' => $alcaldeStats,
            'concejalCandidates' => $concejalCandidates,
            'concejalStats' => $concejalStats,
            'totalVotesAlcalde' => $totalVotesAlcalde,
            'totalVotesConcejal' => $totalVotesConcejal,
            'progressPercentage' => $progressPercentage,
            'totalTables' => $totalTables,
            'reportedTables' => $reportedTables,
            'localityResults' => $localityResults,
            'localityStats' => $localityStats,
            // Variables simplificadas para el JS existente
            'totalVotes' => $totalVotes,
            'candidateStats' => $candidateStats,
            'candidates' => $candidates,
        ];
    }

    private function calculateStats($votes, $totalVotes) {
        $stats = [];
        $rank = 1;

        foreach ($votes as $vote) {
            $percentage = $totalVotes > 0 ? ($vote->total_votes / $totalVotes) * 100 : 0;

            $stats[$vote->candidate_id] = [
                'votes' => (int)$vote->total_votes,
                'percentage' => round($percentage, 1),
                'rank' => $rank++,
                'candidate' => $vote->candidate
            ];
        }

        // Ordenar por votos descendente
        uasort($stats, function($a, $b) {
            return $b['votes'] - $a['votes'];
        });

        return $stats;
    }

    private function getLocalityResults($electionTypeId, $municipalityId, $alcaldeTypeCategoryIds, $concejalTypeCategoryIds) {
        // Obtener todas las localidades del municipio
        $localities = Locality::where('municipality_id', $municipalityId)->get();

        $localityResults = [];

        foreach ($localities as $locality) {
            // Obtener mesas de esta localidad
            $tableIds = VotingTable::whereHas('institution', function($q) use ($locality) {
                $q->where('locality_id', $locality->id);
            })->pluck('id');

            // Votos en esta localidad (todos los candidatos)
            $votes = Vote::whereIn('voting_table_id', $tableIds)
                ->where('election_type_id', $electionTypeId)
                ->select('candidate_id', DB::raw('SUM(quantity) as total'))
                ->groupBy('candidate_id')
                ->with('candidate')
                ->get();

            $totalVotes = $votes->sum('total');

            $localityResults[$locality->id] = [
                'name' => $locality->name,
                'latitude' => $locality->latitude,
                'longitude' => $locality->longitude,
                'total_votes' => $totalVotes,
                'candidates' => []
            ];

            foreach ($votes as $vote) {
                $localityResults[$locality->id]['candidates'][] = [
                    'id' => $vote->candidate_id,
                    'name' => $vote->candidate->name,
                    'party' => $vote->candidate->party,
                    'party_logo' => $vote->candidate->party_logo,
                    'votes' => $vote->total,
                    'percentage' => $totalVotes > 0 ? round(($vote->total / $totalVotes) * 100, 1) : 0
                ];
            }

            // Ordenar por votos
            usort($localityResults[$locality->id]['candidates'], function($a, $b) {
                return $b['votes'] - $a['votes'];
            });
        }

        return $localityResults;
    }

    private function getLocalityStats($municipalityId) {
        return Locality::where('municipality_id', $municipalityId)
            ->withCount([
                'institutions as total_institutions',
                'institutions as total_tables' => function($q) {
                    $q->select(DB::raw('COALESCE(SUM(institutions.total_voting_tables), 0)'));
                }
            ])
            ->get()
            ->map(function($locality) {
                // Calcular mesas reportadas (con votos)
                $reportedTables = DB::table('voting_tables')
                    ->join('institutions', 'voting_tables.institution_id', '=', 'institutions.id')
                    ->where('institutions.locality_id', $locality->id)
                    ->whereExists(function($q) {
                        $q->select(DB::raw(1))
                          ->from('votes')
                          ->whereRaw('votes.voting_table_id = voting_tables.id');
                    })
                    ->count();

                $locality->reported_tables = $reportedTables;
                return $locality;
            });
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
