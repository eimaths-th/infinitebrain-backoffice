<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Student;
use App\Http\Controllers\Controller;
use App\Models\Receipt_School;
use App\Models\SchoolPrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{

    const thmonth = array(
        "",
        "มกราคม",
        "กุมภาพันธ์",
        "มีนาคม",
        "เมษายน",
        "พฤษภาคม",
        "มิถุนายน",
        "กรกฎาคม",
        "สิงหาคม",
        "กันยายน",
        "ตุลาคม",
        "พฤศจิกายน",
        "ธันวาคม"
    );

    public function check_permission()
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->can('general.view') || $user->can('system.view')) {
                return true;
            } else {
                abort(403, 'Unauthorized action 1.');
            }
        } else {
            abort(403, 'Unauthorized action 2.');
            //return Redirect("login")->withSuccess('Please Login');
            //exit();
        }
    }


    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->check_permission();

        $subject = Subject::orderBy("id", "asc")->get();

        $student = Student::orderBy("first_name", "asc")->get();

        if ($request->ajax()) {
            //sleep(2);
            $course = History::select(
                "histories.id as histories_id",
                //"levels.title as student_level",
                "subjects.title as student_subject",
                "students.code as student_code",
                "students.first_name as student_name",
                "students.last_name as student_lastname",
                "students.nick_name as student_nickname",
                "histories.description as description",
                "histories.datetime as datetime",
                "histories.stime as stime",
                "histories.etime as etime",
            )
                ->join("students", "students.id", "=", "histories.student_id")
                ->join("subjects", "subjects.id", "=", "histories.subject_id")
                //->join("levels", "levels.id", "=", "histories.level_id")

                //->where('levels.subject', $request->id)
                ->orderBy("histories.id", "desc")
                ->get();
            $course = History::select(
                "histories.id as histories_id",
                //"levels.title as student_level",
                "subjects.title as student_subject",
                "students.code as student_code",
                "students.first_name as student_name",
                "students.last_name as student_lastname",
                "students.nick_name as student_nickname",
                "histories.description as description",
                "histories.datetime as datetime",
                "histories.stime as stime",
                "histories.etime as etime",
                "users.name as teacher_name"
            )
                ->join("students", "students.id", "=", "histories.student_id")
                ->join("subjects", "subjects.id", "=", "histories.subject_id")
                ->leftJoin("users", "users.id", "=", "histories.user_id")
                //->join("levels", "levels.id", "=", "histories.level_id")

                //->where('levels.subject', $request->id)
                ->orderBy("histories.id", "desc")
                ->get();

            return datatables()->of($course)
                ->editColumn('student_name', function (History $course) {
                    return $course->student_name . ' ' . $course->student_lastname . ' (' . $course->student_nickname . ') ';
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->histories_id . '" class="flat" name="table_records[]" value="' . $row->histories_id . '" >';
                })
                ->addColumn('action', function ($row) {
                    // $html = '<a href="#" class="btn btn-xs btn-primary btn-view" id="getViewData" data-id="' . $row->histories_id . '"><i class="fa fa-eye"></i> view</a> ';
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->histories_id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->histories_id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }

        //get subject
        $teachers = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'teacher');
        })->orWhereHas('permissions', function ($q) {
            $q->where('name', 'teacher.teach');
        })->orderBy('name', 'asc')->get();

        return view('histories.index', compact('teachers'))->with(['data' => $subject])->with(['data2' => $student]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function stdsub($id)
    {
        $studentId = $id;


        if ($studentId == null) {
            # code....
        } else {

            $std = Student::find($studentId);
            // dd($studentId,$std);

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
    public function stdsperiod($id, $sub_id)
    {
        $studentId = $id;

        if ($studentId == null) {
        } else {
            $std = Student::find($studentId);
            $sch_p = SchoolPrice::where(['student_id' => $std->id, 'subject' => $sub_id, 'course_type' => '1'])->get();
            $html = '<option value="">Select Preiod</option><option value=" ">นอกระยะเวลา</option>';
            foreach ($sch_p as $sch_p1) {
                # code...
                $html .= '<option value="' . $sch_p1->id . '"> ' . $sch_p1->period . ' </option>';
            }
            return response()->json([
                'studentperiod' => $html
            ]);
        }
    }

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
        $this->check_permission();

        $validator =  Validator::make($request->all(), [
            'student' => 'required',
            'subject' => 'required',
            // 'preiod' => 'required',
            // 'preiod' => 'required',
            // 'preiod' => 'required',
            'date' => 'required',
            'stime' => 'required',
            'etime' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $std_id = $request->student;

        // $schp = SchoolPrice::where(['student_id' => $std_id, 'subject' => $request->subject])->get();
        $schp = SchoolPrice::find($request->preiod);
        $last_period = null;

        if ($schp) {
            $last_period = $schp->period;
        } else {
            # code...
            $null = null;
            $course = new History([
                'student_id' => $request->get('student'),
                'subject_id' => $request->get('subject'),
                'school_price_id' => $null,
                //'level_id' => $request->get('level'),
                'datetime' => $request->get('date'),
                'stime' => $request->get('stime'),
                'etime' => $request->get('etime'),
                'description' => $request->get('des'),
                'status' => 'non-reciept',
                'user_id' => $request->get('user_id') ?? Auth::id(),
            ]);
            $course->save();
            return response()->json(['success' => 'ไม่ได้อยู่ในระยะเวลาที่ลงทะเบียน']);
            // dd($request->preiod);
        }

        $period = explode(' - ', $last_period);

        // dd($last_period,$period);

        if (count($period) == 1) {
            $periods = explode('-', $last_period);
            $startMonth = Carbon::parse("$periods[0]-$periods[1]")->format('Y-m');
            $endMonth = count($periods) > 2 ? Carbon::parse("$periods[2]-$periods[3]")->format('Y-m') : $startMonth;
        } else {
            $startMonth = Carbon::parse($period[0])->format('Y-m');
            $endMonth = Carbon::parse($period[1])->format('Y-m');
        }
        $endMonth = Carbon::parse($endMonth)->addMonth()->format('Y-m');
        $req_date = Carbon::parse($request->date);

        $checkdate = $req_date->between($startMonth, $endMonth);

        if ($checkdate) {
            # code...
            $course = new History([
                'student_id' => $request->get('student'),
                'subject_id' => $request->get('subject'),
                'school_price_id' => $schp->id,
                //'level_id' => $request->get('level'),
                'datetime' => $request->get('date'),
                'stime' => $request->get('stime'),
                'etime' => $request->get('etime'),
                'description' => $request->get('des'),
                'status' => 'reciept',
                'user_id' => $request->get('user_id') ?? Auth::id(),


            ]);
            $course->save();
            return response()->json(['success' => 'Histories Add successfully']);
        } else {
            $null = null;
            $course = new History([
                'student_id' => $request->get('student'),
                'subject_id' => $request->get('subject'),
                'school_price_id' => $null,
                //'level_id' => $request->get('level'),
                'datetime' => $request->get('date'),
                'stime' => $request->get('stime'),
                'etime' => $request->get('etime'),
                'description' => $request->get('des'),
                'status' => 'non-reciept',
                'user_id' => $request->get('user_id') ?? Auth::id(),
            ]);
            $course->save();
            return response()->json(['success' => 'ไม่ได้อยู่ในระยะเวลาที่ลงทะเบียน']);
        }


        // Getting values from the blade template form

    }




    public function print_day(Request $request)
    {
        //

        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'print_date' => 'required|string',
        ]);

        if ($validator->fails()) {
            //return response()->json(['errors' => $validator->errors()->all()]);
            return back()->with(['error' => 'Please input date']);
        }

        $type = $request->get('type');

        if ($type == "view") {
            $viewr = "histories.print_day_view";
        } else if ($type == "print") {
            $viewr = "histories.print_day_print";
        }
        $a_date = explode("-", $request->get('print_date'));

        $ymcode = (($a_date[0] - 2000) + 43) . $a_date[1];
        $thai_date = $a_date[2];
        $thai_year = $a_date[0] + 543;
        $thai_month = self::thmonth[(int)$a_date[1]];

        $histories = History::select(
            "histories.id as histories_id",
            //"levels.title as student_level",
            "subjects.title as student_subject",
            "students.code as student_code",
            "students.first_name as student_name",
            "students.last_name as student_lastname",
            "students.nick_name as student_nickname",
            "histories.datetime as datetime",
            "histories.stime as stime",
            "histories.etime as etime",
            "histories.description as note",
            "histories.signature as signature",
        )->join("students", "students.id", "=", "histories.student_id")
            ->join("subjects", "subjects.id", "=", "histories.subject_id")
            //->join("levels", "levels.id", "=", "histories.level_id")
            ->where([['histories.datetime', $request->get('print_date')]])
            ->orderBy("stime", "asc")
            ->get();
        $ret_histories = array();
        $i = 0;
        $total_hour = 0;
        foreach ($histories as $histories_result) {

            $start_date = $histories_result->datetime . ' ' . $histories_result->stime;
            $end_date = $histories_result->datetime . ' ' . $histories_result->etime;

            $datetime_1 = $start_date;
            $datetime_2 = $end_date;

            $start_datetime = new \DateTime($datetime_1);
            $diff = $start_datetime->diff(new \DateTime($datetime_2));

            //duration
            $total_minutes = ($diff->days * 24 * 60);
            $total_minutes += ($diff->h * 60 * 60);
            $total_minutes += ($diff->i * 60);
            $total_minutes += $diff->s;

            $hour = ($total_minutes) / 60 / 60;

            $ret_histories[$i]['datetime'] = $histories_result->datetime;
            $ret_histories[$i]['duration'] = $hour;
            $ret_histories[$i]['firstname'] = $histories_result->student_name;
            $ret_histories[$i]['lastname'] = $histories_result->student_lastname;
            $ret_histories[$i]['nickname'] = $histories_result->student_nickname;
            $ret_histories[$i]['stime'] = $histories_result->stime;
            $ret_histories[$i]['etime'] = $histories_result->etime;
            $ret_histories[$i]['subject'] = $histories_result->student_subject;

            /*             if ($hour >= 2) {
                $bgt = 'bg-red';
                $bgti = 'bg-red';
            } else {
                $bgt = 'bg-yellow';
                $bgti = '';
            } */
            $total_hour = $total_hour + $hour;
            $i++;
        }
        return view($viewr)
            ->with(['ymcode' =>  $ymcode])
            ->with(['total_hour' =>  $total_hour])
            ->with(['thaiyear' =>  $thai_year])
            ->with(['fontsize' =>  1])
            ->with(['thaimonth' =>  $thai_month])
            ->with(['thaidate' =>  $thai_date])
            ->with(['data' => $ret_histories])
            ->with(['print_date' => $request->get('print_date')]);
    }

    public function print_std(Request $request)
    {
        # code...
        # code...
        # code...
        $this->check_permission();
        $std = $request->student;

        $type = $request->get('type');

        $input = $request->all();
        if ($type == "print") {
            // dd();
            if (!empty($input['date'])) {
                $date = $input['date'];
            } else {
                $date = "";
            }

            $viewr = 'histories.print_std_print';
        } else {
            $date = $request->std_date;

            $viewr = 'histories.print_std';
        }
        // dd($input,$date);

        if (!empty($date)) {
            $hist_std = History::where('student_id', $std)->where('datetime', $date)->get();
        } else {
            $hist_std = History::where('student_id', $std)->get();
        }
        // dd($std);
        $student = Student::find($std);
        $name = $student->full_name;

        return view($viewr, [
            'hist_std' => $hist_std,
            'fullname' => $name,
            'fontsize' => 1,
            'std' => $std,
            'date' => $date,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
        $this->check_permission();

        // $stdId = $data->student_id;
        // dd($stdId);
        $select_list_subject = "";
        $subject = Subject::orderBy("id", "asc")->get();

        $select_list_student = "";
        $student = Student::orderBy("id", "asc")->get();
        // dd($student);
        foreach ($student as $keys) {

            $select_list_student .= '<option value="' . $keys->id . '" >' . $keys->code . ' ' . $keys->full_name . ' (' . $keys->nick_name . ') ' . '</option>';
        }
        return response()->json([
            'student' => $select_list_student
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */

    // public function view($id)
    // {
    //     //

    //     $this->check_permission();
    //     $data = History::find($id);

    //     $select_list_subject = "";
    //     $subject = Subject::orderBy("id", "asc")->get();
    //     foreach ($subject as $keysu) {
    //         if ($data->subject_id == $keysu->id) {

    //             $select_list_subject .= '<option value="' . $keysu->id . '" selected>' . $keysu->title . '</option>';
    //         } else {
    //             $select_list_subject .= '<option value="' . $keysu->id . '" >' . $keysu->title . '</option>';
    //         }
    //     }

    //     $select_list_student = "";
    //     $student = Student::orderBy("id", "asc")->get();
    //     foreach ($student as $keys) {
    //         if ($data->student_id == $keys->id) {

    //             $select_list_student .= '<option value="' . $keys->id . '" selected>' . $keys->code . ' ' . $keys->first_name . ' ' . $keys->last_name . '</option>';
    //         } else {
    //             $select_list_student .= '<option value="' . $keys->id . '" >' . $keys->code . ' ' . $keys->first_name . ' ' . $keys->last_name . '</option>';
    //         }
    //     }

    //     $date_start = $data->datetime;


    //     $data1 = Level::where('subject', $data->subject_id)->orderBy("id", "asc")->get();

    //     $select_list = "";

    //     foreach ($data1 as $key) {

    //         if ($data->level_id == $key->id) {

    //             $select_list .= '<option value="' . $key->id . '" selected> (' . $key->level_sort . ') ' . $key->title . '</option>';
    //         } else {
    //             $select_list .= '<option value="' . $key->id . '" > (' . $key->level_sort . ') ' . $key->title . '</option>';
    //         }
    //     }

    //     $data2 = Subject::find($data->subject_id);

    //     //print_r($data2);

    //     $select_list2 = "";

    //     for ($i = 1; $i <= $data2->book; $i++) {
    //         if ($data->book_start == $i) {

    //             $select_list2 .= '<option value="' . $i . '" selected>' . $i . '</option>';
    //         } else {
    //             $select_list2 .= '<option value="' . $i . '" >' . $i . '</option>';
    //         }
    //     }

    //     $photo_att = '';
    //     $urla = url('/') . '/form_images/' . $data->signature;
    //     //$retu = $this->get_img_url($urla);
    //     //if ($this->URL_exists($retu)) {
    //     $photo_att .= "<div><a href=\"" . urldecode($urla) . "\" target=\"blank\"><img src=\"" . $urla . "\" height=\"100\">click to view</a></div>&nbsp;&nbsp;";

    //     $all = 0;
    //     $stdId = $data->student_id;
    //     $success = History::where('student_id',$stdId)->get();
    //     $his_succsss = count($success);

    //     $result_ = 0 ;
    //     // dd($his_succsss);


    //     return response()->json([
    //         'html' => $select_list, 'html2' => $select_list2, 'date' => $date_start, 'student' => $select_list_student, 'subject' => $select_list_subject, 'des' => $data->description, 'stime' => $data->stime, 'etime' => $data->etime, 'signature' => $photo_att, 'toa' => $his_succsss
    //     ]);
    // }

    public function edit($id)
    {
        $this->check_permission();
        $data = History::find($id);
        // $stdId = $data->student_id;
        // dd($stdId);

        $select_list_subject = "";
        $subject = Subject::orderBy("id", "asc")->get();
        foreach ($subject as $keysu) {
            if ($data->subject_id == $keysu->id) {

                $select_list_subject .= '<option value="' . $keysu->id . '" selected>' . $keysu->title . '</option>';
            } else {
                $select_list_subject .= '<option value="' . $keysu->id . '" >' . $keysu->title . '</option>';
            }
        }

        $select_list_student = "";
        $student = Student::orderBy("id", "asc")->get();
        foreach ($student as $keys) {
            if ($data->student_id == $keys->id) {

                $select_list_student .= '<option value="' . $keys->id . '" selected>' . $keys->code . ' ' . $keys->first_name . ' ' . $keys->last_name . '</option>';
            } else {
                $select_list_student .= '<option value="' . $keys->id . '" >' . $keys->code . ' ' . $keys->first_name . ' ' . $keys->last_name . '</option>';
            }
        }

        $date_start = $data->datetime;


        $data1 = Level::where('subject', $data->subject_id)->orderBy("id", "asc")->get();

        $select_list = "";

        foreach ($data1 as $key) {

            if ($data->level_id == $key->id) {

                $select_list .= '<option value="' . $key->id . '" selected> (' . $key->level_sort . ') ' . $key->title . '</option>';
            } else {
                $select_list .= '<option value="' . $key->id . '" > (' . $key->level_sort . ') ' . $key->title . '</option>';
            }
        }

        $data2 = Subject::find($data->subject_id);

        //print_r($data2);

        $select_list2 = "";

        for ($i = 1; $i <= $data2->book; $i++) {
            if ($data->book_start == $i) {

                $select_list2 .= '<option value="' . $i . '" selected>' . $i . '</option>';
            } else {
                $select_list2 .= '<option value="' . $i . '" >' . $i . '</option>';
            }
        }
        $photo_att = '';
        $urla = url('/') . '/form_images/' . $data->signature;
        //$retu = $this->get_img_url($urla);
        //if ($this->URL_exists($retu)) {
        $photo_att .= "<div><a href=\"" . urldecode($urla) . "\" target=\"blank\"><img src=\"" . $urla . "\" height=\"100\">click to view</a></div>&nbsp;&nbsp;";

        return response()->json([
            'html' => $select_list,
            'html2' => $select_list2,
            'date' => $date_start,
            'student' => $select_list_student,
            'subject' => $select_list_subject,
            'des' => $data->description,
            'stime' => $data->stime,
            'etime' => $data->etime,
            'signature' => $photo_att,
            'user_id' => $data->user_id
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'student' => 'required',
            'subject' => 'required',
            //'level' => 'required',
            'date' => 'required',
            'stime' => 'required',
            'etime' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $course = [
            'student_id' => $request->get('student'),
            'subject_id' => $request->get('subject'),
            'subject_id' => $request->get('subject'),
            //'level_id' => $request->get('level'),
            'user_id' => $request->get('user_id'), // Update teacher
            'datetime' => $request->get('date'),
            'stime' => $request->get('stime'),
            'etime' => $request->get('etime'),
            'description' => $request->get('des'),
        ];
        //echo $id;
        //print_r($course);

        $ret = History::find($id)->update($course);


        return response()->json(['success' => 'Histories Save Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $this->check_permission();
        History::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {
        $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            History::find($arr_del[$xx])->delete();
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        return redirect('/histories')->with('success', 'Deleted Successfully');
    }
}
