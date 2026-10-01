<?php

namespace App\Services;

use App\Model\SystemProcessRun;
use Closure;

class SystemProcessMonitor
{
    public function record(string $process, Closure $operation): int
    {
        $run = SystemProcessRun::create(['process' => $process, 'status' => 'running', 'started_at' => now()]);

        try {
            $result = (int) $operation();
            $run->update(['status' => 'success', 'finished_at' => now()]);
            return $result;
        } catch (\Throwable $exception) {
            $run->update(['status' => 'failed', 'finished_at' => now(), 'error' => mb_substr($exception->getMessage(), 0, 2000)]);
            throw $exception;
        }
    }
}
