<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ParentRegister;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
//use Auth;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportCustomer;

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

                    if ($Student->photo_att !== "") {
                        $urla = url('/') . '/receipt_form_images/' . $Student->photo_att;
                    } else {
                        $urla = url('/') . '/images/user2.png';
                    }

                    $ltext = "<img src='" . $urla . "' width='34' height='34' class='rounded-circle'>";
                    return $ltext;
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
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
            'code' => 'required|string|max:4|unique:students',
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
            if ($data->pid==$key2->id) {
                $select_html.= '<option value="'.$key2->id.'" selected>'.$key2->code.' '.$key2->name_parent.'</option>';
            } else {
                $select_html.= '<option value="'.$key2->id.'">'.$key2->code.' '.$key2->name_parent.'</option>';
            }

        }




        $html = '<div class="form-group">
                    <label for="Parent">Name Parent:</label><br>
                    <select style="width: 100%;" class="parent select2 select2_single form-control" id="EditParent"
                        name="name_parent">
                        <option value="" selected>Select Parent</option>
                        '.$select_html.'
                    </select>
                </div>
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nick_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:10',
            'pid' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

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
