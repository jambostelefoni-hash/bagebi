<?php

use App\Services\ApplicationWorkflowService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$connection = config('database.default');
$database = (string) config("database.connections.{$connection}.database");
if (!preg_match('/(^|_)testing$/', $database)) {
    fwrite(STDERR, "Refusing concurrency check outside a testing database.\n");
    exit(2);
}
if (!function_exists('pcntl_fork')) {
    fwrite(STDERR, "The pcntl extension is required.\n");
    exit(2);
}

$workers = max(2, min(50, (int) ($argv[1] ?? 20)));
$suffix = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
$region = DB::table('regions')->insertGetId(['name' => "Race {$suffix}"]);
$municipality = DB::table('municipalities')->insertGetId(['name' => "Race {$suffix}", 'region_id' => $region]);
$garden = DB::table('kindergartens')->insertGetId(['name' => "Race {$suffix}", 'municipality_id' => $municipality]);
DB::table('kindergarten_group_age_range')->insert([
    'kindergarten_id' => $garden, 'group_age_range' => 1,
    'space_length' => 1, 'space_filled' => 0, 'space_free' => 1, 'space_reserved' => 0,
]);

$start = microtime(true) + 2;
$pids = [];
for ($i = 0; $i < $workers; $i++) {
    $pid = pcntl_fork();
    if ($pid === -1) {
        fwrite(STDERR, "Could not start worker {$i}.\n");
        exit(2);
    }
    if ($pid !== 0) {
        $pids[] = $pid;
        continue;
    }
    try {
        DB::purge();
        $delay = $start - microtime(true);
        if ($delay > 0) usleep((int) ($delay * 1000000));
        app(ApplicationWorkflowService::class)->save([
            'kids_personal_number' => '8'.substr($suffix, 0, 8).str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            'kids_first_name' => 'ტესტი', 'kids_last_name' => 'ბავშვი',
            'birth_date' => '2022-01-01', 'mobile_number' => '555000000',
            'municipality_id' => $municipality, 'kindergarten_id' => $garden, 'group_id' => 1,
        ]);
        exit(0);
    } catch (Throwable $error) {
        fwrite(STDERR, "Worker {$i}: ".get_class($error)."\n");
        exit(1);
    }
}

$failed = 0;
foreach ($pids as $pid) {
    pcntl_waitpid($pid, $status);
    if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) $failed++;
}
DB::purge();
$enrolled = DB::table('kindergarteners')->where('kindergarten_id', $garden)->where('application_status', 'enrolled')->count();
$waiting = DB::table('kindergarteners')->where('kindergarten_id', $garden)->where('application_status', 'waiting')->count();
$slot = DB::table('kindergarten_group_age_range')->where('kindergarten_id', $garden)->where('group_age_range', 1)->first();
$queued = DB::table('waiting_list_entries')->where('kindergarten_id', $garden)->where('state', 'waiting')->count();
echo "workers={$workers} failed={$failed} enrolled={$enrolled} waiting={$waiting} queued={$queued} filled={$slot->space_filled} free={$slot->space_free}\n";
exit($failed === 0 && $enrolled === 1 && $waiting === $workers - 1 && $queued === $workers - 1
    && (int) $slot->space_filled === 1 && (int) $slot->space_free === 0 ? 0 : 1);
