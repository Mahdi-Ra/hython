<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission(\App\Models\User::PERMISSION_AUDIT_VIEW), 403);

        $logs = AuditLog::query()
            ->with('user:id,name')
            ->latest()
            ->paginate(30);

        return view('admin.audit.index', compact('logs'));
    }
}
