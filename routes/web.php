<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SystemAnalysisController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\CloudBackupController as AdminCloudBackupController;
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
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        return match ($user->role) {
            'midwife' => redirect()->route('midwife.dashboard'),
            'bhw'     => redirect()->route('bhw.dashboard'),
            'admin'   => redirect()->route('admin.dashboard'),
            default   => abort(403, 'Unauthorized role'),
        };
    })->name('dashboard');

    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'getRecent'])->name('recent');
        Route::get('/new', [NotificationController::class, 'getNewNotifications'])->name('new');
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
            Route::get('patients-search', [PatientController::class, 'search'])->name('patients.search');
            Route::get('patients/{id}/profile', [PatientController::class, 'profile'])->name('patients.profile');
            Route::get('patients/{id}/print', [PatientController::class, 'printProfile'])->name('patients.print');

            /* --- NEW: complete resource for PrenatalRecord --- */
            Route::resource('prenatalrecord', PrenatalRecordController::class);

            //Prenatal Checkup Routes
            Route::resource('prenatalcheckup', PrenatalCheckupController::class);
            Route::get('prenatalcheckup/{id}/data', [PrenatalCheckupController::class, 'getData'])->name('prenatalcheckup.data');
            Route::post('prenatalcheckup/{id}/complete', [PrenatalCheckupController::class, 'markCompleted'])->name('prenatalcheckup.complete');
            Route::put('prenatalcheckup/{id}/schedule', [PrenatalCheckupController::class, 'updateSchedule'])->name('prenatalcheckup.schedule');
            Route::post('prenatalcheckup/{id}/mark-missed', [PrenatalCheckupController::class, 'markAsMissed'])->name('prenatalcheckup.mark-missed');
            Route::post('prenatalcheckup/{id}/reschedule', [PrenatalCheckupController::class, 'rescheduleMissed'])->name('prenatalcheckup.reschedule');
            Route::get('prenatalcheckup-patients/search', [PrenatalCheckupController::class, 'getPatientsWithActivePrenatalRecords'])->name('prenatalcheckup.patients.search');

            //Appointment Routes
            Route::resource('appointments', AppointmentController::class);
            Route::post('appointments/{id}/complete', [AppointmentController::class, 'markCompleted'])->name('appointments.complete');
            Route::post('appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
            Route::post('appointments/{id}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
            Route::get('appointments-data/upcoming', [AppointmentController::class, 'getUpcoming'])->name('appointments.upcoming');
            Route::get('appointments-data/today', [AppointmentController::class, 'getToday'])->name('appointments.today');

            //Child Record Routes
            Route::resource('childrecord', ChildRecordController::class);
            Route::put('childrecord/{id}', [ChildRecordController::class, 'update'])->name('childrecord.update');
            Route::get('childrecord-search', [ChildRecordController::class, 'search'])->name('childrecord.search');
            
            //Child Immunization Routes
            Route::post('childrecord/{childRecord}/immunizations', [App\Http\Controllers\ChildImmunizationController::class, 'store'])->name('childrecord.immunizations.store');
            Route::put('childrecord/{childRecord}/immunizations/{immunization}', [App\Http\Controllers\ChildImmunizationController::class, 'update'])->name('childrecord.immunizations.update');
            Route::delete('childrecord/{childRecord}/immunizations/{immunization}', [App\Http\Controllers\ChildImmunizationController::class, 'destroy'])->name('childrecord.immunizations.destroy');



            Route::post('vaccines/stock-transaction', [VaccineController::class, 'stockTransaction'])
                 ->name('vaccines.stock-transaction');

            //Immunization Routes - Specific routes must come BEFORE resource routes
            Route::get('immunization/children-data', [ImmunizationController::class, 'getChildrenForImmunization'])->name('immunization.children-data');
            Route::get('immunization/child/{childId}/vaccines', [ImmunizationController::class, 'getAvailableVaccinesForChild'])->name('immunization.child-vaccines');
            Route::get('immunization/child/{childId}/vaccines/{vaccineId}/doses', [ImmunizationController::class, 'getAvailableDosesForChild'])->name('immunization.child-doses');
            Route::post('immunization/{id}/quick-status', [ImmunizationController::class, 'quickUpdateStatus'])->name('immunization.quick-status');
            Route::resource('immunization', ImmunizationController::class);

            //Vaccine Routes
            Route::resource('vaccines', VaccineController::class);

            //Report
            Route::get('/reports', [ReportController::class, 'midwifeIndex'])->name('report');
            Route::get('/reports/print', [ReportController::class, 'printView'])->name('report.print');
            Route::post('/reports/generate', [ReportController::class, 'generateReport'])->name('report.generate');
            Route::post('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('report.export.pdf');
            Route::post('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('report.export.excel');

            // System Analysis Report
            Route::get('/system-analysis-report', [SystemAnalysisController::class, 'generateAnalysisReport'])->name('system.analysis.report');

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
            
            //Patient Routes for BHW
            Route::resource('patients', PatientController::class);
            Route::get('patients/search', [PatientController::class, 'search'])->name('patients.search');
            Route::get('patients/{id}/profile', [PatientController::class, 'profile'])->name('patients.profile');
            Route::get('patients/{id}/print', [PatientController::class, 'printProfile'])->name('patients.print');

            //Prenatal Record Routes for BHW
            Route::resource('prenatalrecord', PrenatalRecordController::class);
            
            //Prenatal Checkup Routes for BHW
            Route::resource('prenatalcheckup', PrenatalCheckupController::class);
            Route::get('prenatalcheckup/{id}/data', [PrenatalCheckupController::class, 'getData'])->name('prenatalcheckup.data');
            Route::post('prenatalcheckup/{id}/complete', [PrenatalCheckupController::class, 'markCompleted'])->name('prenatalcheckup.complete');
            Route::put('prenatalcheckup/{id}/schedule', [PrenatalCheckupController::class, 'updateSchedule'])->name('prenatalcheckup.schedule');
            Route::post('prenatalcheckup/{id}/mark-missed', [PrenatalCheckupController::class, 'markAsMissed'])->name('prenatalcheckup.mark-missed');
            Route::post('prenatalcheckup/{id}/reschedule', [PrenatalCheckupController::class, 'rescheduleMissed'])->name('prenatalcheckup.reschedule');
            

            //Child Record Routes for BHW
            Route::resource('childrecord', ChildRecordController::class);

            //Immunization Routes for BHW - Specific routes must come BEFORE resource routes
            Route::get('immunizations/children-data', [ImmunizationController::class, 'getChildrenForImmunization'])->name('immunizations.children-data');
            Route::get('immunizations/child/{childId}/vaccines', [ImmunizationController::class, 'getAvailableVaccinesForChild'])->name('immunizations.child-vaccines');
            Route::get('immunizations/child/{childId}/vaccines/{vaccineId}/doses', [ImmunizationController::class, 'getAvailableDosesForChild'])->name('immunizations.child-doses');
            Route::post('immunizations/{id}/quick-status', [ImmunizationController::class, 'quickUpdateStatus'])->name('immunizations.quick-status');
            Route::resource('immunizations', ImmunizationController::class);

            //Appointment Routes for BHW
            Route::resource('appointments', AppointmentController::class);
            Route::post('appointments/{id}/complete', [AppointmentController::class, 'markCompleted'])->name('appointments.complete');
            Route::post('appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
            Route::post('appointments/{id}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
            Route::get('appointments-data/upcoming', [AppointmentController::class, 'getUpcoming'])->name('appointments.upcoming');
            Route::get('appointments-data/today', [AppointmentController::class, 'getToday'])->name('appointments.today');
            
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

    // Admin routes
    Route::prefix('admin')
            ->name('admin.')
            ->group(function () {

            // Dashboard route
            Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

            // User management (view-only)
            Route::get('/users', [AdminController::class, 'users'])->name('users.index');
            Route::get('/users/{id}', [AdminController::class, 'userShow'])->name('users.show');

            // Patient management (view-only)
            Route::get('/patients', [AdminController::class, 'patients'])->name('patients.index');
            Route::get('/patients/{id}', [AdminController::class, 'patientShow'])->name('patients.show');

            // Records overview (view-only)
            Route::get('/records', [AdminController::class, 'records'])->name('records.index');

            // Cloud Backup Management
            Route::prefix('cloudbackup')->name('cloudbackup.')->group(function () {
                Route::get('/', [AdminCloudBackupController::class, 'index'])->name('index');
                Route::get('/data', [AdminCloudBackupController::class, 'getData'])->name('data');
                Route::post('/create', [AdminCloudBackupController::class, 'store'])->name('store');
                Route::get('/progress/{id}', [AdminCloudBackupController::class, 'progress'])->name('progress');
                Route::get('/download/{id}', [AdminCloudBackupController::class, 'download'])->name('download');
                Route::post('/restore', [AdminCloudBackupController::class, 'restore'])->name('restore');
                Route::delete('/{id}', [AdminCloudBackupController::class, 'destroy'])->name('destroy');
                Route::post('/estimate-size', [AdminCloudBackupController::class, 'estimateSize'])->name('estimate-size');
                Route::post('/sync', [AdminCloudBackupController::class, 'syncGoogleDrive'])->name('sync');
            });
         });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});