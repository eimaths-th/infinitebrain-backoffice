<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        $userMaxLevel = Auth::user()->maxRoleLevel();

        // Filter roles that are higher than user's max level from the selection list
        // Or keep them but mark as disabled in view?
        // Let's filter them out for now so they can't even see/select them or maybe just validation is enough
        // but for UX, passing max level is good.

        return view('user_roles.edit', compact('user', 'roles', 'userRoles', 'userMaxLevel'));
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
            // Sync roles
            $requestedRoles = $request->roles ?? [];
            $highLevelRoles = Role::whereIn('name', $requestedRoles)
                ->where('level', '>', Auth::user()->maxRoleLevel())
                ->exists();

            if ($highLevelRoles) {
                return back()->with('error', 'คุณไม่สามารถกำหนด Role ที่มีระดับสูงกว่าของคุณได้');
            }

            // Also check if user is trying to remove a role that is higher than their level?
            // "You cannot manage roles higher than yours".
            // So if target user has a higher role, you shouldn't be able to edit them at all?
            // Or just not touch those roles?

            // For now, strict check on assigned roles.

            // Allow update but preserve existing higher roles?
            // Or simply Block if target user has higher role?
            if ($user->maxRoleLevel() > Auth::user()->maxRoleLevel()) {
                // If we are strict:
                return back()->with('error', 'คุณไม่สามารถแก้ไขสิทธิ์ของผู้ใช้ที่มีระดับสูงกว่าคุณได้');
            }

            $user->syncRoles($requestedRoles);

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
            $targetRole = Role::where('name', $request->role)->first();
            if (!$targetRole) {
                throw new \Exception('Role does not exist');
            }

            if ($targetRole->level > Auth::user()->maxRoleLevel()) {
                return back()->with('error', 'คุณไม่สามารถกำหนด Role ที่มีระดับสูงกว่าของคุณได้');
            }

            foreach ($users as $user) {
                // Optional: Prevent assigning if target user already has higher role?
                if ($user->maxRoleLevel() > Auth::user()->maxRoleLevel()) {
                    continue; // Skip or Error? Let's skip safely
                }
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
