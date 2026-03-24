<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolPrice;
use App\Models\PayrollIncomeItem;
use App\Models\PayrollPeriod;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Dashboard สรุปรายได้ครูจากการสอน
 * คำนวณ: ชั่วโมงสอน × (ราคาคอร์ส / 8)
 */
class TeacherRevenueController extends Controller
{
    /** จำนวนชั่วโมงต่อคอร์สมาตรฐาน */
    const HOURS_PER_COURSE = 8;
    private $payroll_cache = [];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * ตรวจสอบ permission ก่อนเข้าใช้งาน
     * super-admin/admin ผ่านเสมอ, role อื่นๆ ต้องมี permission 'teacher-revenue.view'
     */
    private function CheckPermission()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('teacher-revenue.view') && !$user->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }
    }

    /**
     * หน้า Dashboard สรุปรายได้ครู
     */
    public function index(Request $request)
    {
        // ตรวจสอบสิทธิ์การเข้าถึง
        $this->CheckPermission();
        $data = $this->getRevenueData($request);

        return view('teacher-revenue.index', $data);
    }

    /**
     * สำหรับพิมพ์รายงานหน้า A4 (Portrait)
     */
    public function print(Request $request)
    {
        $this->CheckPermission();
        $data = $this->getRevenueData($request);

        return view('teacher-revenue.print', $data);
    }

    /**
     * ดึงข้อมูลและคำนวณรายได้ของครู
     */
    private function getRevenueData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', null);

        // ดึง histories เฉพาะที่มี user_id (ครู) เท่านั้น
        $query = History::whereNotNull('user_id')
            ->whereYear('datetime', $year);

        // กรองตามเดือน (ถ้าเลือก)
        if ($month) {
            $query->whereMonth('datetime', $month);
        }

        $histories = $query->with(['student', 'subject', 'user'])->get();

        // คำนวณรายได้ครูแต่ละคน
        $teacher_revenue = [];
        $unmatched_records = []; // รายการที่ไม่พบใบเสร็จ
        
        $total_all_hours = 0;
        $total_all_revenue = 0;
        $total_all_cost = 0;
        $total_all_wage = 0;
        $total_all_sessions = 0;

        // ขั้นตอนที่ 1: รวบรวมข้อมูลพื้นฐานและชั่วโมงสอนรวมต่อเดือน
        $temp_data = [];
        $monthly_summary = []; // [teacher_id][month_key] => total_hours
        
        foreach ($histories as $history) {
            $teacher_id = $history->user_id;
            if (!$teacher_id) continue;

            $teaching_hours = $this->CalculateHours($history->stime, $history->etime);
            if ($teaching_hours <= 0) continue;

            $price_data = $this->FindCoursePrice($history);
            $history_month = Carbon::parse($history->datetime)->format('Y-m');

            if ($price_data === null) {
                $temp_data[] = [
                    'teacher_id' => $teacher_id,
                    'is_valid' => false,
                    'history' => $history,
                    'hours' => $teaching_hours,
                    'month_key' => $history_month
                ];
            } else {
                $temp_data[] = [
                    'teacher_id' => $teacher_id,
                    'is_valid' => true,
                    'history' => $history,
                    'hours' => $teaching_hours,
                    'price_data' => $price_data,
                    'month_key' => $history_month
                ];
            }
            
            // สะสมชั่วโมงสอนรวมต่อเดือนของครูแต่ละคนเพื่อใช้หารเฉลี่ยค่าจ้าง
            if (!isset($monthly_summary[$teacher_id][$history_month])) {
                $monthly_summary[$teacher_id][$history_month] = 0;
            }
            $monthly_summary[$teacher_id][$history_month] += $teaching_hours;
        }

        // ขั้นตอนที่ 2: ดึงรายได้จาก Payroll และคำนวณสะสม
        foreach ($temp_data as $data) {
            $teacher_id = $data['teacher_id'];
            $history = $data['history'];
            $teaching_hours = $data['hours'];
            $month_key = $data['month_key'];

            if (!isset($teacher_revenue[$teacher_id])) {
                $teacher_revenue[$teacher_id] = [
                    'teacher_id' => $teacher_id,
                    'teacher_name' => $history->user->name ?? 'User ID: ' . $teacher_id,
                    'total_hours' => 0,
                    'total_revenue' => 0,
                    'total_cost' => 0,
                    'total_wage' => 0,
                    'session_count' => 0,
                    'monthly' => [],
                ];
            }

            // คำนวณค่าจ้าง (เฉลี่ยจาก Payroll รวมทุกรายการของครูในเดือนนั้น)
            $total_paid_in_month = $this->GetMonthlyPayrollTotal($teacher_id, $history->datetime);
            $total_hours_in_month = $monthly_summary[$teacher_id][$month_key] ?? 0;
            
            // ปันส่วนค่าจ้างตามชั่วโมงสอน: (ยอดรับจริงทั้งหมด / ชั่วโมงสอนรวมทั้งหมด) * ชั่วโมงสอนของ Session นี้
            $wage = 0;
            if ($total_hours_in_month > 0) {
                $wage = ($total_paid_in_month / $total_hours_in_month) * $teaching_hours;
            }

            // สะสมยอดรวมครู (ชั่วโมงและค่าจ้าง คิดจากทุก Session ที่มีการบันทึกการสอน)
            $teacher_revenue[$teacher_id]['total_hours'] += $teaching_hours;
            $teacher_revenue[$teacher_id]['total_wage'] += $wage;
            $teacher_revenue[$teacher_id]['session_count']++;

            // สะสมยอดรายเดือน (ส่วนของค่าจ้างและชื่อเดือน)
            if (!isset($teacher_revenue[$teacher_id]['monthly'][$month_key])) {
                $teacher_revenue[$teacher_id]['monthly'][$month_key] = [
                    'hours' => 0,
                    'revenue' => 0,
                    'cost' => 0,
                    'wage' => 0,
                    'sessions' => 0,
                ];
            }
            $teacher_revenue[$teacher_id]['monthly'][$month_key]['hours'] += $teaching_hours;
            $teacher_revenue[$teacher_id]['monthly'][$month_key]['wage'] += $wage;
            $teacher_revenue[$teacher_id]['monthly'][$month_key]['sessions']++;

            if (!$data['is_valid']) {
                // จัดการรายการที่ไม่มีใบเสร็จ (Unmatched)
                $unmatched_records[] = (object) [
                    'history_id' => $history->id,
                    'student_name' => $history->student
                        ? ($history->student->first_name . ' ' . $history->student->last_name)
                        : 'ID: ' . $history->student_id,
                    'student_nickname' => $history->student->nick_name ?? '',
                    'subject_name' => $history->subject->title ?? 'ID: ' . $history->subject_id,
                    'datetime' => $history->datetime,
                    'stime' => $history->stime,
                    'etime' => $history->etime,
                    'teaching_hours' => $teaching_hours,
                    'teacher_id' => $teacher_id,
                    'teacher_name' => $history->user->name ?? 'ครู ID: ' . $teacher_id,
                ];
                
                // สะสมยอดรวมทั้งสถาบัน (เฉพาะส่วนที่เป็นต้นทุนครู)
                $total_all_hours += $teaching_hours;
                $total_all_wage += $wage;
                $total_all_sessions++;
                continue;
            }

            $price_data = $data['price_data'];

            // คำนวณรายได้และต้นทุนวัสดุ
            $revenue_per_hour = $price_data['net_tuition'] / self::HOURS_PER_COURSE;
            $revenue = $revenue_per_hour * $teaching_hours;
            $cost_share = ($price_data['cost'] / self::HOURS_PER_COURSE) * $teaching_hours;

            // สะสมยอดรวมครู (เฉพาะส่วนรายได้และต้นทุนวัสดุ)
            $teacher_revenue[$teacher_id]['total_revenue'] += $revenue;
            $teacher_revenue[$teacher_id]['total_cost'] += $cost_share;

            // สะสมยอดรายเดือน (เฉพาะส่วนรายได้และต้นทุนวัสดุ)
            $teacher_revenue[$teacher_id]['monthly'][$month_key]['revenue'] += $revenue;
            $teacher_revenue[$teacher_id]['monthly'][$month_key]['cost'] += $cost_share;

            // สะสมยอดรวมทั้งสถาบัน
            $total_all_hours += $teaching_hours;
            $total_all_revenue += $revenue;
            $total_all_cost += $cost_share;
            $total_all_wage += $wage;
            $total_all_sessions++;
        }

        $total_all_profit = $total_all_revenue - $total_all_wage;

        // เรียงลำดับรายเดือน
        foreach ($teacher_revenue as &$data) {
            ksort($data['monthly']);
        }
        unset($data);

        return compact(
            'teacher_revenue',
            'unmatched_records',
            'year',
            'month',
            'total_all_hours',
            'total_all_revenue',
            'total_all_cost',
            'total_all_wage',
            'total_all_profit',
            'total_all_sessions'
        );
    }

    /**
     * คำนวณชั่วโมงสอนจาก stime → etime
     */
    private function CalculateHours($stime, $etime)
    {
        try {
            if (empty($stime) || empty($etime)) return 0;

            $start = Carbon::parse($stime);
            $end = Carbon::parse($etime);
            $diff_minutes = $start->diffInMinutes($end);

            return round($diff_minutes / 60, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * ค้นหายอดเงินค่าจ้างครูรวมทุกรายการทั้งเดือน จากระบบ Payroll
     */
    private function GetMonthlyPayrollTotal($teacher_id, $datetime)
    {
        $date = Carbon::parse($datetime);
        $year = $date->year;
        $month = $date->month;
        $be_year = $year + 543;
        $cache_key = "{$teacher_id}-{$year}-{$month}-total";

        if (isset($this->payroll_cache[$cache_key])) {
            return $this->payroll_cache[$cache_key];
        }

        $periods = PayrollPeriod::where('year', $be_year)
            ->where('month', $month)
            ->pluck('id');

        if ($periods->isEmpty()) {
            return $this->payroll_cache[$cache_key] = 0;
        }

        $payroll_records = Payroll::whereIn('payroll_period_id', $periods)
            ->where('user_id', $teacher_id)
            ->get();

        if ($payroll_records->isEmpty()) {
            return $this->payroll_cache[$cache_key] = 0;
        }

        // ใช้ยอด net_pay รวมทุกใบของเดือนนั้น เพื่อให้ตรงกับยอดที่ผู้ใช้เห็นจริงในใบ Payroll
        $total_net_pay = $payroll_records->sum('net_pay');

        return $this->payroll_cache[$cache_key] = (float)$total_net_pay;
    }

    private function FindCoursePrice(History $history)
    {
        // กรณี 1: มี school_price_id → ใช้ตรง
        if ($history->school_price_id) {
            $sp = SchoolPrice::find($history->school_price_id);
            if ($sp && $sp->total > 0) {
                $cost = (float)($sp->bookprice ?? 0) + (float)($sp->media_price ?? 0) + (float)($sp->entrance_fee ?? 0);
                return [
                    'total' => (float)$sp->total,
                    'cost' => $cost,
                    'net_tuition' => (float)$sp->total - $cost
                ];
            }
        }

        // กรณี 2: ไม่มี school_price_id → ค้นหาตาม student_id + subject_id
        $matched_prices = SchoolPrice::where('student_id', $history->student_id)
            ->where('subject', $history->subject_id)
            ->where('course_type', 1)
            ->where('total', '>', 0)
            ->orderBy('id', 'desc')
            ->get();

        $req_date = Carbon::parse($history->datetime);

        foreach ($matched_prices as $sp) {
            $last_period = $sp->period;
            if (empty($last_period)) continue;

            try {
                $period = explode(' - ', $last_period);
                if (count($period) == 1) {
                    $periods = explode('-', $last_period);
                    $startMonth = Carbon::parse("$periods[0]-$periods[1]")->format('Y-m');
                    $endMonth = count($periods) > 2 ? Carbon::parse("$periods[2]-$periods[3]")->format('Y-m') : $startMonth;
                } else {
                    $startMonth = Carbon::parse($period[0])->format('Y-m');
                    $endMonth = Carbon::parse($period[1])->format('Y-m');
                }

                $endMonth = Carbon::parse($endMonth)->addMonth()->format('Y-m');
                $startMonth_parsed = Carbon::parse($startMonth)->startOfMonth();
                $endMonth_parsed = Carbon::parse($endMonth)->endOfMonth();

                if ($req_date->between($startMonth_parsed, $endMonth_parsed)) {
                    $cost = (float)($sp->bookprice ?? 0) + (float)($sp->media_price ?? 0) + (float)($sp->entrance_fee ?? 0);
                    return [
                        'total' => (float)$sp->total,
                        'cost' => $cost,
                        'net_tuition' => (float)$sp->total - $cost
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }
}
