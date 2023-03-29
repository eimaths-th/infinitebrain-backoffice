<?php

namespace App\Http\Controllers;

use App\Models\ParentRegister;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function loginWithGoogle()
    {
        
        return Socialite::driver('google')->redirect();
    }

    public function callbackFromGoogle()
    {
        try {
            
            $user = Socialite::driver('google')->user();

            // Check Users Email If Already There
            $is_user = ParentRegister::where('email', $user->getEmail())->first();
            if(!$is_user){

                $saveUser = ParentRegister::updateOrCreate([
                    'google_id' => $user->getId(),
                ],[
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'password' => Hash::make($user->getName().'@'.$user->getId())
                ]);
            }else{
                $saveUser = ParentRegister::where('email',  $user->getEmail())->update([
                    'google_id' => $user->getId(),
                ]);
                $saveUser = ParentRegister::where('email', $user->getEmail())->first();
            }


            Auth::loginUsingId($saveUser->id);
         
            return redirect()->route('parent');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}