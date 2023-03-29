<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;



class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
    protected function redirectTo()
    {
        if (Auth::user()->level == 1) {
            return '/home';
        } elseif (Auth::user()->level == 2) { 
            return '/home';
        } else {
            
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:parent')->except('logout');

    }
    public function showParentLoginForm()
    {
        return view('auth.login', ['url' => 'parent']);
    }

    public function parentLogin(Request $request)
    {
        $this->validate($request, [
            'email'   => 'required|email',
            'password' => 'required|min:6'
        ]);
         if (Auth::guard('parent')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
            //echo ("hello1");
            //echo Auth::user()->email;
            //dd(session()->all());
            //echo $users = Auth::guard('parent')->user()->email;
            //$users->email;
            //exit();
            //return redirect()->intended('/parent');
            // $this->check_permission();

            return redirect('/parent');
        } 
        //echo ("hello2");
        return back()->withInput($request->only('email', 'remember'))
        ->withErrors(['error' => 'Your Username Or Password Not Match']);
    }
}
