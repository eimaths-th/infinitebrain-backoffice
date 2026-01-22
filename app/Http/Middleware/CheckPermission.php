<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $permission
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin (level 0) always has access
        if ($user->level === 0) {
            return $next($request);
        }

        // Check if user has the required permission via spatie/laravel-permission
        if ($user->hasPermissionTo($permission)) {
            return $next($request);
        }

        // Permission denied
        abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    }
}
