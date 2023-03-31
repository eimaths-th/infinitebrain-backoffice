<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Student;
use App\Models\ParentRegister;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FileUpload;
// use App\Models\Receipt_RunningNumber;
use App\Models\RunningNumber;
use App\Models\Parent_RunningNumber;

class ParentRegisterController extends Controller
{

    public function register()
    {
        return view("register.parentregister");
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        // $this->check_permission();
        if ($request->ajax()) {
            //sleep(2);
            $parents = ParentRegister::orderBy("code", "asc")
                ->get();
            return datatables()->of($parents)
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

        return view('parents.parents');
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
        $validator =  Validator::make($request->all(), [
            'email' => 'required|email|unique:parent',
            // 'password' => [
            //     'required',
            //     'string',
            //     'regex:/^[a-zA-Z0-9 ]+$/',
            //     'min:8',            
               
            // ],
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6',
            'parent' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'postcode' => 'required|string',
            // 'firstname' => 'required|string',
            // 'lastname' => 'required|string',
            // 'nickname' => 'required|string',
            // 'birthdate' => 'required|string',
            // 'schoolname' => 'required|string',
            // 'grade' => 'required|string',

        ]);

        if ($validator->fails()) {
            //return response()->json(['errors' => $validator->errors()->all()]);
            return back()->withErrors(['errors' => $validator->errors()->all()]);
        }
        $filesa = $request->get('imgFiles');
        // $filesb = $request->get('imgFiles2');

        // print_r($filesa);
        // print_r($filesb);
        // exit();
        $fileas = [];
        foreach ($filesa as $filea) {
            //echo $filea;
            $oldfile = FileUpload::where('oldname', $filea)->get();
            $fileas[] .= $oldfile[0]->filename;
            // $file2 = $oldfile[1]->filename ;
            File::move(public_path() . '/form_images/' . $oldfile[0]->filename, public_path() . '/receipt_form_images/' . $oldfile[0]->filename);
            FileUpload::where('filename', $oldfile[0]->filename)->delete();
        }
        // $fileas = rtrim($fileas, "|");

        //         print_r($fileas) ;
        // //         echo $filea;

        //         exit();

        // $rnumber = RunningNumber::generate(new Register,'code',4,"");

        $rpnumber = Parent_RunningNumber::generate();
        
        $registerForm = new ParentRegister([
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'name_parent' => $request->get('parent'),
            'mobile' => $request->get('phone'),
            'street_address' => $request->get('address'),
            'postcode' => $request->get('postcode'),
            'code' => "$rpnumber",

        ]);
        
        $registerForm->save();
        $pid = $registerForm->id;

        // echo $request->get('val');
        // exit();
        // $error = 1 ;
        for ($x = 0; $x < $request->get('val'); $x++) {
            // $rnumber1 = RunningNumber::generate(new Student,'code',4,"");
            $rcnumber = RunningNumber::generate();
            $data = [];
            $data[] = [
                'code' => $rcnumber,
                'first_name' => $request->get('firstname')[$x],
                'last_name' => $request->get('lastname')[$x],
                'nick_name' => $request->get('nickname')[$x],
                'birth_date' => $request->get('birthdate')[$x],
                'school_name' => $request->get('schoolname')[$x],
                'grade' => $request->get('grade')[$x],
                'mobile' => "",
                'photo_att' => $fileas[$x],
                'sex' => $request->get('sex')[$x],
                'pid' => $pid,
            ];
            // $data = [];

            // print_r($data);
            Student::upsert($data, [
                'code', 'first_name', 'last_name', 'nick_name', 'birth_data', 'school_name', 'grade', 'photo_att', 'sex', 'pid'
            ]);
        }
        // if(count($data)>0){
        //     $error = '';
        // }

        // $student = new Student([
        //     'code'=> $rnumber1 ,
        //     'first_name' => $request->get('firstname'),
        //     'last_name' => $request->get('lastname'),
        //     'nick_name' => $request->get('nickname'),
        //     'birth_date' => $request->get('birthdate'),
        //     'school_name' => $request->get('schoolname'),
        //     'grade' => $request->get('grade'),
        //     'mobile'=> "" ,
        //     'photo_att' =>"",
        // ]);
        // $student->save();
        return redirect('/parent-register/complete');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Register  $register
     * @return \Illuminate\Http\Response
     */
    public function show(ParentRegister $register)
    {
        //
        return view("register.complete");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Register  $register
     * @return \Illuminate\Http\Response
     */
 

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
        $data = ParentRegister::find($id);


        $html = '<div class="form-group">
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
