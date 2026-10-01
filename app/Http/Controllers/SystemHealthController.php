<?php

namespace App\Http\Controllers;

use App\Model\NotificationDelivery;
use App\Model\SystemProcessRun;
use Carbon\Carbon;

class SystemHealthController extends Controller
{
    public function index()
    {
        $now = now();
        $waiting = SystemProcessRun::where('process', 'waiting_list')->latest('started_at')->first();
        $attendance = SystemProcessRun::where('process', 'attendance_evaluation')->latest('started_at')->first();
        $lastDelivery = NotificationDelivery::where('status', 'sent')->latest('sent_at')->first();
        $failedDeliveries = NotificationDelivery::query()
            ->where('status', 'failed')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('notification_deliveries as successful_deliveries')
                    ->whereColumn('successful_deliveries.kindergartener_id', 'notification_deliveries.kindergartener_id')
                    ->whereColumn('successful_deliveries.event', 'notification_deliveries.event')
                    ->whereColumn('successful_deliveries.channel', 'notification_deliveries.channel')
                    ->where('successful_deliveries.status', 'sent')
                    ->whereColumn('successful_deliveries.sent_at', '>=', 'notification_deliveries.updated_at');
            })
            ->count();
        $queuedDeliveries = NotificationDelivery::where('status', 'queued')->count();
        $staleQueuedDeliveries = NotificationDelivery::where('status', 'queued')->where('created_at', '<', $now->copy()->subMinutes(15))->count();

        return view('system-health.index', [
            'processes' => [
                $this->processCard('ავტომატური რიგი', 'ყოველ 10 წუთში', $waiting, $now->copy()->subMinutes(25)),
                $this->attendanceCard($attendance, $now),
                [
                    'name' => 'შეტყობინებების მიწოდება', 'schedule' => 'ფონური რიგის მეშვეობით', 'run' => $lastDelivery,
                    'status' => $failedDeliveries ? 'failed' : ($staleQueuedDeliveries ? 'warning' : 'success'),
                    'message' => $failedDeliveries ? $failedDeliveries.' შეტყობინება ვერ გაიგზავნა.' : ($staleQueuedDeliveries ? $staleQueuedDeliveries.' შეტყობინება 15 წუთზე მეტია გაგზავნის რიგშია.' : ($queuedDeliveries ? $queuedDeliveries.' შეტყობინება იგზავნება ფონური რიგის მეშვეობით.' : 'მიწოდების რიგში შეფერხება არ არის.')),
                    'meta' => $lastDelivery ? 'ბოლო გაგზავნა: '.$lastDelivery->sent_at->format('d.m.Y H:i') : 'წარმატებით გაგზავნილი შეტყობინება ჯერ არ არის.',
                ],
            ],
        ]);
    }

    private function processCard(string $name, string $schedule, ?SystemProcessRun $run, Carbon $staleAfter): array
    {
        if (!$run) return compact('name', 'schedule', 'run') + ['status' => 'unknown', 'message' => 'შესრულების ჩანაწერი ჯერ არ არის.', 'meta' => 'შემდეგი ავტომატური გაშვების შემდეგ სტატუსი განახლდება.'];
        if ($run->status === 'failed') return compact('name', 'schedule', 'run') + ['status' => 'failed', 'message' => 'ბოლო გაშვება შეცდომით დასრულდა.', 'meta' => $run->error ?: 'შეცდომის დეტალი არ არის.'];
        if ($run->status === 'running' || $run->started_at->lt($staleAfter)) return compact('name', 'schedule', 'run') + ['status' => 'warning', 'message' => 'ბოლო წარმატებული გაშვება მოძველებულია.', 'meta' => 'ბოლო დაწყება: '.$run->started_at->format('d.m.Y H:i')];
        return compact('name', 'schedule', 'run') + ['status' => 'success', 'message' => 'ავტომატური პროცესი გამართულად მუშაობს.', 'meta' => 'ბოლო წარმატება: '.$run->finished_at->format('d.m.Y H:i')];
    }

    private function attendanceCard(?SystemProcessRun $run, Carbon $now): array
    {
        $todayRun = $run && $run->started_at->isSameDay($now);
        if ($now->format('H:i') < '20:15' && !$todayRun) return ['name' => 'გაცდენების შემოწმება', 'schedule' => 'ყოველდღე 20:00-ზე', 'run' => $run, 'status' => 'scheduled', 'message' => 'დღევანდელი შემოწმება ჯერ დაგეგმილია.', 'meta' => $run ? 'წინა წარმატება: '.$run->finished_at->format('d.m.Y H:i') : 'პირველი გაშვება მოსალოდნელია 20:00-ზე.'];
        return $this->processCard('გაცდენების შემოწმება', 'ყოველდღე 20:00-ზე', $run, $now->copy()->subDay());
    }
}
