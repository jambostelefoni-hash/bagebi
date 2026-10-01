<?php

namespace App\Http\Controllers;

use App\Model\ShortActionLink;

class ShortActionLinkController extends Controller
{
    public function show(string $code)
    {
        $link = ShortActionLink::where('code_hash', hash('sha256', $code))->firstOrFail();

        abort_if($link->expires_at->isPast(), 410);

        return redirect()->to($link->destination_url);
    }
}
