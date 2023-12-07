<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class switchhome
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if (Auth::user()->level >= 0 && Auth::user()->level < 3) {
            # code...
            return $next($request);
        } elseif (Auth::user()->level == 3) {
            # code...
            return redirect(route('formsControl'));
        } else {
            # code...
            return redirect(route('home2'));
        }
    }
}
