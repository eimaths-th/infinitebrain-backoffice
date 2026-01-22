<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    /**
     * แสดงรายการผู้ใช้และ Role
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20);
        $roles = Role::orderBy('name')->get();

        return view('user_roles.index', compact('users', 'roles'));
    }

    /**
     * แสดงฟอร์มกำหนด Role ให้ผู้ใช้
     */
    public function edit($id)
    {
        $user = User::with('roles', 'permissions')->findOrFail($id);
        $roles = Role::with('permissions')->orderBy('name')->get();
        $userRoles = $user->roles->pluck('name')->toArray();

        return view('user_roles.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * อัปเดต Role ของผู้ใช้
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'roles' => 'array',
        ]);

        DB::beginTransaction();
        try {
            // Sync roles
            $user->syncRoles($request->roles ?? []);

            DB::commit();
            return redirect()->route('admin.user-roles.index')
                ->with('success', 'อัปเดต Role ของผู้ใช้สำเร็จแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Bulk assign roles
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'role' => 'required|string|exists:roles,name',
        ]);

        DB::beginTransaction();
        try {
            $users = User::whereIn('id', $request->user_ids)->get();
            foreach ($users as $user) {
                $user->assignRole($request->role);
            }

            DB::commit();
            return redirect()->route('admin.user-roles.index')
                ->with('success', 'กำหนด Role ให้ผู้ใช้ ' . count($request->user_ids) . ' คนสำเร็จแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
