<?php

namespace App\Http\Controllers;

use App\Models\SmsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsLogController extends Controller
{
    /**
     * Display SMS logs
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized access');
        }

        // Build query
        $query = SmsLog::with('sentBy')->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by recipient number or name
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('recipient_number', 'like', "%{$request->search}%")
                  ->orWhere('recipient_name', 'like', "%{$request->search}%")
                  ->orWhere('message', 'like', "%{$request->search}%");
            });
        }

        // Paginate results
        $smsLogs = $query->paginate(20)->appends($request->query());

        // Statistics
        $stats = [
            'total' => SmsLog::count(),
            'sent' => SmsLog::where('status', 'sent')->count(),
            'failed' => SmsLog::where('status', 'failed')->count(),
            'today' => SmsLog::whereDate('created_at', today())->count(),
        ];

        // View path based on role
        $viewPath = $user->role === 'bhw'
            ? 'bhw.sms-logs.index'
            : 'midwife.sms-logs.index';

        return view($viewPath, compact('smsLogs', 'stats'));
    }

    /**
     * Show details of a specific SMS log
     */
    public function show($id)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized access');
        }

        $smsLog = SmsLog::with('sentBy')->findOrFail($id);

        return response()->json($smsLog);
    }
}
