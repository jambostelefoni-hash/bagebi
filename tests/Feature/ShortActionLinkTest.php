<?php

namespace Tests\Feature;

use App\Model\ShortActionLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortActionLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_a_valid_short_link_to_its_protected_action(): void
    {
        $code = 'abcdefghijklmnopqrst';
        ShortActionLink::create([
            'code_hash' => hash('sha256', $code),
            'destination_url' => '/placement-offers/protected-token',
            'expires_at' => now()->addDay(),
        ]);

        $this->get('/s/'.$code)->assertRedirect('/placement-offers/protected-token');
    }

    public function test_an_expired_short_link_returns_the_existing_gone_page(): void
    {
        $code = 'qrstuvwxyzabcdefghij';
        ShortActionLink::create([
            'code_hash' => hash('sha256', $code),
            'destination_url' => '/placement-offers/expired-token',
            'expires_at' => now()->subMinute(),
        ]);

        $this->get('/s/'.$code)->assertStatus(410)->assertSee('ბმულის ვადა ამოიწურა');
    }
}
