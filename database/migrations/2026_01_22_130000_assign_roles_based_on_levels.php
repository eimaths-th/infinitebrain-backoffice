<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Assign roles to existing users based on their level field
     *
     * @return void
     */
    public function up()
    {
        // Mapping level to role names
        $levelToRole = [
            0 => 'super-admin',  // Owner
            1 => 'admin',        // Admin
            2 => 'staff',        // Teacher
            3 => 'finance',      // Account
            4 => 'staff',        // Officer
            5 => 'parent',       // Normal
            7 => 'finance',      // Finance
            8 => 'approver',     // Header
        ];

        // Make sure roles exist
        foreach (array_unique(array_values($levelToRole)) as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Assign roles to users based on their level
        $users = User::all();
        foreach ($users as $user) {
            $level = $user->level;
            if (isset($levelToRole[$level])) {
                $roleName = $levelToRole[$level];
                if (!$user->hasRole($roleName)) {
                    $user->assignRole($roleName);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove all role assignments from users
        $users = User::all();
        foreach ($users as $user) {
            $user->syncRoles([]);
        }
    }
};
