<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all modules and their actions
        $modules = [
            'users' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'permissions' => ['view', 'create', 'edit', 'delete'],
            'students' => ['view', 'create', 'edit', 'delete', 'export'],
            'parents' => ['view', 'create', 'edit', 'delete', 'export'],
            'receipts' => ['view', 'create', 'edit', 'delete', 'export'],
            'receipt-school' => ['view', 'create', 'edit', 'delete', 'export'],
            'payment-voucher' => ['view', 'create', 'edit', 'delete', 'approve', 'review', 'export'],
            'courses' => ['view', 'create', 'edit', 'delete'],
            'subjects' => ['view', 'create', 'edit', 'delete'],
            'form-manage' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view', 'export'],
            'attendance' => ['view', 'create', 'edit', 'delete', 'export'],
            'settings' => ['view', 'edit'],
            'employees' => ['view', 'create', 'edit', 'delete'],
        ];

        // Create permissions
        $allPermissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$module}.{$action}";
                $allPermissions[] = $permissionName;
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }

        $this->command->info('Created ' . count($allPermissions) . ' permissions');

        // Create Roles
        // 1. Super Admin - has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($allPermissions);
        $this->command->info('Created super-admin role with all permissions');

        // 2. Admin - has most permissions except role/permission management
        $adminPermissions = array_filter($allPermissions, function ($p) {
            return !str_starts_with($p, 'roles.') && !str_starts_with($p, 'permissions.');
        });
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($adminPermissions);
        $this->command->info('Created admin role');

        // 3. Finance - focus on financial operations
        $financePermissions = [
            'receipts.view', 'receipts.create', 'receipts.edit', 'receipts.export',
            'receipt-school.view', 'receipt-school.create', 'receipt-school.edit', 'receipt-school.export',
            'payment-voucher.view', 'payment-voucher.create', 'payment-voucher.edit', 'payment-voucher.export',
            'students.view',
            'parents.view',
            'reports.view', 'reports.export',
        ];
        $finance = Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web']);
        $finance->syncPermissions($financePermissions);
        $this->command->info('Created finance role');

        // 4. Reviewer - can review payment vouchers
        $reviewerPermissions = [
            'payment-voucher.view', 'payment-voucher.review',
            'receipts.view',
            'receipt-school.view',
        ];
        $reviewer = Role::firstOrCreate(['name' => 'reviewer', 'guard_name' => 'web']);
        $reviewer->syncPermissions($reviewerPermissions);
        $this->command->info('Created reviewer role');

        // 5. Approver - can approve payment vouchers
        $approverPermissions = [
            'payment-voucher.view', 'payment-voucher.approve',
            'receipts.view',
            'receipt-school.view',
        ];
        $approver = Role::firstOrCreate(['name' => 'approver', 'guard_name' => 'web']);
        $approver->syncPermissions($approverPermissions);
        $this->command->info('Created approver role');

        // 6. Staff - basic staff access
        $staffPermissions = [
            'students.view', 'students.create', 'students.edit',
            'parents.view', 'parents.create', 'parents.edit',
            'receipts.view',
            'receipt-school.view',
            'attendance.view', 'attendance.create', 'attendance.edit',
            'courses.view',
            'subjects.view',
        ];
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions($staffPermissions);
        $this->command->info('Created staff role');

        // 7. Parent - limited access for parents
        $parentPermissions = [
            'students.view',
            'receipts.view',
            'receipt-school.view',
        ];
        $parent = Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $parent->syncPermissions($parentPermissions);
        $this->command->info('Created parent role');

        $this->command->info('');
        $this->command->info('All roles and permissions have been created successfully!');
        $this->command->info('Run "php artisan cache:clear" to clear permission cache.');
    }
}
