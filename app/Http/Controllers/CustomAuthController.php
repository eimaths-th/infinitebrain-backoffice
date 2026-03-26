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
        $this_month = date("Y-m");
        $this_month = '2023-05';
        $this_year = date("Y");

        if (Auth::attempt($credentials)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $roles = $user->getRoleNames();

            // 1. ถ้ามี role 'external-branch' อย่างเดียว
            if ($roles->count() === 1 && $roles->contains('external-branch')) {
                return redirect()->route('payment-voucher.index');
            }

            // 2. ยกเลิกการใช้ level แล้วใช้ Role แทน
            // ถ้าไม่มี Role อะไรเลย
            if ($roles->isEmpty()) {
                Session::flush();
                Auth::logout();
                return back()->withErrors(['error' => 'You are users but not have permissions. Please Contact Administrator']);
            } else {
                if ($user->hasAnyRole(['super-admin', 'admin'])) {
                    return redirect(route('home'));
                } elseif ($user->hasRole('parent')) {
                    return redirect('/receipts/print_month?pmsfield=9&type=report_view&print_month='.$this_month.'&Pmdescription=ค่าสมัคร OLPCS รอบแรก '. $this_year);
                } else {
                    // สำหรับ Role อื่นๆ (เช่น finance, staff, reviewer, approver)
                    return redirect(route('home2'));
                }
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
        // dd($request);
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $data = $request->all();
        $check = $this->create($data);
        $check->update(['level' => 5]);

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
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
