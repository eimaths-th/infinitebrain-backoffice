<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentVoucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voucher_no',
        'voucher_date',
        'branch_code',
        'pay_to',
        'pay_to_address',
        'pay_to_account_no',
        'pay_to_bank_name',
        'payee_id',
        'total_amount',
        'withholding_tax_rate',
        'withholding_tax_amount',
        'net_amount',
        'remark',
        'status',
        'created_by',
        'created_by_name',
        'approved_by',
        'approved_by_name',
        'approved_at',
        'approve_remark',
        'reviewed_by',
        'reviewed_by_name',
        'reviewed_at',
        'review_remark',
        'receiver_name',
        'received_date',
        'payer_name',
        'paid_date',
        'payment_cycle',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'approved_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'received_date' => 'date',
        'paid_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    // Status Constants
    const STATUS_DRAFT = 'draft';          // แบบร่าง
    const STATUS_PENDING = 'pending';      // รอตรวจสอบ (Reviewer)
    const STATUS_REVIEWED = 'reviewed';    // ตรวจสอบแล้ว รออนุมัติ
    const STATUS_APPROVED = 'approved';    // อนุมัติแล้ว
    const STATUS_REJECTED = 'rejected';    // ถูกปฏิเสธ
    const STATUS_COMPLETED = 'completed';  // เสร็จสิ้น

    public static $statusLabels = [
        'draft' => 'แบบร่าง',
        'pending' => 'รอตรวจสอบ',
        'reviewed' => 'รออนุมัติ',
        'approved' => 'รอจ่ายเงิน',
        'rejected' => 'ถูกปฏิเสธ',
        'completed' => 'เสร็จสิ้น',
    ];

    public static $statusColors = [
        'draft' => 'secondary',
        'pending' => 'warning',
        'reviewed' => 'info',
        'approved' => 'success',
        'rejected' => 'danger',
        'completed' => 'success',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(PaymentVoucherItem::class)->orderBy('item_no');
    }

    public function logs()
    {
        return $this->hasMany(PaymentVoucherLog::class)->orderBy('created_at', 'desc');
    }

    public function attachments()
    {
        return $this->hasMany(PaymentVoucherAttachment::class)
            ->where('attachment_type', PaymentVoucherAttachment::TYPE_VOUCHER);
    }

    public function allAttachments()
    {
        return $this->hasMany(PaymentVoucherAttachment::class);
    }

    public function payee()
    {
        return $this->belongsTo(Payee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Helpers
    public function getStatusLabelAttribute()
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return self::$statusColors[$this->status] ?? 'secondary';
    }

    public function canEdit()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    public function canSubmit()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    public function canReview()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canApprove()
    {
        return $this->status === self::STATUS_REVIEWED;
    }

    public function canComplete()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canUndoComplete()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    // Branch Codes
    const BRANCH_EL = 'EL'; // สาขาหลัก
    const BRANCH_EB = 'EB'; // สาขาย่อย (ถ้ามี)

    public static $branchCodes = [
        'EL' => 'EyeLavel (EL)',
        'IF' => 'InfiniteBrain (IF)',
        'MT' => 'META Knowledge (MT)',
        'EiRP' => 'eiMaths RP (EiRP)',
        'EiBK' => 'eiMaths BK (EiBK)',
    ];
    public static $gropBranchCodes = [
        'INF' => ['IF', 'EL'],
        'META' => ['MT'],
        'META2' => ['EiRP', 'EiBK'],
    ];

    // Generate Voucher Number: EL20260112 = รหัสสาขา + ปี + เดือน + ลำดับ
    public static function generateVoucherNo($branchCode = 'EL')
    {
        $year = date('Y');
        $month = date('m');
        $prefix = $branchCode . $year . $month;

        $lastVoucher = self::where('voucher_no', 'like', $prefix . '%')
            ->orderBy('voucher_no', 'desc')
            ->first();

        if ($lastVoucher) {
            // ดึงเลขลำดับจากท้าย voucher_no
            $lastNumber = (int) substr($lastVoucher->voucher_no, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
    }

    // Calculate Total
    public function calculateTotal()
    {
        $this->total_amount = $this->items()->sum('amount');
        $this->save();
    }
}
