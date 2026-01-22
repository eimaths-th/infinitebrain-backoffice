<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * แสดงรายการ Permission ทั้งหมด
     */
    public function index()
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);
        return view('permissions.index', compact('permissions', 'groupedPermissions'));
    }

    /**
     * แสดงฟอร์มสร้าง Permission ใหม่
     */
    public function create()
    {
        $modules = $this->getModules();
        return view('permissions.create', compact('modules'));
    }

    /**
     * บันทึก Permission ใหม่
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            Permission::create([
                'name' => $request->name,
                'guard_name' => 'web',
            ]);

            return redirect()->route('admin.permissions.index')
                ->with('success', 'สร้าง Permission สำเร็จแล้ว');
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * แสดงฟอร์มแก้ไข Permission
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        $modules = $this->getModules();
        return view('permissions.edit', compact('permission', 'modules'));
    }

    /**
     * อัปเดต Permission
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $permission->update([
                'name' => $request->name,
            ]);

            return redirect()->route('admin.permissions.index')
                ->with('success', 'อัปเดต Permission สำเร็จแล้ว');
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * ลบ Permission
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'ลบ Permission สำเร็จแล้ว');
    }

    /**
     * สร้าง Permissions แบบ Bulk สำหรับ Module
     */
    public function bulkCreate(Request $request)
    {
        $request->validate([
            'module' => 'required|string|max:100',
            'actions' => 'required|array|min:1',
        ]);

        $module = strtolower($request->module);
        $actions = $request->actions;
        $created = 0;

        DB::beginTransaction();
        try {
            foreach ($actions as $action) {
                $permissionName = $module . '.' . $action;
                if (!Permission::where('name', $permissionName)->exists()) {
                    Permission::create([
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ]);
                    $created++;
                }
            }
            DB::commit();
            return redirect()->route('admin.permissions.index')
                ->with('success', "สร้าง {$created} Permissions สำเร็จแล้ว");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
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
        ksort($grouped);
        return $grouped;
    }

    /**
     * รายการ Modules ที่มีในระบบ
     */
    private function getModules()
    {
        return [
            'users' => 'จัดการผู้ใช้',
            'roles' => 'จัดการ Role',
            'permissions' => 'จัดการ Permission',
            'students' => 'จัดการนักเรียน',
            'parents' => 'จัดการผู้ปกครอง',
            'receipts' => 'จัดการใบเสร็จ',
            'payment-voucher' => 'ใบสำคัญจ่าย',
            'courses' => 'จัดการคอร์ส',
            'reports' => 'รายงาน',
            'settings' => 'ตั้งค่าระบบ',
        ];
    }

    /**
     * Actions มาตรฐาน
     */
    public static function getStandardActions()
    {
        return [
            'view' => 'ดู',
            'create' => 'สร้าง',
            'edit' => 'แก้ไข',
            'delete' => 'ลบ',
            'approve' => 'อนุมัติ',
            'export' => 'ส่งออก',
        ];
    }
}
