<?php

use App\Model\API\Kindergartener;
use App\Services\ApplicationWorkflowService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoKindergartenSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $now = now();
            $regionId = DB::table('regions')->where('name', 'დემო რეგიონი')->value('id')
                ?: DB::table('regions')->insertGetId(['name'=>'დემო რეგიონი','created_at'=>$now,'updated_at'=>$now]);
            $municipalityId = DB::table('municipalities')->where('region_id', $regionId)->where('name', 'დემო მუნიციპალიტეტი')->value('id')
                ?: DB::table('municipalities')->insertGetId(['region_id'=>$regionId,'name'=>'დემო მუნიციპალიტეტი','created_at'=>$now,'updated_at'=>$now]);
            $gardenId = DB::table('kindergartens')->where('municipality_id', $municipalityId)->where('name', 'დემო საბავშვო ბაღი')->value('id')
                ?: DB::table('kindergartens')->insertGetId(['municipality_id'=>$municipalityId,'name'=>'დემო საბავშვო ბაღი','created_at'=>$now,'updated_at'=>$now]);

            $groups = DB::table('group_age_ranges')->orderBy('id')->get();
            foreach ($groups as $group) {
                DB::table('kindergarten_group_age_range')->insertOrIgnore([
                    'kindergarten_id'=>$gardenId,
                    'group_age_range'=>$group->id,
                    'space_length'=>20,
                    'space_filled'=>0,
                    'space_free'=>20,
                    'space_reserved'=>0,
                ]);
            }

            $names = [['ანა','ბერიძე'],['ნიკა','გელაშვილი'],['მარიამ','დავითაშვილი'],['ლუკა','ელიზბარაშვილი'],['ელენე','ვაჩნაძე'],['გიორგი','თოდუა'],['სალომე','იოსელიანი'],['ანდრია','კაპანაძე']];
            $birthYears = [1=>2023,2=>2022,3=>2021,4=>2020];
            $workflow = app(ApplicationWorkflowService::class);
            $offset = 0;
            foreach ($groups as $group) {
                for ($number = 1; $number <= 2; $number++) {
                    $personalNumber = sprintf('9900000%04d', ((int)$group->id * 10) + $number);
                    if (Kindergartener::where('kids_personal_number', $personalNumber)->exists()) { $offset++; continue; }
                    [$firstName,$lastName] = $names[$offset++ % count($names)];
                    $workflow->save([
                        'kids_personal_number'=>$personalNumber,
                        'kids_first_name'=>$firstName,
                        'kids_last_name'=>$lastName,
                        'birth_date'=>($birthYears[$group->id] ?? 2022).'-10-01',
                        'mother_first_name'=>'დემო მშობელი',
                        'mother_last_name'=>$lastName,
                        'mobile_number'=>'555000000',
                        'email'=>null,
                        'municipality_id'=>$municipalityId,
                        'kindergarten_id'=>$gardenId,
                        'group_id'=>$group->id,
                    ]);
                }
            }
        }, 3);
    }
}
