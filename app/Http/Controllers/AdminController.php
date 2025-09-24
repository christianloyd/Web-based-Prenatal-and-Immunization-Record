<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\PrenatalRecord;
use App\Models\ChildRecord;
use App\Models\Immunization;
use App\Models\Vaccine;
use App\Models\CloudBackup;
use App\Models\RestoreOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        // No middleware needed - using same pattern as midwife/bhw
    }

    /**
     * Check if current user is admin
     */
    private function checkAdminAccess()
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Admin access required');
        }
    }

    /**
     * Display the admin dashboard
     */
    public function index()
    {
        $this->checkAdminAccess();
        // Get system statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'midwives' => User::where('role', 'Midwife')->count(),
            'bhws' => User::where('role', 'BHW')->count(),
            'total_patients' => Patient::count(),
            'prenatal_records' => PrenatalRecord::count(),
            'child_records' => ChildRecord::count(),
            'immunizations' => Immunization::count(),
            'vaccines' => Vaccine::count(),
            'cloud_backups' => CloudBackup::count(),
            'recent_backups' => CloudBackup::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        // Get recent activities
        $recentBackups = CloudBackup::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBackups', 'recentUsers'));
    }

    /**
     * Show all users (view-only)
     */
    public function users(Request $request)
    {
        $this->checkAdminAccess();
        $query = User::query();

        // Apply filters
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->byStatus($request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $query->search($request->search);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details (view-only)
     */
    public function userShow($id)
    {
        $this->checkAdminAccess();
        $user = User::findOrFail($id);

        // Get user's activities based on role
        $activities = [];
        if ($user->isMidwife() || $user->isBhw()) {
            $activities = [
                'patients' => Patient::where('created_by', $user->id)->count(),
                'prenatal_records' => PrenatalRecord::where('created_by', $user->id)->count(),
                'child_records' => ChildRecord::where('created_by', $user->id)->count(),
                'immunizations' => Immunization::where('administered_by', $user->id)->count(),
            ];
        }

        return view('admin.users.show', compact('user', 'activities'));
    }

    /**
     * Show all patients (view-only)
     */
    public function patients(Request $request)
    {
        $this->checkAdminAccess();
        $query = Patient::query();

        // Apply search filter
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%")
                  ->orWhere('formatted_patient_id', 'like', "%{$search}%");
            });
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.patients.index', compact('patients'));
    }

    /**
     * Show patient details (view-only)
     */
    public function patientShow($id)
    {
        $this->checkAdminAccess();
        $patient = Patient::with(['prenatalRecords', 'childRecords'])->findOrFail($id);

        return view('admin.patients.show', compact('patient'));
    }

    /**
     * Show all records (view-only)
     */
    public function records()
    {
        $this->checkAdminAccess();
        $stats = [
            'prenatal_records' => PrenatalRecord::count(),
            'child_records' => ChildRecord::count(),
            'completed_immunizations' => Immunization::where('status', 'Done')->count(),
            'pending_immunizations' => Immunization::where('status', 'Upcoming')->count(),
        ];

        $recentPrenatal = PrenatalRecord::with(['patient'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentChildren = ChildRecord::with(['mother'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.records.index', compact('stats', 'recentPrenatal', 'recentChildren'));
    }
}