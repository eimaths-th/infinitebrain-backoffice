<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class BranchManagementPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'branch-groups' => 'จัดการกลุ่มสาขา',
            'branches' => 'จัดการสาขา'
        ];

        $actions = [
            'view' => 'ดูข้อมูล',
            'create' => 'สร้างข้อมูล',
            'edit' => 'แก้ไขข้อมูล',
            'delete' => 'ลบข้อมูล'
        ];

        $all_permission_names = [];

        foreach ($modules as $module_name => $module_label) {
            foreach ($actions as $action => $description) {
                $permission_name = "{$module_name}.{$action}";
                Permission::firstOrCreate(
                    ['name' => $permission_name, 'guard_name' => 'web']
                );
                $all_permission_names[] = $permission_name;
            }
        }

        // Assign to super-admin
        $super_admin = Role::where('name', 'super-admin')->first();
        if ($super_admin) {
            $super_admin->givePermissionTo($all_permission_names);
        }

        // Assign to admin
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($all_permission_names);
        }

        $this->command->info('Branch Management permissions seeded successfully!');
    }
}
