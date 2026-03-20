<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolPrice;
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

        $histories = $query->with(['student', 'subject'])->get();

        // คำนวณรายได้ครูแต่ละคน
        $teacher_revenue = [];
        $unmatched_records = []; // รายการที่ไม่พบใบเสร็จ

        foreach ($histories as $history) {
            $teacher_id = $history->user_id;

            // คำนวณชั่วโมงสอนจาก stime → etime
            $teaching_hours = $this->CalculateHours($history->stime, $history->etime);
            if ($teaching_hours <= 0) continue;

            // หาราคาคอร์สจาก school_price
            $price_per_course = $this->FindCoursePrice($history);

            if ($price_per_course === null) {
                // ไม่พบใบเสร็จ → เก็บเป็นรายการ unmatched
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
                ];
                continue;
            }

            // คำนวณรายได้: (ราคาคอร์ส / 8) × ชั่วโมงสอน
            $revenue_per_hour = $price_per_course / self::HOURS_PER_COURSE;
            $revenue = $revenue_per_hour * $teaching_hours;

            // สะสมรายได้ตามครู
            if (!isset($teacher_revenue[$teacher_id])) {
                $teacher_revenue[$teacher_id] = [
                    'teacher_id' => $teacher_id,
                    'teacher_name' => '',
                    'total_hours' => 0,
                    'total_revenue' => 0,
                    'session_count' => 0,
                    'monthly' => [],
                ];
            }

            $teacher_revenue[$teacher_id]['total_hours'] += $teaching_hours;
            $teacher_revenue[$teacher_id]['total_revenue'] += $revenue;
            $teacher_revenue[$teacher_id]['session_count']++;

            // สะสมรายเดือน
            $history_month = Carbon::parse($history->datetime)->format('Y-m');
            if (!isset($teacher_revenue[$teacher_id]['monthly'][$history_month])) {
                $teacher_revenue[$teacher_id]['monthly'][$history_month] = [
                    'hours' => 0,
                    'revenue' => 0,
                    'sessions' => 0,
                ];
            }
            $teacher_revenue[$teacher_id]['monthly'][$history_month]['hours'] += $teaching_hours;
            $teacher_revenue[$teacher_id]['monthly'][$history_month]['revenue'] += $revenue;
            $teacher_revenue[$teacher_id]['monthly'][$history_month]['sessions']++;
        }

        // ดึงชื่อครูทั้งหมด
        $teacher_ids = array_keys($teacher_revenue);
        $all_teacher_ids = array_merge($teacher_ids, array_column($unmatched_records, 'teacher_id'));
        $teachers = User::whereIn('id', array_unique($all_teacher_ids))->pluck('name', 'id');

        foreach ($teacher_revenue as &$data) {
            $data['teacher_name'] = $teachers[$data['teacher_id']] ?? 'ครู ID: ' . $data['teacher_id'];
            // เรียงลำดับรายเดือนตาม key
            ksort($data['monthly']);
        }
        unset($data);

        // เรียงตามรายได้มากไปน้อย
        usort($teacher_revenue, function ($a, $b) {
            return $b['total_revenue'] <=> $a['total_revenue'];
        });

        // เพิ่มชื่อครูใน unmatched_records
        foreach ($unmatched_records as &$record) {
            $record->teacher_name = $teachers[$record->teacher_id] ?? 'ครู ID: ' . $record->teacher_id;
        }
        unset($record);

        return compact(
            'teacher_revenue',
            'unmatched_records',
            'year',
            'month',
            'teachers'
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
     * ค้นหาราคาคอร์สจาก school_price
     * 1. ถ้ามี school_price_id → ใช้ตรง
     * 2. ถ้าไม่มี → ค้นหาตาม student_id + subject_id และกรองเวลาตาม period ใบเสร็จ
     * 3. ถ้ายังไม่เจอ → return null
     */
    private function FindCoursePrice(History $history)
    {
        // กรณี 1: มี school_price_id → ใช้ตรง
        if ($history->school_price_id) {
            $school_price = SchoolPrice::find($history->school_price_id);
            if ($school_price && $school_price->total > 0) {
                return (float) $school_price->total;
            }
        }

        // กรณี 2: ไม่มี school_price_id → ค้นหาตาม student_id + subject_id
        // ต้องตรวจสอบข้อมูล period เพื่อความแม่นยำ (เพราะอาจจ่ายเงินก่อนเดือนเรียน)
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
                // Logic แยก period แบบเดียวกับ HistoryController::store()
                $period = explode(' - ', $last_period);

                if (count($period) == 1) {
                    $periods = explode('-', $last_period);
                    $startMonth = Carbon::parse("$periods[0]-$periods[1]")->format('Y-m');
                    $endMonth = count($periods) > 2 ? Carbon::parse("$periods[2]-$periods[3]")->format('Y-m') : $startMonth;
                } else {
                    $startMonth = Carbon::parse($period[0])->format('Y-m');
                    $endMonth = Carbon::parse($period[1])->format('Y-m');
                }

                // บวกเพิ่ม 1 เดือนตาม logic ใบเสร็จ (ครอบคลุมถึงสิ้นเดือนของ endMonth)
                $endMonth = Carbon::parse($endMonth)->addMonth()->format('Y-m');

                $startMonth_parsed = Carbon::parse($startMonth)->startOfMonth();
                $endMonth_parsed = Carbon::parse($endMonth)->endOfMonth();

                if ($req_date->between($startMonth_parsed, $endMonth_parsed)) {
                    return (float) $sp->total;
                }
            } catch (\Exception $e) {
                // ข้ามไปเช็คใบเสร็จก่อนหน้าหาก parse period ไม่ผ่าน
                continue;
            }
        }

        // กรณี 3: ไม่เจอเลย → return null
        return null;
    }
}
