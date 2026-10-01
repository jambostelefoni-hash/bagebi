<?php

namespace Tests\Feature;

use App\Exports\AttendanceExport;
use App\Exports\KindergartenerExport;
use App\Model\API\Kindergartener;
use App\Model\Attendance;
use App\Model\PlacementOffer;
use App\Model\NotificationDelivery;
use App\Model\ReinstatementRequest;
use App\Model\WorkCalendarDay;
use App\Services\ApplicationWorkflowService;
use App\Services\ParentNotificationService;
use App\Notifications\ParentSmsNotification;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkflowLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_page_has_a_friendly_empty_state_without_gardens(): void
    {
        $this->configureLearning();
        $admin = User::create(['name'=>'Admin','email'=>'empty-attendance@example.test','password'=>Hash::make('password'),'role'=>'union_admin']);

        $this->actingAs($admin)->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('ბაღი ჯერ არ არის დამატებული');
    }

    public function test_registration_personal_number_check_blocks_an_existing_child(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000091');

        $this->postJson('/api/registration/check-personal-number', ['kids_personal_number'=>'01000000091'])
            ->assertOk()
            ->assertExactJson(['exists'=>true]);
        $this->postJson('/api/registration/check-personal-number', ['kids_personal_number'=>'01000000092'])
            ->assertOk()
            ->assertExactJson(['exists'=>false]);
    }

    public function test_public_registration_data_only_contains_fields_needed_by_the_form(): void
    {
        DB::table('settings')->insert([
            ['slug' => 'basic', 'object' => json_encode(['isRegistrationStart' => true, 'isPrioritetiesStart' => true, 'canPorting' => true]), 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'date', 'object' => json_encode(['start' => '2026-09-01', 'end' => '2027-06-15']), 'created_at' => now(), 'updated_at' => now()],
        ]);
        [$municipality] = $this->gardenWithCapacity(2);

        $response = $this->postJson('/api/data-object')->assertOk();

        $response->assertJsonPath('setting.object.isRegistrationStart', true)
            ->assertJsonPath('setting.object.isPrioritetiesStart', true)
            ->assertJsonMissing(['canPorting' => true])
            ->assertJsonMissing(['end' => '2027-06-15'])
            ->assertJsonPath('municipalities.0.id', $municipality);
    }

    public function test_expired_reinstatement_link_shows_the_gone_page(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000093');
        app(ApplicationWorkflowService::class)->transition($child, 'suspended');
        $token = Str::random(64);
        ReinstatementRequest::create([
            'kindergartener_id' => $child->id,
            'token_hash' => hash('sha256', $token),
            'status' => 'pending',
            'expires_at' => now()->subMinute(),
        ]);

        $this->get(route('reinstatement.show', $token))->assertStatus(410)->assertSee('ბმულის ვადა ამოიწურა');
    }

    public function test_waiting_list_reserves_the_first_eligible_child(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);

        $enrolled = $this->createChild($workflow, $municipality, $garden, '01000000101');
        $waiting = $this->createChild($workflow, $municipality, $garden, '01000000102', ['priority_id' => 1, 'has_permission' => true]);

        $this->assertSame('enrolled', $enrolled->application_status);
        $this->assertSame('waiting', $waiting->application_status);

        $workflow->transition($enrolled, 'cancelled', 'Test vacancy');
        Artisan::call('waiting-list:process');

        $entry = $waiting->fresh()->waitingListEntry;
        $this->assertSame('offered', $entry->state);
        $this->assertDatabaseHas('placement_offers', ['waiting_list_entry_id' => $entry->id]);
        $this->assertDatabaseHas('kindergarten_group_age_range', [
            'kindergarten_id' => $garden,
            'group_age_range' => 1,
            'space_free' => 1,
            'space_reserved' => 1,
        ]);
    }

    public function test_accepting_a_placement_offer_enrolls_once_and_consumes_reservation(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $enrolled = $this->createChild($workflow, $municipality, $garden, '01000000201');
        $waiting = $this->createChild($workflow, $municipality, $garden, '01000000202');

        $workflow->transition($enrolled, 'cancelled', 'Test vacancy');
        Artisan::call('waiting-list:process');
        $entry = $waiting->fresh()->waitingListEntry;
        $offer = PlacementOffer::where('waiting_list_entry_id', $entry->id)->firstOrFail();

        $workflow->acceptReservedPlacement($waiting->fresh(), $entry);

        $this->assertSame('enrolled', $waiting->fresh()->application_status);
        $this->assertSame('completed', $entry->fresh()->state);
        $this->assertDatabaseHas('kindergarten_group_age_range', [
            'kindergarten_id' => $garden,
            'group_age_range' => 1,
            'space_filled' => 1,
            'space_free' => 0,
            'space_reserved' => 0,
        ]);
        $this->assertNotNull($offer->token_hash);
    }

    public function test_declining_a_placement_offer_cancels_application_and_releases_reservation(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $enrolled = $this->createChild($workflow, $municipality, $garden, '01000000211');
        $waiting = $this->createChild($workflow, $municipality, $garden, '01000000212');

        $workflow->transition($enrolled, 'cancelled', 'Test vacancy');
        Artisan::call('waiting-list:process');
        $token = 'decline-test-token';
        $offer = PlacementOffer::firstOrFail();
        $offer->update(['token_hash' => hash('sha256', $token)]);

        $this->post('/placement-offers/'.$token, ['response' => 'declined'])->assertOk();

        $this->assertSame('cancelled', $waiting->fresh()->application_status);
        $this->assertSame('completed', $waiting->fresh()->waitingListEntry->state);
        $this->assertSame('declined', $offer->fresh()->response);
        $this->assertEquals(0, DB::table('kindergarten_group_age_range')->where('kindergarten_id', $garden)->value('space_reserved'));
    }

    public function test_expired_offer_shows_friendly_page(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $enrolled = $this->createChild($workflow, $municipality, $garden, '01000000221');
        $this->createChild($workflow, $municipality, $garden, '01000000222');
        $workflow->transition($enrolled, 'cancelled', 'Test vacancy');
        Artisan::call('waiting-list:process');
        $token = 'expired-page-token';
        PlacementOffer::firstOrFail()->update(['token_hash' => hash('sha256', $token), 'expires_at' => now()->subMinute()]);

        $this->get('/placement-offers/'.$token)->assertStatus(410)->assertSee('ბმულის ვადა ამოიწურა');
    }

    public function test_ten_consecutive_working_day_absences_suspend_registration_once(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000301');

        $date = today()->copy();
        $recorded = 0;
        while ($recorded < 10) {
            if (!$date->isWeekend()) {
                Attendance::create([
                    'kindergartener_id' => $child->id,
                    'kindergarten_id' => $garden,
                    'group_id' => 1,
                    'attendance_date' => $date->toDateString(),
                    'status' => 'absent',
                ]);
                $recorded++;
            }
            $date->subDay();
        }

        Artisan::call('attendance:evaluate');
        Artisan::call('attendance:evaluate');

        $this->assertSame('suspended', $child->fresh()->application_status);
        $this->assertSame(1, ReinstatementRequest::where('kindergartener_id', $child->id)->count());
    }

    public function test_union_admin_can_run_attendance_evaluation_before_schedule(): void
    {
        config(['services.mailtrap.api_token' => null]);
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000303');
        $admin = User::create(['name'=>'Admin','email'=>'attendance-admin@example.test','password'=>Hash::make('password'),'role'=>'union_admin']);
        DB::table('settings')->insert([
            ['slug'=>'basic','object'=>json_encode(['isRegistrationStart'=>true,'isLearningStart'=>true,'canPorting'=>false]),'created_at'=>now(),'updated_at'=>now()],
            ['slug'=>'date','object'=>json_encode(['start'=>'2026-09-01','end'=>'2027-06-15']),'created_at'=>now(),'updated_at'=>now()],
        ]);
        $date = today()->copy();
        $recorded = 0;

        while ($recorded < 10) {
            if (!$date->isWeekend()) {
                Attendance::create(['kindergartener_id'=>$child->id,'kindergarten_id'=>$garden,'group_id'=>1,'attendance_date'=>$date->toDateString(),'status'=>'absent']);
                $recorded++;
            }
            $date->subDay();
        }

        $this->actingAs($admin)->post(route('attendance.evaluate'), ['kindergarten_id'=>$garden])->assertRedirect();

        $this->assertSame('suspended', $child->fresh()->application_status);
        $this->assertDatabaseHas('audit_logs', ['action'=>'attendance.evaluate','user_id'=>$admin->id]);
    }

    public function test_monthly_absence_limit_ignores_non_working_calendar_days(): void
    {
        Carbon::setTestNow('2026-09-30 21:00:00');
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000302');
        $date = now()->startOfMonth();
        $recordedDates = [];

        while (count($recordedDates) < 15) {
            if (!$date->isWeekend()) {
                $recordedDates[] = $date->toDateString();
                Attendance::create([
                    'kindergartener_id' => $child->id,
                    'kindergarten_id' => $garden,
                    'group_id' => 1,
                    'attendance_date' => $date->toDateString(),
                    'status' => 'absent',
                ]);
            }
            $date->addDay();
        }

        WorkCalendarDay::create([
            'date' => $recordedDates[0],
            'kindergarten_id' => $garden,
            'is_working_day' => false,
            'label' => 'Test closure',
        ]);

        Artisan::call('attendance:evaluate');

        $this->assertSame('enrolled', $child->fresh()->application_status);
        $this->assertSame(0, ReinstatementRequest::where('kindergartener_id', $child->id)->count());
        Carbon::setTestNow();
    }

    public function test_approved_reinstatement_marks_absences_excused_and_restores_enrollment(): void
    {
        $notifications = \Mockery::mock(ParentNotificationService::class);
        $notifications->shouldReceive('send')->once()->withArgs(function ($sentChild, $event, $subject, $message) use (&$child) {
            return $sentChild->id === $child->id
                && $event === 'reinstatement_approved'
                && $subject === 'აღდგენის მოთხოვნის პასუხი'
                && str_contains($message, 'დოკუმენტი სრულად შეესაბამება მოთხოვნებს.');
        });
        $this->app->instance(ParentNotificationService::class, $notifications);
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $workflow = app(ApplicationWorkflowService::class);
        $child = $this->createChild($workflow, $municipality, $garden, '01000000401');
        $workflow->transition($child, 'suspended', 'Test suspension');

        $from = today()->subDays(2)->toDateString();
        Attendance::create(['kindergartener_id' => $child->id, 'kindergarten_id' => $garden, 'group_id' => 1, 'attendance_date' => $from, 'status' => 'absent']);
        Attendance::create(['kindergartener_id' => $child->id, 'kindergarten_id' => $garden, 'group_id' => 1, 'attendance_date' => today()->toDateString(), 'status' => 'absent']);
        $request = ReinstatementRequest::create([
            'kindergartener_id' => $child->id,
            'token_hash' => hash('sha256', Str::random(64)),
            'expires_at' => now()->addDays(5),
            'absence_from' => $from,
            'absence_to' => today()->toDateString(),
            'document_path' => 'reinstatement-documents/test-document.pdf',
        ]);
        DB::table('settings')->insert([
            ['slug' => 'basic', 'object' => json_encode(['canPorting' => false, 'isLearningStart' => true]), 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'date', 'object' => json_encode(['start' => '2026-09-01', 'end' => '2027-06-15']), 'created_at' => now(), 'updated_at' => now()],
        ]);
        $admin = User::create([
            'name' => 'Test administrator',
            'email' => 'admin@example.test',
            'password' => Hash::make('testing-password'),
            'role' => 'union_admin',
        ]);

        $this->actingAs($admin)
            ->post("/reinstatement-requests/{$request->id}/review", ['decision' => 'approved', 'review_note' => 'დოკუმენტი სრულად შეესაბამება მოთხოვნებს.'])
            ->assertRedirect();

        $this->assertSame('enrolled', $child->fresh()->application_status);
        $this->assertSame('approved', $request->fresh()->status);
        $this->assertSame(0, Attendance::where('kindergartener_id', $child->id)->where('status', 'absent')->count());
        $this->assertSame(2, Attendance::where('kindergartener_id', $child->id)->where('status', 'excused')->count());
    }

    public function test_application_and_attendance_exports_use_georgian_status_labels(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000402');
        $attendance = Attendance::create([
            'kindergartener_id' => $child->id,
            'kindergarten_id' => $garden,
            'group_id' => 1,
            'attendance_date' => today()->toDateString(),
            'status' => 'absent',
        ]);

        $this->assertSame('ჩარიცხული', $child->application_status_label);
        $this->assertSame('გაცდენილი', $attendance->status_label);
        $this->assertSame('ჩარიცხული', (new KindergartenerExport($garden))->map($child)[3]);
        $this->assertSame('გაცდენილი', (new AttendanceExport($garden))->map($attendance)[3]);
        $this->assertStringContainsString('გაცდენილი', view('attendance.report', ['rows' => collect([$attendance])])->render());
    }

    public function test_waiting_edit_preserves_position_and_active_offer(): void
    {
        [$m, $g] = $this->gardenWithCapacity(1);
        $w = app(ApplicationWorkflowService::class);
        $enrolled = $this->createChild($w, $m, $g, '01000000801');
        $child = $this->createChild($w, $m, $g, '01000000802');
        $entry = $child->waitingListEntry;
        $entry->update(['queued_at' => now()->subDays(3)]);
        $queued = $entry->fresh()->queued_at->toDateTimeString();
        $w->transition($enrolled, 'cancelled');
        Artisan::call('waiting-list:process');
        $w->save($child->toArray(), $child);
        $this->assertSame($queued, $entry->fresh()->queued_at->toDateTimeString());
        $this->assertSame('offered', $entry->fresh()->state);
    }

    public function test_expired_offer_cancels_application_and_moves_to_next_child(): void
    {
        [$m, $g] = $this->gardenWithCapacity(1);
        $w = app(ApplicationWorkflowService::class);
        $enrolled = $this->createChild($w, $m, $g, '01000000811');
        $first = $this->createChild($w, $m, $g, '01000000812');
        $second = $this->createChild($w, $m, $g, '01000000813');
        $first->waitingListEntry->update(['queued_at' => now()->subDay()]);
        $w->transition($enrolled, 'cancelled');
        Artisan::call('waiting-list:process');
        PlacementOffer::first()->update(['expires_at' => now()->subMinute()]);
        Artisan::call('waiting-list:process');
        $this->assertSame('cancelled', $first->fresh()->application_status);
        $this->assertSame('completed', $first->fresh()->waitingListEntry->state);
        $this->assertSame('offered', $second->fresh()->waitingListEntry->state);
        $w->transition($second, 'cancelled');
        Artisan::call('waiting-list:process');
        $this->assertSame('completed', $second->fresh()->waitingListEntry->state);
        $this->assertSame(0, PlacementOffer::whereNull('responded_at')->count());
        $this->assertEquals(0, DB::table('kindergarten_group_age_range')->where('kindergarten_id',$g)->value('space_reserved'));
    }

    public function test_suspended_transfer_preserves_status_and_releases_old_place(): void
    {
        [$m, $g] = $this->gardenWithCapacity(1);
        [, $target] = $this->gardenWithCapacity(1);
        $w = app(ApplicationWorkflowService::class);
        $child = $this->createChild($w, $m, $g, '01000000821');
        $child = $w->transition($child, 'suspended');
        $moved = $w->save(array_merge($child->toArray(), ['kindergarten_id'=>$target]), $child);
        $this->assertSame('suspended', $moved->application_status);
        $this->assertEquals(0, DB::table('kindergarten_group_age_range')->where('kindergarten_id',$g)->value('space_filled'));
        $this->assertEquals(1, DB::table('kindergarten_group_age_range')->where('kindergarten_id',$target)->value('space_filled'));
    }

    public function test_uploaded_document_survives_deadline_and_review_is_atomic(): void
    {
        $this->configureLearning();
        [$m, $g] = $this->gardenWithCapacity(1);
        $w = app(ApplicationWorkflowService::class);
        $child = $w->transition($this->createChild($w,$m,$g,'01000000831'), 'suspended');
        $request = ReinstatementRequest::create(['kindergartener_id'=>$child->id,'token_hash'=>hash('sha256','review-test'),'expires_at'=>now()->subDay(),'document_path'=>'test.pdf','absence_from'=>today(),'absence_to'=>today()]);
        Attendance::create(['kindergartener_id'=>$child->id,'kindergarten_id'=>$g,'group_id'=>1,'attendance_date'=>today(),'status'=>'absent']);
        Artisan::call('attendance:evaluate');
        $this->assertSame('pending', $request->fresh()->status);
        $admin=User::create(['name'=>'Admin','email'=>'review@example.test','password'=>Hash::make('testing-password'),'role'=>'union_admin']);
        $mock=\Mockery::mock(ApplicationWorkflowService::class);
        $mock->shouldReceive('transition')->once()->andThrow(new \RuntimeException('Test rollback'));
        $this->app->instance(ApplicationWorkflowService::class,$mock);
        $this->actingAs($admin)->post('/reinstatement-requests/'.$request->id.'/review',['decision'=>'approved'])->assertStatus(500);
        $this->assertSame('absent', Attendance::first()->status);
        $this->assertSame('pending', $request->fresh()->status);
        $this->app->instance(ApplicationWorkflowService::class,$w);
        $this->actingAs($admin)->post('/reinstatement-requests/'.$request->id.'/review',['decision'=>'approved'])->assertRedirect();
        $this->assertSame('enrolled', $child->fresh()->application_status);
        $this->assertSame('approved', $request->fresh()->status);
    }

    public function test_attendance_audit_records_old_and_new_status(): void
    {
        $this->configureLearning();
        [$m,$g]=$this->gardenWithCapacity(1);
        $child=$this->createChild(app(ApplicationWorkflowService::class),$m,$g,'01000000841');
        $admin=User::create(['name'=>'Admin','email'=>'attendance@example.test','password'=>Hash::make('testing-password'),'role'=>'union_admin']);
        $attendanceDate = Carbon::parse('2026-09-18')->toDateString();
        foreach(['absent','excused'] as $status) {
            $this->actingAs($admin)
                ->post('/attendance',['kindergarten_id'=>$g,'date'=>$attendanceDate,'records'=>[$child->id=>$status]])
                ->assertRedirect()
                ->assertSessionHasNoErrors();
        }
        $this->assertDatabaseHas('attendances', ['kindergartener_id' => $child->id, 'status' => 'excused']);
        $this->assertDatabaseCount('audit_logs', 2);
        $log=\App\Model\AuditLog::where('action','attendance.store')->latest('id')->firstOrFail();
        $this->assertSame(['old'=>'absent','new'=>'excused'], $log->changes['status']);
        $this->assertEquals($child->id,$log->changes['kindergartener_id']);
        $this->assertEquals($admin->id,$log->user_id);
    }

    public function test_reinstatement_can_be_returned_for_correction_without_cancelling_registration(): void
    {
        $this->configureLearning();
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $child = $workflow->transition($this->createChild($workflow, $municipality, $garden, '01000000852'), 'suspended');
        $request = ReinstatementRequest::create(['kindergartener_id'=>$child->id,'token_hash'=>hash('sha256', 'old-token'),'expires_at'=>now()->addDays(3),'absence_from'=>today(),'absence_to'=>today(),'document_path'=>'reinstatement-documents/test.pdf']);
        $admin = User::create(['name'=>'Admin','email'=>'correction@example.test','password'=>Hash::make('testing-password'),'role'=>'union_admin']);
        $notifications = \Mockery::mock(ParentNotificationService::class);
        $notifications->shouldReceive('send')->once()->withArgs(fn ($sentChild, $event, $subject, $message, $url) => $sentChild->id === $child->id && $event === 'reinstatement_needs_correction' && str_contains($message, 'საჭიროებს დაზუსტებას') && $url);
        $this->app->instance(ParentNotificationService::class, $notifications);

        $this->actingAs($admin)->post('/reinstatement-requests/'.$request->id.'/review',['decision'=>'needs_correction','review_note'=>'ატვირთეთ სწორი ცნობა.'])->assertRedirect()->assertSessionHas('flashMessage','გადაწყვეტილება შენახულია.')->assertSessionHasNoErrors();

        $this->assertSame('suspended', $child->fresh()->application_status);
        $this->assertSame('needs_correction', $request->fresh()->status);
        $this->assertSame('ატვირთეთ სწორი ცნობა.', $request->fresh()->review_note);
        $this->assertNull($request->fresh()->document_path);
        $this->assertNotSame(hash('sha256', 'old-token'), $request->fresh()->token_hash);
    }

    public function test_parent_notifications_queue_sms_only_with_a_short_action_link(): void
    {
        config(['services.smsoffice.enabled' => true]);
        \Illuminate\Support\Facades\Notification::fake();
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000851', ['email' => 'parent@example.test']);

        app(ParentNotificationService::class)->send($child, 'placement_offer', 'ადგილის შეთავაზება', 'გთხოვთ დაადასტუროთ.', url('/placement-offers/private-token'));

        $delivery = NotificationDelivery::firstOrFail();
        $this->assertSame('sms', $delivery->channel);
        $this->assertStringContainsString('/s/', $delivery->payload['action_url']);
        $this->assertSame(0, NotificationDelivery::where('channel', 'mail')->count());
    }

    public function test_failed_sms_cannot_be_resent_to_an_invalid_mobile_number(): void
    {
        $this->configureLearning();
        config(['services.smsoffice.enabled' => true]);
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $child = $this->createChild(app(ApplicationWorkflowService::class),$municipality,$garden,'01000000875');
        $child->update(['mobile_number' => '123456789']);
        $delivery = NotificationDelivery::create([
            'kindergartener_id'=>$child->id,'event'=>'application_created','channel'=>'sms',
            'recipient_hash'=>hash('sha256','123456789'),'idempotency_key'=>hash('sha256','invalid-mobile-test'),
            'payload'=>['message'=>'ტესტი'],'status'=>'failed','attempts'=>3,
            'last_error'=>'Invalid Georgian mobile number for SMS Office. in /var/www/bagebi/app/Services/SmsOfficeService.php:43',
        ]);
        $admin = User::create(['name'=>'Admin','email'=>'invalid-resend@example.test','password'=>Hash::make('testing-password'),'role'=>'union_admin']);
        $this->actingAs($admin)->get('/operations?notification_status=failed')->assertOk()->assertSee('ნომრის გასწორება')->assertDontSee('SmsOfficeService.php');
        $this->post('/operations/notifications/'.$delivery->id.'/resend')->assertSessionHasErrors('mobile_number');
        $this->assertSame('failed',$delivery->fresh()->status);
        $this->assertSame(0,DB::table('jobs')->count());
    }

    public function test_get_status_tracker_ignores_lookup_parameters(): void
    {
        $this->get('/status-tracker?kids_personal_number=01000000851&mobile_last_four=0000')
            ->assertOk()->assertViewHas('kid', null)->assertViewHas('query', null);
    }

    public function test_unassigned_director_cannot_export_children(): void
    {
        $this->configureLearning();
        $director = User::create(['name'=>'Director','email'=>'unassigned@example.test','password'=>Hash::make('testing-password'),'role'=>'director']);
        $this->actingAs($director)->get('/kindergarteners/export')->assertForbidden();
    }

    public function test_waiting_child_cannot_be_suspended_without_occupying_a_seat(): void
    {
        [$municipality, $garden] = $this->gardenWithCapacity(0);
        $workflow = app(ApplicationWorkflowService::class);
        $child = $this->createChild($workflow, $municipality, $garden, '01000000861');
        try {
            $workflow->transition($child, 'suspended');
            $this->fail('Waiting children must not be suspended.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertSame('waiting', $child->fresh()->application_status);
            $this->assertDatabaseHas('kindergarten_group_age_range', ['kindergarten_id'=>$garden,'space_filled'=>0]);
        }
    }

    public function test_sms_delivery_and_database_job_roll_back_together(): void
    {
        config(['services.smsoffice.enabled'=>true]);
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000862');
        $jobs = DB::table('jobs')->count();
        DB::beginTransaction();
        app(ParentNotificationService::class)->send($child,'audit_test','Test','Test');
        $this->assertSame($jobs + 1, DB::table('jobs')->count());
        $this->assertSame(1, NotificationDelivery::where('event','audit_test')->count());
        DB::rollBack();
        $this->assertSame($jobs, DB::table('jobs')->count());
        $this->assertSame(0, NotificationDelivery::where('event','audit_test')->count());
    }

    public function test_sms_delivery_can_only_be_claimed_by_one_job(): void
    {
        $delivery = NotificationDelivery::create([
            'event'=>'claim-test','channel'=>'sms','recipient_hash'=>hash('sha256','551120240'),
            'idempotency_key'=>hash('sha256','claim-test'),'payload'=>['message'=>'ტესტი'],'status'=>'queued',
        ]);
        $notification = new ParentSmsNotification('ტესტი', $delivery->id);

        $this->assertTrue($notification->shouldSend(null, \App\Notifications\Channels\SmsOfficeChannel::class));
        $this->assertSame('sending', $delivery->fresh()->status);
        $this->assertFalse($notification->shouldSend(null, \App\Notifications\Channels\SmsOfficeChannel::class));
        $delivery->update(['status'=>'sent']);
        $this->assertFalse($notification->shouldSend(null, \App\Notifications\Channels\SmsOfficeChannel::class));
    }

    public function test_bulk_status_change_notifies_each_child_and_is_atomic(): void
    {
        $this->configureLearning();
        config(['services.smsoffice.enabled'=>true]);
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $first = $this->createChild($workflow, $municipality, $garden, '01000000863');
        $second = $this->createChild($workflow, $municipality, $garden, '01000000864');
        $admin = User::create(['name'=>'Admin','email'=>'bulk-audit@example.test','password'=>Hash::make('testing-password'),'role'=>'union_admin']);
        $this->actingAs($admin)->post('/kindergarteners/order', ['ids'=>[$first->id,$second->id],'action'=>2,'destination'=>'suspended','reason'=>'სატესტო მასობრივი ცვლილება'])
            ->assertSessionHasErrors('status');
        $this->assertSame('enrolled', $first->fresh()->application_status);
        $this->assertSame(0, NotificationDelivery::count());
        $this->post('/kindergarteners/order', ['ids'=>[$first->id],'action'=>2,'destination'=>'suspended','reason'=>'სატესტო შეჩერების მიზეზი'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('suspended', $first->fresh()->application_status);
        $this->assertSame(1, NotificationDelivery::where('event','registration_suspended')->count());
        $this->assertSame(1, ReinstatementRequest::where('kindergartener_id',$first->id)->count());
    }

    public function test_uploaded_document_cannot_be_overwritten(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $child = $workflow->transition($this->createChild($workflow,$municipality,$garden,'01000000865'),'suspended');
        $item = ReinstatementRequest::create(['kindergartener_id'=>$child->id,'token_hash'=>hash('sha256','upload-test'),'expires_at'=>now()->addDay(),'absence_from'=>today(),'absence_to'=>today()]);
        $this->post('/reinstatement/upload-test',['document'=>\Illuminate\Http\UploadedFile::fake()->create('first.pdf',10,'application/pdf')])->assertOk();
        $path = $item->fresh()->document_path;
        $this->post('/reinstatement/upload-test',['document'=>\Illuminate\Http\UploadedFile::fake()->create('second.pdf',10,'application/pdf')])->assertStatus(410);
        $this->assertSame($path, $item->fresh()->document_path);
        $this->assertCount(1, \Illuminate\Support\Facades\Storage::disk('local')->allFiles('reinstatement-documents'));
    }

    public function test_expiry_respects_garden_scope_and_queues_cancellation_once(): void
    {
        config(['services.smsoffice.enabled'=>true]);
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        [$otherMunicipality, $otherGarden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $child = $workflow->transition($this->createChild($workflow,$municipality,$garden,'01000000866'),'suspended');
        $other = $workflow->transition($this->createChild($workflow,$otherMunicipality,$otherGarden,'01000000867'),'suspended');
        foreach ([$child,$other] as $item) ReinstatementRequest::create(['kindergartener_id'=>$item->id,'token_hash'=>hash('sha256','expiry-'.$item->id),'expires_at'=>now()->subDay(),'absence_from'=>today()->subDays(10),'absence_to'=>today()->subDays(2)]);
        Artisan::call('attendance:evaluate',['--kindergarten'=>$garden]);
        Artisan::call('attendance:evaluate',['--kindergarten'=>$garden]);
        $this->assertSame('cancelled',$child->fresh()->application_status);
        $this->assertSame('suspended',$other->fresh()->application_status);
        $this->assertSame(1,NotificationDelivery::where('event','status_cancelled')->count());
        $this->assertSame(1,ReinstatementRequest::where('status','expired')->count());
    }

    public function test_offer_response_filter_uses_the_selected_status(): void
    {
        $this->configureLearning();
        [$municipality, $garden] = $this->gardenWithCapacity(0);
        $child = $this->createChild(app(ApplicationWorkflowService::class),$municipality,$garden,'01000000868');
        $entry = \App\Model\WaitingListEntry::where('kindergartener_id',$child->id)->firstOrFail();
        foreach (['accepted','declined','expired'] as $state) PlacementOffer::create(['waiting_list_entry_id'=>$entry->id,'token_hash'=>hash('sha256',$state),'expires_at'=>now()->addDay(),'responded_at'=>now(),'response'=>$state]);
        $admin = User::create(['name'=>'Admin','email'=>'filter-audit@example.test','password'=>Hash::make('testing-password'),'role'=>'union_admin']);
        foreach (['accepted','declined','expired'] as $state) {
            $this->actingAs($admin)->get('/operations?offer_response='.$state)->assertOk()
                ->assertViewHas('offers',fn($offers)=>$offers->total()===1 && $offers->first()->response===$state);
        }
    }

    public function test_attendance_rejects_nonworking_status_on_a_workday_and_other_status_on_a_day_off(): void
    {
        $this->configureLearning();
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000869');
        $admin = User::create(['name'=>'Admin','email'=>'calendar-validation@example.test','password'=>Hash::make('testing-password'),'role'=>'union_admin']);
        $this->actingAs($admin)->post('/attendance', ['kindergarten_id'=>$garden,'date'=>'2026-09-14','records'=>[$child->id=>'non_working']])
            ->assertSessionHasErrors('records');
        $this->post('/attendance', ['kindergarten_id'=>$garden,'date'=>'2026-09-13','records'=>[$child->id=>'absent']])
            ->assertSessionHasErrors('records');
        $this->post('/attendance', ['kindergarten_id'=>$garden,'date'=>'2026-09-13','records'=>[$child->id=>'non_working']])
            ->assertSessionHasNoErrors();
    }

    public function test_director_cannot_create_attendance_for_a_suspended_child(): void
    {
        $this->configureLearning();
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $child = $workflow->transition($this->createChild($workflow,$municipality,$garden,'01000000870'),'suspended');
        $director = User::create(['name'=>'Director','email'=>'suspended-attendance@example.test','password'=>Hash::make('testing-password'),'role'=>'director','kindergarten_id'=>$garden]);
        $this->actingAs($director)->from('/attendance?date=2026-09-14')
            ->post('/attendance',['date'=>'2026-09-14','records'=>[$child->id=>'present']])
            ->assertRedirect('/attendance?date=2026-09-14')
            ->assertSessionHasErrors('records');
        $this->assertDatabaseMissing('attendances',['kindergartener_id'=>$child->id,'attendance_date'=>'2026-09-14']);
    }

    public function test_director_can_record_attendance_for_an_enrolled_child_in_own_kindergarten(): void
    {
        $this->configureLearning();
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000991');
        $director = User::create(['name'=>'Director','email'=>'director-attendance@example.test','password'=>Hash::make('testing-password'),'role'=>'director','kindergarten_id'=>$garden]);

        $this->actingAs($director)->post('/attendance', [
            'date' => '2026-09-14',
            'records' => [$child->id => 'present'],
            'notes' => [$child->id => 'მშობლის მიერ მოწოდებული შენიშვნა'],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('attendances', ['kindergartener_id'=>$child->id, 'kindergarten_id'=>$garden,
            'status'=>'present', 'note'=>'მშობლის მიერ მოწოდებული შენიშვნა']);
        $this->get('/attendance?date=2026-09-14')->assertOk()
            ->assertSee('დასწრებული')->assertSee('გაცდენილი')->assertSee('საპატიო')
            ->assertSee('მშობლის მიერ მოწოდებული შენიშვნა');
    }

    public function test_union_admin_can_correct_existing_attendance_after_child_is_suspended(): void
    {
        $this->configureLearning();
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $child = $this->createChild($workflow,$municipality,$garden,'01000000874');
        $admin = User::create(['name'=>'Admin','email'=>'historical-attendance@example.test','password'=>Hash::make('testing-password'),'role'=>'union_admin']);
        $this->actingAs($admin)->post('/attendance',['kindergarten_id'=>$garden,'date'=>'2026-09-14','records'=>[$child->id=>'absent']])->assertSessionHasNoErrors();
        $otherGroup = DB::table('group_age_ranges')->insertGetId(['range'=>'3-4']);
        $child->update(['group_id'=>$otherGroup]);
        $workflow->transition($child,'suspended');
        $this->get('/attendance?kindergarten_id='.$garden.'&date=2026-09-14')->assertOk()->assertSee($child->kids_personal_number);
        $this->post('/attendance',['kindergarten_id'=>$garden,'date'=>'2026-09-14','records'=>[$child->id=>'excused']])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('attendances',['kindergartener_id'=>$child->id,'group_id'=>1,'status'=>'excused']);
    }

    public function test_freed_place_is_offered_to_existing_waiter_before_a_new_applicant(): void
    {
        $this->configureLearning();
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $enrolled = $this->createChild($workflow,$municipality,$garden,'01000000871');
        $waiting = $this->createChild($workflow,$municipality,$garden,'01000000872');
        $workflow->transition($enrolled,'cancelled','Test release');
        $new = $this->createChild($workflow,$municipality,$garden,'01000000873');
        $this->assertSame('waiting',$waiting->fresh()->application_status);
        $this->assertSame('waiting',$new->application_status);
        Artisan::call('waiting-list:process');
        $this->assertDatabaseHas('waiting_list_entries',['kindergartener_id'=>$waiting->id,'state'=>'offered']);
        $this->assertDatabaseHas('waiting_list_entries',['kindergartener_id'=>$new->id,'state'=>'waiting']);
    }

    public function test_public_registration_cannot_update_an_existing_child_or_approve_priority(): void
    {
        DB::table('settings')->insert([
            ['slug'=>'basic','object'=>json_encode(['isRegistrationStart'=>true])],
            ['slug'=>'date','object'=>json_encode(['start'=>'2026-09-01','end'=>'2027-06-15'])],
        ]);
        [$municipality, $garden] = $this->gardenWithCapacity(3);
        $existing = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000901');
        $priorityId = DB::table('priorities')->insertGetId(['name'=>'სატესტო პრიორიტეტი','created_at'=>now(),'updated_at'=>now()]);

        $response = $this->withHeader('X-Requested-With','XMLHttpRequest')->postJson('/api/registration', [
            'id'=>$existing->id,
            'active_status_id'=>2,
            'has_permission'=>true,
            'kids_personal_number'=>'01000000902',
            'kids_first_name'=>'ნინო',
            'kids_last_name'=>'ტესტი',
            'birth_date'=>'2024-01-01',
            'mobile_number'=>'551120240',
            'municipality_id'=>$municipality,
            'kindergarten_id'=>$garden,
            'group_id'=>1,
            'priority_id'=>$priorityId,
        ])->assertOk();

        $created = Kindergartener::where('kids_personal_number','01000000902')->firstOrFail();
        $this->assertNotSame($existing->id, $created->id);
        $this->assertSame('01000000901', $existing->fresh()->kids_personal_number);
        $this->assertFalse((bool) $created->priority->has_permission);
        $response->assertJsonPath('status', 'success');
    }

    public function test_director_edit_preserves_existing_priority(): void
    {
        $this->configureLearning();
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        $child = $this->createChild(app(ApplicationWorkflowService::class), $municipality, $garden, '01000000903');
        $priorityId = DB::table('priorities')->insertGetId(['name'=>'სატესტო პრიორიტეტი','created_at'=>now(),'updated_at'=>now()]);
        DB::table('kindergartner_priorities')->insert(['kindergartner_id'=>$child->id,'priority_id'=>$priorityId,'has_permission'=>true,'created_at'=>now(),'updated_at'=>now()]);
        $director = User::create(['name'=>'Director','email'=>'priority-director@example.test','password'=>Hash::make('password'),'role'=>'director','kindergarten_id'=>$garden]);

        $this->actingAs($director)->post('/kindergarteners/store', array_merge($child->toArray(), [
            'id'=>$child->id,
            'kids_first_name'=>'ნინო',
        ]))->assertRedirect();

        $this->assertDatabaseHas('kindergartner_priorities', ['kindergartner_id'=>$child->id,'priority_id'=>$priorityId,'has_permission'=>1]);
    }

    public function test_deleting_a_child_with_an_active_offer_releases_the_reservation(): void
    {
        $this->configureLearning();
        [$municipality, $garden] = $this->gardenWithCapacity(1);
        $workflow = app(ApplicationWorkflowService::class);
        $enrolled = $this->createChild($workflow,$municipality,$garden,'01000000904');
        $waiting = $this->createChild($workflow,$municipality,$garden,'01000000905');
        $workflow->transition($enrolled,'cancelled','Test vacancy');
        Artisan::call('waiting-list:process');
        $offer = PlacementOffer::firstOrFail();
        $admin = User::create(['name'=>'Admin','email'=>'delete-offer@example.test','password'=>Hash::make('password'),'role'=>'union_admin']);

        $this->actingAs($admin)->delete('/kindergarteners/destroy/'.$waiting->id)
            ->assertRedirect(route('kindergarteners.index'))
            ->assertSessionHas('flashMessage','აღსაზრდელი წაიშალა ბაზიდან!');

        $this->assertDatabaseMissing('kindergarteners', ['id'=>$waiting->id]);
        $this->assertEquals(0, DB::table('kindergarten_group_age_range')->where('kindergarten_id',$garden)->value('space_reserved'));
        $this->assertDatabaseMissing('placement_offers', ['id'=>$offer->id]);
    }

    public function test_kindergartener_table_is_paginated_scoped_and_omits_parent_identifiers(): void
    {
        $this->configureLearning();
        [$municipality, $garden] = $this->gardenWithCapacity(2);
        [$otherMunicipality, $otherGarden] = $this->gardenWithCapacity(2);
        $own = $this->createChild(app(ApplicationWorkflowService::class),$municipality,$garden,'01000000906',['mother_personal_number'=>'01001001001']);
        $this->createChild(app(ApplicationWorkflowService::class),$otherMunicipality,$otherGarden,'01000000907',['mother_personal_number'=>'01001001002']);
        $director = User::create(['name'=>'Director','email'=>'table-director@example.test','password'=>Hash::make('password'),'role'=>'director','kindergarten_id'=>$garden]);

        $response = $this->actingAs($director)->getJson(route('kindergarteners.data').'?draw=1&start=0&length=10')->assertOk();

        $response->assertJsonPath('recordsTotal',1)->assertJsonPath('recordsFiltered',1)->assertJsonPath('data.0.id',$own->id);
        $this->assertArrayNotHasKey('mother_personal_number', $response->json('data.0'));
    }

    private function configureLearning(): void
    {
        DB::table('settings')->insert([
            ['slug'=>'basic','object'=>json_encode(['isLearningStart'=>true,'canPorting'=>false])],
            ['slug'=>'date','object'=>json_encode(['start'=>'2026-09-01','end'=>'2027-06-15'])],
        ]);
    }

    private function gardenWithCapacity(int $capacity): array
    {
        $region = DB::table('regions')->insertGetId(['name' => 'Test region', 'created_at' => now(), 'updated_at' => now()]);
        $municipality = DB::table('municipalities')->insertGetId(['region_id' => $region, 'name' => 'Test municipality', 'created_at' => now(), 'updated_at' => now()]);
        $garden = DB::table('kindergartens')->insertGetId(['municipality_id' => $municipality, 'name' => 'Test garden', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('kindergarten_group_age_range')->insert(['kindergarten_id' => $garden, 'group_age_range' => 1, 'space_length' => $capacity, 'space_filled' => 0, 'space_free' => $capacity, 'space_reserved' => 0]);
        return [$municipality, $garden];
    }

    private function createChild(ApplicationWorkflowService $workflow, int $municipality, int $garden, string $personalNumber, array $extra = []): Kindergartener
    {
        return $workflow->save(array_merge([
            'kids_personal_number' => $personalNumber,
            'kids_first_name' => 'ანა',
            'kids_last_name' => 'ტესტი',
            'birth_date' => '2022-01-01',
            'mobile_number' => '555000000',
            'municipality_id' => $municipality,
            'kindergarten_id' => $garden,
            'group_id' => 1,
        ], $extra));
    }
}
