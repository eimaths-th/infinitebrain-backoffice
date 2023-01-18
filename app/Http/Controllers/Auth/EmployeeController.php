<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
//use Auth;
use Illuminate\Support\Facades\Auth;



class EmployeeController extends Controller
{

    public function check_permission()
    {
        if (Auth::check()) {
            if (Auth::user()->level !== 1) {
                abort(403, 'Unauthorized action.');
            }
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
        /*$levels = User::latest()->paginate(10);
        return view('users.index', compact('users'))
            ->with('i', (request()->input('page', 1) - 1) * 10);*/
        //sleep(3);
        $this->check_permission();

        if ($request->ajax()) {
            //sleep(2);
            $levels = User::orderBy("id", "desc")->get();
            return datatables()->of($levels)
                ->editColumn('level', function (User $level) {
                    if ($level->level == 1) {
                        $ltext = "Admin";
                    } elseif ($level->level == 2) {
                        $ltext = "Teacher";
                    } else {
                        $ltext = "Normal";
                    }

                    return $ltext;
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

        return view('users.index');
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        //return view('users.create'); // -> resources/views/users/create.blade.php
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation for required fields (and using some regex to validate our numeric value)

        //return response()->json(['success'=>$request->all()]);
        //exit();

        $this->check_permission();

        $validator =  Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'level' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        // Getting values from the blade template form
        $level = new User([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'level' => $request->get('level'),
        ]);
        $level->save();
        return response()->json(['success' => 'User Add successfully']);
        //return redirect('/users')->with('success', 'User saved.');   // -> resources/views/users/index.blade.php
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $this->check_permission();

        $data = User::find($id);
        if ($data->level == 1) {
            $aselected = 'selected';
        } else {
            $aselected = '';
        }

        if ($data->level == 2) {
            $bselected = 'selected';
        } else {
            $bselected = '';
        }

        if ($data->level == 3) {
            $dselected = 'selected';
        } else {
            $dselected = '';
        }


        if ($data->level == 0) {
            $cselected = 'selected';
        } else {
            $cselected = '';
        }

        $html = '<div class="form-group">
                    <label for="Name">Name:</label>
                    <input type="text" class="form-control" name="name" id="editName" value="' . $data->name . '">
                </div>
                <div class="form-group">
                    <label for="Name">Email:</label>
                    <input type="text" class="form-control" name="email" id="editEmail" value="' . $data->email . '">
                </div>
                    <div class="form-group">
                    <label for="Name">Password:</label>
                    <input type="password" class="form-control" name="password" required autocomplete="new-password"
                        id="EditPassword" >
                </div>
                <div class="form-group">
                    <label for="Name">Confirm Password:</label>
                    <input type="password" class="form-control" name="password_confirmation" id="EditPasswordC"
                        required autocomplete="new-password" >
                </div>
                <div class="form-group">
                    <label for="Name">Level:</label>
                    <select class="form-control" id="editLevel" name="level">
                     <option value="">Select Level</option>
                     <option value="0" ' . $cselected . '>Normal</option>
                     <option value="1" ' . $aselected . '>Admin</option>
                     <option value="2" ' . $bselected . '>Teacher</option>
                     <option value="3" ' . $dselected . '>Account</option>
                    </select>
                </div>';

        return response()->json(['html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        //print_r($request);
        //return response()->json(['success'=>$request->all()]);
        //exit();
        $this->check_permission();

            
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
        ];
        
        if ($request->get('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validator =  Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $level = [
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'level' => $request->get('level'),
            //'password' => Hash::make($request->get('password')),
        ];


        if ($request->get('password')) {
            $level['password'] = Hash::make($request->get('password'));
        }

        User::find($id)->update($level);

        return response()->json(['success' => 'User updated successfully']);
    }

    public function mupdate(Request $request, $id)
    {

       
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
        ];
        
        if ($request->get('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validator =  Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $level = [
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            //'password' => Hash::make($request->get('password')),
        ];


        if ($request->get('password')) {
            $level['password'] = Hash::make($request->get('password'));
        }

       
        User::find($id)->update($level);

        return response()->json(['success' => 'User updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $this->check_permission();
        User::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {
        $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            User::find($arr_del[$xx])->delete();
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        return redirect('/users')->with('success', 'Deleted Successfully');
    }
}
