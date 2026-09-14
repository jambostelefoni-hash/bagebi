<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



use App\User;
use App\Model\Setting;
use App\Model\Municipality;
use App\Model\Kindergarten;
use App\Model\API\Kindergartener;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        $children = Kindergartener::query()->when($user->role === 'director', fn ($q) => $q->where('kindergarten_id', $user->kindergarten_id));
        $user_count = $user->isUnionAdmin() ? User::count() : 1;
        $municipality_count = $user->isUnionAdmin() ? Municipality::count() : 1;
        $kindergarten_count = $user->isUnionAdmin() ? Kindergarten::count() : 1;
        $kindergartner_count = (clone $children)->count();
        $enrolled_count = (clone $children)->where('application_status', 'enrolled')->count();
        $waiting_count = (clone $children)->where('application_status', 'waiting')->count();
        $suspended_count = (clone $children)->where('application_status', 'suspended')->count();
        
        $date = Setting::where('slug', 'date')->firstOrNew()->toArray();
        $basic = Setting::where('slug', 'basic')->firstOrNew()->toArray();

        return view('home', [
            'user_count' => $user_count,
            'municipality_count' => $municipality_count,
            'kindergarten_count' => $kindergarten_count,
            'kindergartner_count' => $kindergartner_count,
            'enrolled_count' => $enrolled_count,
            'waiting_count' => $waiting_count,
            'suspended_count' => $suspended_count,
            'date' => $date,
            'basic' => $basic
        ]);
    }
}













