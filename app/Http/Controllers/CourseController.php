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
//use Auth;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{

    public function check_permission()
    {
        if (Auth::check()) {
            if (Auth::user()->level == 1 || Auth::user()->level == 2) {
                
            } else {
                abort(403, 'Unauthorized action.');
            }
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
        //
        $this->check_permission();

        if ($request->ajax()) {
            //sleep(2);
            $course = Course::select(
                "courses.id as course_id",
                "levels.title as student_level",
                "subjects.title as student_subject",
                "students.code as student_code",
                "students.name as student_name",
                "students.lastname as student_lastname",
                "students.nickname as student_nickname",
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
                    $html = '<a href="course/show/view/en/' . $row->course_id . '" class="btn btn-xs btn-success btn-view" id="ViewData" data-id="' . $row->course_id . '"><i class="fa fa-eye"></i> View Chart</a> ';
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
        $this->check_permission();
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
    public function show($type, $lang, $id)
    {
        //
        //echo $type;
        //exit();
        $this->check_permission();
        $course = Course::select(
            "courses.id as course_id",
            "levels.title as student_level",
            "levels.title_th as student_level_th",
            "levels.id as level_id",
            "levels.book_per_level as book",
            "subjects.id as subject_id",
            "subjects.book_per_month as subject_book",
            "subjects.title as student_subject",
            "students.code as student_code",
            "students.name as student_name",
            "students.lastname as student_lastname",
            "students.nickname as student_nickname",
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

        $levels = Level::where('subject', $course[0]->subject_id)->orderBy("level_sort", "asc")->get();

        $levels_start = Level::where('id', $course[0]->level_id)->get();

        //print_r($levels_start);
        $english1 = array("","Pre - A","A","B","B","D","E","F","G","H","I");
        $english2 = array("","5","6","7","8");

        $start_date = explode("-", $course[0]->date_start);

        $start_month = (int)$start_date[1];
        $start_year = $start_date[0];
        if ($start_month == 1) {
            $last_year = $start_year;
            $span1 = 12;
            $span2 = 12;
        } else {
            $span1 = (12 - $start_month) + 1;
            $span2 = 12 - $span1;
            $last_year = $start_year + 1;
        }

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

        //echo $course[0]->level_id;
        //$course[0]->subject_book
        $start_sort = $levels_start[0]->level_sort;
        //exit();
        $tdhtml = array();
        $all_level = 1;
        $start_cell = 0;

        $level_progress_loop = 0;
        $level_progress_standard = $levels_start[0]->book_per_level / $course[0]->subject_book;
        $endprogress = 0;
        $last_per = 0;
        $div_right = "";
        $bar_percent = "";
        foreach ($levels as $key) {
            //if ($key->id == $course[0]->level_id) {
            //echo $key->level_sort;
            if ($lang == "th") {
                $level_title = $key->title_th;
            } else if ($lang == "en") {
                $level_title = $key->title;
            }
           
             if ($course[0]->subject_id==4) {
                $level_sort = $english1[$key->level_sort];
             } elseif ($course[0]->subject_id==5) {
                $level_sort = $english2[$key->level_sort];
             } else {
                $level_sort = $key->level_sort;
             }

            $tdhtml[$key->level_sort] = "";
            $tdhtml[$key->level_sort] .= "<tr>
                <td width=\"35%\"><font size=\"2\">" . $level_title . "</font></td>
                <td width=\"100\" align=\"center\">" . $level_sort . "</td>";

            //start level
            if ($key->level_sort >= $start_sort) {
                if ($all_level == $start_sort) {
                    if ($course[0]->book_start == 1) {
                        $level_progress = $levels_start[0]->book_per_level / $course[0]->subject_book;
                    } else {
                        $level_progress = (($levels_start[0]->book_per_level - $course[0]->book_start)) / $course[0]->subject_book;
                    }
                } else {
                    $level_progress = $levels_start[0]->book_per_level / $course[0]->subject_book;
                }

                if ($level_progress_loop == 0) {
                    $level_progress_loop = $level_progress;
                }
                //if ($level_progress_loop>10) {
                //$monthcell = round($level_progress_loop);
                //} else {
                $monthcell = ceil($level_progress_loop);
                //}


                for ($x = 1; $x <= 12; $x++) {

                    if ($x <= $monthcell) {
                        if ($level_progress_loop - $x > 0) {
                            $bar_percent = "100";
                        } else {
                            $bar_percent = (($level_progress_loop - $x) + 1) * 100;
                        }

                        if ($x < ceil($start_cell)) {
                            $tdhtml[$key->level_sort] .= "<td></td>";
                        } else {
                            if ($x == ceil($start_cell)) {
                                $bar_percent = 100 - $last_per;
                                $div_right = "align=\"right\"";
                            } else {
                                $div_right = "align=\"left\"";
                            }

                            if ($type == "view") {
                                $progress_view = "
                                <div class=\"progress-bar-xs mb-3 progress\" style=\"width: " . $bar_percent . "%;\">
                                <div class=\"progress-bar\" role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: 100%;\"></div>
                                </div>";
                            } else if ($type == "print") {

                                $progress_view = "
                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width: " . $bar_percent . "%; height: 8px; top: 0; margin-top: -9px; \" ><tr><td bgcolor=\"blue\"></td></tr></table>";
                            }

                            $tdhtml[$key->level_sort] .= "<td " . $div_right . " style=\"padding:0; margin: 0; margin-right: 0px; margin-left: 0px; \" width=\"80\">" . $progress_view . "
                            </td>";
                        }
                    } else {
                        $tdhtml[$key->level_sort] .= "<td width=\"80\"></td>";
                    }
                }


                $start_cell += $level_progress;
                $last_per = $bar_percent;
                $level_progress_loop += $level_progress_standard;
                $endprogress += $course[0]->book_per_month;
                //echo "<br>";
            } else {
                $tdhtml[$key->level_sort] = "";
                $tdhtml[$key->level_sort] .= "<tr>
                <td><font size=\"2\">" . $level_title . "</font></td>
                <td align=\"center\">" . $level_sort . "</td>";
                for ($x = 1; $x <= 12; $x++) {
                    $tdhtml[$key->level_sort] .= "<td></td>";
                }
            }


            //exit();

            $tdhtml[$key->level_sort] .= "</tr>";
            $all_level++;
        }

        //print_r($tdhtml);
        //echo $all_level;
        for ($ret = ($all_level - 1); $ret >= 1; $ret--) {
            $tb_level .= $tdhtml[$ret];
        }

        if ($type == "view") {
            $viewr = "course.view";
        } else if ($type == "print") {
            $viewr = "course.print";
        }

        return view($viewr)->with(['year' => $start_year])->with(['last_year' => $last_year])
            ->with(['start_month' => $start_month])
            ->with(['months' => $months])
            ->with(['year_span' => $year_span])
            ->with(['levels' => $tb_level])
            ->with(['course' => $course])
            ->with(['id' => $id])
            ->with(['lang' => $lang]);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function simulate(Request $request, $type, $lang)
    {
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'student' => 'required',
            'subject' => 'required',
            'level' => 'required',
            'book' => 'required',
            'date' => 'required',
            'grade' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        /*$course = [
            'student_id' => $request->get('student'),
            'subject_id' => $request->get('subject'),
            'level_id' => $request->get('level'),
            'book_start' => $request->get('book'),
            'date_start' => $request->get('date'),
        ];*/

        //print_r($course);
        //exit();


        $levels = Level::where('subject', $request->get('subject'))->orderBy("level_sort", "asc")->get();

        $levels_start = Level::where('id', $request->get('level'))->get();

        $course = Subject::where('id', $request->get('subject'))->get();

        //print_r($course);
        //$course[0]->book;
        //exit();

        $start_date = explode("-", $request->get('date'));

        $start_month = (int)$start_date[1];
        $start_year = $start_date[0];
        if ($start_month == 1) {
            $last_year = $start_year;
            $span1 = 12;
            $span2 = 12;
        } else {
            $span1 = (12 - $start_month) + 1;
            $span2 = 12 - $span1;
            $last_year = $start_year + 1;
        }

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

        //echo $request->get('level');
        //$course[0]->subject_book
        $start_sort = $levels_start[0]->level_sort;
        //exit();
        $tdhtml = array();
        $all_level = 1;
        $start_cell = 0;

        $level_progress_loop = 0;
        $level_progress_standard = $levels_start[0]->book_per_level / $course[0]->book_per_month;
        $endprogress = 0;
        $last_per = 0;
        $div_right = "";
        $bar_percent = "";

        $english1 = array("","Pre - A","A","B","B","D","E","F","G","H","I");
        $english2 = array("","5","6","7","8");

        foreach ($levels as $key) {
            //if ($key->id == $request->get('level')) {
            //echo $key->level_sort;
            if ($lang == "th") {
                $level_title = $key->title_th;
            } else if ($lang == "en") {
                $level_title = $key->title;
            }

            if ($course[0]->id==4) {
                $level_sort = $english1[$key->level_sort];
             } elseif ($course[0]->id==5) {
                $level_sort = $english2[$key->level_sort];
             } else {
                $level_sort = $key->level_sort;
             }


            $tdhtml[$key->level_sort] = "";
            $tdhtml[$key->level_sort] .= "<tr>
                <td width=\"35%\"><font size=\"2\">" . $level_title . "</font></td>
                <td width=\"100\" align=\"center\">" . $level_sort . "</td>";

            //start level
            if ($key->level_sort >= $start_sort) {
                if ($all_level == $start_sort) {
                    if ($course[0]->book_start == 1) {
                        $level_progress = $levels_start[0]->book_per_level / $course[0]->book_per_month;
                    } else {
                        $level_progress = (($levels_start[0]->book_per_level - $request->get('book'))) / $course[0]->book_per_month;
                    }
                } else {
                    $level_progress = $levels_start[0]->book_per_level / $course[0]->book_per_month;
                }

                if ($level_progress_loop == 0) {
                    $level_progress_loop = $level_progress;
                }
                //if ($level_progress_loop>10) {
                //$monthcell = round($level_progress_loop);
                //} else {
                $monthcell = ceil($level_progress_loop);
                //}


                for ($x = 1; $x <= 12; $x++) {

                    if ($x <= $monthcell) {
                        if ($level_progress_loop - $x > 0) {
                            $bar_percent = "100";
                        } else {
                            $bar_percent = (($level_progress_loop - $x) + 1) * 100;
                        }

                        if ($x < ceil($start_cell)) {
                            $tdhtml[$key->level_sort] .= "<td></td>";
                        } else {
                            if ($x == ceil($start_cell)) {
                                $bar_percent = 100 - $last_per;
                                $div_right = "align=\"right\"";
                            } else {
                                $div_right = "align=\"left\"";
                            }

                            if ($type == "view") {
                                $progress_view = "
                                <div class=\"progress-bar-xs mb-3 progress\" style=\"width: " . $bar_percent . "%;\">
                                <div class=\"progress-bar\" role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: 100%;\"></div>
                                </div>";
                            } else if ($type == "print") {

                                $progress_view = "
                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width: " . $bar_percent . "%; height: 8px; top: 0; margin-top: -9px; \" ><tr><td bgcolor=\"blue\"></td></tr></table>";
                            }

                            $tdhtml[$key->level_sort] .= "<td " . $div_right . " style=\"padding:0; margin: 0; margin-right: 0px; margin-left: 0px; \" width=\"80\">" . $progress_view . "
                            </td>";
                        }
                    } else {
                        $tdhtml[$key->level_sort] .= "<td width=\"80\"></td>";
                    }
                }


                $start_cell += $level_progress;
                $last_per = $bar_percent;
                $level_progress_loop += $level_progress_standard;
                $endprogress += $course[0]->book_per_month;
                //echo "<br>";
            } else {
                $tdhtml[$key->level_sort] = "";
                $tdhtml[$key->level_sort] .= "<tr>
                <td><font size=\"2\">" . $level_title . "</font></td>
                <td align=\"center\">" . $level_sort . "</td>";
                for ($x = 1; $x <= 12; $x++) {
                    $tdhtml[$key->level_sort] .= "<td></td>";
                }
            }


            //exit();

            $tdhtml[$key->level_sort] .= "</tr>";
            $all_level++;
        }

        //print_r($tdhtml);
        //echo $all_level;
        for ($ret = ($all_level - 1); $ret >= 1; $ret--) {
            $tb_level .= $tdhtml[$ret];
        }

        if ($type == "view") {
            $viewr = "course.simulate_view";
        } else if ($type == "print") {
            $viewr = "course.simulate_print";
        }

        return view($viewr)->with(['year' => $start_year])->with(['last_year' => $last_year])
            ->with(['start_month' => $start_month])
            ->with(['months' => $months])
            ->with(['year_span' => $year_span])
            ->with(['levels' => $tb_level])
            ->with(['course' => $course])
            ->with(['student' => $request->get('student')])
            ->with(['subject' => $request->get('subject')])
            ->with(['level' => $request->get('level')])
            ->with(['book' => $request->get('book')])
            ->with(['date' => $request->get('date')])
            ->with(['grade' => $request->get('grade')])
            ->with(['lang' => $lang]);
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
        $this->check_permission();
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
        $this->check_permission();
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
        $this->check_permission();
        Course::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {
        $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Course::find($arr_del[$xx])->delete();
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        return redirect('/course')->with('success', 'Deleted Successfully');
    }
}
