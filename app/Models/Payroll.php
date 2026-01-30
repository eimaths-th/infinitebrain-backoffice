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
    const DEDUCTION_ABSENCE = 'absence'; // NEW
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
        self::DEDUCTION_ABSENCE => 'ขาดงาน', // NEW
        self::DEDUCTION_LOAN => 'เงินกู้',
        self::DEDUCTION_ADVANCE => 'เงินทดรอง',
        self::DEDUCTION_OTHER => 'หักอื่นๆ',
    ];

    // ...

    /**
     * คำนวณหักขาดงาน (Job Abandonment)
     * สูตร: (เงินเดือน / 30) * จำนวนวัน
     */
    public function CalculateAbsenceDeduction($days, $base_salary = null)
    {
        if ($days <= 0) {
            return 0;
        }

        $salary = $base_salary ?? $this->base_salary;

        if ($salary <= 0) {
            // Fallback similar to Late Deduction
            $salary_item = $this->incomeItems->firstWhere('income_type', self::INCOME_SALARY);
            $salary = $salary_item?->amount ?? 0;

            if ($salary <= 0) {
                $salary = $this->incomeItems->sum(fn($item) => $item->quantity * $item->amount);
            }
        }

        if ($salary <= 0) {
            return 0;
        }

        $rate_per_day = $salary / 30;
        return round($rate_per_day * $days, 2);
    }

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
     * คำนวณภาษีเงินได้บุคคลธรรมดา (Progressive Tax)
     * สูตร: (รายได้ - ค่าใช้จ่าย - ลดหย่อน) -> ฐานภาษี -> คำนวณตามขั้นบันได
     */
    public function CalculateProgressiveTax($monthly_income, $monthly_social_security)
    {
        // 1. ประมาณการรายได้ทั้งปี
        $annual_income = $monthly_income * 12;

        // 2. หักค่าใช้จ่าย (50% ไม่เกิน 100,000)
        $expense_allowance = min($annual_income * 0.50, 100000);

        // 3. หักค่าลดหย่อน
        $personal_allowance = 60000; // ลดหย่อนส่วนตัว
        $sso_allowance = $monthly_social_security * 12; // ลดหย่อนประกันสังคม (Annualized)

        $total_deductions = $expense_allowance + $personal_allowance + $sso_allowance;
        $net_taxable_income = max(0, $annual_income - $total_deductions);

        // 4. คำนวณภาษีตามขั้นบันได
        $tax = 0;

        // 0 - 150,000: ยกเว้น
        $remain = $net_taxable_income - 150000;

        if ($remain > 0) {
            // 150,001 - 300,000: 5% (Range 150k)
            $step = min($remain, 150000);
            $tax += $step * 0.05;
            $remain -= $step;
        }

        if ($remain > 0) {
            // 300,001 - 500,000: 10% (Range 200k)
            $step = min($remain, 200000);
            $tax += $step * 0.10;
            $remain -= $step;
        }

        if ($remain > 0) {
            // 500,001 - 750,000: 15% (Range 250k)
            $step = min($remain, 250000);
            $tax += $step * 0.15;
            $remain -= $step;
        }

        if ($remain > 0) {
            // 750,001 - 1,000,000: 20% (Range 250k)
            $step = min($remain, 250000);
            $tax += $step * 0.20;
            $remain -= $step;
        }

        if ($remain > 0) {
            // 1,000,001 - 2,000,000: 25% (Range 1m)
            $step = min($remain, 1000000);
            $tax += $step * 0.25;
            $remain -= $step;
        }

        if ($remain > 0) {
            // 2,000,001 - 5,000,000: 30% (Range 3m)
            $step = min($remain, 3000000);
            $tax += $step * 0.30;
            $remain -= $step;
        }

        if ($remain > 0) {
            // > 5,000,000: 35%
            $tax += $remain * 0.35;
        }

        // 5. หาร 12 เพื่อเป็นภาษีต่อเดือน
        return round($tax / 12, 2);
    }

    /**
     * คำนวณภาษีทั้งระบบ (PIT + WHT) และอัปเดต Deduction Items
     */
    public function CalculateTax()
    {
        if (!$this->calc_tax) {
            return 0; // คืนค่า 0 แต่จริงๆ เราจะจัดการใน CalculateTotals
        }

        // Refetch items
        $this->load(['incomeItems']);

        // ---- A. ภาษีหัก ณ ที่จ่าย 3% (WHT) ----
        // หา income items ที่ระบุ is_wht_3_percent = true
        $wht_income_items = $this->incomeItems->where('is_wht_3_percent', true);
        $wht_total_income = $wht_income_items->sum(fn($item) => $item->amount * $item->quantity);
        $wht_tax = 0;

        if ($wht_total_income > 0) {
            $wht_tax = round($wht_total_income * 0.03, 2);
            // Update or Create Deduction Item
            $this->AddDeductionItem(
                'wht_3_percent',
                'ภาษีหัก ณ ที่จ่าย 3%',
                $wht_tax
            );
        } else {
            // Remove if exists
            $this->deductionItems()->where('deduction_type', 'wht_3_percent')->delete();
        }

        // ---- B. ภาษีเงินได้บุคคลธรรมดา (PIT) ----
        // รายได้ที่นำมาคิด PIT คือ รายได้ทั้งหมด ลบด้วย รายได้ที่ถูกคิด WHT ไปแล้ว (หรือรวม? ปกติแยกกัน)
        // ถ้าถูกหัก WHT 3% ถือเป็น 40(2) หรือรับเหมารับจ้าง ซึ่งต้องนำมารวมคำนวณภาษีปลายปีอยู่ดี?
        // แต่ในบริบท Payroll รายเดือน:
        // ถ้าเป็นพนักงานบริษัท -> คิด PIT
        // ถ้าเป็นฟรีแลนซ์/รับจ้าง -> คิด WHT 3% แล้วจบ (ส่ง ภงด.3/53)
        // Assumption: ถ้าติ๊ก WHT 3% -> ถูกหัก 3% แล้ว ไม่นำมาคิด PIT ใน Payroll นี้อีก (แยกคำนวณ)
        // ดังนั้น PIT Base Income = Total Income - WHT Income Base

        $total_income = $this->incomeItems->sum(fn($item) => $item->amount * $item->quantity);
        $pit_base_income = $total_income - $wht_total_income;

        if ($pit_base_income > 0) {
            // ต้องหา SSO ที่หักจริงในเดือนนี้
            $sso_item = $this->deductionItems()->where('deduction_type', self::DEDUCTION_SOCIAL_SECURITY)->first();
            $sso_amount = $sso_item ? $sso_item->amount : 0;

            $pit_tax = $this->CalculateProgressiveTax($pit_base_income, $sso_amount);

            if ($pit_tax > 0) {
                $this->AddDeductionItem(
                    self::DEDUCTION_TAX,
                    'ภาษีหัก ณ ที่จ่าย (ภ.ง.ด.1)',
                    $pit_tax
                );
            } else {
                // Remove if 0
                $this->deductionItems()->where('deduction_type', self::DEDUCTION_TAX)->delete();
            }
            return $pit_tax;
        }

        return 0;
    }

    /**
     * คำนวณหักมาสาย
     */
    public function CalculateLateDeduction($minutes_late, $base_salary = null)
    {
        if ($minutes_late <= 0) {
            return 0;
        }

        $salary = $base_salary ?? $this->base_salary;

        if ($salary <= 0) {
            // Try to find from income items (similar to SSO logic)
            $salary_item = $this->incomeItems->firstWhere('income_type', self::INCOME_SALARY);
            $salary = $salary_item?->amount ?? 0;

            // Final fallback: Use total income if salary is still 0
            // (Assumes total income is better than 0 for calculation base)
            if ($salary <= 0) {
                $salary = $this->incomeItems->sum(fn($item) => $item->quantity * $item->amount);
            }
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

        // รายได้
        $this->total_income = $this->incomeItems->sum(fn($item) => $item->quantity * $item->amount);

        // รายการหัก (รวมที่คำนวณ Auto แล้ว)
        $this->total_deduction = $this->deductionItems->sum('amount');

        $this->net_pay = $this->total_income - $this->total_deduction;
        $this->save();
    }

    /**
     * Helper สำหรับแยกกลุ่มรายการหัก
     */
    public static function getDeductionGroup($type)
    {
        return match ($type) {
            self::DEDUCTION_SOCIAL_SECURITY,
            self::DEDUCTION_TAX,
            'wht_3_percent' => 'government',
            default => 'company',
        };
    }

    /**
     * เพิ่มรายการรายได้
     */
    public function AddIncomeItem($type, $description, $amount, $quantity = 1, $rate = 0, $is_wht = false)
    {
        // ใช้ default description จาก income type ถ้าไม่มี
        $description = $description ?: (self::$incomeTypes[$type] ?? $type);

        return $this->incomeItems()->create([
            'income_type' => $type,
            'description' => $description,
            'quantity' => $quantity,
            'rate' => $rate,
            'amount' => $amount,
            'is_wht_3_percent' => $is_wht,
            'sort_order' => $this->incomeItems()->count(),
        ]);
    }

    /**
     * เพิ่มรายการหัก (Update if exists for Auto types)
     */
    public function AddDeductionItem($type, $description, $amount, $rate = null)
    {
        $description = $description ?: (self::$deductionTypes[$type] ?? $type);

        // ถ้าเป็น Auto Types ให้ Update แทน Create
        $autoTypes = [self::DEDUCTION_SOCIAL_SECURITY, self::DEDUCTION_TAX, 'wht_3_percent', self::DEDUCTION_LATE];

        if (in_array($type, $autoTypes)) {
            $item = $this->deductionItems()->where('deduction_type', $type)->first();
            if ($item) {
                $item->update([
                    'description' => $description,
                    'amount' => $amount,
                    'rate' => $rate
                ]);
                return $item;
            }
        }

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
