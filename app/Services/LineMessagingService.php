<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineMessagingService
{
    protected $channel_access_token;
    protected $group_id;
    protected $api_url = 'https://api.line.me/v2/bot/message/push';

    public function __construct()
    {
        $this->channel_access_token = config('services.line_messaging.channel_access_token');
        $this->group_id = config('services.line_messaging.group_id');
    }

    public function Send(string $message): bool
    {
        if (empty($this->channel_access_token) || empty($this->group_id)) {
            Log::warning('LINE Messaging API credentials are not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->channel_access_token,
                'Content-Type' => 'application/json',
            ])->post($this->api_url, [
                'to' => $this->group_id,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ]
                ]
            ]);

            if ($response->successful()) {
                Log::info('LINE Message sent successfully', ['message' => $message]);
                return true;
            }

            Log::error('LINE Message failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('LINE Message exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function SendLeaveRequestNotification($leave_request): bool
    {
        $user = $leave_request->user;
        $leave_type = \App\Models\LeaveRequest::LEAVE_TYPES[$leave_request->leave_type] ?? $leave_request->leave_type;

        $message = "📋 มีใบลาใหม่เข้ามา\n";
        $message .= "👤 พนักงาน: {$user->name}\n";
        $message .= "📝 ประเภท: {$leave_type}\n";
        $message .= "📅 วันที่: {$leave_request->start_date->format('d/m/Y')}";

        if ($leave_request->start_date != $leave_request->end_date) {
            $message .= " - {$leave_request->end_date->format('d/m/Y')}";
        }

        $message .= "\n⏱️ จำนวน: {$leave_request->total_days} วัน";
        $message .= "\n📌 เหตุผล: {$leave_request->reason}";
        $message .= "\n\n🔗 ดูรายละเอียด: " . route('leave-requests.show', $leave_request->id);

        return $this->Send($message);
    }
}
