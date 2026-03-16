<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

$permission = Permission::firstOrCreate(['name' => 'performance.view', 'guard_name' => 'web']);

$roles = ['super-admin', 'admin', 'finance', 'OWNER'];

foreach ($roles as $roleName) {
    $role = Role::where('name', $roleName)->first();
    if ($role) {
        $role->givePermissionTo($permission);
        echo "Given performance.view to $roleName\n";
    } else {
        echo "Role $roleName not found\n";
    }
}

echo "Permission performance.view added successfully.\n";
