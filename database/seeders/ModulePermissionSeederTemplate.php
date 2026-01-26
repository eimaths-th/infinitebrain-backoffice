<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Template Seeder สำหรับสร้าง Permissions ของ Module ใหม่
 *
 * วิธีใช้:
 * 1. Copy file นี้และเปลี่ยนชื่อ class เป็น {ModuleName}PermissionSeeder
 * 2. แก้ไข $module_name และ $permissions
 * 3. รัน: php artisan db:seed --class={ModuleName}PermissionSeeder
 *
 * หลักการ:
 * - super-admin จะได้รับ ALL permissions โดยอัตโนมัติ
 * - admin จะได้รับ permissions ทั้งหมดของ module นั้น
 * - staff จะได้รับ permissions ตาม $staff_permissions
 */
class ModulePermissionSeederTemplate extends Seeder
{
    /**
     * ชื่อ Module (ใช้เป็น prefix สำหรับ permission name)
     * เช่น: 'leave-requests', 'payment-voucher', 'students'
     */
    protected string $module_name = 'module-name';

    /**
     * Permissions ของ module นี้
     * Format: 'action' => 'description'
     */
    protected array $permissions = [
        'view' => 'ดูข้อมูล',
        'view-all' => 'ดูข้อมูลทั้งหมด',
        'create' => 'สร้างข้อมูล',
        'edit' => 'แก้ไขข้อมูล',
        'delete' => 'ลบข้อมูล',
        'export' => 'ส่งออกข้อมูล',
    ];

    /**
     * Permissions ที่ staff ควรได้รับ (ระบุเฉพาะ action)
     */
    protected array $staff_permissions = ['view', 'create', 'edit'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $created_permissions = [];

        // Create permissions
        foreach ($this->permissions as $action => $description) {
            $permission_name = "{$this->module_name}.{$action}";
            Permission::firstOrCreate(
                ['name' => $permission_name, 'guard_name' => 'web']
            );
            $created_permissions[] = $permission_name;
            $this->command->info("Created permission: {$permission_name}");
        }

        // Assign ALL permissions to super-admin
        $this->AssignToSuperAdmin();

        // Assign module permissions to admin
        $this->AssignToAdmin($created_permissions);

        // Assign staff permissions
        $this->AssignToStaff();

        $this->command->info('');
        $this->command->info("{$this->module_name} permissions seeded successfully!");
        $this->command->info('Run "php artisan cache:clear" to clear permission cache.');
    }

    /**
     * Assign ALL permissions ให้ super-admin
     */
    protected function AssignToSuperAdmin(): void
    {
        $super_admin = Role::where('name', 'super-admin')->first();
        if ($super_admin) {
            $all_permissions = Permission::all()->pluck('name')->toArray();
            $super_admin->syncPermissions($all_permissions);
            $this->command->info('Assigned ALL permissions to super-admin');
        }
    }

    /**
     * Assign module permissions ให้ admin
     */
    protected function AssignToAdmin(array $permissions): void
    {
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permissions);
            $this->command->info("Assigned {$this->module_name} permissions to admin");
        }
    }

    /**
     * Assign staff permissions
     */
    protected function AssignToStaff(): void
    {
        $staff = Role::where('name', 'staff')->first();
        if ($staff) {
            $staff_permission_names = array_map(
                fn($action) => "{$this->module_name}.{$action}",
                $this->staff_permissions
            );
            $staff->givePermissionTo($staff_permission_names);
            $this->command->info("Assigned basic {$this->module_name} permissions to staff");
        }
    }
}
