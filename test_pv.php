<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PaymentVoucher;

$pv = PaymentVoucher::with(['expenseItems', 'deductions'])->find(161);

if (!$pv) {
    echo "PV 161 not found.\n";
    exit;
}

echo "=== รายละเอียด Payment Voucher ===\n";
echo "Voucher No: {$pv->voucher_no}\n";
echo "Voucher Date: {$pv->voucher_date->toDateString()}\n";
echo "Pay To: {$pv->pay_to}\n";
echo "Total Amount: {$pv->total_amount}\n";
echo "Total Deduction: {$pv->total_deduction_amount}\n";
echo "Withholding Tax Amount: {$pv->withholding_tax_amount}\n";
echo "Net Amount: {$pv->net_amount}\n";

echo "\n--- Expense Items (รายการค่าใช้จ่าย) ---\n";
foreach ($pv->expenseItems as $item) {
    echo "- รายการ: {$item->description} | จำนวน: {$item->amount}\n";
}

echo "\n--- Deductions (รายการหัก) ---\n";
if ($pv->deductions->count() > 0) {
    foreach ($pv->deductions as $item) {
        echo "- รายการ: {$item->description} | จำนวน: {$item->amount}\n";
    }
} else {
    echo "- ไม่มีรายการหัก\n";
}
echo "\n============================================\n";

