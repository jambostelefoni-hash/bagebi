<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_locked_after_five_failed_attempts(): void
    {
        $email = 'locked@example.test';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => $email, 'password' => 'wrong-password'])
                ->assertRedirect()
                ->assertSessionHasErrors('email');
        }

        $this->post('/login', ['email' => $email, 'password' => 'wrong-password'])
            ->assertRedirect()
            ->assertSessionHasErrors(['email' => 'ძალიან ბევრი წარუმატებელი მცდელობაა. სცადეთ 15 წუთის შემდეგ.']);
    }

    public function test_successful_login_clears_failed_attempts(): void
    {
        $email = 'admin@example.test';
        $user = User::create([
            'name' => 'Administrator',
            'email' => $email,
            'password' => Hash::make('correct-password'),
            'role' => 'union_admin',
        ]);
        $throttleKey = sha1(Str::lower($email).'|127.0.0.1');
        RateLimiter::hit($throttleKey, 900);

        $this->post('/login', ['email' => $email, 'password' => 'correct-password'])
            ->assertRedirect('/home');

        $this->assertSame(0, RateLimiter::attempts($throttleKey));
        $this->assertAuthenticatedAs($user);
    }
}
