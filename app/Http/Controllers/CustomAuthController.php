<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CustomAuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function customLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            if (Auth::user()->level == 0) {

                Session::flush();
                Auth::logout();

                /*return back()->withErrors([
                    'email' => 'You are users but not have permissions. Please Contact Administrator',
                ]);*/
                return back()->withErrors(['error' => 'You are users but not have permissions. Please Contact Administrator']);
            } else {
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
                //->withSuccess('Signed in');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function registration()
    {
        return view('auth.registration');
    }

    public function customRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $data = $request->all();
        $check = $this->create($data);

        return redirect("login")->withSuccess('Register Complete. Please Contact Administrator for permission');
        //return redirect("home")->withSuccess('You have signed-in');
    }

    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'level' => User::DEFAULT_TYPE,
        ]);
    }

    public function dashboard()
    {
        if (Auth::check()) {
            return view('dashboard');
        }

        return redirect("login")->withSuccess('You are not allowed to access');
    }

    public function signOut()
    {
        Session::flush();
        Auth::logout();

        return Redirect('login');
    }
}
