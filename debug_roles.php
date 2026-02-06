<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Roles ---\n";
echo \Spatie\Permission\Models\Role::all()->pluck('name')->implode(', ') . "\n\n";

echo "--- Permissions ---\n";
echo \Spatie\Permission\Models\Permission::all()->pluck('name')->implode(', ') . "\n\n";

echo "--- Users with 'teacher' role ---\n";
$users = \App\Models\User::whereHas('roles', function ($q) {
    $q->where('name', 'teacher');
})->get();
echo $users->pluck('name')->implode(', ') . "\n\n";

echo "--- Users with 'teacher.teach' permission ---\n";
$users = \App\Models\User::whereHas('permissions', function ($q) {
    $q->where('name', 'teacher.teach');
})->get();
echo $users->pluck('name')->implode(', ') . "\n\n";

echo "--- Testing Controller Query EXACTLY ---\n";
try {
    $teachers = \App\Models\User::whereHas('roles', function ($q) {
        $q->where('name', 'teacher');
    })->orWhereHas('permissions', function ($q) {
        $q->where('name', 'teacher.teach');
    })->orderBy('name', 'asc')->get();

    echo "Count: " . $teachers->count() . "\n";
    echo $teachers->pluck('name')->implode(', ') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
