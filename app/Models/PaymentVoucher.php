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
        'voucher_code',
        'voucher_date',
        'branch_code',
        'group_branch_code',
        'pay_to',
        'pay_to_address',
        'pay_to_account_no',
        'pay_to_bank_name',
        'payee_id',
        'total_amount',
        'total_deduction_amount',
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
        'is_salary',
        'category',
    ];

    // Voucher Categories
    const CAT_SHOP_RENT = 'shop_rent';                       // ค่าเช่าร้าน
    const CAT_FRANCHISE_SHARE = 'franchise_share';           // ค่าส่วนแบ่ง แฟรนไชส์
    const CAT_OFFICE_EXPENSE = 'office_expense';             // ค่าใช้จ่ายสำนักงาน
    const CAT_SOCIAL_SECURITY = 'social_security';           // ค่าประกันสังคมและเงินสมทบ
    const CAT_WHT = 'wht';                                   // ภงด. 1,3,53
    const CAT_VAT30 = 'vat30';                               // ภพ.30
    const CAT_ACCOUNTING_FEE = 'accounting_fee';             // ค่าทำบัญชี
    const CAT_BANK_FEE = 'bank_fee';                         // ค่าธรรมเนียมธนาคาร
    const CAT_WAGE = 'wage';                                 // ค่าแรง
    const CAT_OTHER = 'other';                               // อื่นๆ

    public static $categoryLabels = [
        self::CAT_SHOP_RENT => 'ค่าเช่าร้าน',
        self::CAT_FRANCHISE_SHARE => 'ค่าส่วนแบ่ง แฟรนไชส์',
        self::CAT_OFFICE_EXPENSE => 'ค่าใช้จ่ายสำนักงาน',
        self::CAT_SOCIAL_SECURITY => 'ค่าประกันสังคมและเงินสมทบ',
        self::CAT_WHT => 'ภงด. 1,3,53',
        self::CAT_VAT30 => 'ภพ.30',
        self::CAT_ACCOUNTING_FEE => 'ค่าทำบัญชี',
        self::CAT_BANK_FEE => 'ค่าธรรมเนียมธนาคาร',
        self::CAT_WAGE => 'ค่าแรง',
        self::CAT_OTHER => 'อื่นๆ',
    ];

    public function getCategoryLabelAttribute()
    {
        return self::$categoryLabels[$this->category] ?? $this->category;
    }


    protected $casts = [
        'voucher_date' => 'date',
        'approved_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'received_date' => 'date',
        'paid_date' => 'date',
        'total_amount' => 'decimal:2',
        'is_salary' => 'boolean',
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

    public function expenseItems()
    {
        return $this->hasMany(PaymentVoucherItem::class)
            ->where('type', PaymentVoucherItem::TYPE_ITEM)
            ->orderBy('item_no');
    }

    public function deductions()
    {
        return $this->hasMany(PaymentVoucherItem::class)
            ->where('type', PaymentVoucherItem::TYPE_DEDUCTION)
            ->orderBy('item_no');
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

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_code', 'code');
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

    /**
     * แปลงกลุ่มสาขา (เช่น META, EiMaths) ให้เป็นรหัสสาขาจริง (เช่น MT, EiRP, EiBK, ...)
     */
    public static function resolveBranchCodes($groups)
    {
        if (empty($groups)) return [];
        if (!is_array($groups)) $groups = [$groups];

        return Branch::whereHas('group', function($query) use ($groups) {
                $query->whereIn('code', $groups);
            })
            ->pluck('code')
            ->toArray();
    }

    // Generate Voucher Number: EL20260112 = รหัสสาขา + ปี + เดือน + ลำดับ
    public static function generateVoucherNo($voucherCode = 'ELPV', $voucherDate = null)
    {
        if ($voucherDate) {
            $date = is_string($voucherDate) ? strtotime($voucherDate) : $voucherDate;
            $year = date('Y', $date);
            $month = date('m', $date);
        } else {
            $year = date('Y');
            $month = date('m');
        }
        $prefix = $voucherCode . $year . $month;

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
