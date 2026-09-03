<?php

namespace App\Http\Controllers;

use App\Model\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('user')->orderByDesc('id')->paginate(50);

        return view('audit-logs.index', [
            'logs' => $logs
        ]);
    }
}
