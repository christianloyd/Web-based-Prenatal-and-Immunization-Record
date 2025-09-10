<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PrenatalController;
use App\Http\Controllers\PrenatalRecordController;
use App\Http\Controllers\ChildRecordController;
use App\Http\Controllers\ImmunizationController;
use App\Http\Controllers\PrenatalCheckupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\VaccineController;
use App\Http\Controllers\CloudBackupController;
use App\Http\Controllers\DashboardController;
// Redirect root to login
Route::get('/', fn () => redirect()->route('login'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('login'))->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
});

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Dashboard routes by role
    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'midwife' => redirect()->route('midwife.dashboard'),
            'bhw'     => redirect()->route('bhw.dashboard'),
            default   => abort(403, 'Unauthorized role'),
        };
    })->name('dashboard');

    /* ----------------------------------------------------------
       MIDWIFE AREA — prefixed & named
       ---------------------------------------------------------- */
    Route::prefix('midwife')
         ->name('midwife.')
         ->group(function () {

            // Dashboard route
            Route::get('/dashboard', [DashboardController::class, 'index'])
                 ->name('dashboard');

            // Patient Prenatal Record Routes
            Route::resource('patients', PatientController::class);

            /* --- NEW: complete resource for PrenatalRecord --- */
            Route::resource('prenatalrecord', PrenatalRecordController::class);

            //Child Record Routes
            Route::resource('childrecord', ChildRecordController::class);
            Route::put('childrecord/{id}', [ChildRecordController::class, 'update'])->name('childrecord.update');

            //Prenatal Checkup Routes
            Route::resource('prenatalcheckup', PrenatalCheckupController::class);
            Route::get('prenatalcheckup/patient/{patient}', [PrenatalCheckupController::class, 'showPatient'])
                 ->name('prenatalcheckup.patient');

            //Cloud Backup Route
            Route::get('cloudbackup', [CloudBackupController::class, 'index'])
                 ->name('cloudbackup.index');

            Route::post('vaccines/stock-transaction', [VaccineController::class, 'stockTransaction'])
                 ->name('vaccines.stock-transaction');

            //Immunization Routes
            Route::resource('immunization', ImmunizationController::class);

            //Vaccine Routes
            Route::resource('vaccines', VaccineController::class);

            //Report
            Route::view('/report', 'midwife.report')->name('report');

            // User Management Routes
            Route::resource('user', UserController::class);
            Route::patch('user/{user}/deactivate', [UserController::class, 'deactivate'])->name('user.deactivate');
            Route::patch('user/{user}/activate', [UserController::class, 'activate'])->name('user.activate');
             
        });

    // BHW routes (role middleware)
    Route::prefix('bhw')
            ->name('bhw.')
            ->group(function () {

            // Dashboard route (simple view for now, you can create BHW DashboardController later)
            Route::get('/dashboard', function() {
                return view('bhw.dashboard');
            })->name('dashboard');
            
            Route::resource('patients', PatientController::class);
            Route::resource('prenatalrecord', PrenatalRecordController::class);
            Route::resource('childrecord', ChildRecordController::class); 
            Route::view('/report', 'bhw.report')->name('report');
         });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});