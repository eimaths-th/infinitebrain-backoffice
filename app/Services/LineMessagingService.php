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

    public function Send(string $message, ?string $targetGroupId = null): bool
    {
        $groupId = $targetGroupId ?? $this->group_id;

        if (empty($this->channel_access_token) || empty($groupId)) {
            Log::warning('LINE Messaging API credentials or Group ID are not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->channel_access_token,
                'Content-Type' => 'application/json',
            ])->post($this->api_url, [
                'to' => $groupId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ]
                ]
            ]);

            if ($response->successful()) {
                Log::info('LINE Message sent successfully', ['message' => $message, 'to' => $groupId]);
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

    public function SendPaymentVoucherNotification($voucher, $status, $isTest = false): bool
    {
        $message = "";
        $targetGroup = null;
        $icon = "💰";

        switch ($status) {
            case \App\Models\PaymentVoucher::STATUS_PENDING:
                $message = "🆕 มีใบสำคัญจ่ายรอตรวจสอบ";
                $targetGroup = config('services.line_messaging.reviewer_group_id');
                $icon = "🟡";
                break;
            case \App\Models\PaymentVoucher::STATUS_REVIEWED:
                $message = "✅ ใบสำคัญจ่ายผ่านการตรวจสอบ (รออนุมัติ)";
                $targetGroup = config('services.line_messaging.approver_group_id');
                $icon = "🔵";
                break;
            case \App\Models\PaymentVoucher::STATUS_APPROVED:
                $message = "🎉 ใบสำคัญจ่ายอนุมัติแล้ว";
                $targetGroup = config('services.line_messaging.manager_group_id');
                $icon = "🟢";
                break;
            case \App\Models\PaymentVoucher::STATUS_REJECTED:
                $message = "❌ ใบสำคัญจ่ายถูกปฏิเสธ";
                $targetGroup = config('services.line_messaging.reviewer_group_id');
                break;
            default:
                return false;
        }

        // Test Mode Override
        if ($isTest) {
            $targetGroup = config('services.line_messaging.system_test_group_id');
            $message = "[TEST] " . $message;
            Log::info("PaymentVoucherNotification: Test Mode Logic Applied. TargetGroup changed to $targetGroup");
        } else {
            Log::info("PaymentVoucherNotification: Normal Mode. TargetGroup: $targetGroup (Manager Group Config: " . config('services.line_messaging.manager_group_id') . ")");
        }

        $formattedAmount = number_format($voucher->total_amount, 2);

        $content = "{$icon} {$message}\n";
        $content .= "เลขที่: {$voucher->voucher_no}\n";
        $content .= "จ่ายให้: {$voucher->pay_to}\n";
        $content .= "จำนวนเงิน: {$formattedAmount} บาท\n";
        $content .= "โดย: {$voucher->created_by_name}\n";

        if ($status === \App\Models\PaymentVoucher::STATUS_REJECTED && $voucher->remark) {
            $content .= "เหตุผล: {$voucher->remark}\n";
        }

        $content .= "\n🔗 ดูรายละเอียด: " . route('payment-voucher.show', $voucher->id);

        return $this->Send($content, $targetGroup);
    }
}
