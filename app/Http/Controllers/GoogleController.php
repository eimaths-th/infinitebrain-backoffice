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
            $saveUser = ParentRegister::where('email', $user->getEmail())->first();
            // echo $saveUser;
            // exit();
            //if(!$is_user){
            if($saveUser!="" && $saveUser!=NULL){
                (Auth::guard('parent')->loginUsingId($saveUser->id)) ;
                return redirect('/parent');
            
            }else{
              
                return redirect('/parent-register')->withErrors([
                    'email' => 'Your email has not been registered , Please register',
                ]);
            }
               // $saveUser = ParentRegister::updateOrCreate([
                //    'google_id' => $user->getId(),
                //],[
                //    'name' => $user->getName(),
                 //   'email' => $user->getEmail(),
                //    'password' => Hash::make($user->getName().'@'.$user->getId())
                //]);
            //}else{
                //$saveUser = ParentRegister::where('email',  $user->getEmail())->update([
                 //   'google_id' => $user->getId(),
                //]);
               // $saveUser = ParentRegister::where('email', $user->getEmail())->first();
            //}

            //echo $saveUser->id;
            //exit();


           // Auth::loginUsingId($saveUser->id);
         
            //return redirect()->route('parent');
           
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}