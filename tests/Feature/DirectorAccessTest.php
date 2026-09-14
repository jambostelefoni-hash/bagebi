<?php

namespace Tests\Feature;

use App\Model\API\Kindergartener;
use App\Model\Setting;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DirectorAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_director_is_restricted_to_the_assigned_kindergarten(): void
    {
        [$municipality, $ownGarden, $otherGarden] = $this->structure();
        $ownChild = $this->child($municipality, $ownGarden, '01000000901', 'საკუთარი');
        $otherChild = $this->child($municipality, $otherGarden, '01000000902', 'სხვა');
        $director = User::create(['name'=>'Director','email'=>'director@example.test','password'=>Hash::make('password'),'role'=>'director','kindergarten_id'=>$ownGarden]);

        $this->actingAs($director)->get(route('kindergarteners.index'))
            ->assertOk()->assertSee('01000000901')->assertDontSee('01000000902');
        $this->actingAs($director)->get(route('kindergarteners.show',$ownChild))->assertOk();
        $this->actingAs($director)->get(route('kindergarteners.show',$otherChild))->assertForbidden();
        $this->actingAs($director)->get(route('attendance.index',['kindergarten_id'=>$otherGarden]))
            ->assertOk()->assertSee('01000000901')->assertDontSee('01000000902');
        $this->actingAs($director)->get(route('kindergartens.list'))->assertForbidden();
        $this->actingAs($director)->get(route('users.list'))->assertForbidden();
    }

    public function test_only_union_admin_can_manage_age_groups(): void
    {
        [, $ownGarden] = $this->structure();
        $admin = User::create(['name'=>'Admin','email'=>'admin-groups@example.test','password'=>Hash::make('password'),'role'=>'union_admin']);
        $director = User::create(['name'=>'Director','email'=>'director-groups@example.test','password'=>Hash::make('password'),'role'=>'director','kindergarten_id'=>$ownGarden]);

        $this->actingAs($admin)->post(route('group-age-ranges.store'), ['range'=>'2-4'])
            ->assertRedirect(route('group-age-ranges.list'));
        $this->assertDatabaseHas('group_age_ranges', ['range'=>'2-4']);

        $this->actingAs($director)->post(route('group-age-ranges.store'), ['range'=>'სხვა ჯგუფი'])
            ->assertForbidden();
        $this->assertDatabaseMissing('group_age_ranges', ['range'=>'სხვა ჯგუფი']);
    }

    public function test_director_can_manage_own_profile_but_cannot_open_operations_control(): void
    {
        [, $garden] = $this->structure();
        $director = User::create(['name'=>'Director','email'=>'director-profile@example.test','password'=>Hash::make('password'),'role'=>'director','kindergarten_id'=>$garden]);

        $this->actingAs($director)->get(route('profile.edit'))->assertOk();
        $this->actingAs($director)->patch(route('profile.update'), ['name'=>'Updated Director','email'=>'director-profile@example.test'])
            ->assertRedirect();
        $this->assertDatabaseHas('users',['id'=>$director->id,'name'=>'Updated Director']);
        $this->actingAs($director)->get(route('operations.index'))->assertForbidden();
    }

    public function test_public_registration_is_rejected_when_registration_is_closed(): void
    {
        $this->structure();
        $basic=Setting::where('slug','basic')->firstOrFail();
        $basic->object=['isRegistrationStart'=>false,'isLearningStart'=>true,'canPorting'=>false];
        $basic->save();

        $this->postJson('/api/registration', [])->assertStatus(422)
            ->assertJsonValidationErrors('registration');
    }

    public function test_existing_child_can_be_edited_when_legacy_group_capacity_link_is_missing(): void
    {
        [$municipality, $garden] = $this->structure();
        $child = $this->child($municipality, $garden, '01000000903', 'ძველი');
        DB::table('kindergarten_group_age_range')->where('kindergarten_id', $garden)->where('group_age_range', 1)->delete();
        $admin = User::create(['name'=>'Admin','email'=>'admin-edit@example.test','password'=>Hash::make('password'),'role'=>'union_admin']);

        $this->actingAs($admin)->post(route('kindergarteners.store'), [
            'id'=>$child->id,
            'municipality_id'=>$municipality,
            'kindergarten_id'=>$garden,
            'group_id'=>1,
            'birth_date'=>'2022-01-01',
            'kids_personal_number'=>'01000000903',
            'kids_first_name'=>'განახლებული',
            'kids_last_name'=>'ბავშვი',
            'mobile_number'=>'555000000',
            'email'=>'parent@example.test',
        ])->assertRedirect();

        $this->assertDatabaseHas('kindergarteners', ['id'=>$child->id, 'kids_first_name'=>'განახლებული', 'application_status'=>'enrolled']);
    }

    private function structure(): array
    {
        $region=DB::table('regions')->insertGetId(['name'=>'რეგიონი','created_at'=>now(),'updated_at'=>now()]);
        $municipality=DB::table('municipalities')->insertGetId(['region_id'=>$region,'name'=>'მუნიციპალიტეტი','created_at'=>now(),'updated_at'=>now()]);
        $own=DB::table('kindergartens')->insertGetId(['municipality_id'=>$municipality,'name'=>'საკუთარი ბაღი','created_at'=>now(),'updated_at'=>now()]);
        $other=DB::table('kindergartens')->insertGetId(['municipality_id'=>$municipality,'name'=>'სხვა ბაღი','created_at'=>now(),'updated_at'=>now()]);
        foreach([$own,$other] as $garden) DB::table('kindergarten_group_age_range')->insert(['kindergarten_id'=>$garden,'group_age_range'=>1,'space_length'=>10,'space_filled'=>1,'space_free'=>9,'space_reserved'=>0]);
        Setting::create(['slug'=>'date','object'=>['start'=>'2026-09-15','end'=>'2027-06-15']]);
        Setting::create(['slug'=>'basic','object'=>['isRegistrationStart'=>true,'isLearningStart'=>true,'canPorting'=>false]]);
        return [$municipality,$own,$other];
    }

    private function child(int $municipality,int $garden,string $number,string $name): Kindergartener
    {
        return Kindergartener::create(['municipality_id'=>$municipality,'kindergarten_id'=>$garden,'group_id'=>1,'active_status_id'=>2,'application_status'=>'enrolled','kids_personal_number'=>$number,'kids_first_name'=>$name,'kids_last_name'=>'ბავშვი','birth_date'=>'2022-01-01','mobile_number'=>'555000000','email'=>'parent@example.test']);
    }
}
