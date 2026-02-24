<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LineWebhookController extends Controller
{
    public function Handle(Request $request)
    {
        $events = $request->input('events', []);

        foreach ($events as $event) {
            $this->ProcessEvent($event);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function ProcessEvent(array $event)
    {
        $event_type = $event['type'] ?? '';
        $source = $event['source'] ?? [];
        $source_type = $source['type'] ?? '';

        Log::info('LINE Webhook Event', [
            'event_type' => $event_type,
            'source_type' => $source_type,
            'source' => $source,
        ]);

        if ($source_type === 'group') {
            $group_id = $source['groupId'] ?? null;

            if ($group_id) {
                Log::info('LINE Group ID detected', ['group_id' => $group_id]);

                $this->SaveGroupId($group_id);
            }
        }

        if ($event_type === 'join' && $source_type === 'group') {
            $group_id = $source['groupId'] ?? null;
            Log::info('Bot joined group', ['group_id' => $group_id]);

            if ($group_id) {
                $this->SaveGroupId($group_id);
                $this->SendWelcomeMessage($group_id);
            }
        }
    }

    protected function SaveGroupId(string $group_id)
    {
        $file_path = storage_path('app/line_group_id.txt');
        file_put_contents($file_path, $group_id);

        Log::info('Group ID saved to file', [
            'group_id' => $group_id,
            'file_path' => $file_path,
        ]);
    }

    protected function SendWelcomeMessage(string $group_id)
    {
        $tokens = [
            config('services.line_messaging.channel_access_token'),
            config('services.line_messaging.channel_access_token_finance')
        ];

        foreach ($tokens as $token) {
            if (empty($token)) {
                continue;
            }

            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])->post('https://api.line.me/v2/bot/message/push', [
                    'to' => $group_id,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => "🎉 บอทถูกเพิ่มเข้ากลุ่มเรียบร้อยแล้ว!\n\n📋 ระบบแจ้งเตือนพร้อมใช้งาน\n\n🔑 Group ID ของกลุ่มนี้คือ:\n{$group_id}\n\n(กรุณาคัดลอก Group ID ด้านบนไปใส่ในไฟล์ .env ของระบบ)",
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    Log::info('Welcome message sent successfully', ['token_prefix' => substr($token, 0, 10)]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send welcome message', ['error' => $e->getMessage()]);
            }
        }
    }
}
