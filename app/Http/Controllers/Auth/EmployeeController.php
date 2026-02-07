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
        $this->check_permission();

        if ($request->ajax()) {
            $query = User::query();

            // Apply filters
            if ($request->filled('department')) {
                $query->where('department', $request->department);
            }
            if ($request->filled('employee_type')) {
                $query->where('employee_type', $request->employee_type);
            }
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_disabled', false);
                } elseif ($request->status === 'disabled') {
                    $query->where('is_disabled', true);
                }
            }

            $users = $query->orderBy("is_disabled", "asc")->orderBy("code", "asc");

            return datatables()->of($users)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('id', function ($row) {
                    return $row->id;
                })
                ->editColumn('code', function (User $user) {
                    return $user->code ? '<span class="badge bg-dark">' . e($user->code) . '</span>' : '<span class="text-muted">-</span>';
                })
                ->editColumn('name', function (User $user) {
                    $prefix = $user->title_prefix ? \App\Models\User::TITLE_PREFIXES[$user->title_prefix] ?? '' : '';
                    $fullName = trim($prefix . ' ' . $user->name);
                    return '<div class="fw-bold">' . e($fullName) . '</div>';
                })
                ->editColumn('email', function ($row) {
                    return $row->email;
                })
                ->editColumn('birth_date', function ($row) {
                    return $row->birth_date ? \Carbon\Carbon::parse($row->birth_date)->format('d m Y') : '-';
                })
                ->addColumn('department', function (User $user) {
                    return $user->department ? e($user->department) : '<span class="text-muted">-</span>';
                })
                ->addColumn('position', function (User $user) {
                    return $user->position ? e($user->position) : '<span class="text-muted">-</span>';
                })
                ->editColumn('level', function (User $user) {
                    // Get user roles from Spatie
                    $roles = $user->getRoleNames();

                    if ($roles->isEmpty()) {
                        return '<span class="badge bg-light text-dark">No Role</span>';
                    }

                    // Role color mapping
                    $roleColors = [
                        'super-admin' => 'danger',
                        'admin' => 'primary',
                        'finance' => 'success',
                        'reviewer' => 'info',
                        'approver' => 'warning',
                        'staff' => 'secondary',
                        'parent' => 'dark',
                    ];

                    // Display all roles as badges
                    $badges = [];
                    foreach ($roles as $role) {
                        $color = $roleColors[$role] ?? 'light text-dark';
                        $badges[] = '<span class="badge bg-' . $color . '">' . ucwords(str_replace('-', ' ', $role)) . '</span>';
                    }

                    return implode(' ', $badges);
                })
                ->editColumn('status', function ($row) {
                    if ($row->is_disabled) {
                        return '<span class="badge bg-secondary"><i class="fa fa-ban me-1"></i>ปิดใช้งาน</span>';
                    }
                    return '<span class="badge bg-success"><i class="fa fa-check me-1"></i>ใช้งาน</span>';
                })
                ->addColumn('tags', function (User $user) {
                    $tags = $user->tags;
                    if ($tags->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }
                    $badges = [];
                    foreach ($tags as $tag) {
                        $badges[] = '<span class="badge" style="background-color: var(--bs-' . $tag->color . '); color: white;">' . e($tag->name) . '</span>';
                    }
                    return implode(' ', $badges);
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group" role="group">';
                    $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '" title="แก้ไข"><i class="fa fa-edit"></i></a>';

                    if ($row->is_disabled) {
                        $html .= '<a href="#" data-id="' . $row->id . '" class="btn btn-xs btn-success btn-toggle-disable" title="เปิดใช้งาน"><i class="fa fa-check"></i></a>';
                    } else {
                        $html .= '<a href="#" data-id="' . $row->id . '" class="btn btn-xs btn-secondary btn-toggle-disable" title="ปิดใช้งาน"><i class="fa fa-ban"></i></a>';
                    }

                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete" title="ลบ"><i class="fa fa-trash"></i></a>';
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['checkbox', 'code', 'name', 'department', 'position', 'level', 'status', 'tags', 'action'])
                ->toJson();
        }

        // Get unique departments and employee types for filters
        $departments = User::whereNotNull('department')->distinct()->pluck('department');
        $employee_types = User::whereNotNull('employee_type')->distinct()->pluck('employee_type');

        return view('users.index', compact('departments', 'employee_types'));
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
            // Personal info
            'title_prefix' => 'nullable|string|max:50',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female',
            'nationality' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            // Employment info
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'employee_type' => 'nullable|in:fulltime,parttime,contract',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_probation' => 'nullable|boolean',
            'probation_end_date' => 'nullable|required_if:is_probation,true|date',
            'payroll_frequency' => 'nullable|in:monthly,weekly',
            // Payroll info
            'base_salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:20',
            'social_security_no' => 'nullable|string|max:20',
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
            // Personal info
            'title_prefix' => 'nullable|string|max:50',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female',
            'nationality' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            // Employment info
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'employee_type' => 'nullable|in:fulltime,parttime,contract',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_probation' => 'nullable|boolean',
            'probation_end_date' => 'nullable|required_if:is_probation,true|date',
            'payroll_frequency' => 'nullable|in:monthly,weekly',
            // Payroll info
            'base_salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:20',
            'social_security_no' => 'nullable|string|max:20',
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

        // บันทึก Tags
        if ($request->has('tags')) {
            $user->tags()->sync($request->input('tags', []));
        }

        // บันทึก Salary Items (สำหรับ Part-time)
        if ($request->has('salary_items')) {
            $existing_ids = [];
            foreach ($request->input('salary_items', []) as $key => $item) {
                if (empty($item['description']) || empty($item['rate'])) {
                    continue;
                }

                $item_data = [
                    'description' => $item['description'],
                    'rate' => $item['rate'],
                    'unit' => $item['unit'] ?? 'ชม.',
                ];

                // ถ้ามี id แสดงว่าเป็น item ที่มีอยู่แล้ว
                if (!empty($item['id']) && is_numeric($item['id'])) {
                    $salary_item = \App\Models\UserSalaryItem::find($item['id']);
                    if ($salary_item && $salary_item->user_id == $user->id) {
                        $salary_item->update($item_data);
                        $existing_ids[] = $salary_item->id;
                    }
                } else {
                    // สร้างใหม่
                    $new_item = $user->salaryItems()->create($item_data);
                    $existing_ids[] = $new_item->id;
                }
            }

            // ลบ items ที่ไม่ได้ส่งมา (is_active = false)
            \App\Models\UserSalaryItem::where('user_id', $user->id)
                ->whereNotIn('id', $existing_ids)
                ->update(['is_active' => false]);
        }

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
