<?php

namespace App\Services;

use App\Model\ShortActionLink;
use Illuminate\Support\Str;

class ShortActionLinkService
{
    public function create(?string $destinationUrl): ?string
    {
        if (!$destinationUrl) return null;

        $code = Str::lower(Str::random(20));
        ShortActionLink::create([
            'code_hash' => hash('sha256', $code),
            'destination_url' => $destinationUrl,
            'expires_at' => now()->addDays(7),
        ]);

        return route('short-action-links.show', $code);
    }
}
