<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Subject;
use App\Models\Student;
use App\Models\History;
use App\Models\SchoolPrice;
use App\Models\Receipt_School;
use Carbon\Carbon;

class APIController extends Controller
{

    public function searchStudent(Request $request)
    {
        //$query = $request->query('query');

        $results = Student::orderBy("code", "asc")
            ->get();

        return response()->json($results);
    }
    //
    public function searchSubject(Request $request)
    {
        //$query = $request->query('query');

        /* $results = Subject::orderBy("id", "asc")
            ->get(); */

        $studentId = $request->get('std');

        if ($studentId == null) {
            # code...
            $results = [];
        } else {

            $std = Student::find($studentId);
            // $rc = Receipt_School::where('pid', $std->id)->get();
            // $rcIds = $rc->pluck('id'); // Retrieve all 'id' values from the collection
            $sch_p = SchoolPrice::where('student_id', $std->id)->get();
            $sch_p_sub = [];

            foreach ($sch_p as $sch_p1) {
                # code...
                if ($sch_p1->qty !== null) {
                    $sch_p_sub[] = $sch_p1->subject;
                }
            }

            $results = Subject::orderBy("id", "asc")
                ->whereIn('id', $sch_p_sub)
                ->get();
        }

        return response()->json($results);
    }

    public function stdsub($id)
    {
        $studentId = $id;

        if ($studentId == null) {
            # code...
        } else {

            $std = Student::find($studentId);
            $rc = Receipt_School::where('pid', $std->id)->get();
            $rcIds = $rc->pluck('id'); // Retrieve all 'id' values from the collection
            $sch_p = SchoolPrice::whereIn('rid', $rcIds)->get();
            $sch_p_sub = [];

            foreach ($sch_p as $sch_p1) {
                # code...
                if ($sch_p1->qty !== null) {
                    $sch_p_sub[] = $sch_p1->subject;
                }
            }

            $subject = Subject::orderBy("id", "asc")
                ->whereIn('id', $sch_p_sub)
                ->get();


            $html = '<option value="">Select Subject</option>';
            foreach ($subject as $subject1) {
                # code...
                $html .= '<option value="' . $subject1->id . '"> ' . $subject1->title . ' </option>';
            }
            return response()->json([
                'studentsub' => $html
            ]);
        }
    }

    /* public function saveHistories(Request $request)
    {

        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $imageName = $image->getClientOriginalName();

            $newname = Str::random(40) . "_" . $imageName;
            $image->move(public_path('form_images'), $newname);

            //endtime
            $time =  $request->get('stime'); // Time string
            $durationHours = $request->get('hour'); // Duration in hours to add

            $updatedTime = Carbon::createFromFormat('H:i:s', $time)
                ->addHours($durationHours)
                ->format('H:i:s');

            $course = new History([
                'student_id' => $request->get('student'),
                'subject_id' => $request->get('subject'),
                //'level_id' => $request->get('level'),
                'datetime' => $request->get('date'),
                'stime' => $request->get('stime'),
                'etime' => $updatedTime,
                //'description' => $request->get('des'),
                'signature' => $newname,
            ]);
            $course->save();

            return response()->json([
                'message' => 'Data Save Successfully',
                'file' => $newname
            ]);
        }

        return response()->json(['message' => 'No file found'], 400);
    } */

    public function saveHistories(Request $request)
    {
        $today = $request->get('date'); // Y-m-d format

        // Your date range in the format "2024-01 - 2024-01"
        $range = SchoolPrice::where('student_id', $request->get('student'))
            ->orderBy("id", "desc")
            ->first();

        //dd($range);

        // Check if $range is not null and $range->period is not an empty string
        $schoolp = '';
        if ($range && $range->period !== '') {
            $dateRange = $range->period;

            $rem = str_replace(" ", "", $dateRange);

            //dd($rem);

            // Split the date range into start and end months
            //list($startMonth, $endMonth) = explode('-', str_replace(" ", "", $dateRange));

            // Convert start and end months to Carbon instances
            $startCarbon = Carbon::parse(substr($rem, 0, 7))->startOfMonth();
            $endCarbon = Carbon::parse(substr($rem, 8, 7))->endOfMonth();

            // Convert the current date to a Carbon instance
            $todayCarbon = Carbon::parse($today);

            // Check if the present date is within the date range
            $isWithinRange = $todayCarbon->between($startCarbon, $endCarbon);

            if ($isWithinRange) {
                $schoolp = $range->id;
                $status = 'reciept';
            } else {
                // Present date is outside the specified range
                //return response()->json(['message' => 'Date is outside of school price period.'], 400);
                $status = 'non-reciept';
            }
        } else {
            /// $range is null or $range->period is an empty string
            //return response()->json(['message' => 'School price range not found.'], 400);
            $status = 'non-reciept';
        }

        if ($request->hasFile('file')) {
            // Your logic for handling file upload
            $image = $request->file('file');
            $imageName = $image->getClientOriginalName();

            $newname = Str::random(40) . "_" . $imageName;
            $image->move(public_path('form_images'), $newname);

            //endtime
            $time =  $request->get('stime'); // Time string
            $durationHours = $request->get('hour'); // Duration in hours to add

            $updatedTime = Carbon::createFromFormat('H:i:s', $time)
                ->addHours($durationHours)
                ->format('H:i:s');

            if ($schoolp !== '') {
                $course = new History([
                    'student_id' => $request->get('student'),
                    'subject_id' => $request->get('subject'),
                    'school_price_id' => $schoolp,
                    'status' => $status,
                    'datetime' => $request->get('date'),
                    'stime' => $request->get('stime'),
                    'etime' => $updatedTime,
                    'signature' => $newname,
                ]);
            } else {
                $course = new History([
                    'student_id' => $request->get('student'),
                    'subject_id' => $request->get('subject'),
                    'status' => $status,
                    'datetime' => $request->get('date'),
                    'stime' => $request->get('stime'),
                    'etime' => $updatedTime,
                    'signature' => $newname,
                ]);
            }


            $course->save();

            return response()->json([
                'message' => 'Data Save Successfully',
                'file' => $newname
            ], 200);
        }

        return response()->json(['message' => 'No file found'], 400);
    }

