<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuidedTourController extends Controller
{
    /** Store completion state per user, so the tour is not shown again automatically. */
    public function complete(Request $request)
    {
        $data = $request->validate([
            'version' => ['required', 'regex:/^page-tour-v4:[A-Za-z0-9._-]{1,80}$/'],
        ]);

        $user = $request->user();
        $progress = $user->tour_progress ?: [];
        $progress[$data['version']] = now()->toIso8601String();
        $user->tour_progress = $progress;
        $user->save();

        return response()->json(['ok' => true]);
    }
}
