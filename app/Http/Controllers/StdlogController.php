<?php

namespace App\Http\Controllers;

use App\Models\SchoolPrice;
use App\Models\Statuslog;
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
                    ->groupBy('student_id', 'group'); // Group by student_id and group
            })
            ->whereYear('status_log.status_date', $year)
            ->whereMonth('status_log.status_date', $month)
            ->join('students', 'status_log.student_id', '=', 'students.id') // Assuming 'students' is the name of your students table
            ->orderBy('students.code')
            ->get();


        // $select_status = Statuslog::all()
        // ->groupBy('status');

        // dd($select_status);
        return view('students.report_std', [

            'latestStatusCounts' => $latestStatusCounts,
            'latestStatusByStudent' => $latestStatusByStudent,
            // 'select_status' => $select_status,

        ]);
    }

    public function search_status(Request $request)
    {
        $request->validate([
            // 'month' => 'required',
            'year' => 'required',
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