    /**
     * ดึงข้อมูลใบลาที่รอการอนุมัติ
     */
    public function getPendingLeaveRequests(Request $request)
    {
        $pending_requests = \App\Models\LeaveRequest::where('status', 'pending')
            ->with(['user:id,name,code,position'])
            ->orderBy('submitted_date', 'desc')
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'user_id' => $leave->user_id,
                    'user_name' => $leave->user->name ?? 'ไม่ระบุ',
                    'user_code' => $leave->user->code ?? '',
                    'user_position' => $leave->user->position ?? '',
                    'leave_type' => $leave->leave_type,
                    'leave_type_name' => \App\Models\LeaveRequest::LEAVE_TYPES[$leave->leave_type] ?? $leave->leave_type,
                    'submitted_date' => $leave->submitted_date ? $leave->submitted_date->format('Y-m-d') : null,
                    'start_date' => $leave->start_date ? $leave->start_date->format('Y-m-d') : null,
                    'end_date' => $leave->end_date ? $leave->end_date->format('Y-m-d') : null,
                    'total_days' => $leave->total_days,
                    'half_day' => $leave->half_day,
                    'half_day_name' => \App\Models\LeaveRequest::HALF_DAY_OPTIONS[$leave->half_day] ?? 'เต็มวัน',
                    'reason' => $leave->reason,
                    'location' => $leave->location,
                    'contact_phone' => $leave->contact_phone,
                    'status' => $leave->status,
                    'status_name' => \App\Models\LeaveRequest::STATUSES[$leave->status] ?? $leave->status,
                    'created_at' => $leave->created_at ? $leave->created_at->format('Y-m-d H:i:s') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $pending_requests->count(),
            'data' => $pending_requests,
        ]);
    }

    /**
     * เช็ควันหยุดบริษัท และวันพุธ (owner หยุด)
     * ใช้สำหรับ n8n เพื่อไม่ให้ส่งข้อความรบกวนวันหยุด
     */
    public function checkHoliday(Request $request)
    {
        $check_date = $request->get('date') 
            ? Carbon::parse($request->get('date')) 
            : Carbon::today();
        
        $is_holiday = false;
        $holiday_reason = null;
        $holiday_name = null;

        // 1. เช็คว่าเป็นวันพุธหรือไม่ (Owner หยุดทุกวันพุธ)
        if ($check_date->isWednesday()) {
            $is_holiday = true;
            $holiday_reason = 'owner_day_off';
            $holiday_name = 'วันหยุดประจำสัปดาห์ (วันพุธ)';
        }

        // 2. เช็คว่าเป็นวันหยุดบริษัทหรือไม่
        $company_holiday = \App\Models\CompanyHoliday::where('date', $check_date->format('Y-m-d'))->first();
        if ($company_holiday) {
            $is_holiday = true;
            $holiday_reason = 'company_holiday';
            $holiday_name = $company_holiday->name;
        }

        // 3. เช็คว่าเป็นวันเสาร์-อาทิตย์หรือไม่
        if ($check_date->isWeekend()) {
            $is_holiday = true;
            $holiday_reason = 'weekend';
            $holiday_name = $check_date->isSaturday() ? 'วันเสาร์' : 'วันอาทิตย์';
        }

        return response()->json([
            'success' => true,
            'date' => $check_date->format('Y-m-d'),
            'day_name' => $check_date->locale('th')->dayName,
            'is_holiday' => $is_holiday,
            'holiday_reason' => $holiday_reason,
            'holiday_name' => $holiday_name,
            'should_send_notification' => !$is_holiday, // true = ส่งได้, false = ไม่ควรส่ง
        ]);
    }
}
