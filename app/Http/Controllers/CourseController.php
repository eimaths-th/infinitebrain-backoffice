<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Student;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{

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
        //
        if ($request->ajax()) {
            //sleep(2);
            $course = Course::select(
                "courses.id as course_id",
                "levels.title as student_level",
                "subjects.title as student_subject",
                "students.code as student_code",
                "students.name as student_name",
                "students.lastname as student_lastname",
                "courses.book_start as book_start",
                "courses.date_start as date_start",
            )
                ->join("students", "students.id", "=", "courses.student_id")
                ->join("subjects", "subjects.id", "=", "courses.subject_id")
                ->join("levels", "levels.id", "=", "courses.level_id")

                //->where('levels.subject', $request->id)
                ->orderBy("courses.id", "desc")
                ->get();
            //print_r($levels);
            //exit();
            return datatables()->of($course)
                ->editColumn('student_name', function (Course $course) {
                    return $course->student_name . ' ' . $course->student_lastname;
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->course_id . '" class="flat" name="table_records[]" value="' . $row->course_id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="course/show/' . $row->course_id . '" class="btn btn-xs btn-success btn-view" id="ViewData" data-id="' . $row->course_id . '"><i class="fa fa-eye"></i> View Chart</a> ';
                    $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->course_id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->course_id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }

        //get subject
        $subject = Subject::orderBy("id", "asc")->get();

        //get student
        $student = Student::orderBy("name", "asc")->get();
        return view('course.index')->with(['data' => $subject])->with(['data2' => $student]);
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
        //print_r($request);
        //exit();
        $validator =  Validator::make($request->all(), [
            'student' => 'required',
            'subject' => 'required',
            'level' => 'required',
            'book' => 'required',
            'date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        // Getting values from the blade template form
        $course = new Course([
            'student_id' => $request->get('student'),
            'subject_id' => $request->get('subject'),
            'level_id' => $request->get('level'),
            'book_start' => $request->get('book'),
            'date_start' => $request->get('date'),
        ]);
        $course->save();
        return response()->json(['success' => 'Course Add successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $course = Course::select(
            "courses.id as course_id",
            "levels.title as student_level",
            "subjects.id as subject_id",
            "subjects.title as student_subject",
            "students.code as student_code",
            "students.name as student_name",
            "students.lastname as student_lastname",
            "courses.book_start as book_start",
            "courses.date_start as date_start",
            "students.grade as student_grade",
        )
            ->join("students", "students.id", "=", "courses.student_id")
            ->join("subjects", "subjects.id", "=", "courses.subject_id")
            ->join("levels", "levels.id", "=", "courses.level_id")

            ->where('courses.id', $id)
            //->orderBy("courses.id", "desc")
            ->get();

        $levels = Level::where('subject', $course[0]->subject_id)->orderBy("level_sort", "desc")->get();

        //print_r($level);



        $start_year = "2022";
        $last_year = $start_year + 1;
        $start_month = "9";
        $span1 = (12 - $start_month) + 1;
        $span2 = 12 - $span1;
        $key = "start_year";
        $key2 = "last_year";
        $year_span = array($$key => $span1, $$key2 => $span2);

        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = $start_month;
            $start_month++;
            if ($start_month == 13) {
                $start_month = 1;
            }
        }

        $tb_level = "";
        foreach ($levels as $key) {
            if ($key->id == $course[0]->level_id) {
                
            }
            $tb_level .= '<tr>
                        <td>' . $key->title . '</td>
                        <td align="center">' . $key->level_sort . '</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>';
        }

        return view('course.view')->with(['year' => $start_year])->with(['last_year' => $last_year])
            ->with(['start_month' => $start_month])
            ->with(['months' => $months])
            ->with(['year_span' => $year_span])
            ->with(['levels' => $tb_level])
            ->with(['course' => $course]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $data = Course::find($id);

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

                $select_list_student .= '<option value="' . $keys->id . '" selected>' . $keys->name . ' ' . $keys->lastname . '</option>';
            } else {
                $select_list_student .= '<option value="' . $keys->id . '" >' . $keys->name . ' ' . $keys->lastname . '</option>';
            }
        }

        $date_start = $data->date_start;


        $data1 = Level::where('subject', $data->subject_id)->orderBy("id", "asc")->get();

        $select_list = "";

        foreach ($data1 as $key) {

            if ($data->level_id == $key->id) {

                $select_list .= '<option value="' . $key->id . '" selected>' . $key->title . '</option>';
            } else {
                $select_list .= '<option value="' . $key->id . '" >' . $key->title . '</option>';
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


        return response()->json([
            'html' => $select_list, 'html2' => $select_list2, 'date' => $date_start, 'student' => $select_list_student, 'subject' => $select_list_subject
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $validator =  Validator::make($request->all(), [
            'student' => 'required',
            'subject' => 'required',
            'level' => 'required',
            'book' => 'required',
            'date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $course = [
            'student_id' => $request->get('student'),
            'subject_id' => $request->get('subject'),
            'level_id' => $request->get('level'),
            'book_start' => $request->get('book'),
            'date_start' => $request->get('date'),
        ];
        //echo $id;
        //print_r($course);

        $ret = Course::find($id)->update($course);


        return response()->json(['success' => 'Course Save Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        Course::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Course::find($arr_del[$xx])->delete();
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        return redirect('/course')->with('success', 'Deleted Successfully');;
    }
}
