<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentVoucherLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_voucher_id',
        'action',
        'from_status',
        'to_status',
        'user_id',
        'user_name',
        'remark',
    ];

    // Action Constants
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_SUBMIT = 'submit';
    const ACTION_REVIEW = 'review';
    const ACTION_APPROVE = 'approve';
    const ACTION_REJECT = 'reject';
    const ACTION_COMPLETE = 'complete';

    public static $actionLabels = [
        'create' => 'สร้างใบสำคัญจ่าย',
        'update' => 'แก้ไขข้อมูล',
        'submit' => 'ส่งตรวจสอบ',
        'review' => 'ตรวจสอบแล้ว',
        'approve' => 'อนุมัติ',
        'reject' => 'ปฏิเสธ',
        'complete' => 'เสร็จสิ้น',
    ];

    public function paymentVoucher()
    {
        return $this->belongsTo(PaymentVoucher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute()
    {
        return self::$actionLabels[$this->action] ?? $this->action;
    }
}
