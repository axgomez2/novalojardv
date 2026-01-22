<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request): View
    {
        $query = AdminActivityLog::with('user');

        // Filter by action
        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        // Filter by user
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by date range
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        $actions = AdminActivityLog::distinct()->pluck('action');

        return view('admin.activity-logs.index', compact('logs', 'actions'));
    }

    /**
     * Display the specified activity log.
     */
    public function show(AdminActivityLog $activityLog): View
    {
        $activityLog->load('user');

        return view('admin.activity-logs.show', compact('activityLog'));
    }
}
