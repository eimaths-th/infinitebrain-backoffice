<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_period_id',
        'user_id',
        'employee_code',
        'employee_name',
        'position',
        'employee_type',
        'bank_account',
        'bank_name',
        'base_salary',
        'total_income',
        'total_deduction',
        'net_pay',
        'calc_social_security',
        'calc_tax',
        'social_security_rate',
        'social_security_cap',
        'tax_rate',
        'remark',
        'status',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'total_income' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'calc_social_security' => 'boolean',
        'calc_tax' => 'boolean',
        'social_security_rate' => 'decimal:2',
        'social_security_cap' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PAID = 'paid';

    // Employee type constants
    const TYPE_FULLTIME = 'fulltime';
    const TYPE_PARTTIME = 'parttime';
    const TYPE_CONTRACT = 'contract';

    // Income type constants
    const INCOME_SALARY = 'salary';
    const INCOME_OT = 'ot';
    const INCOME_TRAVEL = 'travel';
    const INCOME_COMMISSION = 'commission';
    const INCOME_BONUS = 'bonus';
    const INCOME_OTHER = 'other';

    // Deduction type constants
    const DEDUCTION_SOCIAL_SECURITY = 'social_security';
    const DEDUCTION_TAX = 'tax';
    const DEDUCTION_LATE = 'late';
    const DEDUCTION_LOAN = 'loan';
    const DEDUCTION_ADVANCE = 'advance';
    const DEDUCTION_OTHER = 'other';

    public static $incomeTypes = [
        self::INCOME_SALARY => 'เงินเดือน',
        self::INCOME_OT => 'ค่าล่วงเวลา',
        self::INCOME_TRAVEL => 'ค่าเดินทาง',
        self::INCOME_COMMISSION => 'คอมมิสชั่น',
        self::INCOME_BONUS => 'โบนัส',
        self::INCOME_OTHER => 'รายได้อื่นๆ',
    ];

    public static $deductionTypes = [
        self::DEDUCTION_SOCIAL_SECURITY => 'ประกันสังคม',
        self::DEDUCTION_TAX => 'ภาษี',
        self::DEDUCTION_LATE => 'มาสาย',
        self::DEDUCTION_LOAN => 'เงินกู้',
        self::DEDUCTION_ADVANCE => 'เงินทดรอง',
        self::DEDUCTION_OTHER => 'หักอื่นๆ',
    ];

    public static $employeeTypes = [
        self::TYPE_FULLTIME => 'พนักงานประจำ',
        self::TYPE_PARTTIME => 'พนักงานพาร์ทไทม์',
        self::TYPE_CONTRACT => 'พนักงานสัญญาจ้าง',
    ];

    /**
     * Relationships
     */
    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function incomeItems()
    {
        return $this->hasMany(PayrollIncomeItem::class)->orderBy('sort_order');
    }

    public function deductionItems()
    {
        return $this->hasMany(PayrollDeductionItem::class)->orderBy('sort_order');
    }

    /**
     * คำนวณ ประกันสังคม
     */
    public function CalculateSocialSecurity()
    {
        if (!$this->calc_social_security) {
            return 0;
        }

        // ใช้ base_salary ถ้ามี หรือใช้ total_income จาก salary items
        $base = $this->base_salary;

        if ($base <= 0) {
            // หาจาก salary income item
            $salary_item = $this->incomeItems()->where('income_type', self::INCOME_SALARY)->first();
            $base = $salary_item?->amount ?? $this->total_income;
        }

        // คำนวณ 5% แต่ไม่เกินเพดาน
        $base = min($base, $this->social_security_cap);
        return round($base * ($this->social_security_rate / 100), 2);
    }

    /**
     * คำนวณภาษี
     */
    public function CalculateTax($total_income = null)
    {
        if (!$this->calc_tax) {
            return 0;
        }

        $income = $total_income ?? $this->total_income;
        return round($income * ($this->tax_rate / 100), 2);
    }

    /**
     * คำนวณหักมาสาย
     * สูตร: (เงินเดือน / 30 / 8 / 60) * นาทีที่มาสาย
     */
    public function CalculateLateDeduction($minutes_late, $base_salary = null)
    {
        if ($minutes_late <= 0) {
            return 0;
        }

        // ใช้ base_salary หรือหาจาก salary income item
        $salary = $base_salary ?? $this->base_salary;

        if ($salary <= 0) {
            $salary_item = $this->incomeItems()->where('income_type', self::INCOME_SALARY)->first();
            $salary = $salary_item ? ($salary_item->quantity * $salary_item->amount) : 0;
        }

        if ($salary <= 0) {
            return 0;
        }

        // อัตราต่อนาที = เงินเดือน / 30วัน / 8ชม. / 60นาที
        $rate_per_minute = $salary / 30 / 8 / 60;

        return round($rate_per_minute * $minutes_late, 2);
    }

    /**
     * คำนวณ totals จาก items
     */
    public function CalculateTotals()
    {
        // Refresh relations to ensure we have the latest items from DB/Transaction
        $this->load(['incomeItems', 'deductionItems']);

        // รายได้ = sum(quantity * amount)
        $this->total_income = $this->incomeItems
            ->sum(fn($item) => $item->quantity * $item->amount);

        $this->total_deduction = $this->deductionItems->sum('amount');
        $this->net_pay = $this->total_income - $this->total_deduction;
        $this->save();
    }

    /**
     * เพิ่มรายการรายได้
     */
    public function AddIncomeItem($type, $description, $amount, $quantity = 1, $rate = 0)
    {
        // ใช้ default description จาก income type ถ้าไม่มี
        $description = $description ?: (self::$incomeTypes[$type] ?? $type);

        return $this->incomeItems()->create([
            'income_type' => $type,
            'description' => $description,
            'quantity' => $quantity,
            'rate' => $rate,
            'amount' => $amount,
            'sort_order' => $this->incomeItems()->count(),
        ]);
    }

    /**
     * เพิ่มรายการหัก
     */
    public function AddDeductionItem($type, $description, $amount, $rate = null)
    {
        // ใช้ default description จาก deduction type ถ้าไม่มี
        $description = $description ?: (self::$deductionTypes[$type] ?? $type);

        return $this->deductionItems()->create([
            'deduction_type' => $type,
            'description' => $description,
            'rate' => $rate,
            'amount' => $amount,
            'sort_order' => $this->deductionItems()->count(),
        ]);
    }

    /**
     * สร้าง Payroll พร้อมคำนวณอัตโนมัติ
     */
    public static function CreateForUser($payroll_period_id, User $user, $options = [])
    {
        $payroll = self::create([
            'payroll_period_id' => $payroll_period_id,
            'user_id' => $user->id,
            'employee_code' => $user->code,
            'employee_name' => $user->name,
            'position' => $user->position,
            'employee_type' => $user->employee_type ?? 'fulltime',
            'bank_account' => $user->bank_account,
            'bank_name' => $user->bank_name,
            'base_salary' => $user->base_salary ?? 0,
            'calc_social_security' => $options['calc_social_security'] ?? ($user->employee_type !== 'parttime'),
            'calc_tax' => $options['calc_tax'] ?? true,
            'social_security_rate' => $options['social_security_rate'] ?? 5.00,
            'social_security_cap' => $options['social_security_cap'] ?? 17500,
            'tax_rate' => $options['tax_rate'] ?? 3.00,
        ]);

        // เพิ่มเงินเดือนฐาน
        if ($payroll->base_salary > 0) {
            $payroll->AddIncomeItem(
                self::INCOME_SALARY,
                'เงินเดือน',
                $payroll->base_salary
            );
        }

        // คำนวณประกันสังคม
        if ($payroll->calc_social_security) {
            $social_security = $payroll->CalculateSocialSecurity();
            if ($social_security > 0) {
                $payroll->AddDeductionItem(
                    self::DEDUCTION_SOCIAL_SECURITY,
                    'ประกันสังคม ' . $payroll->social_security_rate . '%',
                    $social_security,
                    $payroll->social_security_rate
                );
            }
        }

        // คำนวณ totals
        $payroll->CalculateTotals();

        // คำนวณภาษี (หลังจากได้ total_income)
        if ($payroll->calc_tax) {
            $tax = $payroll->CalculateTax();
            if ($tax > 0) {
                $payroll->AddDeductionItem(
                    self::DEDUCTION_TAX,
                    'ภาษี ' . $payroll->tax_rate . '%',
                    $tax,
                    $payroll->tax_rate
                );
            }
            $payroll->CalculateTotals();
        }

        return $payroll;
    }

    /**
     * Status badge สำหรับ UI
     */
    public function GetStatusBadge()
    {
        return match ($this->status) {
            self::STATUS_DRAFT => '<span class="badge bg-secondary">ร่าง</span>',
            self::STATUS_CONFIRMED => '<span class="badge bg-success">ยืนยันแล้ว</span>',
            self::STATUS_PAID => '<span class="badge bg-primary">จ่ายแล้ว</span>',
            default => '<span class="badge bg-secondary">-</span>',
        };
    }

    /**
     * Employee type label
     */
    public function GetEmployeeTypeLabel()
    {
        return self::$employeeTypes[$this->employee_type] ?? $this->employee_type;
    }
}
