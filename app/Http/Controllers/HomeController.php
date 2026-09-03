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
        $user_count = User::all()->count();
        $municipality_count = Municipality::all()->count();
        $kindergarten_count = Kindergarten::all()->count();
        $kindergartner_count = Kindergartener::all()->count();
        
        $date = Setting::where('slug', 'date')->firstOrNew()->toArray();
        $basic = Setting::where('slug', 'basic')->firstOrNew()->toArray();

        return view('home', [
            'user_count' => $user_count,
            'municipality_count' => $municipality_count,
            'kindergarten_count' => $kindergarten_count,
            'kindergartner_count' => $kindergartner_count,
            'date' => $date,
            'basic' => $basic
        ]);
    }
}















