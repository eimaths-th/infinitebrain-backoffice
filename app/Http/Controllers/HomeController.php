<?php

namespace App\Http\Controllers;

use App\Models\Receipt_School;
use App\Models\Recordtime;
use App\Models\SchoolPrice;
use App\Models\Statuslog;
use App\Models\Statuswork;
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
            if (Auth::user()->level == 1 || Auth::user()->level == 2 || Auth::user()->level == 0) {
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

                    if ($existingStatus->first()->status === 'drop' || $existingStatus->first()->status === 'waitting . . .') {
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
                $currentMonthYear = date('Y-m');

                $status_date = $existingStatus->first()->status_date ?? '';
                $status_date_year_month = date('Y-m', strtotime($status_date));

                $select_status_date_year_month = date('Y-m', strtotime($currentMonthYear));
                // dd($status_date_year_month, $select_status_date_year_month);

                if (count($existingStatus) < 1) {
                    Statuslog::create([
                        'student_id' => $student->id,
                        'nick_name' => $student->nick_name,
                        'group' => $subjectGroup,
                        'status' => $status,
                        'status_date' => now(),
                    ]);
                } elseif ($existingStatus->first()->status === $status && $existingStatus->first()->group === $subjectGroup && $status_date_year_month == $select_status_date_year_month) {
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
        // $stdId = 161;
        $receipt = Receipt_School::whereRaw("FIND_IN_SET($stdId, REPLACE(pid, '|', ','))")->get();

        $ssss = SchoolPrice::where('student_id', $stdId)
            ->where('qty', '>', 0)
            ->orderBy('rid')
            ->get();
        $group_scp = $ssss->groupBy('group');
        // dd($group_scp);

        $status = [];
        if (!$group_scp->isEmpty()) {
            foreach ($group_scp as $group => $school_pice) {
                $startdate = null;
                // $submission_date = $school_pice->rec_school->submission_date;
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

                // $count_receipt = count($receipt);
                $count_qty = count($school_pice);

                if ($count_qty < 1) {
                    $status[$group] = 'None Receipt';
                } elseif ($count_qty == 1) {
                    if ($currentMonth->diffInMonths($currentReceiptStartDate) <= 0) {
                        $status[$group] = 'New Student';
                    } else {

                        if (count($school_pice) > 1) {
                            $submission_date = $school_pice->last()->rec_school->submission_date;
                            $period = $school_pice->last()->period;
                        } else {
                            $submission_date = $school_pice->last()->rec_school->submission_date;
                            $period = $school_pice->first()->period;
                        }


                        $parts = explode('-', $period);
                        $parts_submission_date = explode('-', $submission_date);

                        $lastTwoParts = array_slice($parts, -2);
                        $firstTwoParts = array_slice($parts, 0, 2);
                        $first_submission_date = array_slice($parts_submission_date, 0, 2);



                        $start_date = implode('-', $firstTwoParts);
                        $end_date = implode('-', $lastTwoParts);
                        $submissionDate = implode('-', $first_submission_date);

                        $currentReceiptStatDate = Carbon::parse($start_date);
                        $currentReceiptEndDate = Carbon::parse($end_date);
                        $currentReceiptsubmission_date = Carbon::parse($submissionDate);

                        if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
                            //พ้นสภาพนักเรียน
                            // $status = 'พ้นสภาพนักเรียน';
                            $status[$group] = 'drop';
                        } else {
                            if ($currentMonth->greaterThan($currentReceiptEndDate)) {
                                //กำลังยุติการการเรียน
                                $status[$group] = 'Termination of study';
                            } else {

                                if ($currentMonth->diffInMonths($currentReceiptsubmission_date) == 0 && $currentReceiptsubmission_date->lessThan($currentReceiptStatDate)) {
                                    # code...
                                    //แก้บัค 22/09/2023
                                    $status[$group] = 'waitting . . .';
                                } else {
                                    $status[$group] = 'studying . . .';
                                }
                            }
                        }
                        // $statuses[$group] = 'old Student';
                    }
                } elseif ($count_qty > 1) {

                    if (count($school_pice) > 1) {
                        // $test1 = $school_pice->last()->rec_school->id;
                        // $test2 = $school_pice->last()->rid;
                        // $test3 = $school_pice->last()->rec_school->submission_date;
                        $submission_date = $school_pice->last()->rec_school->submission_date;
                        $period = $school_pice->last()->period;
                    } else {
                        // $test1 = $school_pice->last()->rec_school->id;
                        // $test2 = $school_pice->last()->rid;
                        // $test3 = $school_pice->last()->rec_school->submission_date;
                        $submission_date = $school_pice->last()->rec_school->submission_date;
                        $period = $school_pice->last()->period;
                    }

                    $parts = explode('-', $period);
                    $parts_submission_date = explode('-', $submission_date);

                    $lastTwoParts = array_slice($parts, -2);
                    $firstTwoParts = array_slice($parts, 0, 2);
                    $first_submission_date = array_slice($parts_submission_date, 0, 2);

                    $start_date = implode('-', $firstTwoParts);
                    $end_date = implode('-', $lastTwoParts);
                    $submissionDate = implode('-', $first_submission_date);

                    $currentReceiptStatDate = Carbon::parse($start_date);
                    $currentReceiptEndDate = Carbon::parse($end_date);
                    $currentReceiptsubmission_date = Carbon::parse($submissionDate);

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
                                // ->whereMonth('status_date', $resume_month)
                                // ->whereYear('status_date', $resume_year)
                                ->latest()
                                ->get();
                            // dd($check_resume);
                            // if (count($check_resume) > 0 && $check_resume->first()->status == 'resume') {

                            //     $status[$group] = 'resume';
                            // } elseif ($currentMonth->diffInMonths($currentReceiptsubmission_date) == 0 && $currentReceiptsubmission_date->lessThan($currentReceiptStatDate)) {
                            //     //แก้บัค 22/09/2023
                            //     if ($check_resume->first()->status == 'studying . . .') {
                            //         $status[$group] = 'studying . . .';
                            //     } else {
                            //     }
                            //     // $status[$group] = 'waitting . . .';

                            //     // dd($check_resume,$stdId,$status,$school_pice);
                            // } else {
                            //     // dd($check_resume,$check_resume->first()->status);

                            //     // if ($check_resume && $check_resume->first() && property_exists($check_resume->first(), 'status')) {
                            //     if ($check_resume->first()->status == 'waiting . . .') {
                            //         $status[$group] = 'waiting . . .';
                            //     } else {
                            //         $status[$group] = 'studying . . .';
                            //     }
                            //     // } else {
                            //     // dd($stdId, $check_resume);
                            //     // }
                            // }
                            if (count($check_resume) > 0) {
                                $firstLog = $check_resume->first();

                                if ($firstLog->status == 'resume') {
                                    $status[$group] = 'resume';
                                } elseif ($currentMonth->diffInMonths($currentReceiptsubmission_date) == 0 && $currentReceiptsubmission_date->lessThan($currentReceiptStatDate)) {
                                    if ($firstLog->status == 'studying . . .') {
                                        $status[$group] = 'studying . . .';
                                    } else {
                                    }
                                } else {
                                    if ($firstLog->status == 'waiting . . .') {
                                        $status[$group] = 'waiting . . .';
                                    } else {
                                        $status[$group] = 'studying . . .';
                                    }
                                }
                            } else {
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

    public function home2()
    {
        $currentMonth = date('m');
        $currentYear = date('Y');
        $rec_times = Recordtime::where('user_id', Auth::user()->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            // ->get();
            ->get()
            ->groupBy('status_name')
            ->map->count();
        $rec_times_year = Recordtime::where('user_id', Auth::user()->id)
            ->whereYear('date', $currentYear)
            // ->get();
            ->get()
            ->groupBy('status_name')
            ->map->count();

        $rec_timess = Recordtime::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->orderBy('user_id', 'desc')
            ->get()
            ->groupBy('user_id');

        // dd($rec_timess);

        return view('users.working_report', [
            'rec_timess' => $rec_timess,
            'rec_times' => $rec_times,
            'rec_times_year' => $rec_times_year,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,

        ]);
    }
    public function reportTime()
    {
        $currentMonth = date('m');
        $currentYear = date('Y');
        // $rec_times = Recordtime::where('user_id', Auth::user()->id)
        //     ->whereMonth('date', $currentMonth)
        //     ->whereYear('date', $currentYear)
        //     // ->get();
        //     ->get()
        //     ->groupBy('status_name')
        //     ->map->count();
        // $rec_times_year = Recordtime::where('user_id', Auth::user()->id)
        //     ->whereYear('date', $currentYear)
        //     // ->get();
        //     ->get()
        //     ->groupBy('status_name')
        //     ->map->count();

        $rec_timess = Recordtime::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->orderBy('user_id', 'desc')
            ->get()
            ->groupBy('user_id');
        $rec_times_year = Recordtime::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->orderBy('user_id', 'desc')
            ->get()
            ->groupBy('user_id');
        $stwork = Statuswork::orderBy('id', 'asc')->get();

        // dd($rec_timess);

        return view('users.def_working_report', [
            'rec_timess' => $rec_timess,
            // 'rec_times' => $rec_times,
            'rec_times_year' => $rec_times_year,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'stwork' => $stwork,

        ]);
    }
}
