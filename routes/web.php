<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\AutoAuthController;
use App\Http\Controllers\CRMConnectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::prefix('agency')->name('agency.')->group(function () {
        Route::get('index', [AgencyController::class, 'index'])->name('index');
        Route::get('create', [AgencyController::class, 'create'])->name('create');
        Route::post('save', [AgencyController::class, 'save'])->name('store');
        Route::get('{id}/edit', [AgencyController::class, 'edit'])->name('edit');
        Route::post('{id}/save', [AgencyController::class, 'save'])->name('update');
        Route::delete('{id}', [AgencyController::class, 'destroy'])->name('destroy');
        Route::get('/departments', [AgencyController::class, 'getAllDepartments'])->name('departments');
    });

    Route::prefix('setting')->name('setting.')->group(function () {
        Route::get('index', [SettingController::class, 'index'])->name('index');
        Route::post('save', [SettingController::class, 'save'])->name('save');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';


Route::prefix('authorization')->name('crm.')->group(function () {
    Route::get('/crm/oauth/callback', [CRMConnectionController::class, 'crmCallback'])->name('oauth_callback');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/dashboard/stats', [DashboardController::class, 'dashboardStats'])->name('dashboard.stats')->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/api/tickets', [DashboardController::class, 'getTickets'])->name('dashboard.tickets')->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::get('/tickets/{ticketId}/messages', [DashboardController::class, 'getMessages'])->name('tickets.messages');
Route::get('/user/{userId?}', [DashboardController::class, 'getUserInfo'])->name('tickets.user');
