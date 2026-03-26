<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has super-admin role
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        // Role check failed
        abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    }
}
