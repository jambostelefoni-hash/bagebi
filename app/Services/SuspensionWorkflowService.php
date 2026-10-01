<?php

namespace App\Services;

use App\Model\API\Kindergartener;
use App\Model\ReinstatementRequest;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuspensionWorkflowService
{
    public function __construct(
        private ApplicationWorkflowService $workflow,
        private WorkCalendarService $calendar,
        private ParentNotificationService $notifications
    ) {}

    public function suspend(Kindergartener $child, string $reason, ?CarbonInterface $absenceFrom = null, ?CarbonInterface $absenceTo = null): Kindergartener
    {
        return DB::transaction(function () use ($child, $reason, $absenceFrom, $absenceTo) {
            $child = Kindergartener::whereKey($child->id)->lockForUpdate()->firstOrFail();
            if ($child->application_status === ApplicationWorkflowService::SUSPENDED) return $child;

            $child = $this->workflow->transition($child, ApplicationWorkflowService::SUSPENDED, $reason);
            $raw = Str::random(64);
            ReinstatementRequest::create([
                'kindergartener_id' => $child->id,
                'token_hash' => hash('sha256', $raw),
                'expires_at' => $this->calendar->addWorkingDays(now(), 5, $child->kindergarten_id),
                'absence_from' => $absenceFrom ?: today(),
                'absence_to' => $absenceTo ?: today(),
            ]);
            $this->notifications->send($child, 'registration_suspended', 'რეგისტრაცია შეჩერებულია', 'რეგისტრაცია დროებით შეჩერდა. საპატიო მიზეზის დოკუმენტი ატვირთეთ 5 სამუშაო დღეში.', route('reinstatement.show', $raw));

            return $child;
        }, 3);
    }
}
