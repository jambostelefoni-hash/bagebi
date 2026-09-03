<?php

namespace App\Http\Middleware;

use Closure;

use App\Model\Setting;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Route;

class CheckIfInitSite
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $setting_basic = Setting::where('slug', 'basic')->first();

        if (!$setting_basic && !Str::startsWith(Route::currentRouteName(), 'settings.date')) return redirect('settings/date');

        return $next($request);
    }
}



