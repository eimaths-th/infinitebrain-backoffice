<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RecruitmentPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define permissions
        $permissions = [
            'recruitment.view',
            'recruitment.upload', // For Admin
            'recruitment.review', // For Reviewer
            'recruitment.verify', // For Manager
            'recruitment.view_all',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Roles definition (existing or new)
        $roles = [
            'admin' => ['recruitment.view', 'recruitment.upload', 'recruitment.view_all'],
            'reviewer' => ['recruitment.view', 'recruitment.review'],
            'approver' => ['recruitment.view', 'recruitment.verify', 'recruitment.view_all'],
            'super-admin' => $permissions,
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->givePermissionTo($rolePermissions);
        }
    }
}
