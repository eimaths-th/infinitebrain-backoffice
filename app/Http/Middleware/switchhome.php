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

        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // ยกเลิกการใช้ level แล้วใช้ Role แทน
        if ($user->hasRole('parent')) {
            return redirect(route('formsControl'));
        }

        return $next($request);
    }
}
