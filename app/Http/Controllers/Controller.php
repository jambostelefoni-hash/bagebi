<?php

namespace App\Http\Controllers;

use App\Model\AuditLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function logAudit($action, $modelType = null, $modelId = null, $description = null, $changes = null, bool $required = false)
    {
        try {
            $actor = auth()->user();
            AuditLog::create([
                'user_id' => optional($actor)->id,
                'actor_name' => optional($actor)->name,
                'actor_email' => optional($actor)->email,
                'actor_role' => optional($actor)->role,
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'description' => $description,
                'changes' => $changes,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } catch (\Throwable $e) {
            if ($required) throw $e;
            \Log::warning('Audit log failed', ['action' => $action, 'exception' => get_class($e)]);
        }
    }

    protected function buildAuditChanges($model)
    {
        $changes = [];
        foreach ($model->getDirty() as $key => $newValue) {
            if (in_array($key, ['kids_personal_number','mother_personal_number','father_personal_number','mobile_number','email','password','remember_token'], true)) {
                $changes[$key] = ['changed' => true];
                continue;
            }
            $changes[$key] = [
                'old' => $model->getOriginal($key),
                'new' => $newValue
            ];
        }

        return $changes;
    }

    protected function buildAuditChangesFromValues(array $before, array $after): array
    {
        $changes = [];
        $hidden = ['password', 'remember_token'];

        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $key) {
            $oldValue = $before[$key] ?? null;
            $newValue = $after[$key] ?? null;

            if ((string) $oldValue === (string) $newValue) {
                continue;
            }

            if (in_array($key, $hidden, true)) {
                $changes[$key] = ['changed' => true];
                continue;
            }

            $changes[$key] = ['old' => $oldValue, 'new' => $newValue];
        }

        return $changes;
    }
}
