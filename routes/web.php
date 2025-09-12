<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PrenatalController;
use App\Http\Controllers\PrenatalRecordController;
use App\Http\Controllers\ChildRecordController;
use App\Http\Controllers\ImmunizationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\VaccineController;
use App\Http\Controllers\Midwife\CloudBackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PrenatalCheckupController;
// Redirect root to login
Route::get('/', fn () => redirect()->route('login'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('login'))->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
});

// Google OAuth routes (outside auth middleware for callback)
Route::get('/google/auth', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.auth');
Route::get('/google/callback', [GoogleAuthController::class, 'handleCallback'])->name('google.callback');
Route::post('/google/disconnect', [GoogleAuthController::class, 'disconnect'])->name('google.disconnect');

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

    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'getRecent'])->name('recent');
        Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{id}', [NotificationController::class, 'delete'])->name('delete');
        Route::post('/send-test', [NotificationController::class, 'sendTest'])->name('send-test');
    });

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

            //Prenatal Checkup Routes
            Route::resource('prenatalcheckup', PrenatalCheckupController::class);

            //Child Record Routes
            Route::resource('childrecord', ChildRecordController::class);
            Route::put('childrecord/{id}', [ChildRecordController::class, 'update'])->name('childrecord.update');
            
            //Child Immunization Routes
            Route::post('childrecord/{childRecord}/immunizations', [App\Http\Controllers\ChildImmunizationController::class, 'store'])->name('childrecord.immunizations.store');
            Route::put('childrecord/{childRecord}/immunizations/{immunization}', [App\Http\Controllers\ChildImmunizationController::class, 'update'])->name('childrecord.immunizations.update');
            Route::delete('childrecord/{childRecord}/immunizations/{immunization}', [App\Http\Controllers\ChildImmunizationController::class, 'destroy'])->name('childrecord.immunizations.destroy');


            //Cloud Backup Routes
            Route::prefix('cloudbackup')->name('cloudbackup.')->group(function () {
                Route::get('/', [CloudBackupController::class, 'index'])->name('index');
                Route::get('/data', [CloudBackupController::class, 'getData'])->name('data');
                Route::post('/create', [CloudBackupController::class, 'store'])->name('store');
                Route::get('/progress/{id}', [CloudBackupController::class, 'progress'])->name('progress');
                Route::get('/download/{id}', [CloudBackupController::class, 'download'])->name('download');
                Route::post('/restore', [CloudBackupController::class, 'restore'])->name('restore');
                Route::delete('/{id}', [CloudBackupController::class, 'destroy'])->name('destroy');
                Route::post('/estimate-size', [CloudBackupController::class, 'estimateSize'])->name('estimate-size');
            });

            Route::post('vaccines/stock-transaction', [VaccineController::class, 'stockTransaction'])
                 ->name('vaccines.stock-transaction');

            //Immunization Routes
            Route::resource('immunization', ImmunizationController::class);

            //Vaccine Routes
            Route::resource('vaccines', VaccineController::class);

            //Report
            Route::get('/reports', [ReportController::class, 'midwifeIndex'])->name('report');
            Route::get('/reports/print', [ReportController::class, 'printView'])->name('report.print');
            Route::post('/reports/generate', [ReportController::class, 'generateReport'])->name('report.generate');
            Route::post('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('report.export.pdf');
            Route::post('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('report.export.excel');

            // User Management Routes
            Route::resource('user', UserController::class);
            Route::patch('user/{user}/deactivate', [UserController::class, 'deactivate'])->name('user.deactivate');
            Route::patch('user/{user}/activate', [UserController::class, 'activate'])->name('user.activate');
             
        });

    // BHW routes (role middleware)
    Route::prefix('bhw')
            ->name('bhw.')
            ->group(function () {

            // Dashboard route
            Route::get('/dashboard', [DashboardController::class, 'bhwIndex'])->name('dashboard');
            
            Route::resource('patients', PatientController::class);
            Route::resource('prenatalrecord', PrenatalRecordController::class);
            Route::resource('childrecord', ChildRecordController::class);
            Route::resource('immunizations', ImmunizationController::class);
            
            //Child Immunization Routes for BHW
            Route::post('childrecord/{childRecord}/immunizations', [App\Http\Controllers\ChildImmunizationController::class, 'store'])->name('childrecord.immunizations.store');
            Route::put('childrecord/{childRecord}/immunizations/{immunization}', [App\Http\Controllers\ChildImmunizationController::class, 'update'])->name('childrecord.immunizations.update');
            Route::delete('childrecord/{childRecord}/immunizations/{immunization}', [App\Http\Controllers\ChildImmunizationController::class, 'destroy'])->name('childrecord.immunizations.destroy');
            
            Route::get('/report', [ReportController::class, 'bhwIndex'])->name('report');
            Route::get('/report/print', [ReportController::class, 'printView'])->name('report.print');
            Route::post('/report/generate', [ReportController::class, 'generateReport'])->name('report.generate');
            Route::post('/report/export-pdf', [ReportController::class, 'exportPdf'])->name('report.export.pdf');
            Route::post('/report/export-excel', [ReportController::class, 'exportExcel'])->name('report.export.excel');
         });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});