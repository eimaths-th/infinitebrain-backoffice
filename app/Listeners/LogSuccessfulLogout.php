<?php
// app/Listeners/LogSuccessfulLogout.php
namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\ActivityLog;

class LogSuccessfulLogout
{
    public function handle(Logout $event)
    {
        ActivityLog::create([
            'user_id'    => optional($event->user)->id,
            'action'     => 'logout',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description'=> 'ออกจากระบบเมื่อ ' . now()->format('Y-m-d H:i:s'),
        ]);
    }
}
