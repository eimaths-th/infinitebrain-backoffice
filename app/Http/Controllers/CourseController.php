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
                    return $course->student_name.' '.$course->student_lastname;
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
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
    public function show(Course $course)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function edit(Course $course)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        //
    }
}
