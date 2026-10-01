<?php

namespace Tests\Feature;

use App\Model\API\Kindergartener;
use App\Services\ApplicationWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CapacityLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_place_is_not_allocated_twice()
    {
        $region=DB::table('regions')->insertGetId(['name'=>'Test','created_at'=>now(),'updated_at'=>now()]);
        $municipality=DB::table('municipalities')->insertGetId(['region_id'=>$region,'name'=>'Test','created_at'=>now(),'updated_at'=>now()]);
        $garden=DB::table('kindergartens')->insertGetId(['municipality_id'=>$municipality,'name'=>'Garden','created_at'=>now(),'updated_at'=>now()]);
        DB::table('kindergarten_group_age_range')->insert(['kindergarten_id'=>$garden,'group_age_range'=>1,'space_length'=>1,'space_filled'=>0,'space_free'=>1,'space_reserved'=>0]);
        $service=app(ApplicationWorkflowService::class);
        $base=['kids_first_name'=>'ანა','kids_last_name'=>'ტესტი','birth_date'=>'2022-01-01','mobile_number'=>'555000000','municipality_id'=>$municipality,'kindergarten_id'=>$garden,'group_id'=>1];
        $first=$service->save($base+['kids_personal_number'=>'01000000001']);
        $second=$service->save($base+['kids_personal_number'=>'01000000002']);
        $this->assertSame('enrolled',$first->application_status);
        $this->assertSame('waiting',$second->application_status);
        $this->assertDatabaseHas('kindergarten_group_age_range',['kindergarten_id'=>$garden,'group_age_range'=>1,'space_filled'=>1,'space_free'=>0]);
    }

    public function test_public_lookup_does_not_expose_parent_data()
    {
        Kindergartener::create(['kids_personal_number'=>'01000000003','kids_first_name'=>'ანა','kids_last_name'=>'ტესტი','mother_personal_number'=>'01000000004','mobile_number'=>'555000000','application_status'=>'waiting']);
        $this->postJson('/api/find-kid',['kids_personal_number'=>'01000000003','mobile_last_four'=>'0000'])->assertOk()->assertJsonMissing(['mother_personal_number'=>'01000000004'])->assertJsonPath('data.application_status','waiting');
        $this->postJson('/api/find-kid',['kids_personal_number'=>'01000000003','mobile_last_four'=>'1234'])->assertOk()->assertJsonPath('data',null);
    }
}
