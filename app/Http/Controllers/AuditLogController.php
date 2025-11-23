<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can view audit logs.');
        }

        $logs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('audit-logs.index', compact('logs'));
    }

    public function show(AuditLog $auditLog)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can view audit logs.');
        }

        return view('audit-logs.show', compact('auditLog'));
    }
}

