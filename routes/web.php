<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TargetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/targets/update', [TargetController::class, 'updateTargets'])
    ->name('targets.update')
    ->middleware('auth'); // Only authenticated users can update global targets
Route::get('/search', [DonationController::class, 'search'])->name('donations.search');
Route::get('/recap', [DonationController::class, 'recap'])->name('donations.recap');
Route::get('/distribution', [DonationController::class, 'distribution'])->name('donations.distribution');
Route::get('/donations/donors', [DonationController::class, 'getDonors'])->name('donations.donors');
Route::get('/donations/donor-donations', [DonationController::class, 'getDonorDonations'])->name('donations.donor-donations');
Route::get('/donations/donor-suggestions', [DonationController::class, 'getDonorSuggestions'])->name('donations.donor-suggestions');
Route::get('/donations/date-pdf/{date}', [DonationController::class, 'downloadDatePDF'])->name('donations.date-pdf');
Route::get('/donations/donor-pdf/{donorName}', [DonationController::class, 'downloadDonorPDF'])->name('donations.donor-pdf');

// Auth Routes
Route::get('/login', [AuthController::class, 'loginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Petugas & Admin
    Route::middleware(['can:petugas'])->group(function () {
        Route::post('/donations', [DonationController::class, 'store'])->name('donations.store');
        Route::put('/donations/{donation}', [DonationController::class, 'update'])->name('donations.update');
        Route::delete('/donations/{donation}', [DonationController::class, 'destroy'])->name('donations.destroy');
        Route::get('/donations/flexible', [DonationController::class, 'flexible'])->name('donations.flexible');
        Route::post('/donations/schedule', [DonationController::class, 'scheduleFlexible'])->name('donations.schedule');
        
        // Donor management
        Route::put('/donors/{donor}', [\App\Http\Controllers\DonorController::class, 'update'])->name('donors.update');
    });

    // Admin Only
    Route::middleware(['can:admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});
