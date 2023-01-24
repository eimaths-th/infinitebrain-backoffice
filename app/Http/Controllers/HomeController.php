<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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
        if (Auth::check()) {
            if (Auth::user()->level == 1) {
                //return redirect()->intended('users');
                return redirect('/users');
            } elseif (Auth::user()->level == 2) {
                //return redirect()->intended('course');
                return redirect('/course');
            } else if (Auth::user()->level == 3) {
                //return redirect()->intended('receipts');
                return redirect('/receipts?sfield=2');
            }
        }
    }
}
