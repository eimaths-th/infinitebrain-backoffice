<?php

namespace App\Http\Controllers;

// use App\Models\Receipt_School;

use App\Models\FutureStd;
use App\Models\Receipt_School;
use App\Models\SchoolPrice;
use App\Models\Statuslog;
use App\Models\Student;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StdlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
        $latestStatusByStudent = Statuslog::select('status_log.student_id', 'status_log.group', 'status_log.status', 'status_log.status_date', 'status_log.nick_name')
            ->whereIn('status_log.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('status_log')
                    ->groupBy('student_id', 'group');
            })
            ->whereYear('status_log.status_date', $year)
            ->whereMonth('status_log.status_date', $month)
            ->join('students', 'status_log.student_id', '=', 'students.id')
            ->orderBy('students.code')
            ->get();

        return view('students.report_std', [

            'latestStatusCounts' => $latestStatusCounts,
            'latestStatusByStudent' => $latestStatusByStudent,
            // 'select_status' => $select_status,
            // 'select_status' => $select_status,
            // 'select_status' => $select_status,
            // 'select_status' => $select_status,

        ]);
    }

    public function search_status(Request $request)
    {
        $request->validate([
            'month' => 'required',
            'year' => 'required',
        ], [
            'month.required' => 'Pls. Select year !!',
            'year.required' => 'Pls. Select year !!'
        ]);
        $month = $request->month;
        $year = $request->year;
        if ($month == null) {
            $latestStatusCounts = Statuslog::select('group', 'student_id', 'status')
                ->whereIn('id', function ($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('status_log')
                        ->groupBy('group', 'student_id');
                })
                ->whereYear('status_date', $year)
                ->orderBy('group')
                ->get();
            $latestStatusByStudent = Statuslog::select('status_log.student_id', 'status_log.group', 'status_log.status', 'status_log.status_date', 'status_log.nick_name')
                ->whereIn('status_log.id', function ($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('status_log')
                        ->groupBy('student_id', 'group');
                })
                ->whereYear('status_log.status_date', $year)
                ->join('students', 'status_log.student_id', '=', 'students.id')
                ->orderBy('students.code')
                ->get();
        } else {
            $latestStatusCounts = Statuslog::select(
                'status_log.student_id',
                'status_log.group',
                'status_log.status',
                'status_log.status_date',
                'status_log.nick_name'
            )
                ->whereIn('status_log.id', function ($query) use ($year, $month) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('status_log')
                        ->whereYear('status_date', $year)
                        ->whereMonth('status_date', $month)
                        ->groupBy('student_id', 'group');
                })
                ->join('students', 'status_log.student_id', '=', 'students.id')
                ->orderBy('students.code')
                ->get();


            $latestStatusByStudent = Statuslog::select(
                'status_log.student_id',
                'status_log.group',
                'status_log.status',
                'status_log.status_date',
                'status_log.nick_name'
            )
                ->whereIn('status_log.id', function ($query) use ($year, $month) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('status_log')
                        ->whereYear('status_date', $year)
                        ->whereMonth('status_date', $month)
                        ->groupBy('student_id', 'group');
                })
                ->join('students', 'status_log.student_id', '=', 'students.id')
                ->orderBy('students.code')
                ->get();
        }

        // dd($latestStatusByStudent);
        return view('students.report_std', [

            'latestStatusCounts' => $latestStatusCounts,
            'latestStatusByStudent' => $latestStatusByStudent,

        ]);
    }


    private function getMatchingSchoolPrices($test_scp, $currentMonth, $search)
    {
        $matchingSchoolPrices = [];
        $schoolPrice_id = [];

        foreach ($test_scp as $schoolPrice) {
            if ($schoolPrice->rec_school && $schoolPrice->rec_school->submission_date) {
                $submissionDate = Carbon::parse($schoolPrice->rec_school->submission_date)->format('Y-m');

                // if ($submissionDate == $currentMonth) {
                if ($submissionDate == $currentMonth && $schoolPrice->course_type == 1) {
                    $matchingSchoolPrices[] = [
                        'id' => $schoolPrice->id,
                        'course_type' => $schoolPrice->course_type,
                        'reciept_id' => $schoolPrice->rid,
                        'std_id' => $schoolPrice->student_id,
                        'submission_date' => $schoolPrice->rec_school->submission_date
                    ];
                    $schoolPrice_id[] = $schoolPrice->id;
                }
            }
        }

        $reportMonth_schoolPrice = SchoolPrice::whereIn('id', $schoolPrice_id)
            ->orderBy('rid', 'desc')
            ->get()
            ->groupBy('student_id', 'group');

        return $reportMonth_schoolPrice;
    }

    private function queryGrafgh($test_scp)
    {

        $schoolPrice_id_all = [];
        foreach ($test_scp as $schoolPrice) {
            if ($schoolPrice->rec_school) {
                // $submissionDate = Carbon::parse($schoolPrice->rec_school->submission_date)->format('Y-m');
                if ($schoolPrice->rec_school && $schoolPrice->course_type == 1) {
                    $matchingSchoolPrices[] = [
                        'id' => $schoolPrice->id,
                        'course_type' => $schoolPrice->course_type,
                        'reciept_id' => $schoolPrice->rid,
                        'std_id' => $schoolPrice->student_id,
                        'submission_date' => $schoolPrice->rec_school->submission_date
                    ];
                    $schoolPrice_id_all[] = $schoolPrice->id;
                }
                // $matchingSchoolPrices[] = [
                //     'id' => $schoolPrice->id,
                //     'course_type' => $schoolPrice->course_type,
                //     'reciept_id' => $schoolPrice->rid,
                //     'std_id' => $schoolPrice->student_id,
                //     'submission_date' => $schoolPrice->rec_school->submission_date
                // ];
                // $schoolPrice_id_all[] = $schoolPrice->id;
            }
        }
        $reportMonth_schoolPrice_all = SchoolPrice::whereIn('id', $schoolPrice_id_all)
            ->orderBy('rid')
            ->get()
            ->groupBy('student_id', 'group');

        $totalForAllMonths = [];
        foreach ($reportMonth_schoolPrice_all as $studentId => $groupedData) {
            foreach ($groupedData as $key => $data) {
                $month = Carbon::parse($data->rec_school->submission_date)->format('F-Y');
                if (!isset($totalForAllMonths[$month])) {
                    $totalForAllMonths[$month] = 0;
                }
                $totalForAllMonths[$month] += $data->total;
            }
        }

        uksort($totalForAllMonths, function ($a, $b) {
            $dateA = DateTime::createFromFormat('F-Y', $a);
            $dateB = DateTime::createFromFormat('F-Y', $b);

            return $dateA <=> $dateB;
        });
        return $totalForAllMonths;
    }

    public function reportMonthStd()
    {
        $test_scp = SchoolPrice::all();
        $currentMonth = Carbon::now()->format('Y-m');

        $reportMonth_schoolPrice = $this->getMatchingSchoolPrices($test_scp, $currentMonth, $currentMonth);
        $totalForAllMonths = $this->queryGrafgh($test_scp);
        // ksort($totalForAllMonths);
        // dd($totalForAllMonths,  $reportMonth_schoolPrice);
        return view('students.reportMonthStd', [
            'reportMonth_schoolPrice' => $reportMonth_schoolPrice,
            'totalForAllMonths' => $totalForAllMonths
        ]);
    }

    public function searchr_reportMonthStd(Request $request)
    {

        $request->validate([
            'month' => 'required',
            'year' => 'required',
        ], [
            'month.required' => 'Pls. Select year !!',
            'year.required' => 'Pls. Select year !!'
        ]);
        $test_scp = SchoolPrice::all();
        $month = $request->month;
        $year = $request->year;
        $search = Carbon::parse($year . '-' . $month)->format('Y-m');
        $reportMonth_schoolPrice = $this->getMatchingSchoolPrices($test_scp, $search, $search);
        $totalForAllMonths = $this->queryGrafgh($test_scp);
        return view('students.reportMonthStd', [
            'reportMonth_schoolPrice' => $reportMonth_schoolPrice,
            'totalForAllMonths' => $totalForAllMonths
        ]);
    }


    public function reportFutureStd(Request $request)
    {
        // dd($request);
        $month  = date('m');
        $year   = date('Y');
        $latestStatusCounts = FutureStd::select('group', 'student_id', 'status')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('future_student')
                    ->groupBy('group', 'student_id');
            })
            ->whereYear('status_date', $year)
            ->whereMonth('status_date', $month)
            ->orderBy('group')

            ->get();
        $latestStatusByStudent = FutureStd::select('future_student.student_id', 'future_student.group', 'future_student.status', 'future_student.status_date', 'future_student.nick_name')
            ->whereIn('future_student.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('future_student')
                    ->groupBy('student_id', 'group');
            })
            ->whereYear('future_student.status_date', $year)
            ->whereMonth('future_student.status_date', $month)
            ->join('students', 'future_student.student_id', '=', 'students.id')
            ->orderBy('students.code')
            ->get();

        // dd($latestStatusByStudent, $latestStatusCounts);

        return view('students.report_std_future', [

            'latestStatusCounts' => $latestStatusCounts,
            'latestStatusByStudent' => $latestStatusByStudent,
            // 'select_status' => $select_status,

        ]);
        // return view('students.report_std_future');
    }

    public function searchFuture(Request $request)
    {

        // dd($request);
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
        $selected_month = $request->month;
        $selected_year = $request->year;
        $current_day = date('d');

        $select_date = "$selected_year-$selected_month-$current_day";
        $select_month_year = "$selected_year-$selected_month";

        foreach ($students as $student) {
            $statuses = $this->determineStatus($student, $select_month_year);

            foreach ($statuses as $subjectGroup => $status) {

                $existingStatus = FutureStd::where('student_id', $student->id)
                    ->where('group', $subjectGroup)
                    ->latest()
                    ->get();
                if (count($existingStatus) > 0) {

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
                }

                // dd($existingStatus->first()->status_date);

                $status_date = $existingStatus->first()->status_date ?? '';
                $status_date_year_month = date('Y-m', strtotime($status_date));
                $select_status_date_year_month = date('Y-m', strtotime($select_month_year));

                // $currentUpdate = Carbon::createFromFormat('Y-m', $status_date_year_month);

                // dd($status_date_year_month, $select_status_date_year_month);

                if (count($existingStatus) < 1) {
                    FutureStd::create([
                        'student_id' => $student->id,
                        'nick_name' => $student->nick_name,
                        'group' => $subjectGroup,
                        'status' => $status,
                        'status_date' => $select_date,
                    ]);
                } elseif (
                    $existingStatus->first()->status === $status &&
                    $existingStatus->first()->group === $subjectGroup &&
                    $status_date_year_month == $select_status_date_year_month
                ) {
                } else {
                    FutureStd::create([
                        'student_id' => $student->id,
                        'nick_name' => $student->nick_name,
                        'group' => $subjectGroup,
                        'status' => $status,
                        'status_date' => $select_date,
                    ]);
                }
                $lastStatusLog = $student->status_log->last();
                if ($lastStatusLog) {
                    $student->update(['status' => $lastStatusLog->status]);
                }
            }
        }

        $month  = $selected_month;
        $year   = $selected_year;
        $latestStatusCounts = FutureStd::whereYear('future_student.status_date', $year)
            ->whereMonth('future_student.status_date', $month)
            ->select('group', 'student_id', 'status')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('future_student')
                    ->groupBy('group', 'student_id');
            })
            ->whereYear('status_date', $year)
            ->whereMonth('status_date', $month)
            ->orderBy('group')

            ->get();
        $latestStatusByStudent = FutureStd::whereYear('future_student.status_date', $year)
            ->whereMonth('future_student.status_date', $month)
            ->select('future_student.student_id', 'future_student.group', 'future_student.status', 'future_student.status_date', 'future_student.nick_name')
            ->whereIn('future_student.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('future_student')
                    ->groupBy('student_id', 'group');
            })
            // ->whereYear('future_student.status_date', $year)
            // ->whereMonth('future_student.status_date', $month)
            ->join('students', 'future_student.student_id', '=', 'students.id')
            ->orderBy('students.code')
            ->get();


        // $testquery = FutureStd::whereMonth('status_date',$month)
        //                         ->whereYear('status_date',$year)
        //                         ->get();

        // dd($latestStatusByStudent, $latestStatusCounts);

        return view('students.report_std_future', [

            'latestStatusCounts' => $latestStatusCounts,
            'latestStatusByStudent' => $latestStatusByStudent,
            // 'select_status' => $select_status,

        ]);
    }


    public function search_future(Request $request)
    {
        $request->validate([
            'month' => 'required',
            'year' => 'required',
        ], [
            'month.required' => 'Pls. Select year !!',
            'year.required' => 'Pls. Select year !!'
        ]);
        $month = $request->month;
        $year = $request->year;
        // dd($year,$month);
        if ($month == null) {
            $latestStatusCounts = FutureStd::whereYear('status_date', $year)
                ->select('group', 'student_id', 'status')
                ->whereIn('id', function ($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('future_student')
                        ->groupBy('group', 'student_id');
                })
                ->whereYear('status_date', $year)
                ->orderBy('group')
                ->get();
            $latestStatusByStudent = FutureStd::whereYear('future_student.status_date', $year)
                ->select('future_student.student_id', 'future_student.group', 'future_student.status', 'future_student.status_date', 'future_student.nick_name')
                ->whereIn('future_student.id', function ($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('future_student')
                        ->groupBy('student_id', 'group');
                })
                // ->whereYear('future_student.status_date', $year)
                ->join('students', 'future_student.student_id', '=', 'students.id')
                ->orderBy('students.code')
                ->get();
        } else {
            $latestStatusCounts = FutureStd::whereYear('status_date', $year)
                ->whereMonth('status_date', $month)
                ->select('group', 'student_id', 'status')
                ->whereIn('id', function ($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('future_student')

                        ->groupBy('group', 'student_id');
                })
                // ->whereYear('status_date', $year)
                // ->whereMonth('status_date', $month)
                ->orderBy('group')
                ->get();

            $latestStatusByStudent = FutureStd::whereYear('future_student.status_date', $year)
                ->whereMonth('future_student.status_date', $month)
                ->select('future_student.student_id', 'future_student.group', 'future_student.status', 'future_student.status_date', 'future_student.nick_name')
                ->whereIn('future_student.id', function ($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('future_student')
                        ->groupBy('student_id', 'group');
                })
                // ->whereYear('future_student.status_date', $year)
                // ->whereMonth('future_student.status_date', $month)
                ->join('students', 'future_student.student_id', '=', 'students.id')
                ->orderBy('students.code')
                ->get();
        }

        // $testquery = FutureStd::whereMonth('status_date', $month)
        //     ->whereYear('status_date', $year)
        //     ->groupBy('student_id', 'group')
        //     //

        //     ->get();
        $month = $request->month;
        $year = $request->year;
        $latestStatusCounts = FutureStd::select('group', 'student_id', DB::raw('MAX(status) as max_status'), DB::raw('COUNT(*) as count'))
            ->whereYear('status_date', $year)
            ->whereMonth('status_date', $month)
            ->groupBy('group', 'student_id')
            ->orderBy('group')
            ->get();


        $testquery = FutureStd::select('student_id', 'group', 'status_date', DB::raw('COUNT(*) as count'))
            ->whereMonth('status_date', $month)
            ->whereYear('status_date', $year)
            ->groupBy('student_id', 'group', 'status_date')
            ->latest('status_date')
            ->orderby('student_id') // Order by status_date in descending order
            ->join('students', 'future_student.student_id', '=', 'students.id')
            // ->orderBy('students.code')
            ->get();

        foreach ($testquery as $result) {
            $studentId = $result->student_id;
            $group = $result->group;
            $count = $result->count;
            $status_date = $result->status_date;

            // Get the latest status for this student and group
            $latestStatus = FutureStd::where('student_id', $studentId)
                ->where('group', $group)
                ->where('status_date', $status_date)
                ->latest('status_date')
                ->value('status');

            // Do something with $studentId, $group, $count, and $latestStatus
            // For example, you can print them out:
            // echo "Student ID: $studentId    | Group: $group | Count: $count | Latest Status: $latestStatus  | status_date : $status_date";
            // echo "<br/>";
        }

        // dd($testquery);

        // dd($latestStatusByStudent);
        return view('students.report_std_future', [

            'latestStatusCounts' => $latestStatusCounts,
            'latestStatusByStudent' => $testquery,

        ]);
    }

    private function determineStatus($student, $select_month_year)
    {
        $stdId = $student->id;
        // $stdId = 157;
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

                $current_Month = $select_month_year;
                // $current_Month = Carbon::createFromFormat('Y-m', );
                $currentMonth = Carbon::parse($current_Month);
                // dd('test : '.$currentMonth,$current_Month);

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
                                ->whereMonth('status_date', $resume_month)
                                ->whereYear('status_date', $resume_year)
                                ->latest()
                                ->get();
                            // dd($check_resume);
                            if (count($check_resume) > 0 && $check_resume->first()->status == 'resume') {

                                $status[$group] = 'resume';
                            } elseif (
                                $currentMonth->diffInMonths($currentReceiptsubmission_date) == 0
                                && $currentReceiptsubmission_date->lessThan($currentReceiptStatDate)
                                // && $check_resume->first()->status !== 'studying . . .'
                            ) {
                                //แก้บัค 22/09/2023
                                if ($check_resume->first()->status == 'studying . . .') {
                                    $status[$group] = 'studying . . .';
                                } else {
                                    $status[$group] = 'waitting . . .';
                                }
                                // $status[$group] = 'waitting . . .';

                                // dd($check_resume,$stdId,$status,$school_pice);
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
