<?php

namespace Tests\Feature;

use App\Model\API\Kindergartener;
use App\Services\ApplicationWorkflowService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConcurrentCapacityTest extends TestCase
{
    use DatabaseMigrations;

    public function test_two_processes_cannot_allocate_the_same_last_place(): void
    {
        if (!function_exists('pcntl_fork')) $this->markTestSkipped('pcntl is required for the concurrency test.');
        if (config('database.connections.'.config('database.default').'.database') !== 'bagebi_testing') {
            throw new \RuntimeException('This test requires the isolated bagebi_testing database.');
        }

        $region = DB::table('regions')->insertGetId(['name'=>'Concurrency region','created_at'=>now(),'updated_at'=>now()]);
        $municipality = DB::table('municipalities')->insertGetId(['region_id'=>$region,'name'=>'Concurrency municipality','created_at'=>now(),'updated_at'=>now()]);
        $garden = DB::table('kindergartens')->insertGetId(['municipality_id'=>$municipality,'name'=>'Concurrency garden','created_at'=>now(),'updated_at'=>now()]);
        DB::table('kindergarten_group_age_range')->insert(['kindergarten_id'=>$garden,'group_age_range'=>1,'space_length'=>1,'space_filled'=>0,'space_free'=>1,'space_reserved'=>0]);

        $pids = [];
        foreach (['01009990001','01009990002'] as $index => $personalNumber) {
            $pid = pcntl_fork();
            if ($pid === -1) $this->fail('Unable to fork concurrency worker.');
            if ($pid === 0) {
                try {
                    DB::purge();
                    app(ApplicationWorkflowService::class)->save([
                        'kids_personal_number'=>$personalNumber,
                        'kids_first_name'=>$index === 0 ? 'პირველი' : 'მეორე',
                        'kids_last_name'=>'ტესტი',
                        'birth_date'=>'2023-01-01',
                        'mobile_number'=>'551120240',
                        'municipality_id'=>$municipality,
                        'kindergarten_id'=>$garden,
                        'group_id'=>1,
                    ]);
                    exit(0);
                } catch (\Throwable $exception) {
                    fwrite(STDERR, $exception->getMessage().PHP_EOL);
                    exit(1);
                }
            }
            $pids[] = $pid;
        }

        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
            $this->assertTrue(pcntl_wifexited($status) && pcntl_wexitstatus($status) === 0, 'A concurrency worker failed.');
        }

        DB::purge();
        $this->assertSame(1, Kindergartener::where('application_status','enrolled')->count());
        $this->assertSame(1, Kindergartener::where('application_status','waiting')->count());
        $this->assertDatabaseHas('kindergarten_group_age_range', [
            'kindergarten_id'=>$garden,'group_age_range'=>1,'space_length'=>1,
            'space_filled'=>1,'space_free'=>0,'space_reserved'=>0,
        ]);
    }
}
