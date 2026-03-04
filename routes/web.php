<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\VotingTableController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\VotingTableVoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActaController;
use App\Http\Controllers\ObservationController;

Auth::routes();
Route::get('index/{locale}', [HomeController::class, 'lang']);
Route::get('/', [HomeController::class, 'root'])->name('root');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/refresh-dashboard', [HomeController::class, 'getDashboardData'])->name('refresh-dashboard');
    Route::post('/toggle-dashboard-visibility', [HomeController::class, 'toggleDashboardVisibility'])->name('toggle-dashboard-visibility');

    // ===== GESTIÓN DE USUARIOS =====
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/activate', [UserController::class, 'activate'])->name('activate');
        Route::get('/{user}/assign-roles', [UserController::class, 'assignRolesForm'])->name('assign-roles.form');
        Route::post('/{user}/assign-roles', [UserController::class, 'assignRoles'])->name('assign-roles');
        Route::get('/{user}/permissions', [UserController::class, 'permissionsForm'])->name('permissions.form');
        Route::put('/{user}/permissions', [UserController::class, 'updatePermissions'])->name('permissions.update');
        Route::get('/{user}/assign-institution', [UserController::class, 'assignInstitutionForm'])->name('assign-institution.form');
        Route::post('/{user}/assign-institution', [UserController::class, 'assignInstitution'])->name('assign-institution');
        Route::get('/{user}/assign-table', [UserController::class, 'assignTableForm'])->name('assign-table.form');
        Route::post('/{user}/assign-table', [UserController::class, 'assignTable'])->name('assign-table');
        Route::delete('/{user}/assignment/{assignment}', [UserController::class, 'removeAssignment'])->name('remove-assignment');
        Route::get('/check-email', [UserController::class, 'checkEmail'])->name('users.check-email');
    });
    // ===== INSTITUCIONES (RECINTOS) =====
    Route::prefix('institutions')->name('institutions.')->group(function () {
        // Export/Import
        Route::get('/export-all', [InstitutionController::class, 'exportAll'])->name('export-all');
        Route::post('/export-selected', [InstitutionController::class, 'exportSelected'])->name('export-selected');
        Route::get('/template', [InstitutionController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [InstitutionController::class, 'import'])->name('import');
        Route::post('/delete-multiple', [InstitutionController::class, 'deleteMultiple'])->name('deleteMultiple');

        // Carga dinámica (CORREGIDO - sin parámetros en el nombre de la ruta)
        Route::get('/provinces/{department}', [InstitutionController::class, 'getProvinces'])->name('provinces');
        Route::get('/municipalities/{province}', [InstitutionController::class, 'getMunicipalities'])->name('municipalities');
        Route::get('/localities/{municipality}', [InstitutionController::class, 'getLocalities'])->name('localities');
        Route::get('/districts/{locality}', [InstitutionController::class, 'getDistricts'])->name('districts');
        Route::get('/zones/{district}', [InstitutionController::class, 'getZones'])->name('zones');

        // CRUD
        Route::get('/', [InstitutionController::class, 'index'])->name('index');
        Route::get('/create', [InstitutionController::class, 'create'])->name('create');
        Route::post('/', [InstitutionController::class, 'store'])->name('store');
        Route::get('/{institution}', [InstitutionController::class, 'show'])->name('show');
        Route::get('/{institution}/edit', [InstitutionController::class, 'edit'])->name('edit');
        Route::put('/{institution}', [InstitutionController::class, 'update'])->name('update');
        Route::delete('/{institution}', [InstitutionController::class, 'destroy'])->name('destroy');
    });

    // ===== MESAS ELECTORALES =====
    Route::prefix('voting-tables')->name('voting-tables.')->group(function () {
        Route::get('/template', [VotingTableController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [VotingTableController::class, 'import'])->name('import');
        Route::get('/export-all', [VotingTableController::class, 'exportAll'])->name('export-all');
        Route::post('/export-selected', [VotingTableController::class, 'exportSelected'])->name('export-selected');
        Route::post('/delete-multiple', [VotingTableController::class, 'deleteMultiple'])->name('deleteMultiple');
        Route::get('/by-institution/{institution}', [VotingTableController::class, 'getByInstitution'])->name('by-institution');
        Route::get('/', [VotingTableController::class, 'index'])->name('index');
        Route::get('/create', [VotingTableController::class, 'create'])->name('create');
        Route::post('/', [VotingTableController::class, 'store'])->name('store');
        Route::get('/{voting_table}', [VotingTableController::class, 'show'])->name('show');
        Route::get('/{voting_table}/edit', [VotingTableController::class, 'edit'])->name('edit');
        Route::put('/{voting_table}', [VotingTableController::class, 'update'])->name('update');
        Route::delete('/{voting_table}', [VotingTableController::class, 'destroy'])->name('destroy');
        Route::get('/{voting_table}/assign-delegates', [VotingTableController::class, 'assignDelegatesForm'])->name('assign-delegates');
        Route::post('/{voting_table}/assign-delegates', [VotingTableController::class, 'assignDelegates'])->name('assign-delegates.store');
    });

    // ===== CANDIDATOS =====
    Route::prefix('candidates')->name('candidates.')->group(function () {
        Route::get('/export-all', [CandidateController::class, 'exportAll'])->name('export-all');
        Route::post('/export-selected', [CandidateController::class, 'exportSelected'])->name('export-selected');
        Route::get('/template', [CandidateController::class, 'template'])->name('template');
        Route::post('/import', [CandidateController::class, 'import'])->name('import');
        Route::delete('/multiple-delete', [CandidateController::class, 'multipleDelete'])->name('multiple-delete');
        Route::get('/', [CandidateController::class, 'index'])->name('index');
        Route::get('/create', [CandidateController::class, 'create'])->name('create');
        Route::post('/', [CandidateController::class, 'store'])->name('store');
        Route::get('/{candidate}/edit', [CandidateController::class, 'edit'])->name('edit');
        Route::put('/{candidate}', [CandidateController::class, 'update'])->name('update');
        Route::delete('/{candidate}', [CandidateController::class, 'destroy'])->name('destroy');
        Route::get('/provinces/{department}', [CandidateController::class, 'getProvinces'])->name('provinces');
        Route::get('/municipalities/{province}', [CandidateController::class, 'getMunicipalities'])->name('municipalities');
    });

    // ===== VOTOS =====
    Route::prefix('voting-table-votes')->name('voting-table-votes.')->group(function () {
        Route::get('/', [VotingTableVoteController::class, 'index'])->name('index');
        Route::post('register', [VotingTableVoteController::class, 'registerVotes'])->name('register');
        Route::post('register-all', [VotingTableVoteController::class, 'registerAllVotes'])->name('register-all');
        Route::post('{tableId}/review', [VotingTableVoteController::class, 'reviewTable'])->name('review');
        Route::post('{tableId}/validate', [VotingTableVoteController::class, 'validateTable'])->name('validate');
        Route::post('{tableId}/correct', [VotingTableVoteController::class, 'correctTable'])->name('correct');
        Route::post('{tableId}/close', [VotingTableVoteController::class, 'closeTable'])->name('close');
        Route::get('{tableId}/votes', [VotingTableVoteController::class, 'getTableVotes'])->name('votes');
        Route::get('{tableId}/stats', [VotingTableVoteController::class, 'getTableStats'])->name('stats');
    });
    Route::prefix('observations')->name('observations.')->group(function () {
        Route::post('/', [ObservationController::class, 'store'])->name('store');
        Route::post('{id}/resolve', [ObservationController::class, 'resolve'])->name('resolve');
        Route::get('table/{tableId}', [ObservationController::class, 'getTableObservations'])->name('table');
        Route::get('stats', [ObservationController::class, 'getStats'])->name('stats');
    });
    Route::prefix('actas')->name('actas.')->group(function () {
        // Route::post('/', [ActaController::class, 'store'])->name('store');
        Route::post('/upload', [ActaController::class, 'store'])->name('upload');
        Route::post('{id}/verify', [ActaController::class, 'verify'])->name('verify');
        Route::post('{id}/observe', [ActaController::class, 'observe'])->name('observe');
        Route::post('{id}/approve', [ActaController::class, 'approve'])->name('approve');
        Route::get('table/{tableId}', [ActaController::class, 'getTableActas'])->name('table');
    });

    // ===== PERFIL =====
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/update-password', [ProfileController::class, 'updatePassword'])->name('update-password');
    });
});

Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/provinces/{department}', [HomeController::class, 'getProvinces'])->name('api.provinces');
    Route::get('/municipalities/{province}', [HomeController::class, 'getMunicipalities'])->name('api.municipalities');
    Route::get('/institutions/{institution}/tables', [VotingTableController::class, 'getByInstitution']);
    Route::get('/elections/{election}/candidates', [CandidateController::class, 'getByElection']);
    Route::get('/tables/{table}/votes', [VotingTableVoteController::class, 'getTableVotes']);
    Route::get('/tables/{table}/observations', [ObservationController::class, 'getByTable']);
    Route::post('/tables/{table}/validate', [VotingTableVoteController::class, 'validateTable']);
});

Route::get('{any}', [HomeController::class, 'index'])->name('index')->where('any', '.*');
