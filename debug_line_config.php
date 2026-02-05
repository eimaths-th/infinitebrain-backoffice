<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking LINE Configuration:\n";
echo "--------------------------------\n";
echo "Manager Group ID: " . config('services.line_messaging.manager_group_id') . "\n";
echo "Test Group ID:    " . config('services.line_messaging.system_test_group_id') . "\n";
echo "\n";

echo "Checking .env directly:\n";
$env_path = __DIR__ . '/.env';
$env_content = file_get_contents($env_path);
if (preg_match('/LINE_MANAGER_GROUP_ID=(.*)/', $env_content, $matches)) {
    echo ".env MANAGER: " . trim($matches[1]) . "\n";
}
if (preg_match('/LINE_SYSTEM_TEST_GROUP_ID=(.*)/', $env_content, $matches)) {
    echo ".env TEST:    " . trim($matches[1]) . "\n";
}

echo "\nLogic Simulation:\n";
$status = \App\Models\PaymentVoucher::STATUS_APPROVED;
$message = "🎉 ใบสำคัญจ่ายอนุมัติแล้ว";
$targetGroup = config('services.line_messaging.manager_group_id');
echo "If STATUS_APPROVED, targetGroup = $targetGroup\n";

$isTest = false;
if ($isTest) {
    $targetGroup = config('services.line_messaging.system_test_group_id');
    echo "If isTest=true, targetGroup becomes $targetGroup\n";
} else {
    echo "If isTest=false, targetGroup remains $targetGroup\n";
}
