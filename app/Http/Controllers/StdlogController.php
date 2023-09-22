<?php

namespace App\Http\Controllers;

use App\Models\Receipt_School;
use App\Models\SchoolPrice;
use App\Models\Statuslog;
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


        // $date_sup = [];
        // foreach ($test_scp as $schoolPrice) {
        //     if ($schoolPrice->rec_school) {
        //         $date_sup[] = $schoolPrice->rec_school->submission_date;
        //         // Do something with $date_sup
        //     } else {
        //         // Handle case where rec_school relationship is null
        //     }
        // }
        // $rec_schl = Receipt_School::whereMonth('submission_date',$month)
        // ->whereYear('submission_date',$year)
        // ->get();
        // $qty = $rec_schl->school_pice->course_type = 1;
        // dd($qty,$rec_schl);

        return view('students.report_std', [

            'latestStatusCounts' => $latestStatusCounts,
            'latestStatusByStudent' => $latestStatusByStudent,
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
        }

        // dd($latestStatusByStudent);
        return view('students.report_std', [

            'latestStatusCounts' => $latestStatusCounts,
            'latestStatusByStudent' => $latestStatusByStudent,

        ]);
    }

    public function reportMonthStd()
    {
        $test_scp = SchoolPrice::all();
        $currentMonth = Carbon::now()->format('Y-m');
        $matchingSchoolPrices = [];

        $schoolPrice_id = [];
        foreach ($test_scp as $schoolPrice) {
            if ($schoolPrice->rec_school && $schoolPrice->rec_school->submission_date) {

                $submissionDate = Carbon::parse($schoolPrice->rec_school->submission_date)->format('Y-m');

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
            ->orderBy('rid')
            ->get()
            ->groupBy('student_id', 'group');

        $schoolPrice_id_all = [];
        foreach ($test_scp as $schoolPrice) {
            if ($schoolPrice->rec_school) {
                // $submissionDate = Carbon::parse($schoolPrice->rec_school->submission_date)->format('Y-m');
                if ($schoolPrice->course_type == 1) {
                    $matchingSchoolPrices[] = [
                        'id' => $schoolPrice->id,
                        'course_type' => $schoolPrice->course_type,
                        'reciept_id' => $schoolPrice->rid,
                        'std_id' => $schoolPrice->student_id,
                        'submission_date' => $schoolPrice->rec_school->submission_date
                    ];
                    $schoolPrice_id_all[] = $schoolPrice->id;
                }
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

        // ksort($totalForAllMonths);
        // dd($totalForAllMonths,  $reportMonth_schoolPrice);

        return view('students.reportMonthStd', [
            'reportMonth_schoolPrice' => $reportMonth_schoolPrice,
            'totalForAllMonths' => $totalForAllMonths
        ]);
    }

    public function searchr_reportMonthStd(Request $request)
    {

        $request->validate(
            [
                'month' => 'required',
                'year' => 'required',
            ],
            [
                'month.required' => 'Pls. Select year !!',
                'year.required' => 'Pls. Select year !!'
            ]
        );

        $test_scp = SchoolPrice::all();
        $month = $request->month;
        $year = $request->year;
        $search = Carbon::parse($year . '-' . $month)->format('Y-m');

        $matchingSchoolPrices = [];
        $schoolPrice_id = [];
        foreach ($test_scp as $schoolPrice) {
            if ($schoolPrice->rec_school && $schoolPrice->rec_school->submission_date) {

                $submissionDate = Carbon::parse($schoolPrice->rec_school->submission_date)->format('Y-m');

                if ($submissionDate == $search && $schoolPrice->course_type == 1) {
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
            ->orderBy('rid')
            ->get()
            ->groupBy('student_id', 'group');
        // dd( $reportMonth_schoolPrice);

        $schoolPrice_id_all = [];
        foreach ($test_scp as $schoolPrice) {
            if ($schoolPrice->rec_school) {
                // $submissionDate = Carbon::parse($schoolPrice->rec_school->submission_date)->format('Y-m');
                if ($schoolPrice->course_type == 1) {
                    $matchingSchoolPrices[] = [
                        'id' => $schoolPrice->id,
                        'course_type' => $schoolPrice->course_type,
                        'reciept_id' => $schoolPrice->rid,
                        'std_id' => $schoolPrice->student_id,
                        'submission_date' => $schoolPrice->rec_school->submission_date
                    ];
                    $schoolPrice_id_all[] = $schoolPrice->id;
                }
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
                // dd($data->rec_school->submission_date,$month);

                // $month = Carbon::parse($data->rec_school->submission_date)->format('F-Y');
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
        // ksort($totalForAllMonths);
        // dd($totalForAllMonths,  $reportMonth_schoolPrice);

        return view('students.reportMonthStd', [
            'reportMonth_schoolPrice' => $reportMonth_schoolPrice,
            'totalForAllMonths' => $totalForAllMonths
        ]);
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
