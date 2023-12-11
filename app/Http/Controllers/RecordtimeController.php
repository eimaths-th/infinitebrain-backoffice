<?php

namespace App\Http\Controllers;

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
        $rec_times = Recordtime::orderBy('id', 'desc')->get();
        return view('users.def_working', [
            'rec_times' => $rec_times,
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
