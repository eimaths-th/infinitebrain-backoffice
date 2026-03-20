<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$permissions = ['payment-voucher.view', 'payment-voucher.create'];
foreach($permissions as $p) {
    if (!Permission::where('name', $p)->where('guard_name', 'web')->exists()) {
        Permission::create(['name' => $p, 'guard_name' => 'web']);
    }
}

$r = Role::firstOrCreate(['name' => 'external-branch', 'guard_name' => 'web']);
$r->syncPermissions($permissions);

echo "Role 'external-branch' created successfully.\n";
