<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_code',
        'period_name',
        'frequency',
        'year',
        'month',
        'start_date',
        'end_date',
        'payment_date',
        'status',
        'total_income',
        'total_deduction',
        'total_net_pay',
        'employee_count',
        'created_by',
        'approved_by',
        'approved_at',
        'remark',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'total_income' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'total_net_pay' => 'decimal:2',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PROCESSING = 'processing';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';

    // Thai month names
    public static $thaiMonths = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม',
    ];

    /**
     * สร้าง period_code อัตโนมัติ
     */
    /**
     * สร้าง period_code อัตโนมัติ (YYYY-MM หรือ YYYY-MM-W-DD)
     */
    public static function GeneratePeriodCode($year, $month, $frequency = 'monthly', $start_date = null)
    {
        $base = sprintf('%04d-%02d', $year, $month);

        if ($frequency === 'weekly' && $start_date) {
            // Append day to ensure uniqueness (e.g., 2569-02-W-01)
            $day = date('d', strtotime($start_date));
            return $base . "-W-" . $day;
        }

        return $base . "-M"; // Add -M to distinguish from old system if needed, or keep compliant
    }

    /**
     * สร้าง period_name อัตโนมัติ
     */
    public static function GeneratePeriodName($year, $month, $frequency = 'monthly', $start_date = null, $end_date = null)
    {
        $month_name = self::$thaiMonths[$month] ?? '';
        $base = "$month_name $year";

        if ($frequency === 'weekly' && $start_date && $end_date) {
            $s = date('j', strtotime($start_date));
            $e = date('j', strtotime($end_date));
            return "งวดรายสัปดาห์ $s-$e $base"; // e.g. งวดรายสัปดาห์ 1-7 กุมภาพันธ์ 2569
        }

        return "งวดประจำเดือน $base";
    }

    /**
     * Relationships
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * คำนวณ totals จาก payrolls
     */
    public function CalculateTotals()
    {
        $this->total_income = $this->payrolls()->sum('total_income');
        $this->total_deduction = $this->payrolls()->sum('total_deduction');
        $this->total_net_pay = $this->payrolls()->sum('net_pay');
        $this->employee_count = $this->payrolls()->count();
        $this->save();
    }

    /**
     * Status badge สำหรับ UI
     */
    public function GetStatusBadge()
    {
        return match ($this->status) {
            self::STATUS_DRAFT => '<span class="badge bg-secondary">ร่าง</span>',
            self::STATUS_PROCESSING => '<span class="badge bg-warning">กำลังประมวลผล</span>',
            self::STATUS_APPROVED => '<span class="badge bg-success">อนุมัติแล้ว</span>',
            self::STATUS_PAID => '<span class="badge bg-primary">จ่ายแล้ว</span>',
            default => '<span class="badge bg-secondary">-</span>',
        };
    }

    /**
     * ตรวจสอบว่าสามารถแก้ไขได้หรือไม่
     */
    public function CanEdit()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PROCESSING]);
    }
}
