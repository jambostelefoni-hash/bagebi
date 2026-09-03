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
            AuditLog::create([
                'user_id' => auth()->id(),
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
            $changes[$key] = [
                'old' => $model->getOriginal($key),
                'new' => $newValue
            ];
        }

        return $changes;
    }
}
