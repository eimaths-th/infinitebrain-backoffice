<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$voucher = App\Models\PaymentVoucher::latest()->first();
if ($voucher) {
    echo "ID: " . $voucher->id . "\n";
    echo "Voucher No: " . $voucher->voucher_no . "\n";
    echo "Is Salary (Raw): " . ($voucher->getRawOriginal('is_salary') ?? 'NULL') . "\n";
    echo "Is Salary (Cast): " . ($voucher->is_salary ? 'True' : 'False') . "\n";
} else {
    echo "No vouchers found.\n";
}
