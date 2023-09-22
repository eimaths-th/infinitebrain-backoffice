<?php

namespace App\Http\Controllers;

use App\Models\Receipt_School;
use App\Models\SchoolPrice;
use App\Models\Statuslog;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function check_permission()
    {
        if (Auth::check()) {
            if (Auth::user()->level == 1 || Auth::user()->level == 2) {
            } else {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $this->check_permission();
        $students = Student::with('rec_school')->get();
        // $students = Student::where('id', 1)->get();
        $school_picei_update_group = SchoolPrice::all();
        // $subject_ids = $school_picei_update_group->pluck('subject')->toArray();
        foreach ($school_picei_update_group as $upg) {
            # code...
            $group = $upg->school_sub->group;
            $upg->update(['group' => $group]);
            // dd($upg->id,$group);
        }
        foreach ($students as $student) {
            $statuses = $this->determineStatus($student);

            foreach ($statuses as $subjectGroup => $status) {

                $existingStatus = Statuslog::where('student_id', $student->id)
                    ->where('group', $subjectGroup)
                    ->latest()
                    ->get();
                // dd($existingStatus,$status,$subjectGroup,$student->id);

                if (count($existingStatus) > 0) {
                    # code...
                    // if ($existingStatus->first()->status === 'drop') {
                    //     $newEnrollmentReceipt = Receipt_School::whereRaw("FIND_IN_SET($student->id, REPLACE(pid, '|', ','))")
                    //         ->orderBy('submission_date', 'desc')
                    //         ->first();
                    //     // dd($newEnrollmentReceipt,$status);
                    //     $submissionDate = Carbon::parse($newEnrollmentReceipt->submission_date);
                    //     $currentMonth = Carbon::createFromFormat('Y-m', date('Y-m'));
                    //     if ($currentMonth->diffInMonths($submissionDate) < 1) {
                    //         $status = 'resume';
                    //     }
                    // }

                    if ($existingStatus->first()->status === 'drop') {
                        $newEnrollmentReceipt = SchoolPrice::where('student_id', $student->id)
                            ->where('qty', '>', 0)
                            ->where('group', $subjectGroup)
                            ->orderBy('id', 'desc')
                            ->first();

                        $startdate = $newEnrollmentReceipt->period;
                        $parts = explode('-', $startdate);
                        $lastTwoParts = array_slice($parts, 0, 2);
                        $start_date = implode('-', $lastTwoParts);

                        $submissionDate = Carbon::parse($start_date);
                        // $submissionDate = Carbon::parse($newEnrollmentReceipt->period);
                        $currentMonth = Carbon::createFromFormat('Y-m', date('Y-m'));
                        if ($currentMonth->diffInMonths($submissionDate) < 1) {
                            $status = 'resume';
                        }
                    }
                    // dd($newEnrollmentReceipt, $status, $start_date);
                }

                if (count($existingStatus) < 1) {
                    Statuslog::create([
                        'student_id' => $student->id,
                        'nick_name' => $student->nick_name,
                        'group' => $subjectGroup,
                        'status' => $status,
                        'status_date' => now(),
                    ]);
                } elseif ($existingStatus->first()->status === $status && $existingStatus->first()->group === $subjectGroup) {
                } else {
                    Statuslog::create([
                        'student_id' => $student->id,
                        'nick_name' => $student->nick_name,
                        'group' => $subjectGroup,
                        'status' => $status,
                        'status_date' => now(),
                    ]);
                }
                $lastStatusLog = $student->status_log->last();
                if ($lastStatusLog) {
                    $student->update(['status' => $lastStatusLog->status]);
                }
            }
        }


        $month  = date('m');
        $year   = date('Y');
        $latestStatusCounts = Statuslog::select('group', 'student_id', 'status')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('status_log')
                    ->groupBy('group', 'student_id');
            })
            ->whereYear('status_date', $year)
            ->whereMonth('status_date', $month)
            ->orderBy('group')

            ->get();

        // $latestStatusByStudent = Statuslog::select('status_log.student_id', 'status_log.group', 'status_log.status', 'status_log.status_date', 'status_log.nick_name')
        //     ->whereIn('status_log.id', function ($query) {
        //         $query->select(DB::raw('MAX(id)'))
        //             ->from('status_log')
        //             ->groupBy('student_id', 'group'); // Group by student_id and group
        //     })
        //     ->whereYear('status_log.status_date', $year)
        //     ->whereMonth('status_log.status_date', $month)
        //     ->join('students', 'status_log.student_id', '=', 'students.id') // Assuming 'students' is the name of your students table
        //     ->orderBy('students.code')
        //     ->get();


        // return view('homebk2');
        return view('admin.dashboard', [

            'latestStatusCounts' => $latestStatusCounts,
            // 'latestStatusByStudent' => $latestStatusByStudent,
            // 'select_status' => $select_status,

        ]);
    }

    private function determineStatus($student)
    {
        $stdId = $student->id;
        // $stdId = 15;
        $receipt = Receipt_School::whereRaw("FIND_IN_SET($stdId, REPLACE(pid, '|', ','))")->get();

        $ssss = SchoolPrice::where('student_id', $stdId)
            ->where('qty', '>', 0)
            ->orderBy('rid')
            ->get();
        $group_scp = $ssss->groupBy('group');
        $status = [];
        if (!$group_scp->isEmpty()) {
            foreach ($group_scp as $group => $school_pice) {
                $startdate = null;

                if (!$school_pice->isEmpty()) {
                    $startdate = $school_pice->first()->period;
                }

                $parts = explode('-', $startdate);
                $lastTwoParts = array_slice($parts, 0, 2);
                $start_date = implode('-', $lastTwoParts);
                $currentReceiptStartDate = Carbon::parse($start_date);

                $current_Month = date('Y-m');
                // $current_Month = Carbon::createFromFormat('Y-m', '2023-09');
                $currentMonth = Carbon::parse($current_Month);

                $count_receipt = count($receipt);
                $count_qty = count($school_pice);

                if ($count_qty < 1) {
                    $status[$group] = 'None Receipt';
                } elseif ($count_qty == 1) {
                    if ($currentMonth->diffInMonths($currentReceiptStartDate) <= 0) {
                        $status[$group] = 'New Student';
                    } else {

                        if (count($school_pice) > 1) {
                            $startcourse = $school_pice->first()->period;
                            $period = $school_pice->last()->period;
                        } else {

                            $startcourse = $school_pice->first()->period;
                            $period = $school_pice->first()->period;
                        }
                        $parts = explode('-', $period);
                        $lastTwoParts = array_slice($parts, -2);
                        $end_date = implode('-', $lastTwoParts);
                        $currentReceiptEndDate = Carbon::parse($end_date);
                        if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
                            //พ้นสภาพนักเรียน
                            // $status = 'พ้นสภาพนักเรียน';
                            $status[$group] = 'drop';
                        } else {
                            if ($currentMonth->greaterThan($currentReceiptEndDate)) {
                                //กำลังยุติการการเรียน
                                $status[$group] = 'Termination of study';
                            } else {
                                // if ($student->status == 'resume') {
                                //     # code...
                                //     //กลับมาเรียน
                                // dd($stdId);
                                //     $status[$group] = 'resume';
                                // } else {
                                //     # code...
                                //     //กำลังเรียนอยู่
                                $status[$group] = 'studying . . .';
                                // }
                            }
                        }
                        // $statuses[$group] = 'old Student';
                    }
                } elseif ($count_qty > 1) {

                    if (count($school_pice) > 1) {
                        $startcourse = $school_pice->first()->period;
                        $period = $school_pice->last()->period;
                    } else {

                        $startcourse = $school_pice->first()->period;
                        $period = $school_pice->last()->period;
                    }

                    $parts = explode('-', $period);
                    $lastTwoParts = array_slice($parts, -2);
                    $end_date = implode('-', $lastTwoParts);

                    $currentReceiptEndDate = Carbon::parse($end_date);

                    if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
                        //พ้นสภาพนักเรียน
                        // $status = 'พ้นสภาพนักเรียน';
                        $status[$group] = 'drop';
                    } else {
                        if ($currentMonth->greaterThan($currentReceiptEndDate)) {
                            //กำลังยุติการการเรียน
                            // dd($end_date);
                            $status[$group] = 'Termination of study';
                        } else {
                            //กำลังเรียนอยู่
                            $resume_month = date('m');
                            // $resume_month = '10';
                            // $resume_month = Carbon::createFromFormat('Y-m', '2023-10');
                            $resume_year = date('Y');
                            $check_resume = Statuslog::where('student_id', $stdId)
                                ->where('group', $group)
                                ->whereMonth('status_date', $resume_month)
                                ->whereYear('status_date', $resume_year)
                                ->latest()
                                ->get();
                            // dd($check_resume);
                            if (count($check_resume) > 0 && $check_resume->first()->status == 'resume') {

                                $status[$group] = 'resume';
                            } else {
                                $status[$group] = 'studying . . .';
                            }
                        }
                    }
                    // dd( $end_date,$school_pice,$student);
                }
            }
        } else {
            $status['N'] = 'None Receipt';
        }

        return $status;
    }
}
