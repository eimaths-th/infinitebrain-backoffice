<?php

namespace App\Http\Controllers;

use App\Models\OverLate;
use App\Models\Recordtime;
use App\Models\Statuswork;
use App\Models\User;
use App\Models\WorktimeLog;
use App\Models\WorktimeLogs;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RecordtimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
    public function index()
    {
        //
        $users = User::all();
        $rec_times = Recordtime::orderBy('id', 'desc')->get();
        return view('users.def_working', [
            'rec_times' => $rec_times,
            'users' => $users,

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
        $users = User::all();
        return view('users.working_create', [
            'users' => $users
        ]);
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

        $request->validate([
            'user_id'       => 'required',
            'date'       => 'required',
            // 'precursor_id'  => 'required',
        ]);

        $checkdate = Recordtime::where('user_id', $request->get('user_id'))
            ->where('date', $request->get('date'))->get();

        if ($checkdate->count() > 0) {
            return redirect()->route('recordtime.index')
                ->with('error', 'ผนักงานคนนี้มีข้อมูลทำงานของวันนี้แล้ว');
        }

        $user = User::find($request->get('user_id'));
        $type = $user->type_user;
        $status_id = $request->get('status');
        $stime = $request->get('stime');
        $etime = $request->get('etime');
        $lateThreshold = 35;
        $starttime = Carbon::parse($stime); //"9:50 AM"
        $endtime = Carbon::parse($etime); //"5:25 PM"
        $date =  Carbon::parse($request->get('date'));
        $dayOfWeek = $date->dayName;

        if ($status_id == 1) {
            if (!$stime && $etime) {
                $status_id = "6"; //ไม่แสกนเข้า
            } elseif ($stime && !$etime) {
                $status_id = "7"; //ไม่แสกนออก
            }
            if ($stime && $etime) {
                // if ($type == 1) {
                //     $startOfWorkday = Carbon::createFromTime(9, 10, 0); // 9:10 AM start work user type 1

                //     $endOfWorkday = Carbon::createFromTime(16, 59, 59); // 5:00 PM

                // } else {
                //     if ($dayOfWeek == "Saturday" || $dayOfWeek == "Sunday") {
                //         # code...
                //         $startOfWorkday = Carbon::createFromTime(9, 10, 0); // 9:10 AM start work user type 1
                //         $endOfWorkday = Carbon::createFromTime(16, 59, 59); // 5:00 PM
                //     } else {
                //         # code...
                //         $startOfWorkday = Carbon::createFromTime(10, 10, 0); // 10:10 AM start work
                //         $endOfWorkday = Carbon::createFromTime(17, 59, 59); // 5:00 PM
                //     }
                // }

                if (($type == 1) || ($dayOfWeek == "Saturday" || $dayOfWeek == "Sunday")) {

                    $startOfWorkday = Carbon::createFromTime(9, 10, 0); // 9:10 AM start work user type 1
                    $endOfWorkday = Carbon::createFromTime(16, 59, 59); // 5:00 PM

                } else {

                    $startOfWorkday = Carbon::createFromTime(10, 10, 0); // 10:10 AM start work
                    $endOfWorkday = Carbon::createFromTime(17, 59, 59); // 5:00 PM

                }


                if (!$endtime->gt($endOfWorkday)) {
                    $status_id = "9"; //ออกก่อนเวลา
                } elseif ($starttime->diffInMinutes($startOfWorkday) > $lateThreshold && $starttime > $startOfWorkday) {
                    $status_id = "8"; // เข้างานสายมากกว่า 35 นาที
                } elseif ($starttime->gt($startOfWorkday)) {
                    $status_id = "5"; //เข้างานสาย
                }
            }

            // dd($starttime->diffInMinutes($startOfWorkday));
        }

        // dd($dayOfWeek);

        // dd($status_id);

        // dd($request);
        $status = Statuswork::find($status_id);

        $rect = new Recordtime([
            'user_id' => $user->id,
            'type_user' => $type,
            'date' => $request->get('date'),
            'explain' => $request->get('explain'),
            //'unit' => '',
            'stime' => $stime,
            'etime' => $etime,
            'status' => $status_id,
            'status_name' => $status->name,
            //'amount' => $request->get('book_old_price'),
        ]);
        $rect->save();
        return redirect()->route('recordtime.index')
            ->with('success', 'Record has been saved successfully.');
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

    public function search_rect(Request $request)
    {

        // dd($request);
        $user_id = $request->user_id;
        $month = $request->month;
        $year = $request->year;
        if ($user_id) {

            $rec_times = Recordtime::where('user_id', $user_id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('id', 'desc')
                ->get();
            # code...
        } else {

            $rec_times = Recordtime::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('id', 'desc')
                ->get();
        }

        $users = User::all();
        return view('users.def_working', [
            'rec_times' => $rec_times,
            'users' => $users,

        ]);
    }


    public function reportTime()
    {
        $currentMonth = date('m');
        $currentYear = date('Y');


        $rec_timess = Recordtime::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->orderBy('user_id', 'asc')
            ->get()
            ->groupBy('user_id');
        $rec_times_year = Recordtime::whereYear('date', $currentYear)
            ->orderBy('user_id', 'asc')
            ->get()
            ->groupBy('user_id');
        $stwork = Statuswork::orderBy('id', 'asc')->get();

        // dd($rec_timess);

        return view('users.def_working_report', [
            'rec_timess' => $rec_timess,
            // 'rec_times' => $rec_times,
            'rec_times_year' => $rec_times_year,
            'year' => $currentYear,
            'month' => $currentMonth,
            'stwork' => $stwork,

        ]);
    }
    public function search_reportMonthTime(Request $request)
    {
        $month = $request->month;
        $year = $request->year;
        if ($month) {
            # code...
            $rec_timess = Recordtime::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('user_id', 'asc')
                ->get()
                ->groupBy('user_id');
            $rec_times_year = Recordtime::whereYear('date', $year)
                ->orderBy('user_id', 'asc')
                ->get()
                ->groupBy('user_id');
            $stwork = Statuswork::orderBy('id', 'asc')->get();
        } else {
            # code...
            $month = date('m');
            $rec_timess = Recordtime::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('user_id', 'asc')
                ->get()
                ->groupBy('user_id');
            $rec_times_year = Recordtime::whereYear('date', $year)
                ->orderBy('user_id', 'asc')
                ->get()
                ->groupBy('user_id');
            $stwork = Statuswork::orderBy('id', 'asc')->get();
        }


        // dd($rec_timess);

        return view('users.def_working_report', [
            'rec_timess' => $rec_timess,
            // 'rec_times' => $rec_times,
            'rec_times_year' => $rec_times_year,
            'year' => $year,
            'month' => $month,
            'stwork' => $stwork,

        ]);
    }

    public function find_UserRecTime($user_id, $status, $month, $year)
    {
        //
        $users = User::all();

        // dd($month);
        if ($month === 'null') {
            $rec_times = Recordtime::where(['user_id' => $user_id, 'status' => $status])
                // ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $rec_times = Recordtime::where(['user_id' => $user_id, 'status' => $status])
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('id', 'desc')
                ->get();
        }

        return view('users.def_working', [
            'rec_times' => $rec_times,
            'users' => $users,

        ]);
    }

    public function update_late_time()
    {
        $rec_times_5 = Recordtime::where('status', 5)

            ->orderBy('date')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->date)->format('Y-m');
            });

        // dd($rec_times_5);


        // foreach ($rec_times as $rec_time) {
        //     $recDate = Carbon::parse($rec_time->date);
        //     $isSaturdayOrSunday = $recDate->dayOfWeek == Carbon::SATURDAY || $recDate->dayOfWeek == Carbon::SUNDAY;

        //     if ($rec_time->type_user == '1' || $isSaturdayOrSunday) {
        //         $workTime = Carbon::createFromTime(9, 10, 0); // 9:10 AM
        //     } else {

        //         $workTime = Carbon::createFromTime(10, 10, 0); // 10:10 AM
        //     }

        //     $recTime = Carbon::parse($rec_time->stime);

        //     $timeDifference = $recTime->diff($workTime);

        //     $Minutes = ($timeDifference->h * 60) + $timeDifference->i;

        //     $rec_time->update(['late_time' => $Minutes]);

        //     $totalMinutes += $Minutes;

        //     // echo $rec_time->user->name . " | date : " . $rec_time->date . " | Total time difference: {$Minutes} minutes result : {$totalMinutes}<br>";

        //     list($year, $month, $day) = explode("-", $rec_time->date);

        //     $overlate = OverLate::whereYear('year', $year)
        //         ->whereMonth('month', $month)
        //         ->first(); // Use first() directly

        //     if (!$overlate) {
        //         OverLate::create([
        //             'month' => $month,
        //             'year' => $year,
        //             'late_time_all' => $totalMinutes,
        //         ]);
        //     } else {
        //         $overlate->update(['late_time_all' => $totalMinutes]);
        //     }
        // }

        // $rec_times_miss = Recordtime::where('status', 13)
        //     ->orderBy('user_id', 'desc')
        //     ->get();
        // foreach ($rec_times_miss as $rec_times_mis) {

        //     list($year, $month, $day) = explode("-", $rec_times_mis->date);

        //     $overlate->update(['late_time_all' => $totalMinutes]);
        // }

        foreach ($rec_times_5 as $yearMonth =>  $rec_times) {
            $totalMinutes = 0;
            $recDate = Carbon::parse($yearMonth);
            $isSaturdayOrSunday = $recDate->dayOfWeek == Carbon::SATURDAY || $recDate->dayOfWeek == Carbon::SUNDAY;
            list($year, $month) = explode("-", $yearMonth);

            foreach ($rec_times as $rec_time) {
                if ($rec_time->type_user == '1' || $isSaturdayOrSunday) {
                    $workTime = Carbon::createFromTime(9, 10, 0); // 9:10 AM
                } else {
                    $workTime = Carbon::createFromTime(10, 10, 0); // 10:10 AM
                }

                $recTime = Carbon::parse($rec_time->stime);

                $timeDifference = $recTime->diff($workTime);

                $Minutes = ($timeDifference->h * 60) + $timeDifference->i;

                $rec_time->update(['late_time' => $Minutes]);
                $totalMinutes += $Minutes;
                echo $rec_time->user->name . " | date : " . $rec_time->date . " | work time : {$workTime} start time : {$rec_time->stime} | Total time difference: {$Minutes} minutes result : {$totalMinutes}</br></br>";
            }

            $overlate = OverLate::where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$overlate) {
                OverLate::create([
                    'month' => $month,
                    'year' => $year,
                    'late_time_all' => $totalMinutes,
                ]);
            } else {
                // Use update method directly
                $overlate->update(['late_time_all' => $totalMinutes]);
            }
        }
        // dd($rec_times_5);

        $rec_times = Recordtime::where('status', 13) //missing_work
            ->orderBy('date')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->date)->format('Y-m');
            })
            ->map(function ($group) {
                return $group->count();
            });
        foreach ($rec_times as $yearMonth => $records) {
            list($year, $month) = explode('-', $yearMonth);
            $overlate = OverLate::where('year', $year)
                ->where('month', $month)
                ->first(); // Use first() directly
            // dd($overlate,$year,$month);
            // dd($records);

            if (!$overlate) {
                OverLate::create([
                    'month' => $month,
                    'year' => $year,
                    'missing_work_all' => $records,
                ]);
            } else {
                $update = OverLate::find($overlate->id);
                $update->update(['missing_work_all' => $records]);
            }
        }

        dd($rec_times);
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
        $rect = Recordtime::find($id);
        return view('users.working_edit', [
            'rect' => $rect
        ]);
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

        $oldData = Recordtime::find($id);
        if (!$oldData) {
            return redirect()->route('recordtime.index')->with('error', 'ไม่พบข้อมูล');
        }

        $checkdate = Recordtime::where('user_id', $oldData->user_id)
            ->where('date', $request->get('date'))
            ->where('id', '!=', $id)
            ->exists();

        if ($checkdate) {
            return redirect()->route('recordtime.index')->with('error', 'ผนักงานคนนี้มีข้อมูลทำงานของวันนี้แล้ว');
        }

        // $checkdate = Recordtime::where('user_id', $oldData->user_id)->where('date', $request->get('date'))->get();

        // if ($checkdate->count() > 0 && $checkdate->id != $id) {
        //     return redirect()->route('recordtime.index')->with('error', 'ผนักงานคนนี้มีข้อมูลทำงานของวันนี้แล้ว');
        // }

        $originalAttributes = $oldData->getAttributes();


        $type = $oldData->type_user;
        $status_id = $request->get('status');
        $stime = $request->get('stime');
        $etime = $request->get('etime');
        $lateThreshold = 35;
        $starttime = Carbon::parse($stime); //"9:50 AM"
        $endtime = Carbon::parse($etime); //"5:25 PM"

        $date =  Carbon::parse($request->get('date'));
        $dayOfWeek = $date->dayName;

        if ($status_id == 1) {
            if (!$stime && $etime) {
                $status_id = "6"; //ไม่แสกนเข้า
            } elseif ($stime && !$etime) {
                $status_id = "7"; //ไม่แสกนออก
            }
            if ($stime && $etime) {
                if ($type == 1) {
                    $startOfWorkday = Carbon::createFromTime(9, 10, 0); // 9:10 AM start work user type 1
                    $endOfWorkday = Carbon::createFromTime(16, 59, 59); // 5:00 PM

                } else {
                    if ($dayOfWeek == "Saturday" || $dayOfWeek == "Sunday") {
                        # code...
                        $startOfWorkday = Carbon::createFromTime(9, 10, 0); // 9:10 AM start work user type 1
                        $endOfWorkday = Carbon::createFromTime(16, 59, 59); // 5:00 PM
                    } else {
                        # code...
                        $startOfWorkday = Carbon::createFromTime(10, 10, 0); // 10:10 AM start work
                        $endOfWorkday = Carbon::createFromTime(17, 59, 59); // 5:00 PM
                    }
                }
                // and


                if (!$endtime->gt($endOfWorkday)) {
                    $status_id = "9"; //ออกก่อนเวลา
                } elseif ($starttime->diffInMinutes($startOfWorkday) > $lateThreshold && $starttime > $startOfWorkday) {
                    $status_id = "8"; // เข้างานสายมากกว่า 35 นาที
                } elseif ($starttime->gt($startOfWorkday)) {
                    $status_id = "5"; //เข้างานสาย
                }
            }
            // dd($starttime->diffInMinutes($startOfWorkday));
        }
        $status = Statuswork::find($status_id);

        $oldData->update([
            'date' => $request->get('date'),
            'stime' => $stime,
            'etime' => $etime,
            'status' => $status->id,
            'explain' => $request->get('explain'),

            'status_name' => $status->name
        ]);

        if ($originalAttributes != $oldData->getAttributes()) {
            WorktimeLogs::create([
                'user_id'           => $oldData->user_id, //not change
                'rectime_id'        => $id, //not change
                'type_user'         => $oldData->type_user, //not change

                'old_date'          => $originalAttributes['date'],
                'old_stime'         => $originalAttributes['stime'],
                'old_etime'         => $originalAttributes['etime'],
                'old_status'        => $originalAttributes['status'],
                'old_status_name'   => $originalAttributes['status_name'],
                'old_explain'       => $originalAttributes['explain'],

                'new_date'          => $oldData->date,
                'new_stime'         => $oldData->stime,
                'new_etime'         => $oldData->etime,
                'new_status'        => $status->id,
                'new_status_name'   => $status->name,
                'new_explain'       => $oldData->explain,

                'action'            => 'update',
                'approv_id'         => auth()->user()->id,
            ]);
        }
        // dd($request, $id, $originalAttributes);

        return redirect()->route('recordtime.index')
            ->with('success', 'Record has been update successfully.');
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
        $rect = Recordtime::find($id);
        if ($rect) {
            $rect->delete();
            return redirect()->route('recordtime.index')->with('success', 'Record deleted successfully');
        } else {
            return redirect()->route('recordtime.index')->with('error', 'Record not found');
        }
    }
}
