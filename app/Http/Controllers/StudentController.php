<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
//use Auth;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
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
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }

        return view('students.index');
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
            'code' => 'required|string|max:4|unique:students',
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'mobile' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        // Getting values from the blade template form
        $student = new Student([
            'code' => $request->get('code'),
            'name' => $request->get('name'),
            'lastname' => $request->get('lastname'),
            'nickname' => $request->get('nickname'),
            'mobile' => $request->get('mobile'),
            'grade' => $request->get('grade'),
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


        $html = '<div class="form-group">
                    <label for="Name">Code:</label>
                    <input type="text" class="form-control" name="code" id="EditCode" value="' . $data->code . '">
                </div>
                <div class="form-group">
                    <label for="Name">Name:</label>
                    <input type="text" class="form-control" name="name" id="EditName" value="' . $data->name . '">
                </div>
                <div class="form-group">
                    <label for="Name">Lastname:</label>
                    <input type="text" class="form-control" name="lastname" id="EditLastname" value="' . $data->lastname . '">
                </div>
                <div class="form-group">
                    <label for="Name">Nickname:</label>
                    <input type="text" class="form-control" name="nickname" id="EditNickname" value="' . $data->nickname . '">
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

        return response()->json(['html' => $html]);
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
        //
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'code' => 'required|string|max:4|unique:students,code,' . $id,
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'mobile' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        Student::find($id)->update($request->all());

        return response()->json(['success' => 'Student updated successfully']);
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
