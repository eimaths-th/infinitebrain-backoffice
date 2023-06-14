<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\History;

use App\Models\ParentRegister;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
//use Auth;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportCustomer;
use App\Models\Receipt_School;
use App\Models\SchoolPrice;

class StudentController extends Controller
{

    public function check_permission()
    {
        if (Auth::check()) {
            if (Auth::user()->level == 1 || Auth::user()->level == 2) {
            } else {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
        }
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
            $students = Student::orderBy("code", "asc")
                ->get();
            return datatables()->of($students)
                /*->editColumn('subject', function (Student $Student) {
                    if ($Student->subject == 1) {
                        $ltext = "Math";
                    } else {
                        $ltext = "English";
                    }
                    return $ltext;
                })*/
                ->editColumn('photo', function (Student $Student) {

                    //if ($Student->photo_att !== "" || !is_null($Student->photo_att)) {
                    //$urla = url('/') . '/receipt_form_images/' . $Student->photo_att;
                    //} else {
                    $urla = url('/') . '/images/user2.png';
                    //}

                    $ltext = "<img src='" . $urla . "' width='34' height='34' class='rounded-circle'>";
                    return $ltext;
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-primary btn-edit" id="getViewData" data-id="' . $row->id . '"><i class="fa fa-eye"></i> View </a> ';
                    $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->rawColumns(['checkbox', 'photo', 'action'])->toJson();
        }
        $parent = ParentRegister::orderBy("id", "asc")->get();
        return view('students.index')->with(['data4' => $parent]);
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
        //return response()->json(['success'=>$request->all()]);
        //exit();
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'code' => 'required|string|max:5|unique:students',
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'mobile' => 'required|string|max:10',
            'parent' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        // Getting values from the blade template form
        $student = new Student([
            'code' => $request->get('code'),
            'first_name' => $request->get('name'),
            'last_name' => $request->get('lastname'),
            'nick_name' => $request->get('nickname'),
            'mobile' => $request->get('mobile'),
            'grade' => $request->get('grade'),
            'pid' => $request->get('parent'),
        ]);
        $student->save();
        return response()->json(['success' => 'Student Add successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function show(Student $student)
    {
        //
    }

    public function view($id)
    {
        //
        $this->check_permission();
        $data = Student::find($id);
        $parent = ParentRegister::orderBy("id", "asc")->get();

        $select_html = '';
        foreach ($parent as $key2) {
            if ($data->pid == $key2->id) {
                $select_html .= '<option value="' . $key2->id . '" selected>' . $key2->code . ' ' . $key2->name_parent . '</option>';
            } else {
                $select_html .= '<option value="' . $key2->id . '">' . $key2->code . ' ' . $key2->name_parent . '</option>';
            }
        }
        $all = 0;
        $remain = 0;
        $stdId = $data->id;
        $hissss = History::all();
        $success = History::where('student_id', $stdId)->get();

        $his_succsss = count($success);

        $rec = Receipt_School::where('pid', 'LIKE', '%' . $stdId . '%')->get();

        if (!empty($rec->first()->id)) {
            $school_pice = SchoolPrice::where('rid', $rec->first()->id)->get();
            $qty = 0;
            foreach ($school_pice as $school_pice) {
                $school = (int)($school_pice->qty);
                $qty += $school;
            }
        } else {
            $qty = (int) ($data->rec_school->school_pice->qty ?? null);
        }

        $book = $data->rec_school->school_pice->book ?? null;
        $qty1 = [];
        $ii = 0;
        $bonus = 0;
        if (!empty($qty)) {

            if ($qty > 11) {
                $bonus = 12;
                $all = ($qty *  8) + $bonus ?? 'school_pice';
                $remain = $all - $his_succsss;
            } elseif ($qty > 5) {
                $bonus = 5;
                $all = ($qty *  8) + $bonus ?? 'school_pice';
                $remain = $all - $his_succsss;
            } elseif ($qty > 3) {
                $bonus = 3;
                $all = ($qty *  8) + $bonus ?? 'school_pice';
                $remain = $all - $his_succsss;
            } else {
                $all = $qty *  8 ?? 'school_pice';
                $remain = $all - $his_succsss;
            }
        } elseif ($qty == null && $book !== null && $success !== null) {
            # code...
            $all = -1;
        } else {

            $all = '0';
        }

        if ($all > 0) {
            # code...
            $html = '
                <div class="form-group">
                    <label for="Name">รหัส:</label>
                    <input type="text" class="form-control" name="code" id="EditCode" value="' . $data->code . '">
                </div>

                <div class="form-group">
                    <label for="Name">ชื่อจริง:</label>
                    <input type="text" class="form-control" name="name" id="EditName" value="' . $data->first_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">นามสกุล:</label>
                    <input type="text" class="form-control" name="lastname" id="EditLastname" value="' . $data->last_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">ชื่อเล่น:</label>
                    <input type="text" class="form-control" name="nickname" id="EditNickname" value="' . $data->nick_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">เบอร์มือถือ:</label>
                    <input type="text" class="form-control" name="mobile" id="EditMobile" value="' . $data->mobile . '">
                </div>

                <div class="form-group mb-2">
                    <label for="Name">Grade:</label>
                    <input type="text" class="form-control" name="grade" id="Editgrade" value="' . $data->grade . '">
                </div>

                <div class="row ">
                            <div class="col-lg-4 text-center">
                                <div class="form-group">
                                    <label for="ETime">จำนวนเข้าเรียนทั้งหมด : ' . $all . ' ครั้ง</label>
                                    <div class="row">

                                        <div class="col-lg-12">
                                                <label for="ETime">' . 'สมัคร : ' . $qty . ' เดือน โบนัส : ' . $bonus . ' ครั้ง</label>
                                        </div>

                                    </div>

                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group text-center">
                                    <label for="ETime">เข้าเรียนแล้ว : ' . $his_succsss . ' ครั้ง</label>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group text-center">
                                    <label for="ETime">คงเหลือ :  ' . $remain . ' ครั้ง</label>
                                </div>
                            </div>

                </div>


            ';
            if ($his_succsss > 0) {
                $num =  0;
                $html .= '
                    <div class="form-group">
                    <label for="ETime"> รายวิชาที่เรียนไปแล้ว </label>
                    </div>
                    <div class="row">
                    ';
                foreach ($success as $key => $success) {
                    # code...
                    $num++;

                    $html .= '

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="ETime">' . $num . '. วิชา ' . $success->subject->title . ' เมื่อวันที่วันที่ : ' . $success->datetime . ' เวลา  : ' . $success->full_time . '</label>

                            </div>
                        </div>
                    </div>

                    ';
                }
            } else {
                $html .= '
                    <div class="form-group mt-2 ">
                    <label for="ETime" class="text-danger"> ยังไม่มีการเรียน</label>
                    </div>
                    <div class="row">
                    ';
            }
        } elseif ($all == -1) {
            $html = '
                <div class="form-group">
                    <label for="Name">รหัส:</label>
                    <input type="text" class="form-control" name="code" id="EditCode" value="' . $data->code . '">
                </div>

                <div class="form-group">
                    <label for="Name">ชื่อจริง:</label>
                    <input type="text" class="form-control" name="name" id="EditName" value="' . $data->first_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">นามสกุล:</label>
                    <input type="text" class="form-control" name="lastname" id="EditLastname" value="' . $data->last_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">ชื่อเล่น:</label>
                    <input type="text" class="form-control" name="nickname" id="EditNickname" value="' . $data->nick_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">เบอร์มือถือ:</label>
                    <input type="text" class="form-control" name="mobile" id="EditMobile" value="' . $data->mobile . '">
                </div>

                <div class="form-group">
                    <label for="Name">Grade:</label>
                    <input type="text" class="form-control" name="grade" id="Editgrade" value="' . $data->grade . '">
                </div>

                <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="ETime">จำนวนเข้าเรียนทั้งหมด :</label>
                                    <div class="row">
                                        <div class="col-lg-12">

                                                <label class="text-danger">ไม่พบใบเสร็จในระบบ </label>
                                        </div>

                                    </div>

                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="ETime">เข้าเรียนแล้ว :</label>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" id="toa" name=""
                                                required value="' . $his_succsss . '">
                                        </div>
                                        <div class="col-lg-6">
                                            <label for="ETime">ครั้ง </label>

                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="ETime">คงเหลือ :</label>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" id="Remain" name=""
                                                required value=" ' . $remain . '">
                                        </div>
                                        <div class="col-lg-6">
                                            <label for="ETime">ครั้ง</label>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>


            ';
            if ($his_succsss > 0) {
                $num =  0;
                $html .= '
                    <div class="form-group">
                    <label for="ETime"> รายวิชาที่เรียนไปแล้ว </label>
                    </div>
                    <div class="row">
                    ';
                foreach ($success as $key => $success) {
                    # code...
                    $num++;

                    $html .= '

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="ETime">' . $num . '. วิชา ' . $success->subject->title . ' เมื่อวันที่วันที่ : ' . $success->datetime . '</label>

                            </div>
                        </div>
                    </div>

                    ';
                }
            } else {
                $html .= '
                    <div class="form-group mt-3 ">
                    <label for="ETime" class="text-danger"> ยังไม่มีการเรียน</label>
                    </div>
                    <div class="row">
                    ';
            }
        } else {
            # code...
            $all = 'ไม่พบใบเสร้จ';

            $html = '
                <div class="form-group">
                    <label for="Name">Code:</label>
                    <input type="text" class="form-control" name="code" id="EditCode" value="' . $data->code . '">
                </div>

                <div class="form-group">
                    <label for="Name">Name:</label>
                    <input type="text" class="form-control" name="name" id="EditName" value="' . $data->first_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">Lastname:</label>
                    <input type="text" class="form-control" name="lastname" id="EditLastname" value="' . $data->last_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">Nickname:</label>
                    <input type="text" class="form-control" name="nickname" id="EditNickname" value="' . $data->nick_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">Mobile:</label>
                    <input type="text" class="form-control" name="mobile" id="EditMobile" value="' . $data->mobile . '">
                </div>

                <div class="form-group">
                    <label for="Name">Grade:</label>
                    <input type="text" class="form-control" name="grade" id="Editgrade" value="' . $data->grade . '">
                </div>

                <div class="form-group mt-3">
                    <label for="Name" class="text-danger"> ' . $all . ' </label>
                </div>

            ';
        }

        return response()->json(['html' => $html, 'parent' => $select_html]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $this->check_permission();
        $data = Student::find($id);
        $parent = ParentRegister::orderBy("id", "asc")->get();

        $select_html = '';
        foreach ($parent as $key2) {
            if ($data->pid == $key2->id) {
                $select_html .= '<option value="' . $key2->id . '" selected>' . $key2->code . ' ' . $key2->name_parent . '</option>';
            } else {
                $select_html .= '<option value="' . $key2->id . '">' . $key2->code . ' ' . $key2->name_parent . '</option>';
            }
        }

        $html = '
                <div class="form-group">
                    <label for="Name">Code:</label>
                    <input type="text" class="form-control" name="code" id="EditCode" value="' . $data->code . '">
                </div>

                <div class="form-group">
                    <label for="Name">Name:</label>
                    <input type="text" class="form-control" name="name" id="EditName" value="' . $data->first_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">Lastname:</label>
                    <input type="text" class="form-control" name="lastname" id="EditLastname" value="' . $data->last_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">Nickname:</label>
                    <input type="text" class="form-control" name="nickname" id="EditNickname" value="' . $data->nick_name . '">
                </div>

                <div class="form-group">
                    <label for="Name">Mobile:</label>
                    <input type="text" class="form-control" name="mobile" id="EditMobile" value="' . $data->mobile . '">
                </div>

                <div class="form-group">
                    <label for="Name">Grade:</label>
                    <input type="text" class="form-control" name="grade" id="Editgrade" value="' . $data->grade . '">
                </div>

            ';

        return response()->json(['html' => $html, 'parent' => $select_html]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'code' => 'required|string|max:5|unique:students,code,' . $id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nick_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:10',
            'pid' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        $input = $request->all();
        // dd($input);
        Student::find($id)->update($request->all());
        // $std;

        return response()->json(['success' => 'Student updated successfully']);
    }

    public function importExcel(Request $request)
    {
        if ($request->hasFile('import_file')) {
            //$path = $request->file('import_file')->getRealPath();

            $path = $request->file('import_file');

            //$request->file('file')->store('files')
            Excel::import(new ImportCustomer(), $path);
            return redirect()->back()->with('success', 'Import Data Complete');
        } else {
            return back()->with(['error' => 'Please Check your file, Something is wrong there.']);
        }

        return back()->with('error', 'Please Check your file, Something is wrong there.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $this->check_permission();
        Student::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {

        $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Student::find($arr_del[$xx])->delete();
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        return redirect('/students' . $request->get('id'))->with('success', 'Deleted Successfully');;
    }
}
