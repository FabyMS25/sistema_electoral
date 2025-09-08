<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\VotingTableController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\VotingTableVoteController;
use App\Http\Controllers\ElectionDashboardController;
use App\Http\Controllers\HomeController;

Auth::routes();

Route::get('index/{locale}', [HomeController::class, 'lang']);
Route::get('/', [HomeController::class, 'root'])->name('root');
Route::get('/refresh-dashboard', [HomeController::class, 'getDashboardData'])->name('refresh-dashboard');
Route::get('/get-provinces/{department}', [HomeController::class, 'getProvinces'])->name('get-provinces');
Route::get('/get-municipalities/{province}', [HomeController::class, 'getMunicipalities'])->name('get-municipalities');

Route::middleware('auth')->group(function () {
    Route::post('/toggle-dashboard-visibility', [HomeController::class, 'toggleDashboardVisibility'])
        ->name('toggle-dashboard-visibility');
    
    Route::get('/institutions/export', [InstitutionController::class, 'export'])->name('institutions.export');
    Route::get('/institutions/template', [InstitutionController::class, 'downloadTemplate'])->name('institutions.template');
    Route::post('/institutions/import', [InstitutionController::class, 'import'])->name('institutions.import');
    Route::get('/institutions/provinces/{department}', [InstitutionController::class, 'getProvinces'])->name('institutions.provinces');
    Route::get('/institutions/municipalities/{province}', [InstitutionController::class, 'getMunicipalities'])->name('institutions.municipalities');
    Route::get('/institutions/localities/{municipality}', [InstitutionController::class, 'getLocalities'])->name('institutions.localities');
    Route::get('/institutions/districts/{locality}', [InstitutionController::class, 'getDistricts'])->name('institutions.districts');
    Route::get('/institutions/zones/{district}', [InstitutionController::class, 'getZones'])->name('institutions.zones');
    Route::post('/institutions/delete-multiple', [InstitutionController::class, 'deleteMultiple'])->name('institutions.deleteMultiple');
    Route::resource('institutions', InstitutionController::class);

    Route::get('/voting-tables/export', [VotingTableController::class, 'export'])->name('voting-tables.export');
    Route::get('/voting-tables/template', [VotingTableController::class, 'downloadTemplate'])->name('voting-tables.template');
    Route::post('/voting-tables/import', [VotingTableController::class, 'import'])->name('voting-tables.import');
    Route::post('/voting-tables/delete-multiple', [VotingTableController::class, 'deleteMultiple'])->name('voting-tables.deleteMultiple');
    Route::resource('voting-tables', VotingTableController::class);
    
    Route::resource('managers', ManagerController::class);
    Route::get('/managers/voting-tables/{institution}', [ManagerController::class, 'getVotingTables'])->name('managers.voting-tables');
    Route::resource('candidates', CandidateController::class);
    
    Route::get('voting-table-votes', [VotingTableVoteController::class, 'index'])->name('voting-table-votes.index');
    Route::post('/voting-table-votes/register', [VotingTableVoteController::class, 'registerVotes'])->name('voting-table-votes.register');
    Route::post('/voting-table-votes/register-all', [VotingTableVoteController::class, 'registerAllVotes'])->name('voting-table-votes.register-all');

    Route::get('profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/settings', [App\Http\Controllers\ProfileController::class, 'settings'])->name('profile.settings');
    Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');
});
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
