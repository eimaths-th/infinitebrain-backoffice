<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InactivityTimeoutMiddleware
{
    public function handle($request, Closure $next)
    {
        $result = $this->checkInactiveTimeout(1); // Check for inactivity timeout of 15 minutes
        if ($result) {
            return $result;
        }

        return $next($request);
    }

    public function checkInactiveTimeout($timeoutMinutes = 1)
    {
        $lastActivity = session('last_activity');

        if ($lastActivity && Carbon::now()->diffInMinutes($lastActivity) > $timeoutMinutes) {
            Auth::logout();
            return redirect()->route('login')->with('message', 'You have been logged out due to inactivity.');
        }

        session(['last_activity' => Carbon::now()]);
        return null;
    }
}
