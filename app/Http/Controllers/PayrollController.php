<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\PayrollIncomeItem;
use App\Models\PayrollDeductionItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PayrollController extends Controller
{
    /**
     * แสดงรายการงวดเงินเดือน
     */
    public function index(Request $request)
    {
        $query = PayrollPeriod::query();

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $periods = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);

        // Get years for filter
        $years = PayrollPeriod::selectRaw('DISTINCT year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('payroll.index', compact('periods', 'years'));
    }

    /**
     * แสดงฟอร์มสร้างงวดใหม่
     */
    public function create()
    {
        // ปีปัจจุบัน (พ.ศ.)
        $current_year = date('Y') + 543;
        $current_month = (int) date('m');

        // ตรวจสอบว่างวดนี้มีอยู่แล้วหรือไม่
        $existing = PayrollPeriod::where('year', $current_year)
            ->where('month', $current_month)
            ->first();

        return view('payroll.create', compact('current_year', 'current_month', 'existing'));
    }

    /**
     * บันทึกงวดใหม่
     */
    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2560|max:2600',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->year;
        $month = $request->month;

        // ตรวจสอบว่ามีงวดนี้แล้วหรือไม่
        $existing = PayrollPeriod::where('year', $year)
            ->where('month', $month)
            ->first();

        if ($existing) {
            return redirect()->route('payroll.show', $existing->id)
                ->with('info', 'งวดนี้มีอยู่แล้ว');
        }

        // คำนวณวันเริ่ม-สิ้นสุดงวด (ใช้ปี ค.ศ.)
        $ad_year = $year - 543;
        $start_date = sprintf('%04d-%02d-01', $ad_year, $month);
        $end_date = date('Y-m-t', strtotime($start_date));
        $payment_date = $end_date; // default วันสุดท้ายของเดือน

        $period = PayrollPeriod::create([
            'period_code' => PayrollPeriod::GeneratePeriodCode($year, $month),
            'period_name' => PayrollPeriod::GeneratePeriodName($year, $month),
            'year' => $year,
            'month' => $month,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'payment_date' => $payment_date,
            'status' => PayrollPeriod::STATUS_DRAFT,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('payroll.show', $period->id)
            ->with('success', 'สร้างงวดเงินเดือน ' . $period->period_name . ' เรียบร้อยแล้ว');
    }

    /**
     * แสดงรายละเอียดงวด
     */
    public function show($id)
    {
        $period = PayrollPeriod::with(['payrolls.user', 'payrolls.incomeItems', 'payrolls.deductionItems'])
            ->findOrFail($id);

        // จัดกลุ่มตามประเภทพนักงาน
        $fulltime_payrolls = $period->payrolls->where('employee_type', 'fulltime');
        $parttime_payrolls = $period->payrolls->where('employee_type', 'parttime');
        $contract_payrolls = $period->payrolls->where('employee_type', 'contract');

        return view('payroll.show', compact('period', 'fulltime_payrolls', 'parttime_payrolls', 'contract_payrolls'));
    }

    /**
     * ดึงพนักงานทั้งหมดเข้างวด
     */
    public function generatePayrolls($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if (!$period->CanEdit()) {
            return back()->with('error', 'ไม่สามารถแก้ไขงวดนี้ได้');
        }

        DB::beginTransaction();
        try {
            // ดึง users ที่ active และมีเงินเดือน
            $users = User::where('is_disabled', false)->get();

            $count = 0;
            foreach ($users as $user) {
                // ข้ามถ้ามี payroll อยู่แล้ว
                $exists = Payroll::where('payroll_period_id', $period->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // กำหนด options ตามประเภทพนักงาน
                $options = [
                    'calc_social_security' => $user->employee_type !== 'parttime',
                    'calc_tax' => true,
                ];

                Payroll::CreateForUser($period->id, $user, $options);
                $count++;
            }

            $period->CalculateTotals();

            DB::commit();

            return back()->with('success', "เพิ่มพนักงาน {$count} คนเข้างวดเรียบร้อยแล้ว");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * แสดง/แก้ไข payroll รายบุคคล
     */
    public function editPayroll($period_id, $payroll_id)
    {
        $period = PayrollPeriod::findOrFail($period_id);
        $payroll = Payroll::with(['incomeItems', 'deductionItems', 'user'])
            ->where('payroll_period_id', $period_id)
            ->findOrFail($payroll_id);

        return view('payroll.edit_payroll', compact('period', 'payroll'));
    }

    /**
     * อัปเดต payroll รายบุคคล
     */
    public function updatePayroll(Request $request, $period_id, $payroll_id)
    {
        $period = PayrollPeriod::findOrFail($period_id);
        $payroll = Payroll::where('payroll_period_id', $period_id)
            ->findOrFail($payroll_id);

        if (!$period->CanEdit()) {
            return back()->with('error', 'ไม่สามารถแก้ไขงวดนี้ได้');
        }

        DB::beginTransaction();
        try {
            // อัปเดต options
            $payroll->update([
                'calc_social_security' => $request->boolean('calc_social_security'),
                'calc_tax' => $request->boolean('calc_tax'),
                'social_security_rate' => $request->social_security_rate ?? 5.00,
                'tax_rate' => $request->tax_rate ?? 3.00,
                'remark' => $request->remark,
            ]);

            // Refresh เพื่อให้มีค่าล่าสุด
            $payroll->refresh();

            // ลบ items เก่า
            $payroll->incomeItems()->delete();
            $payroll->deductionItems()->delete();

            // เพิ่ม income items ใหม่ และอัปเดต base_salary
            if ($request->has('income_items')) {
                foreach ($request->income_items as $item) {
                    if (!empty($item['amount']) && $item['amount'] > 0) {
                        $is_wht = isset($item['is_wht_3_percent']);
                        $payroll->AddIncomeItem(
                            $item['type'],
                            $item['description'],
                            $item['amount'],
                            $item['quantity'] ?? 1,
                            $item['rate'] ?? 0,
                            $is_wht // WHT Flag
                        );

                        // Sync base_salary if this is the salary item
                        if ($item['type'] === Payroll::INCOME_SALARY) {
                            $payroll->base_salary = $item['amount'];
                            $payroll->save();
                        }
                    }
                }
            }


            // คำนวณ totals ก่อนคำนวณรายการหัก
            $payroll->CalculateTotals();

            // รายการหักที่คำนวณอัตโนมัติ - ข้ามไม่ต้องบันทึกจากฟอร์ม
            // Note: 'wht_3_percent' is also auto now.
            $auto_deduction_types = [
                Payroll::DEDUCTION_SOCIAL_SECURITY,
                Payroll::DEDUCTION_TAX,
                Payroll::DEDUCTION_LATE,
                'wht_3_percent'
            ];

            // เพิ่ม deduction items ใหม่ (เฉพาะที่ไม่ใช่ auto)
            if ($request->has('deduction_items')) {
                foreach ($request->deduction_items as $item) {
                    // ข้ามรายการหักอัตโนมัติ
                    if (in_array($item['type'], $auto_deduction_types)) {
                        continue;
                    }

                    if (!empty($item['amount']) && $item['amount'] > 0) {
                        $payroll->AddDeductionItem(
                            $item['type'],
                            $item['description'],
                            $item['amount'],
                            $item['rate'] ?? null
                        );
                    }
                }
            }

            // คำนวณ ปกส. อัตโนมัติ
            if ($payroll->calc_social_security) {
                $ss = $payroll->CalculateSocialSecurity();
                if ($ss > 0) {
                    $payroll->AddDeductionItem(
                        Payroll::DEDUCTION_SOCIAL_SECURITY,
                        'ประกันสังคม ' . $payroll->social_security_rate . '%',
                        $ss,
                        $payroll->social_security_rate
                    );
                }
            }

            // คำนวณ totals และภาษี (รวม WHT และ PIT)
            $payroll->CalculateTotals();

            if ($payroll->calc_tax) {
                // CalculateTax creates items internally
                $payroll->CalculateTax();
                $payroll->CalculateTotals();
            }

            // คำนวณหักมาสาย
            $late_minutes = $request->input('late_minutes', 0);
            if ($late_minutes > 0) {
                $late_amount = $payroll->CalculateLateDeduction($late_minutes);
                if ($late_amount > 0) {
                    $payroll->AddDeductionItem(
                        Payroll::DEDUCTION_LATE,
                        'หักเข้ากองทุน',
                        $late_amount,
                        $late_minutes // Save minutes as rate
                    );
                }
                $payroll->CalculateTotals();
            }

            // คำนวณหักขาดงาน
            $absence_days = $request->input('absence_days', 0);
            if ($absence_days > 0) {
                $absence_amount = $payroll->CalculateAbsenceDeduction($absence_days);
                if ($absence_amount > 0) {
                    $payroll->AddDeductionItem(
                        Payroll::DEDUCTION_ABSENCE,
                        'ขาดงาน ' . $absence_days . ' วัน',
                        $absence_amount,
                        $absence_days // Save days as rate
                    );
                }
                $payroll->CalculateTotals();
            }

            // อัปเดต totals ของ period
            $period->CalculateTotals();

            DB::commit();

            return redirect()->route('payroll.show', $period_id)
                ->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ลบ payroll รายบุคคล
     */
    public function deletePayroll($period_id, $payroll_id)
    {
        $period = PayrollPeriod::findOrFail($period_id);
        $payroll = Payroll::where('payroll_period_id', $period_id)
            ->findOrFail($payroll_id);

        if (!$period->CanEdit()) {
            return back()->with('error', 'ไม่สามารถแก้ไขงวดนี้ได้');
        }

        $payroll->delete();
        $period->CalculateTotals();

        return back()->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * พิมพ์ Payslip
     */
    public function payslip($period_id, $payroll_id)
    {
        $period = PayrollPeriod::findOrFail($period_id);
        $payroll = Payroll::with(['incomeItems', 'deductionItems', 'user'])
            ->where('payroll_period_id', $period_id)
            ->findOrFail($payroll_id);

        return view('payroll.payslip', compact('period', 'payroll'));
    }

    /**
     * อนุมัติงวด
     */
    public function approve($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->status !== PayrollPeriod::STATUS_DRAFT && $period->status !== PayrollPeriod::STATUS_PROCESSING) {
            return back()->with('error', 'ไม่สามารถอนุมัติงวดนี้ได้');
        }

        $period->update([
            'status' => PayrollPeriod::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'อนุมัติงวดเรียบร้อยแล้ว');
    }

    /**
     * บันทึกจ่ายเงินเดือน
     */
    public function markAsPaid($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->status !== PayrollPeriod::STATUS_APPROVED) {
            return back()->with('error', 'ต้องอนุมัติก่อนจึงจะบันทึกจ่ายได้');
        }

        $period->update([
            'status' => PayrollPeriod::STATUS_PAID,
        ]);

        // อัปเดต status ของ payrolls ทั้งหมด
        $period->payrolls()->update(['status' => Payroll::STATUS_PAID]);

        return back()->with('success', 'บันทึกจ่ายเงินเดือนเรียบร้อยแล้ว');
    }

    /**
     * ลบงวด
     */
    public function destroy($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if (!$period->CanEdit()) {
            return back()->with('error', 'ไม่สามารถลบงวดนี้ได้');
        }

        $period->delete();

        return redirect()->route('payroll.index')
            ->with('success', 'ลบงวดเรียบร้อยแล้ว');
    }

    /**
     * แสดงหน้า Import Excel
     */
    public function showImport($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if (!$period->CanEdit()) {
            return back()->with('error', 'ไม่สามารถ Import งวดนี้ได้');
        }

        return view('payroll.import', compact('period'));
    }

    /**
     * Preview Import Excel
     */
    public function previewImport(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if (!$period->CanEdit()) {
            return redirect()->route('payroll.import', $id)->with('error', 'ไม่สามารถ Import งวดนี้ได้');
        }

        $path = null;
        $import_type = null;

        if ($request->isMethod('post')) {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls',
                'import_type' => 'required|in:summary,payslip',
            ]);

            $file = $request->file('excel_file');
            $path = $file->store('imports/payroll');
            $import_type = $request->input('import_type');

            // เก็บ path และ type ไว้ใน session
            session([
                'import_payroll_path' => $path,
                'import_payroll_type' => $import_type
            ]);
        } else {
            // GET: Check Session
            if (session('import_payroll_path') && \Illuminate\Support\Facades\Storage::exists(session('import_payroll_path'))) {
                $path = session('import_payroll_path');
                $import_type = session('import_payroll_type', 'summary');
            } else {
                return redirect()->route('payroll.import', $id)->with('error', 'Session หมดอายุหรือไฟล์ไม่ถูกต้อง กรุณาอัปโหลดใหม่');
            }
        }

        try {
            // อ่านไฟล์ Excel
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile(storage_path('app/' . $path));
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load(storage_path('app/' . $path));

            if ($import_type === 'payslip') {
                $preview_data = $this->parsePayslipSheet($spreadsheet);
                $all_users = \App\Models\User::orderBy('code')->get();
                return view('payroll.import_summary', compact('period', 'preview_data', 'all_users'));
            } else {
                $preview_data = $this->parseSummarySheet($spreadsheet);
                return view('payroll.import_preview', compact('period', 'preview_data'));
            }
        } catch (\Exception $e) {
            return redirect()->route('payroll.import', $id)->with('error', 'ไม่สามารถอ่านไฟล์ Excel ได้: ' . $e->getMessage());
        }
    }

    /**
     * Process Import Excel
     */
    public function processImport(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if (!$period->CanEdit()) {
            return back()->with('error', 'ไม่สามารถ Import งวดนี้ได้');
        }

        $user_mapping = $request->input('user_mapping', []);

        // 1. ดึง Path จาก Session (เหมือน previewImport)
        if (!session('import_payroll_path') || !\Illuminate\Support\Facades\Storage::exists(session('import_payroll_path'))) {
            return redirect()->route('payroll.import', $id)->with('error', 'Session หมดอายุ กรุณาอัปโหลดไฟล์ใหม่');
        }
        $path = session('import_payroll_path');
        $import_type = session('import_payroll_type', 'summary');

        DB::beginTransaction();
        try {
            // 2. อ่านไฟล์ Excel อีกครั้ง
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile(storage_path('app/' . $path));
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load(storage_path('app/' . $path));

            // 3. แปลงข้อมูลตาม Type
            $import_data = [];
            if ($import_type === 'payslip') {
                // ใช้ Logic เดียวกับ parsePayslipSheet แต่เราจะเรียกใช้ method เดิม
                $import_data = $this->parsePayslipSheet($spreadsheet);
            } else {
                $import_data = $this->parseSummarySheet($spreadsheet);
            }

            if (empty($import_data)) {
                return back()->with('error', 'ไม่พบข้อมูลในไฟล์ Excel');
            }

            $imported_count = 0;
            $created_user_count = 0;
            $skipped_count = 0;

            foreach ($import_data as $data) {
                // 4. ตรวจสอบว่า User เลือกรายการนี้หรือไม่
                // code ใน $data คือรหัสพนักงาน
                if (!$request->boolean("selected.{$data['code']}")) {
                    continue;
                }

                // หา user จาก code
                $user = \App\Models\User::where('code', $data['code'])->first();

                // ถ้าไม่เจอ ลองดูใน mapping
                if (!$user && isset($user_mapping[$data['code']]) && !empty($user_mapping[$data['code']])) {
                    $user = \App\Models\User::find($user_mapping[$data['code']]);
                }

                // ถ้ายังไม่เจออีก สร้าง user ใหม่ (ถ้าต้องการ)
                if (!$user) {
                    // สร้าง Email ที่ไม่ซ้ำ
                    $email = $data['code'] . '@example.com';
                    if (\App\Models\User::where('email', $email)->exists()) {
                        $email = $data['code'] . '_' . uniqid() . '@example.com';
                    }

                    try {
                        $user = \App\Models\User::create([
                            'code' => $data['code'],
                            'name' => $data['name'],
                            'email' => $email,
                            'password' => bcrypt('password'), // Default password
                            'position' => $data['position'] ?? 'พนักงาน',
                            'role' => 'employee', // Default role
                            'level' => 5, // Normal
                            'type_user' => 'employee',
                            'employee_type' => 'fulltime',
                            'bank_account' => $data['bank_account'] ?? null,
                            'is_disabled' => false,
                        ]);
                        $created_user_count++;
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Import User Creation Failed for {$data['code']}: " . $e->getMessage());
                        $skipped_count++;
                        continue; // ข้ามถ้าสร้างไม่ได้
                    }
                }

                // อัปเดตข้อมูล bank หากมีใน excel แต่ไม่มีใน DB
                if (empty($user->bank_account) && !empty($data['bank_account'])) {
                    $user->bank_account = $data['bank_account'];
                    $user->save();
                }

                // ตรวจสอบว่ามี payroll อยู่แล้วหรือไม่
                $payroll = Payroll::where('payroll_period_id', $period->id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($payroll) {
                    // ลบ items เดิมเพื่อสร้างใหม่
                    $payroll->incomeItems()->delete();
                    $payroll->deductionItems()->delete();
                } else {
                    // สร้างใหม่
                    $payroll = new Payroll();
                    $payroll->payroll_period_id = $period->id;
                    $payroll->user_id = $user->id;
                }

                // อัปเดตข้อมูลพื้นฐาน
                $payroll->employee_code = $user->code;
                $payroll->employee_name = $user->name;
                $payroll->position = $user->position;
                $payroll->employee_type = $user->employee_type ?? 'fulltime';
                $payroll->bank_account = $user->bank_account;
                $payroll->bank_name = $user->bank_name;

                // Dynamic Items (จาก Payslip Mode)
                $payroll->save(); // Save ก่อนเพื่อเอา ID ไปใช้กับ AddItem
                if (isset($data['income_items']) || isset($data['deduction_items'])) { // Check key names from parser
                    $base_salary = 0;

                    // Income Items
                    if (!empty($data['income_items'])) {
                        foreach ($data['income_items'] as $item) {
                            $type = Payroll::INCOME_OTHER;
                            $desc = $item['description'];

                            // Map Types
                            if (stripos($desc, 'เงินเดือน') !== false) {
                                $type = Payroll::INCOME_SALARY;
                                $base_salary = $item['amount'];
                            } elseif (stripos($desc, 'ล่วงเวลา') !== false || stripos($desc, 'OT') !== false) {
                                $type = Payroll::INCOME_OT;
                            } elseif (stripos($desc, 'เดินทาง') !== false) {
                                $type = Payroll::INCOME_TRAVEL;
                            }

                            $payroll->AddIncomeItem($type, $desc, $item['amount']);
                        }
                    }

                    // Deduction Items
                    if (!empty($data['deduction_items'])) {
                        foreach ($data['deduction_items'] as $item) {
                            $type = Payroll::DEDUCTION_OTHER;
                            $desc = $item['description'];

                            // Map Types
                            if (stripos($desc, 'ประกันสังคม') !== false) {
                                $type = Payroll::DEDUCTION_SOCIAL_SECURITY;
                            } elseif (stripos($desc, 'ภาษี') !== false) {
                                $type = Payroll::DEDUCTION_TAX;
                            } elseif (stripos($desc, 'มาสาย') !== false) {
                                $type = Payroll::DEDUCTION_LATE;
                            } elseif (stripos($desc, 'เงินกู้') !== false) {
                                $type = Payroll::DEDUCTION_LOAN;
                            }

                            $payroll->AddDeductionItem($type, $desc, $item['amount']);
                        }
                    }

                    // Note: In original code, it accessed $data['items']['income'] but parser returns 'income_items' directly.
                    // parsePayslipSheet returns: ['income_items' => [...], 'deduction_items' => [...]]
                    // So using $data['income_items'] is correct.

                    $payroll->base_salary = $base_salary;
                } else {
                    // Fixed Columns (จาก Summary Mode) logic...
                    // Check if keys exist in $data (parseSummarySheet returns 'salary', 'ot', etc.)
                    $payroll->base_salary = $data['salary'] ?? 0;
                    $payroll->calc_social_security = ($data['social_security'] ?? 0) > 0;
                    $payroll->calc_tax = ($data['tax'] ?? 0) > 0;
                    $payroll->save();

                    // Add Income
                    if (($data['salary'] ?? 0) > 0) $payroll->AddIncomeItem(Payroll::INCOME_SALARY, 'เงินเดือน', $data['salary']);
                    if (($data['ot'] ?? 0) > 0) $payroll->AddIncomeItem(Payroll::INCOME_OT, 'ค่าล่วงเวลา', $data['ot']);
                    if (($data['travel'] ?? 0) > 0) $payroll->AddIncomeItem(Payroll::INCOME_TRAVEL, 'ค่าเดินทาง', $data['travel']);
                    if (($data['other_income'] ?? 0) > 0) $payroll->AddIncomeItem(Payroll::INCOME_OTHER, 'อื่นๆ', $data['other_income']);

                    // Add Deduction
                    if (($data['social_security'] ?? 0) > 0) $payroll->AddDeductionItem(Payroll::DEDUCTION_SOCIAL_SECURITY, 'ประกันสังคม', $data['social_security']);
                    if (($data['tax'] ?? 0) > 0) $payroll->AddDeductionItem(Payroll::DEDUCTION_TAX, 'ภาษี', $data['tax']);
                    if (($data['other_deduction'] ?? 0) > 0) $payroll->AddDeductionItem(Payroll::DEDUCTION_OTHER, 'หักอื่นๆ', $data['other_deduction']);
                }

                $payroll->save();
                $payroll->CalculateTotals();
                $imported_count++;
            }

            // คำนวณ totals ของ period
            $period->CalculateTotals();

            // ลบไฟล์ temp
            if (session('import_payroll_path')) {
                Storage::delete(session('import_payroll_path'));
                session()->forget('import_payroll_path');
                session()->forget('import_payroll_type');
            }

            DB::commit();

            $message = "นำเข้าข้อมูลสำเร็จ {$imported_count} รายการ";
            if ($created_user_count > 0) $message .= " (สร้าง user ใหม่ {$created_user_count} รายการ)";
            if ($skipped_count > 0) $message .= " (ข้าม {$skipped_count} รายการ)";

            return redirect()->route('payroll.show', $period->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }



    /**
     * Parse Summary Sheet (Sheet 0)
     */
    private function parseSummarySheet($spreadsheet)
    {
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray();
        $header_row = null;
        $data_rows = [];

        foreach ($rows as $index => $row) {
            $row_text = implode(' ', array_filter($row));
            if (
                stripos($row_text, 'รหัส') !== false ||
                stripos($row_text, 'ลำดับ') !== false ||
                stripos($row_text, 'ชื่อ') !== false
            ) {
                $header_row = $row;
                continue;
            }

            if ($header_row !== null) {
                if (empty(array_filter($row))) continue;
                $first_col = trim($row[0] ?? '');
                if (stripos($first_col, 'รวม') !== false) continue;

                if (!empty($row[1]) || !empty($row[2])) {
                    $data_rows[] = $row;
                }
            }
        }

        $preview_data = [];
        foreach ($data_rows as $row) {
            $preview_data[] = [
                'code' => trim($row[3] ?? ''), // ตาม mapping ที่แก้แล้ว
                'name' => trim($row[1] ?? ''),
                'position' => trim($row[2] ?? ''),
                'bank_account' => trim($row[4] ?? ''),
                'salary' => $this->ParseNumber($row[5] ?? 0),
                'ot' => $this->ParseNumber($row[6] ?? 0),
                'travel' => $this->ParseNumber($row[7] ?? 0),
                'other_income' => $this->ParseNumber($row[8] ?? 0),
                'total_income' => $this->ParseNumber($row[9] ?? 0),
                'social_security' => $this->ParseNumber($row[10] ?? 0),
                'tax' => $this->ParseNumber($row[11] ?? 0),
                'other_deduction' => $this->ParseNumber($row[12] ?? 0),
                'total_deduction' => $this->ParseNumber($row[13] ?? 0),
                'net_pay' => $this->ParseNumber($row[14] ?? 0),
            ];
        }
        return $preview_data;
    }

    /**
     * Parse Payslip Sheet (Sheet 1)
     */
    private function parsePayslipSheet($spreadsheet)
    {
        $sheet = $spreadsheet->getSheet(1); // Sheet Payslip
        $rows = $sheet->toArray();
        $employees = [];
        $current_employee = null;

        foreach ($rows as $row) {
            $first_cell = trim($row[0] ?? '');

            // ตรวจจับ header ของแต่ละคน (เริ่มด้วย รหัส)
            if ($first_cell === 'รหัส') {
                if ($current_employee) $employees[] = $current_employee;
                $current_employee = [
                    'code' => trim($row[1] ?? ''),
                    'name' => trim($row[4] ?? ''),
                    'position' => trim($row[8] ?? ''),
                    'bank_account' => trim($row[12] ?? ''),
                    'income_items' => [],
                    'deduction_items' => [],
                ];
                continue;
            }

            // ตรวจจับส่วนท้าย (รวมเงินได้)
            if (isset($row[3]) && stripos($row[3], 'รวมเงินได้') !== false) {
                if ($current_employee) {
                    $current_employee['total_income'] = $this->ParseNumber($row[5] ?? 0);
                    $current_employee['total_deduction'] = $this->ParseNumber($row[9] ?? 0);
                    $employees[] = $current_employee;
                    $current_employee = null;
                }
                continue;
            }

            if ($current_employee) {
                // รายได้: Col 0 Desc, Col 5 Amount
                $desc = trim($row[0] ?? '');
                if ($desc !== '' && $desc !== 'รายได้' && $desc !== 'เงินเพิ่ม' && stripos($desc, 'รวม') === false && stripos($desc, 'ใบจ่าย') === false && stripos($desc, 'บริษัท') === false && stripos($desc, 'เลขประจำตัว') === false && stripos($desc, '555/') === false) {
                    $amt = $this->ParseNumber($row[5] ?? 0);
                    if ($amt > 0) {
                        $current_employee['income_items'][] = ['description' => $desc, 'amount' => $amt];
                    }
                }

                // รายการหัก: Col 7 Desc, Col 9 Amount
                $desc = trim($row[7] ?? '');
                if ($desc !== '' && $desc !== 'รายการหัก' && stripos($desc, 'รวม') === false && $desc !== 'ตำแหน่ง') {
                    $amt = $this->ParseNumber($row[9] ?? 0);
                    if ($amt > 0) {
                        $current_employee['deduction_items'][] = ['description' => $desc, 'amount' => $amt];
                    }
                }
            }
        }

        // เก็บคนสุดท้ายถ้ามี
        if ($current_employee) {
            $employees[] = $current_employee;
        }

        return $employees;
    }

    /**
     * แปลงตัวเลขจาก Excel
     */
    private function ParseNumber($value)
    {
        if (is_numeric($value)) {
            return floatval($value);
        }
        // ลบ comma และแปลงเป็นตัวเลข
        $cleaned = str_replace([',', ' '], '', $value);
        return is_numeric($cleaned) ? floatval($cleaned) : 0;
    }


    /**
     * Show Bulk Edit Form
     */
    public function bulkEdit($id)
    {
        $period = PayrollPeriod::with(['payrolls' => function ($query) {
            $query->with(['incomeItems', 'deductionItems'])->orderBy('employee_code');
        }])->findOrFail($id);

        if (!$period->CanEdit()) {
            return back()->with('error', 'ไม่สามารถแก้ไขงวดนี้ได้');
        }

        return view('payroll.bulk_edit', compact('period'));
    }

    /**
     * Process Bulk Update (New Design: Nested Items)
     */
    public function bulkUpdate(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        if (!$period->CanEdit()) {
            return back()->with('error', 'ไม่สามารถแก้ไขงวดนี้ได้');
        }

        $items = $request->input('payrolls', []);
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($items as $payroll_id => $data) {
                // Find existing payroll record
                $payroll = Payroll::where('payroll_period_id', $id)->find($payroll_id);
                if (!$payroll) continue;

                // 1. Update Basic Settings
                $payroll->base_salary = $this->ParseNumber($data['base_salary'] ?? 0);
                $payroll->calc_social_security = isset($data['calc_social_security']);
                $payroll->calc_tax = isset($data['calc_tax']);
                $payroll->save();

                // 2. Wipe Old Items
                $payroll->incomeItems()->delete();
                $payroll->deductionItems()->delete();

                // 3. Process Income Items
                if (isset($data['income_items']) && is_array($data['income_items'])) {
                    foreach ($data['income_items'] as $item) {
                        $amount = $this->ParseNumber($item['amount'] ?? 0);
                        if ($amount > 0) {
                            $is_wht = isset($item['is_wht_3_percent']);
                            $payroll->AddIncomeItem(
                                $item['type'],
                                $item['description'] ?? '',
                                $amount,
                                1, // Quantity default
                                0, // Rate default
                                $is_wht // WHT 3% Flag
                            );
                        }
                    }
                }

                // 4. Ensure Base Salary Sync
                if ($payroll->base_salary > 0) {
                    $hasSalaryItem = $payroll->incomeItems()->where('income_type', Payroll::INCOME_SALARY)->exists();
                    if (!$hasSalaryItem) {
                        // Salary is usually NOT WHT 3%
                        $payroll->AddIncomeItem(Payroll::INCOME_SALARY, 'เงินเดือน', $payroll->base_salary);
                    }
                }

                // 5. Update Total Income for Calculations
                // Call CalculateTotals to ensure incomeItems sum is up to date before Tax/SSO
                $payroll->CalculateTotals();

                // 6. Process Manual Deduction Items
                if (isset($data['deduction_items']) && is_array($data['deduction_items'])) {
                    foreach ($data['deduction_items'] as $item) {
                        // Skip auto types
                        if (in_array($item['type'], [Payroll::DEDUCTION_SOCIAL_SECURITY, Payroll::DEDUCTION_TAX, Payroll::DEDUCTION_LATE, 'wht_3_percent'])) continue;

                        $amount = $this->ParseNumber($item['amount'] ?? 0);
                        if ($amount > 0) {
                            $payroll->AddDeductionItem(
                                $item['type'],
                                $item['description'] ?? '',
                                $amount
                            );
                        }
                    }
                }

                // 7. Calculate Auto Deductions
                // SSO (Model only returns amount, we must add item)
                if ($payroll->calc_social_security) {
                    // Ensure totals are fresh? CalculateTotals called above.
                    $ss = $payroll->CalculateSocialSecurity();
                    if ($ss > 0) {
                        $payroll->AddDeductionItem(Payroll::DEDUCTION_SOCIAL_SECURITY, 'ประกันสังคม ' . $payroll->social_security_rate . '%', $ss, $payroll->social_security_rate);
                    }
                }

                // Tax (Model creates items internally for both PIT and WHT)
                // We just call it.
                if ($payroll->calc_tax) {
                    // Note: CalculateTax inside Model now relies on incomeItems having is_wht_3_percent set
                    // And it creates deduction items.
                    $payroll->CalculateTax();
                }

                // Late (Model only returns amount)
                $late_minutes = isset($data['late_minutes']) ? (int)$data['late_minutes'] : 0;
                if ($late_minutes > 0) {
                    $late_amt = $payroll->CalculateLateDeduction($late_minutes);
                    if ($late_amt > 0) {
                        $payroll->AddDeductionItem(Payroll::DEDUCTION_LATE, "หักเข้ากองทุน", $late_amt, $late_minutes);
                    }
                }

                // Absence (Job Abandonment)
                $absence_days = isset($data['absence_days']) ? (float)$data['absence_days'] : 0;
                if ($absence_days > 0) {
                    $absence_amt = $payroll->CalculateAbsenceDeduction($absence_days);
                    if ($absence_amt > 0) {
                        $payroll->AddDeductionItem(Payroll::DEDUCTION_ABSENCE, "ขาดงาน $absence_days วัน", $absence_amt, $absence_days);
                    }
                }

                // Final Totals
                $payroll->CalculateTotals();
                $count++;
            }

            $period->CalculateTotals();
            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "บันทึกข้อมูลเรียบร้อย ($count รายการ)",
                    'count' => $count
                ]);
            }

            return redirect()->back()->with('success', "บันทึกข้อมูลเรียบร้อย ($count รายการ)");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
    /**
     * Sync Attendance Data (Late & Absence) from Recordtime
     */
    public function syncAttendance($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if (!$period->CanEdit()) {
            return back()->with('error', 'ไม่สามารถแก้ไขงวดนี้ได้');
        }

        // Calculate Attendance Cycle: 26th of Prev Month - 25th of Current Month
        $ad_year = $period->year - 543;
        $month = $period->month;

        // Current Month 25th
        $attendance_end_date = sprintf('%04d-%02d-25', $ad_year, $month);

        // Previous Month 26th
        if ($month == 1) {
            $prev_month = 12;
            $prev_year = $ad_year - 1;
        } else {
            $prev_month = $month - 1;
            $prev_year = $ad_year;
        }
        $attendance_start_date = sprintf('%04d-%02d-26', $prev_year, $prev_month);

        DB::beginTransaction();
        try {
            $payrolls = $period->payrolls;
            $updated_count = 0;

            foreach ($payrolls as $payroll) {
                // Find User
                $user = $payroll->user;
                if (!$user) continue;

                // Query Recordtime using Custom Date Range
                // Late Minutes: Sum late_time
                $late_minutes = \App\Models\Recordtime::where('user_id', $user->id)
                    ->whereBetween('date', [$attendance_start_date, $attendance_end_date])
                    ->sum('late_time');

                // Absence Days: Count status 13
                $absence_days = \App\Models\Recordtime::where('user_id', $user->id)
                    ->whereBetween('date', [$attendance_start_date, $attendance_end_date])
                    ->where('status', 13) // Missing Work
                    ->count();

                $updated = false;

                // Update Late
                // Update if Late minutes exist OR if deduction exists (to potentially remove it)
                if ($late_minutes > 0 || $payroll->deductionItems()->where('deduction_type', Payroll::DEDUCTION_LATE)->exists()) {
                    // Update Item
                    if ($late_minutes > 0) {
                        // ... logic remains same ...
                        $late_amount = $payroll->CalculateLateDeduction($late_minutes);
                        if ($late_amount > 0) {
                            $payroll->AddDeductionItem(
                                Payroll::DEDUCTION_LATE,
                                'หักเข้ากองทุน',
                                $late_amount,
                                $late_minutes
                            );
                        }
                    } else {
                        // Remove if 0
                        $payroll->deductionItems()->where('deduction_type', Payroll::DEDUCTION_LATE)->delete();
                    }
                    $updated = true;
                }

                // Update Absence
                if ($absence_days > 0 || $payroll->deductionItems()->where('deduction_type', Payroll::DEDUCTION_ABSENCE)->exists()) {
                    // Update Item
                    if ($absence_days > 0) {
                        $absence_amount = $payroll->CalculateAbsenceDeduction($absence_days);
                        if ($absence_amount > 0) {
                            $payroll->AddDeductionItem(
                                Payroll::DEDUCTION_ABSENCE,
                                'ขาดงาน ' . $absence_days . ' วัน',
                                $absence_amount,
                                $absence_days
                            );
                        }
                    } else {
                        // Remove if 0
                        $payroll->deductionItems()->where('deduction_type', Payroll::DEDUCTION_ABSENCE)->delete();
                    }
                    $updated = true;
                }

                // Recalculate if changed
                if ($updated) {
                    $payroll->CalculateTotals();
                    $updated_count++;
                }
            }

            $period->CalculateTotals();
            DB::commit();

            // Format dates for message (convert to Thai Date format if needed, or just d/m/Y)
            $start_str = date('d/m/Y', strtotime($attendance_start_date));
            $end_str = date('d/m/Y', strtotime($attendance_end_date));

            return back()->with('success', "ซิงค์ข้อมูลเวลาเรียบร้อยแล้ว (รอบ {$start_str} - {$end_str}) อัปเดต {$updated_count} รายการ");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาดในการซิงค์: ' . $e->getMessage());
        }
    }
}
