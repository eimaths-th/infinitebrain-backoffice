<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\LogActivity;
use App\Http\Controllers\Controller;
use App\Models\Recordtime;
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
            if (Auth::user()->level == 1 || Auth::user()->level == 0) {
            } else {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
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
                    } elseif ($level->level == 3) {
                        $ltext = "Account";
                    } elseif ($level->level == 0) {
                        $ltext = "Owner";
                    } elseif ($level->level == 4) {
                        $ltext = "Officer";
                    } elseif ($level->level == 7) {
                        $ltext = "Finance";
                    } elseif ($level->level == 8) {
                        $ltext = "Header";
                    } else {
                        $ltext = "Normal";
                    }

                    return $ltext;
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('status', function ($row) {
                    if ($row->is_disabled) {
                        return '<span class="badge bg-secondary">ปิดใช้งาน</span>';
                    }
                    return '<span class="badge bg-success">ใช้งาน</span>';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';

                    if ($row->is_disabled) {
                        $html .= '<a href="#" data-id="' . $row->id . '" class="btn btn-xs btn-success btn-toggle-disable"><i class="fa fa-check"></i> เปิด</a> ';
                    } else {
                        $html .= '<a href="#" data-id="' . $row->id . '" class="btn btn-xs btn-secondary btn-toggle-disable"><i class="fa fa-ban"></i> ปิด</a> ';
                    }

                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })
                ->rawColumns(['checkbox', 'status', 'action'])
                ->toJson();
        }

        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->check_permission();
        $user = null;
        $html = view('users.form_body', compact('user'))->render();
        return response()->json(['html' => $html]);
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
        \Illuminate\Support\Facades\Log::info("User Create Request Data: ", $request->all());

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'level' => 'required',
            // Optional fields don't strictly need regex validation unless specific format
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $data = $request->all();
        $data['password'] = Hash::make($request->get('password'));

        $user = User::create($data);

        LogActivity::modelEvent($user, 'user created', null, $user->toArray());

        return response()->json(['success' => 'User Add successfully']);
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
        $this->check_permission();
        $user = User::find($id);
        $html = view('users.form_body', compact('user'))->render();
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
        $this->check_permission();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'code' => 'required|string|max:255|unique:users,code,' . $id,
        ];

        if ($request->get('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $user = User::find($id);
        $old = $user->toArray();

        $data = $request->except(['password', 'password_confirmation', '_token', '_method']);

        if ($request->get('password')) {
            $data['password'] = Hash::make($request->get('password'));
        }

        $user->update($data);

        LogActivity::modelEvent($user, 'user updated', $old, $user->toArray());

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
        $user = User::find($id);
        $old = $user->toArray();
        Recordtime::where('user_id', $id)->delete();
        $user->delete();
        LogActivity::modelEvent($user, 'user deleted', $old, null);
        // LogActivity::add('delete_user', 'User deleted their profile');

        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {
        $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            $user = User::find($arr_del[$xx]);
            $old = $user->toArray();
            $user->delete();
            LogActivity::modelEvent($user, 'user deleted', $old, null);
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        return redirect('/users')->with('success', 'Deleted Successfully');
    }

    public function ToggleDisable($id)
    {
        $this->check_permission();

        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'ไม่พบผู้ใช้'], 404);
        }

        $old = $user->toArray();

        if ($user->is_disabled) {
            $user->update([
                'is_disabled' => false,
                'disabled_at' => null,
            ]);
            $action = 'เปิดใช้งาน';
        } else {
            $user->update([
                'is_disabled' => true,
                'disabled_at' => now(),
            ]);
            $action = 'ปิดใช้งาน';
        }

        LogActivity::modelEvent($user, "user {$action}", $old, $user->toArray());

        return response()->json([
            'success' => true,
            'message' => "{$action}ผู้ใช้ {$user->name} สำเร็จ",
            'is_disabled' => $user->is_disabled,
        ]);
    }
}
