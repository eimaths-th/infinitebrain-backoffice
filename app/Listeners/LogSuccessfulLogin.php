<?php
// app/Listeners/LogSuccessfulLogin.php
namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\ActivityLog;

class LogSuccessfulLogin
{
    public function handle(Login $event)
    {
        ActivityLog::create([
            'user_id'    => $event->user->id,
            'action'     => 'login',
            'description'=> 'เข้าสู่ระบบเมื่อ ' . now()->format('Y-m-d H:i:s'),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
    }
}
