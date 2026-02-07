<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * แสดงรายการ Role ทั้งหมด
     */
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * แสดงฟอร์มสร้าง Role ใหม่
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);
        $userMaxLevel = Auth::user()->maxRoleLevel();
        return view('roles.create', compact('permissions', 'groupedPermissions', 'userMaxLevel'));
    }

    /**
     * บันทึก Role ใหม่
     */
    public function store(Request $request)
    {
        $userMaxLevel = Auth::user()->maxRoleLevel();

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'level' => 'required|integer|min:1|max:' . $userMaxLevel,
            'permissions' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web',
                'level' => $request->level,
            ]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();
            return redirect()->route('admin.roles.index')
                ->with('success', 'สร้าง Role สำเร็จแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * แสดงรายละเอียด Role
     */
    public function show($id)
    {
        $role = Role::with(['permissions', 'users'])->findOrFail($id);
        return view('roles.show', compact('role'));
    }

    /**
     * แสดงฟอร์มแก้ไข Role
     */
    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        // Prevent editing role with higher level
        if ($role->level > Auth::user()->maxRoleLevel()) {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไข Role ที่มีระดับสูงกว่าของคุณ');
        }

        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $userMaxLevel = Auth::user()->maxRoleLevel();

        return view('roles.edit', compact('role', 'permissions', 'groupedPermissions', 'rolePermissions', 'userMaxLevel'));
    }

    /**
     * อัปเดต Role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $userMaxLevel = Auth::user()->maxRoleLevel();

        // Prevent updating role with higher level
        if ($role->level > $userMaxLevel) {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไข Role ที่มีระดับสูงกว่าของคุณ');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'level' => 'required|integer|min:1|max:' . $userMaxLevel,
            'permissions' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $role->update([
                'name' => $request->name,
                'level' => $request->level,
            ]);

            $role->syncPermissions($request->permissions ?? []);

            DB::commit();
            return redirect()->route('admin.roles.index')
                ->with('success', 'อัปเดต Role สำเร็จแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * ลบ Role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // ป้องกันการลบ super-admin
        // ป้องกันการลบ super-admin
        if ($role->name === 'super-admin') {
            return back()->with('error', 'ไม่สามารถลบ Role super-admin ได้');
        }

        // Prevent deleting role with higher level
        if ($role->level > Auth::user()->maxRoleLevel()) {
            return back()->with('error', 'คุณไม่มีสิทธิ์ลบ Role ที่มีระดับสูงกว่าของคุณ');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')
            ->with('success', 'ลบ Role สำเร็จแล้ว');
    }

    /**
     * จัดกลุ่ม permissions ตาม module
     */
    private function groupPermissions($permissions)
    {
        $grouped = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? 'other';
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $permission;
        }
        return $grouped;
    }
}
