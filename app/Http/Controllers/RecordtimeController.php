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
use App\Imports\RecordtimeImport;
use Maatwebsite\Excel\Facades\Excel;

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
        $users = User::active()->get();
        $rec_times = Recordtime::orderBy('id', 'desc')->get();
        $checkCd = 0;
        return view('users.def_working', [
            'rec_times' => $rec_times,
            'users' => $users,
            'checkCd' => $checkCd,

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
        $users = User::active()->get();
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
                ->with('error', 'พนักงานคนนี้มีข้อมูลทำงานของวันนี้แล้ว');
        }


        $user = User::find($request->get('user_id'));
        $type = $user->type_user;
        $status_id = $request->get('status');
        $stime = $request->get('stime');
        $etime = $request->get('etime');
        $explain = $request->get('explain');
        $lateThreshold = 35;
        $starttime = Carbon::parse($stime); //"9:50 AM"
        $endtime = Carbon::parse($etime); //"5:25 PM"
        $date =  Carbon::parse($request->get('date'));
        $dayOfWeek = $date->dayName;
        $Minutes = 0;

        if (($type == 1) || ($dayOfWeek == "Saturday" || $dayOfWeek == "Sunday")) {

            $startOfWorkday = Carbon::createFromTime(9, 10, 0); // 9:10 AM start work user type 1
            $endOfWorkday = Carbon::createFromTime(16, 59, 59); // 5:00 PM

        } else {

            $startOfWorkday = Carbon::createFromTime(10, 10, 0); // 10:10 AM start work
            $endOfWorkday = Carbon::createFromTime(17, 59, 59); // 6:00 PM
        }

        if ($status_id == 1) {

            if ($stime && $etime) {

                if (!$endtime->gt($endOfWorkday)) {
                    if ($endtime->diffInMinutes($endOfWorkday) > $lateThreshold) {

                        $status_id = "8"; // สแกนออกก่อนมากกว่า 35 นาที = ลาครึ่งวัน
                        $explain .= ' | ลาครึ่งวันบ่าย';
                    } else {

                        $status_id = "9"; //ออกก่อนเวลา

                    }
                } elseif ($starttime->gt($startOfWorkday)) {
                    if ($starttime->diffInMinutes($startOfWorkday) > $lateThreshold) {
                        # code...
                        $status_id = "5"; // เข้างานสายมากกว่า 35 นาที
                        // $explain .= ' | ลาครึ่งวันเช้า';
                    } else {
                        # code...
                        $status_id = "5"; //เข้างานสาย
                    }
                    $timeDifference = $starttime->diff($startOfWorkday);

                    $Minutes = ($timeDifference->h * 60) + $timeDifference->i;
                }
            } elseif (!$stime && $etime) {
                $status_id = "6"; //ไม่แสกนเข้า
            } elseif ($stime && !$etime) {
                $status_id = "7"; //ไม่แสกนออก
            } elseif (!$stime && !$etime) {
                return redirect()->route('recordtime.index')
                    ->with('error', 'โปรดระบุเวลาเข้าและออกงาน');
            }
        }

        $status = Statuswork::find($status_id);

        $rect = new Recordtime([
            'user_id' => $user->id,
            'type_user' => $type,
            'date' => $request->get('date'),
            'explain' => $explain,
            //'unit' => '',
            'stime' => $stime,
            'etime' => $etime,
            'status' => $status_id,
            'status_name' => $status->name,
            //'amount' => $request->get('book_old_price'),
        ]);
        $rect->save();

        $rect->update(['late_time' => $Minutes]);

        return redirect()->route('recordtime.index')
            ->with('success', 'Record has been saved successfully.');
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

        foreach ($rec_times_5 as $yearMonth =>  $rec_times) {
            $totalMinutes = 0;
            // $recDate = Carbon::parse($yearMonth);
            // $isSaturdayOrSunday = $recDate->dayOfWeek == Carbon::SATURDAY || $recDate->dayOfWeek == Carbon::SUNDAY;
            list($year, $month) = explode("-", $yearMonth);

            foreach ($rec_times as $rec_time) {
                $recDate = Carbon::parse($rec_time->date);
                $isSaturdayOrSunday = $recDate->dayOfWeek == Carbon::SATURDAY || $recDate->dayOfWeek == Carbon::SUNDAY;
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
            $user_name = User::find($user_id);
            $name = $user_name->name;
            $rec_times_year2 = Recordtime::where('user_id', $user_id)
                // ->whereYear('date', $currentYear)
                ->get();
        } else {

            $rec_times = Recordtime::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('id', 'desc')
                ->get();
            $name = '';
            $rec_times_year2 = [];
        }

        if ($request->type === 'print') {
            $view = 'users.print_rect';
        } else {
            $view = 'users.view_rect';
        }

        $users = User::active()->get();

        // dd($name);
        $checkCd = 1;

        $date = Carbon::create($year, $month)->format('Y-m');
        return view($view, [
            'rec_times' => $rec_times,
            'users' => $users,
            'user_id' => $user_id,
            'month' => $month,
            'year' => $year,
            'name' => $name,
            'fontsize' => 1,
            'checkCd' => $checkCd,
            'date' => $date,

            'rec_times_year2' => $rec_times_year2,


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


        $rec_times_5 = Recordtime::where('status', 5)
            // ->whereMonth('date', 02)
            // ->whereYear('date', 2024)
            ->orderBy('date')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->date)->format('Y-m');
            });

        foreach ($rec_times_5 as $yearMonth =>  $rec_times) {
            $totalMinutes = 0;
            list($year, $month) = explode("-", $yearMonth);
            // $loop = 0;
            foreach ($rec_times as $rec_time) {

                $recDate = Carbon::parse($rec_time->date);
                $isSaturdayOrSunday = $recDate->dayOfWeek == Carbon::SATURDAY || $recDate->dayOfWeek == Carbon::SUNDAY;

                if ($rec_time->type_user == '1' || $isSaturdayOrSunday) {
                    $workTime = Carbon::createFromTime(9, 10, 0); // 9:10 AM
                } else {
                    $workTime = Carbon::createFromTime(10, 10, 0); // 10:10 AM
                }

                $recTime = Carbon::parse($rec_time->stime);

                $timeDifference = $recTime->diff($workTime);

                $Minutes = ($timeDifference->h * 60) + $timeDifference->i;
                $totalMinutes += $Minutes;

                // $loop++;
                // if ($loop == 2) {
                //     # code...

                // }
            }

            // dd($totalMinutes, $Minutes, $loop, $rec_time, $workTime, $isSaturdayOrSunday, $recDate->dayOfWeek, $recDate);
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
                $overlate->update(['late_time_all' => $totalMinutes]);
            }
        }

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
                ->first();
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

        // dd($request);

        $overlates = OverLate::all();



        return view('users.def_working_report', [
            'rec_timess' => $rec_timess,
            // 'rec_times' => $rec_times,
            'rec_times_year' => $rec_times_year,
            'year' => $currentYear,
            'month' => $currentMonth,
            'stwork' => $stwork,
            'overlates' => $overlates,
            'fontsize' => 1,

        ]);
    }

    public function testrec()
    {
        $users = User::active()->get();
        $ids = User::pluck('id')->toArray();
        $recordtime = Recordtime::whereNotIn('user_id', $ids)->delete();
        // $ids_rec = $recordtime->pluck('user_id')->toArray();

    }
    public function search_reportMonthTime(Request $request)
    {
        // dd($request);
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

        $rec_times_5 = Recordtime::where('status', 5)
            ->orderBy('date')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->date)->format('Y-m');
            });

        foreach ($rec_times_5 as $yearMonth =>  $rec_times) {
            $totalMinutes = 0;
            // $recDate = Carbon::parse($yearMonth);
            // $isSaturdayOrSunday = $recDate->dayOfWeek == Carbon::SATURDAY || $recDate->dayOfWeek == Carbon::SUNDAY;
            list($year2, $month2) = explode("-", $yearMonth);

            foreach ($rec_times as $rec_time) {
                $recDate = Carbon::parse($rec_time->date);
                $isSaturdayOrSunday = $recDate->dayOfWeek == Carbon::SATURDAY || $recDate->dayOfWeek == Carbon::SUNDAY;
                if ($rec_time->type_user == '1' || $isSaturdayOrSunday) {
                    $workTime = Carbon::createFromTime(9, 10, 0); // 9:10 AM
                } else {
                    $workTime = Carbon::createFromTime(10, 10, 0); // 10:10 AM
                }

                $recTime = Carbon::parse($rec_time->stime);

                $timeDifference = $recTime->diff($workTime);

                $Minutes = ($timeDifference->h * 60) + $timeDifference->i;
                $totalMinutes += $Minutes;
            }

            $overlate = OverLate::where('year', $year2)
                ->where('month', $month2)
                ->first();

            if (!$overlate) {
                OverLate::create([
                    'month' => $month2,
                    'year' => $year2,
                    'late_time_all' => $totalMinutes,
                ]);
            } else {
                $overlate->update(['late_time_all' => $totalMinutes]);
            }
        }

        $rec_times_13 = Recordtime::where('status', 13) //missing_work
            ->orderBy('date')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->date)->format('Y-m');
            })
            ->map(function ($group) {
                return $group->count();
            });

        foreach ($rec_times_13 as $yearMonth => $records) {
            list($year1, $month1) = explode('-', $yearMonth);
            $overlate = OverLate::where('year', $year1)
                ->where('month', $month1)
                ->first();
            if (!$overlate) {
                OverLate::create([
                    'month' => $month1,
                    'year' => $year1,
                    'missing_work_all' => $records,
                ]);
            } else {
                $update = OverLate::find($overlate->id);
                $update->update(['missing_work_all' => $records]);
            }
        }

        $overlates = OverLate::all();



        $type = $request->type;
        $tap = $request->tap;
        $recTap4 = '';
        if ($type === "print") {
            # code...
            if ($tap === '1') {
                # code...
                $view = 'users.rect_tap_1_print';
            } elseif ($tap === '2') {
                # code...
                $view = 'users.rect_tap_2_print';
            } elseif ($tap === '3') {
                # code...
                $view = 'users.rect_tap_3_print';
            } elseif ($tap === '4') {
                # code...
                $recTap4 = Recordtime::whereYear('date', $year)
                    ->where('user_id', $request->user_id)
                    ->get();
                $view = 'users.rect_tap_2_print_user';
                // dd(
                //     $request,
                //     $recTap4,
                // );
            }
        } else {
            # code...
            $view = 'users.def_working_report';
        }
        // dd(
        //     $year,
        //     $recTap4,
        // );
        return view($view, [
            'rec_timess' => $rec_timess,
            // 'rec_times' => $rec_times,
            'rec_times_year' => $rec_times_year,
            'year' => $year,
            'month' => $month,
            'stwork' => $stwork,
            'overlates' => $overlates,
            'recTap4' => $recTap4,
            'fontsize' => 1,


        ]);
    }

    public function find_UserRecTime($user_id, $status, $month, $year)
    {
        //
        $users = User::active()->get();

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

    public function find_statusRecTime($status, $month, $year)
    {
        //
        $users = User::active()->get();

        // dd($month);

        $rec_times = Recordtime::where('status', $status)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('id', 'desc')
            ->get();

        return view('users.def_working', [
            'rec_times' => $rec_times,
            'users' => $users,

        ]);
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
            return redirect()->route('recordtime.index')->with('error', 'พนักงานคนนี้มีข้อมูลทำงานของวันนี้แล้ว');
        }

        $originalAttributes = $oldData->getAttributes();

        $type = $oldData->type_user;
        $status_id = $request->get('status');
        $stime = $request->get('stime');
        $etime = $request->get('etime');
        $explain = $request->get('explain');
        $lateThreshold = 35;
        $starttime = Carbon::parse($stime); //"9:50 AM"
        $endtime = Carbon::parse($etime); //"5:25 PM"

        $date =  Carbon::parse($request->get('date'));
        $dayOfWeek = $date->dayName;
        $Minutes = 0;

        if (($type == 1) || ($dayOfWeek == "Saturday" || $dayOfWeek == "Sunday")) {

            $startOfWorkday = Carbon::createFromTime(9, 10, 0); // 9:10 AM start work user type 1
            $endOfWorkday = Carbon::createFromTime(16, 59, 59); // 5:00 PM

        } else {

            $startOfWorkday = Carbon::createFromTime(10, 10, 0); // 10:10 AM start work
            $endOfWorkday = Carbon::createFromTime(17, 59, 59); // 6:00 PM
        }
        if ($status_id == 1) {

            if ($stime && $etime) {
                if (!$endtime->gt($endOfWorkday)) {
                    if ($endtime->diffInMinutes($endOfWorkday) > $lateThreshold) {

                        $status_id = "8"; // สแกนออกก่อนมากกว่า 35 นาที = ลาครึ่งวัน
                        $explain .= ' | ลาครึ่งวันบ่าย';
                    } else {

                        $status_id = "9"; //ออกก่อนเวลา

                    }
                } elseif ($starttime->gt($startOfWorkday)) {
                    if ($starttime->diffInMinutes($startOfWorkday) > $lateThreshold) {
                        # code...
                        $status_id = "8"; // เข้างานสายมากกว่า 35 นาที
                        $explain .= ' | ลาครึ่งวันเช้า';
                    } else {
                        # code...
                        $status_id = "5"; //เข้างานสาย
                    }
                    $timeDifference = $starttime->diff($startOfWorkday);

                    $Minutes = ($timeDifference->h * 60) + $timeDifference->i;
                }
            } elseif (!$stime && $etime) {
                $status_id = "6"; //ไม่แสกนเข้า
            } elseif ($stime && !$etime) {
                $status_id = "7"; //ไม่แสกนออก
            } elseif (!$stime && !$etime) {
                return redirect()->route('recordtime.index')
                    ->with('error', 'โปรดระบุเวลาเข้าและออกงาน');
            }
        }

        if ($status_id == 5) {
            # code...
            $timeDifference = $starttime->diff($startOfWorkday);
            $Minutes = ($timeDifference->h * 60) + $timeDifference->i;
        }

        // dd($Minutes);
        $status = Statuswork::find($status_id);

        $oldData->update([
            'date' => $request->get('date'),
            'stime' => $stime,
            'etime' => $etime,
            'status' => $status->id,
            'explain' => $explain,
            'late_time' => $Minutes,
            'status_name' => $status->name,
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
                'old_late_time'     => $originalAttributes['late_time'],
                'old_status_name'   => $originalAttributes['status_name'],
                'old_explain'       => $originalAttributes['explain'],

                'new_date'          => $oldData->date,
                'new_stime'         => $oldData->stime,
                'new_etime'         => $oldData->etime,
                'new_late_time'     => $Minutes,
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

    public function ShowImportForm()
    {
        $users = User::active()->orderBy('name')->get();
        return view('users.recordtime_import', [
            'users' => $users,
        ]);
    }

    public function ProcessImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:10240',
        ]);

        try {
            $import = new RecordtimeImport();
            Excel::import($import, $request->file('file'));

            $results = $import->GetResults();
            $users = User::active()->orderBy('name')->get();

            return view('users.recordtime_import', [
                'users' => $users,
                'import_results' => $results,
            ])->with('success', "นำเข้าข้อมูลสำเร็จ {$results['success_count']} รายการ");

        } catch (\Exception $e) {
            return redirect()->route('recordtime.import')
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function UpdateFingerprint(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'fingerprint_id' => 'nullable|string|max:50',
            'days_off' => 'nullable|string|max:20',
            'wfh_days' => 'nullable|string|max:20',
        ]);

        try {
            $user = User::find($request->user_id);

            if ($request->fingerprint_id) {
                $existing_user = User::where('fingerprint_id', $request->fingerprint_id)
                    ->where('id', '!=', $user->id)
                    ->first();

                if ($existing_user) {
                    return response()->json([
                        'success' => false,
                        'message' => "Fingerprint ID นี้ถูกใช้โดย {$existing_user->name} แล้ว"
                    ]);
                }
            }

            $user->fingerprint_id = $request->fingerprint_id ?: null;
            $user->days_off = $request->days_off ?: null;
            $user->wfh_days = $request->wfh_days ?: null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'บันทึกสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }
}
