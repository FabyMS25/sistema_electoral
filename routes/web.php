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
use App\Http\Controllers\ReportController;

Auth::routes();

// Rutas públicas
Route::get('index/{locale}', [HomeController::class, 'lang']);
Route::get('/', [HomeController::class, 'root'])->name('root');
Route::get('/refresh-dashboard', [HomeController::class, 'getDashboardData'])->name('refresh-dashboard');
Route::get('/get-provinces/{department}', [HomeController::class, 'getProvinces'])->name('get-provinces');
Route::get('/get-municipalities/{province}', [HomeController::class, 'getMunicipalities'])->name('get-municipalities');

// Rutas protegidas
Route::middleware(['auth', 'verified'])->group(function () {

    // ===== GESTIÓN DE USUARIOS =====
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        
        // Acciones especiales
        Route::post('/{user}/activate', [UserController::class, 'activate'])->name('activate');
        Route::get('/{user}/assign-recinto', [UserController::class, 'assignRecintoForm'])->name('assign-recinto.form');
        Route::post('/{user}/assign-recinto', [UserController::class, 'assignRecinto'])->name('assign-recinto');
        Route::get('/{user}/assign-table', [UserController::class, 'assignTableForm'])->name('assign-table.form');
        Route::post('/{user}/assign-table', [UserController::class, 'assignTable'])->name('assign-table');
        Route::delete('/{user}/{type}/assignment/{assignmentId}/remove', [UserController::class, 'removeAssignment'])->name('remove-assignment');
    });

    // ===== DASHBOARD =====
    Route::post('/toggle-dashboard-visibility', [HomeController::class, 'toggleDashboardVisibility'])->name('toggle-dashboard-visibility');
    
    // ===== INSTITUCIONES (RECINTOS) =====
    Route::prefix('institutions')->name('institutions.')->group(function () {
        // Export/Import
        Route::get('/export', [InstitutionController::class, 'export'])->name('export');
        Route::get('/template', [InstitutionController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [InstitutionController::class, 'import'])->name('import');
        Route::post('/delete-multiple', [InstitutionController::class, 'deleteMultiple'])->name('delete-multiple');
        
        // Carga dinámica
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
        // Export/Import
        Route::get('/export', [VotingTableController::class, 'export'])->name('export');
        Route::get('/template', [VotingTableController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [VotingTableController::class, 'import'])->name('import');
        Route::post('/delete-multiple', [VotingTableController::class, 'deleteMultiple'])->name('delete-multiple');
        
        // Utilidades
        Route::get('/by-institution/{institution}', [VotingTableController::class, 'getByInstitution'])->name('by-institution');
        
        // CRUD
        Route::get('/', [VotingTableController::class, 'index'])->name('index');
        Route::get('/create', [VotingTableController::class, 'create'])->name('create');
        Route::post('/', [VotingTableController::class, 'store'])->name('store');
        Route::get('/{voting_table}', [VotingTableController::class, 'show'])->name('show');
        Route::get('/{voting_table}/edit', [VotingTableController::class, 'edit'])->name('edit');
        Route::put('/{voting_table}', [VotingTableController::class, 'update'])->name('update');
        Route::delete('/{voting_table}', [VotingTableController::class, 'destroy'])->name('destroy');
    });
    
    // ===== CANDIDATOS =====
    Route::resource('candidates', CandidateController::class);
    
    // ===== VOTOS =====
    Route::prefix('voting-table-votes')->name('voting-table-votes.')->group(function () {
        Route::get('/', [VotingTableVoteController::class, 'index'])->name('index');
        Route::post('/register', [VotingTableVoteController::class, 'registerVotes'])->name('register');
        Route::post('/register-all', [VotingTableVoteController::class, 'registerAllVotes'])->name('register-all');
        Route::get('/table/{table}', [VotingTableVoteController::class, 'getTableVotes'])->name('table');
    });

    // ===== ACTAS (nuevo) =====
    // Route::prefix('actas')->name('actas.')->group(function () {
    //     Route::get('/', [ActaController::class, 'index'])->name('index');
    //     Route::post('/upload', [ActaController::class, 'upload'])->name('upload');
    //     Route::get('/{acta}', [ActaController::class, 'show'])->name('show');
    //     Route::delete('/{acta}', [ActaController::class, 'destroy'])->name('destroy');
    //     Route::post('/{acta}/verify', [ActaController::class, 'verify'])->name('verify');
    // });

    // ===== OBSERVACIONES (nuevo) =====
    // Route::prefix('observations')->name('observations.')->group(function () {
    //     Route::get('/', [ObservationController::class, 'index'])->name('index');
    //     Route::post('/', [ObservationController::class, 'store'])->name('store');
    //     Route::get('/{observation}', [ObservationController::class, 'show'])->name('show');
    //     Route::post('/{observation}/resolve', [ObservationController::class, 'resolve'])->name('resolve');
    // });

    // ===== REPORTES (nuevo) =====
    // Route::prefix('reports')->name('reports.')->group(function () {
    //     Route::get('/', [ReportController::class, 'index'])->name('index');
    //     Route::get('/results', [ReportController::class, 'results'])->name('results');
    //     Route::get('/export-pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
    //     Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel');
    // });

    // ===== PERFIL =====
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/update-password', [ProfileController::class, 'updatePassword'])->name('update-password');
    });
});

// API Routes (para peticiones AJAX)
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/institutions/{institution}/tables', [VotingTableController::class, 'getByInstitution']);
    Route::get('/elections/{election}/candidates', [CandidateController::class, 'getByElection']);
    Route::get('/tables/{table}/votes', [VotingTableVoteController::class, 'getTableVotes']);
});

// Catch-all route - DEBE IR AL FINAL
Route::get('{any}', [HomeController::class, 'index'])->name('index');