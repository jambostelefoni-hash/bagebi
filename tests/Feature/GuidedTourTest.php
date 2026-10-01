<?php

namespace Tests\Feature;

use App\User;
use App\Model\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuidedTourTest extends TestCase
{
    use RefreshDatabase;

    private function initializeSite(): void
    {
        Setting::create(['slug' => 'basic', 'object' => ['isLearningStart' => true]]);
    }

    public function test_authenticated_user_can_save_guided_tour_completion(): void
    {
        $this->initializeSite();
        $user = User::create([
            'name' => 'Tour User',
            'email' => 'tour@example.test',
            'password' => Hash::make('password'),
            'role' => 'union_admin',
        ]);

        $this->actingAs($user)
            ->postJson(route('guided-tour.complete'), ['version' => 'page-tour-v4:home'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertNotNull($user->fresh()->tour_progress['page-tour-v4:home'] ?? null);

        $this->actingAs($user)
            ->postJson(route('guided-tour.complete'), ['version' => 'page-tour-v4:settings.index-annual-porting'])
            ->assertOk();

        $this->assertNotNull($user->fresh()->tour_progress['page-tour-v4:settings.index-annual-porting'] ?? null);
    }

    public function test_tour_completion_requires_an_authenticated_user_and_known_version(): void
    {
        $this->initializeSite();
        $this->postJson(route('guided-tour.complete'), ['version' => 'page-tour-v4:home'])->assertUnauthorized();

        $user = User::create([
            'name' => 'Tour User',
            'email' => 'tour-invalid@example.test',
            'password' => Hash::make('password'),
            'role' => 'director',
        ]);

        $this->actingAs($user)
            ->postJson(route('guided-tour.complete'), ['version' => 'unknown-tour'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('version');
    }
}
