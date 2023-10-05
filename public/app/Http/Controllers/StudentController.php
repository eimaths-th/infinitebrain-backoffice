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
use App\Models\Receipt;
use App\Models\Receipt_School;
use App\Models\SchoolPrice;
use App\Models\Statuslog;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{

    public function check_permission()
    {
        if (Auth::check()) {
            if (Auth::user()->level == 1 || Auth::user()->level == 2 || Auth::user()->level == 0) {
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
        //test

        $this->check_permission();

        $newStudentCode = Student::generateNextStudentCode();



        // $month = date('m');
        // // $month = "10";
        // // $month1 = "09";
        // $year = date('Y');

        // $latestStatusCounts = Statuslog::select('group', 'student_id', 'status')
        //     ->whereIn('id', function ($query) {
        //         $query->select(DB::raw('MAX(id)'))
        //             ->from('status_log')
        //             ->groupBy('group', 'student_id');
        //     })
        //     ->whereYear('status_date', $year)
        //     ->whereMonth('status_date', $month)
        //     ->get();

        // $groupStatusCounts = [];

        // foreach ($latestStatusCounts as $record) {

        //     $group = $record->group;
        //     $status = $record->status;

        //     if (!isset($groupStatusCounts[$group])) {
        //         $groupStatusCounts[$group] = [];
        //     }

        //     if (!isset($groupStatusCounts[$group][$status])) {
        //         $groupStatusCounts[$group][$status] = 0;
        //     }
        //     $groupStatusCounts[$group][$status]++;
        // }
        // $cstdlog = Statuslog::all()->count();
        // dd($latestStatusCounts, $groupStatusCounts, $cstdlog);



        // dd($i);

        if ($request->ajax()) {
            // sleep(2);
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

                // ->editColumn('status', function ($row) {
                //     if ($row->status == 'ไม่พบใบเสร็จ' || $row->status == 'พ้นสภาพนักเรียน') {
                //         # code...
                //         $class = 'danger';
                //     } elseif ($row->status == 'กำลังยุติการการเรียน') {
                //         # code...
                //         $class = 'warning';
                //     } elseif ($row->status == 'กำลังเรียนอยู่') {
                //         # code...
                //         $class = 'success';
                //     }
                //     return '<span class="text-' . $class . '">' . $row->status . '</span>';
                // })
                ->addColumn('action', function ($row) {
                    $html = '<a href="' . route("students.show", $row->id) . '" class="btn btn-xs btn-primary btn-edit" data-id=""><i class="fa fa-eye"></i> View </a> ';
                    $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->rawColumns(['checkbox', 'photo', 'status', 'action'])->toJson();
        }
        $parent = ParentRegister::orderBy("id", "asc")->get();
        return view('students.index', [
            'newStudentCode' => $newStudentCode
        ])->with(['data4' => $parent]);
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
        // dd($request);
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            // 'code' => 'required|string|max:5|unique:students',
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'nick_name' => 'required|string|max:255|unique:students',
            'mobile' => 'required|string|max:10',
            'parent' => 'required|integer',
        ], [
            'code.unique' => 'Code: ' . $request->code . ' ของนักเรียน มีในระบบแล้ว',
            'nick_name.unique' => 'ชื่อเล่น ' . $request->nick_name . ' ของนักเรียนมีในระบบแล้ว'
        ]);

        $code = Student::all();
        $last  = $code->last()->code;
        $newStudentCode = Student::generateNextStudentCode();
        // dd($newStudentCode);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        // Getting values from the blade template form
        $student = new Student([
            'code' => $newStudentCode,
            'first_name' => $request->get('name'),
            'last_name' => $request->get('lastname'),
            'nick_name' => $request->get('nick_name'),
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
        // $rec = Receipt_School::where('pid',$stdId)->get();

        $test = null;
        $i = 0;
        foreach ($rec as $rec) {
            $i++;
            if (!empty($rec->school_pice->qty)) {
                $id = $rec->id;
            }
            $test .= $rec->school_pice->qty;
        }
        // dd($rec->id ,$stdId,$test,$i,$id);
        $course = 0;

        if (!empty($rec->id)) {
            $school_pice = SchoolPrice::where('rid', $id)->get();
            // dd($school_pice);
            $startcourse = $school_pice->first()->period;
            $qty = 0;
            $num_month = null;
            foreach ($school_pice as $school_pice) {
                $school = (int)($school_pice->qty);
                if ($school_pice->period == $startcourse) {
                    # code...
                    $num_month = $school;
                    $course++;
                } else {
                    # code...
                    $num_month += $school;
                }
                $qty += $school;
            }
            // dd($qty);
        } else {
            $qty = (int) ($data->rec_school->school_pice->qty ?? null);
        }

        $book = $data->rec_school->school_pice->book ?? null;
        $qty1 = [];
        $ii = 0;
        $bonus = 0;
        if (!empty($qty)) {

            if ($num_month > 11) {
                $bonus = 12 * $course;
                $all = ($qty *  8) + $bonus ?? 'school_pice';
                $remain = $all - $his_succsss;
            } elseif ($num_month > 5) {
                $bonus = 5 * $course;
                $all = ($qty *  8) + $bonus ?? 'school_pice';
                $remain = $all - $his_succsss;
            } elseif ($num_month > 3) {
                $bonus = 3 * $course;
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
                                <div class="form-group text-success">
                                    <label for="ETime">จำนวนเข้าเรียนทั้งหมด : ' . $all . ' ครั้ง</label>
                                    <div class="row">

                                        <div class="col-lg-12">
                                                <label for="ETime">' . 'สมัคร : ' . $num_month . ' เดือน โบนัส : ' . $bonus . ' ครั้ง</label>
                                        </div>

                                    </div>

                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group text-center text-danger">
                                    <label for="ETime">เข้าเรียนแล้ว : ' . $his_succsss . ' ครั้ง</label>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group text-center  text-warning">
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
    public function show($id)
    {
        //
        $this->check_permission();
        // $data = Student::find($id);
        // $stdId = $data->id;

        // $success = History::where('student_id', $stdId)->get();
        // // $his_succsss = count($success);
        // $rec = Receipt_School::where('pid', 'LIKE', '%' . $stdId . '%')->get();

        // $startcourse = null;

        // $crec = count($rec);
        // if ($crec > 0) {
        //     if ($data->pid == (int)($rec->first()->name_parent)) {
        //         $parentCheck = '';
        //         $rec_ids = [];
        //         $rec = Receipt_School::where('name_parent', $data->pid)->get();

        //         foreach ($rec as $record) {
        //             if (!empty($record->school_pice->qty)) {
        //                 $rec_ids[] = $record->id;
        //             }
        //         }

        //         if (count($rec_ids) > 1) {
        //             $school_pice = SchoolPrice::whereIn('rid', $rec_ids)->get();
        //             // dd($rec_ids, $school_pice);
        //         } else {
        //             $school_pice = SchoolPrice::whereIn('rid', $rec_ids)->get();
        //             // dd($rec_ids, $school_pice);
        //         }
        //         //



        //         //
        //         $startcourse = $school_pice->first()->period ?? null;
        //         $period = $school_pice->last()->period;
        //         $parts = explode('-', $period);
        //         $lastTwoParts = array_slice($parts, -2);
        //         $end_date = implode('-', $lastTwoParts);
        //         // dd($school_pice, $result);

        //     } else {
        //         $school_pice = [];
        //         $end_date = '2999-01';
        //         $parentCheck = 'ผู้ปกครองไม่ตรงกัน';
        //     }
        // } else {
        //     $school_pice = [];
        //     $parentCheck = '';
        //     $end_date = '2999-01';
        // }
        // code 2
        $std = Student::find($id);

        $stdId = $std->id;

        $success = History::where('student_id', $stdId)->get();
        // $his_succsss = count($success);
        $rec = Receipt_School::whereRaw("FIND_IN_SET($stdId, REPLACE(pid, '|', ','))")->get();

        $startcourse = null;

        // $id_parent = ;
        // foreach ($rec as $parent) {

        //     if ($std->pid == $parent->name_parent) {

        //         $id_parent = (int)$parent->name_parent;
        //     }
        // }

        $crec = count($rec);

        if ($crec > 0) {

            $parentCheck = '';
            $rec_ids = [];
            $get_qty = [];
            foreach ($rec as $record) {
                $school_pice = SchoolPrice::where('rid', $record->id)->get();
                foreach ($school_pice as  $sp_qty) {
                    # code...
                    if ($sp_qty->qty !== null) {

                        $rec_ids[] = $sp_qty->id;
                    }
                }
            }
            $school_pice = SchoolPrice::whereIn('id', $rec_ids)->get();

            // dd($rec,$rec_ids, $school_pice);

            $count_rec_ids = count($rec_ids);



            if ($count_rec_ids > 1) {
                // $school_pice = SchoolPrice::whereIn('rid', $rec_ids)->get();


                if (count($school_pice) > 1) {
                    $startcourse = $school_pice->first()->period;
                    $period = $school_pice->last()->period;
                } else {

                    $startcourse = $school_pice->first()->period;
                    $period = $school_pice->first()->period;
                    // dd($startcourse,$school_pice,$period);
                    // dd($id_parent, $std->pid, $stdId, $std, $rec, $rec_ids, $school_pice);
                    // dd($period);
                }
                // dd($rec_ids, $school_pice,$period);

                $parts = explode('-', $period);
                $lastTwoParts = array_slice($parts, -2);
                $end_date = implode('-', $lastTwoParts);
                $currentReceiptEndDate = Carbon::parse($end_date);
            } elseif ($count_rec_ids > 0) {
                # code...
                // $school_pice = SchoolPrice::whereIn('rid', $rec_ids)->get();
                // dd($school_pice);

                if (count($school_pice) > 1) {
                    $startcourse = $school_pice->first()->period;
                    $period = $school_pice->last()->period;
                } else {

                    $startcourse = $school_pice->first()->period;
                    $period = $school_pice->first()->period;
                }

                $parts = explode('-', $period);
                $lastTwoParts = array_slice($parts, -2);
                $end_date = implode('-', $lastTwoParts);

                // dd($period,$end_date,$current_Month);


                $currentReceiptEndDate = Carbon::parse($end_date);
            } else {
                // dd($rec_ids, $school_pice);
                $school_pice = [];
                $end_date = '2999-01';
                // $parentCheck = 'ผู้ปกครองไม่ตรงกัน';
            }
            // dd($id_parent, $std->pid, $stdId, $std, $rec,$rec_ids,$school_pice);
            $parentCheck = '';

            // }
            // else {
            //     $school_pice = [];
            //     $end_date = '2999-01';
            //     $parentCheck = 'ผู้ปกครองไม่ตรงกัน';
            // }
        } else {
            $school_pice = [];
            $parentCheck = '';
            $end_date = '2999-01';
        }

        //start test
        // dd($rec_ids, $school_pice,$end_date);

        // dd($end_date);
        // dd($id_parent, $std->pid, $stdId, $std, $rec,$rec_ids,$school_pice);


        // $tset_std = Student::find($id);

        // $rec_test = Receipt_School::where('pid', $tset_std->id)->get();
        // $tset_receipt = $tset_std->rec_school;
        // $arr_test_rec = [];
        // foreach ($tset_receipt as $etset_receipt) {
        //     $arr_test_rec[] = $etset_receipt->id;
        //     # code...
        // }

        // $scp = SchoolPrice::whereIn('rid', $arr_test_rec)->get();

        // $scp_id = [];
        // foreach ($scp as $scps) {

        //     if ($scps->qty) {
        //         # code...
        //         $scp_id[] = $scps->id;
        //     }
        //     # code...
        // }

        // dd($tset_std->id,$tset_receipt,$rec_test,$arr_test_rec,$scp,$scp_id);

        //เทสโค้ด
        $tset_std = Student::find($id);

        $rec_test = Receipt_School::where('pid', $tset_std->id)->get();

        $arr_test_rec = $rec_test->pluck('id')->toArray();

        $scp = SchoolPrice::whereIn('rid', $arr_test_rec)->where('qty', '>', 0)->get();
        $scp_ids = $scp->pluck('id')->toArray();

        // Output debug information
        // dd(
        //     'Student ID:', $tset_std->id,
        //     'Receipts:', $rec_test,
        //     'Receipt IDs:', $arr_test_rec,
        //     'School Prices:', $scp,
        //     'School Prices ids :', $scp_ids
        // );

        //end test

        return view('students.overview_std', [
            'std'           => $std,
            'end_date'    => $end_date,
            'school_pice'   => $school_pice,
            // 'success'       => $success,
            'parentCheck'       => $parentCheck,
            'startcourse'       => $startcourse,
            // 'school_pice'   => $groupedSchoolPice,
            // 'his_succsss'   => $his_succsss,


        ]);
    }
    public function std_his($sub_id, $std_id)
    {

        $std = Student::find($std_id);
        $history = History::where('student_id', $std->id)
            ->where('subject_id', $sub_id)
            ->get();
        // $test = $std->rec_school->id;
        // dd( $sub_id, $std_id, $history);
        $class = $history->first()->subject->title ?? ' ';
        if ($class == ' ') {
            $classs = Subject::find($sub_id);
            $class = $classs->title;
        } else {
        }
        // dd($class);
        $html = ' ';
        $date_history = '';
        $time_history = '';
        foreach ($history as $key => $hist) {
            # code...
            $date_history .= '<label for="date"> ' . $hist->datetime . ' </label><br>';
            $time_history .= '<label for="date"> ' . $hist->full_time . ' </label><br>';
        }
        $select_html = 'welcome';
        return response()->json(['html' => $html, 'parent' => $select_html, 'classname' => $class, 'dateText' => $date_history, 'timeText' => $time_history]);
        // echo "test";
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
        $select_reaon = '';
        if ($data->reason == null || $data->reason == '') {
            # code...
            $select_reaon .= '

        <option value=""selected>เลือกเหตุผลอื่นๆ</option>
        <option value="รอชำระ">รอชำระ</option>
        <option value="พักการเรียน">พักการเรียน</option>
        <option value="เรียนปกติ">เรียนปกติ</option>

        ';
        } else {
            # code...
            $select_reaon .= '
                                <option value="">เลือกเหตุผลอื่นๆ</option>
                                 <option value="' . $data->reason . '" selected>' . $data->reason . '</option>
                                <option value="รอชำระ">รอชำระ</option>
                                <option value="พักการเรียน">พักการเรียน</option>
                                <option value="เรียนปกติ">เรียนปกติ</option>

        ';
        }


        return response()->json(['html' => $html, 'parent' => $select_html, 'select_reaon' => $select_reaon,]);
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
        // dd($request);
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
        // $input = $request->all();
        // dd($input);
        Student::find($id)->update($request->all());
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
