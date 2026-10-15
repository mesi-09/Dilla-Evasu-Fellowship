<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Read-only. There is deliberately no edit or delete action: the log is
     * only worth trusting if nobody, including the admins it records, can
     * change it.
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()->latest('created_at')->latest('id');

        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        $logs = $query->paginate(25)->withQueryString();

        $actions = AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-logs.index', compact('logs', 'actions'));
    }
}