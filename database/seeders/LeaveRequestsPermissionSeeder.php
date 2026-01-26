<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder สำหรับ Leave Requests Permissions
 *
 * วิธีใช้งาน: php artisan db:seed --class=LeaveRequestsPermissionSeeder
 *
 * Seeder นี้จะ:
 * 1. สร้าง permissions สำหรับ leave-requests module
 * 2. Assign ให้ super-admin โดยอัตโนมัติ (มีทุก permission)
 * 3. Assign ให้ admin โดยอัตโนมัติ
 * 4. Assign ให้ staff ตาม permissions ที่กำหนด
 */
class LeaveRequestsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define leave-requests permissions
        $permissions = [
            'leave-requests.view' => 'ดูใบลาของตัวเอง',
            'leave-requests.view-all' => 'ดูใบลาทั้งหมด',
            'leave-requests.create' => 'สร้างใบลาใหม่',
            'leave-requests.edit' => 'แก้ไขใบลา',
            'leave-requests.delete' => 'ลบใบลา',
            'leave-requests.approve' => 'อนุมัติใบลา',
            'leave-requests.reject' => 'ปฏิเสธใบลา',
            'leave-requests.export' => 'ส่งออก PDF/DOCX',
        ];

        $created_permissions = [];

        // Create permissions
        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
            $created_permissions[] = $name;
            $this->command->info("Created permission: {$name}");
        }

        // Assign ALL permissions to super-admin (Owner)
        $super_admin = Role::where('name', 'super-admin')->first();
        if ($super_admin) {
            // Get all existing permissions and add new ones
            $all_permissions = Permission::all()->pluck('name')->toArray();
            $super_admin->syncPermissions($all_permissions);
            $this->command->info('Assigned ALL permissions to super-admin');
        }

        // Assign ALL leave-requests permissions to admin
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($created_permissions);
            $this->command->info('Assigned leave-requests permissions to admin');
        }

        // Assign basic permissions to staff
        $staff = Role::where('name', 'staff')->first();
        if ($staff) {
            $staff_permissions = [
                'leave-requests.view',
                'leave-requests.create',
                'leave-requests.edit',
                'leave-requests.export',
            ];
            $staff->givePermissionTo($staff_permissions);
            $this->command->info('Assigned basic leave-requests permissions to staff');
        }

        $this->command->info('');
        $this->command->info('Leave Requests permissions seeded successfully!');
        $this->command->info('Run "php artisan cache:clear" to clear permission cache.');
    }
}
