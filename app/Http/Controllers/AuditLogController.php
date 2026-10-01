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
        $filters = $this->filters($request);
        $query = $this->query($filters);
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

    public function export(Request $request)
    {
        $logs = $this->query($this->filters($request))->latest('id')->get();
        $this->logAudit('audit_logs.export', AuditLog::class, null, 'Audit log exported', ['count' => $logs->count()]);
        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID','თარიღი','მოქმედება','მომხმარებელი','როლი','ობიექტი','IP','აღწერა']);
            foreach ($logs as $log) fputcsv($out, [$log->id,$log->created_at->format('Y-m-d H:i:s'),$log->action,$log->actor_name ?: optional($log->user)->name ?: 'სისტემა',$log->actor_role ?: optional($log->user)->role ?: 'system',class_basename($log->model_type ?: 'System').($log->model_id ? '#'.$log->model_id : ''),$log->ip,$log->description]);
            fclose($out);
        }, 'audit-log-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'action' => ['nullable', 'string', 'max:100'],
            'severity' => ['nullable', 'in:critical,change,normal'],
            'actor' => ['nullable', 'in:user,system'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }

    private function query(array $filters)
    {
        $query = AuditLog::query()->with('user');

        $query->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId));
        $query->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', $action));
        $critical = ['kindergartener.delete','kindergartener.bulk_action','kindergarten.delete','user.delete','reinstatement.rejected','settings.learning','settings.learningStart','settings.learningEnd','waiting_list.offer_expired'];
        $change = ['application.status','kindergartener.update','group_age_range.update','user.update','settings.update','settings.date','calendar.update','public_page.update','registration_text.update','registration_text.rules.update','attendance.store','waiting_list.offer_created'];
        $query->when($filters['severity'] ?? null, function ($query, $severity) use ($critical, $change) {
            if ($severity === 'critical') return $query->whereIn('action', $critical);
            if ($severity === 'change') return $query->whereIn('action', $change);
            return $query->whereNotIn('action', array_merge($critical, $change));
        });
        $query->when($filters['actor'] ?? null, fn ($query, $actor) => $actor === 'system' ? $query->whereNull('user_id') : $query->whereNotNull('user_id'));
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

        return $query;
    }
}
