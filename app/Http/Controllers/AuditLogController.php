<?php

namespace App\Http\Controllers;

use App\Model\AuditLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'action' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = AuditLog::query()->with('user');

        $query->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId));
        $query->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', $action));
        $query->when($filters['date_from'] ?? null, fn ($query, $date) => $query->where('created_at', '>=', Carbon::parse($date)->startOfDay()));
        $query->when($filters['date_to'] ?? null, fn ($query, $date) => $query->where('created_at', '<=', Carbon::parse($date)->endOfDay()));
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip', 'like', "%{$search}%")
                    ->orWhere('model_type', 'like', "%{$search}%")
                    ->orWhere('model_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        });

        $logs = $query->latest('id')->paginate(30)->appends($request->query());
        $userIds = AuditLog::query()->whereNotNull('user_id')->distinct()->pluck('user_id');

        return view('audit-logs.index', [
            'logs' => $logs,
            'users' => User::query()->whereIn('id', $userIds)->orderBy('name')->get(['id', 'name', 'email', 'role']),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'stats' => [
                'total' => AuditLog::count(),
                'today' => AuditLog::where('created_at', '>=', now()->startOfDay())->count(),
                'week' => AuditLog::where('created_at', '>=', now()->subDays(7))->count(),
                'actors' => AuditLog::whereNotNull('user_id')->distinct()->count('user_id'),
            ],
        ]);
    }
}
