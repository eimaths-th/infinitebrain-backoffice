<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Student;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{


    public function check_permission()
    {
        if (Auth::check()) {
            if (Auth::user()->level == 1 || Auth::user()->level == 2) {
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

            return datatables()->of($course)
                ->editColumn('student_name', function (History $course) {
                    return $course->student_name . ' ' . $course->student_lastname . ' (' . $course->student_nickname . ') ';
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->histories_id . '" class="flat" name="table_records[]" value="' . $row->histories_id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->histories_id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->histories_id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }

        //get subject
        $subject = Subject::orderBy("id", "asc")->get();

        //get student
        $student = Student::orderBy("first_name", "asc")->get();

        return view('histories.index')->with(['data' => $subject])->with(['data2' => $student]);
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
        // Getting values from the blade template form
        $course = new History([
            'student_id' => $request->get('student'),
            'subject_id' => $request->get('subject'),
            //'level_id' => $request->get('level'),
            'datetime' => $request->get('date'),
            'stime' => $request->get('stime'),
            'etime' => $request->get('etime'),
            'description' => $request->get('des'),
        ]);
        $course->save();
        return response()->json(['success' => 'Histories Add successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function show(History $history)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\History  $history
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->check_permission();
        $data = History::find($id);

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
            'html' => $select_list, 'html2' => $select_list2, 'date' => $date_start, 'student' => $select_list_student, 'subject' => $select_list_subject, 'des' => $data->description, 'stime' => $data->stime, 'etime' => $data->etime, 'signature' => $photo_att
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
            //'level_id' => $request->get('level'),
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
