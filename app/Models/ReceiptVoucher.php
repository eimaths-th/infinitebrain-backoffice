<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceiptVoucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voucher_no',
        'voucher_date',
        'branch_code',
        'payee_id',
        'receive_from',
        'receive_from_address',
        'receive_from_account_no',
        'receive_from_bank_name',
        'total_amount',
        'total_deduction_amount',
        'net_amount',
        'remark',
        'status',
        'created_by',
        'created_by_name',
        'verified_by',
        'verified_by_name',
        'verified_at',
        'verify_remark',
        'completed_by',
        'completed_by_name',
        'completed_at',
        'complete_remark',
        'receiver_name',
        'received_date',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'verified_at' => 'datetime',
        'completed_at' => 'datetime',
        'received_date' => 'date',
        'total_amount' => 'decimal:2',
        'total_deduction_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    // Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';      // รอตรวจสอบ
    const STATUS_VERIFIED = 'verified';    // ตรวจสอบแล้ว
    const STATUS_COMPLETED = 'completed';  // เสร็จสิ้น
    const STATUS_REJECTED = 'rejected';    // ปฏิเสธ

    public static $statusLabels = [
        'draft' => 'แบบร่าง',
        'pending' => 'รอตรวจสอบ',
        'verified' => 'ตรวจสอบแล้ว',
        'completed' => 'เสร็จสิ้น',
        'rejected' => 'ถูกปฏิเสธ',
    ];

    public static $statusColors = [
        'draft' => 'secondary',
        'pending' => 'warning',
        'verified' => 'info',
        'completed' => 'success',
        'rejected' => 'danger',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(ReceiptVoucherItem::class)->orderBy('item_no');
    }

    public function receiptItems()
    {
        return $this->hasMany(ReceiptVoucherItem::class)
            ->where('type', 'item')
            ->orderBy('item_no');
    }

    public function deductions()
    {
        return $this->hasMany(ReceiptVoucherItem::class)
            ->where('type', 'deduction')
            ->orderBy('item_no');
    }

    public function logs()
    {
        return $this->hasMany(ReceiptVoucherLog::class)->orderBy('created_at', 'desc');
    }

    public function attachments()
    {
        return $this->hasMany(ReceiptVoucherAttachment::class)
            ->where('attachment_type', 'voucher');
    }

    public function allAttachments()
    {
        return $this->hasMany(ReceiptVoucherAttachment::class);
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

    public function canVerify()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canComplete()
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public static $branchCodes = [
        'ELRV' => 'EyeLavel (EL)',
        'IFRV' => 'InfiniteBrain (IF)',
        'MTRV' => 'META Knowledge (MT)',
        'EiRP' => 'eiMaths RP (EiRP)',
        'EiBK' => 'eiMaths BK (EiBK)',
        'EiHQ' => 'eiMaths HQ (EiHQ)',
    ];

    public static $gropBranchCodes = [
        'INF' => ['IFRV', 'ELRV'],
        'META' => ['MTRV'],
        'META2' => ['EiRP', 'EiBK', 'EiHQ'],
    ];

    public static function generateVoucherNo($branchCode = 'ELRV')
    {
        $year = date('Y');
        $month = date('m');
        $prefix = $branchCode . $year . $month;

        $lastVoucher = self::where('voucher_no', 'like', $prefix . '%')
            ->orderBy('voucher_no', 'desc')
            ->first();

        if ($lastVoucher) {
            $lastNumber = (int) substr($lastVoucher->voucher_no, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
    }
}
