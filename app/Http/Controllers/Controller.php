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

    protected function logAudit($action, $modelType = null, $modelId = null, $description = null, $changes = null)
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
            \Log::warning('Audit log failed: '.$e->getMessage());
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
}
