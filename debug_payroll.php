<?php
// Debug script to check income items

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$period = App\Models\PayrollPeriod::find(21);
if (!$period) {
    echo "Period 21 not found\n";
    exit;
}

echo "Period: " . $period->name . "\n\n";

// Find specific payroll where user has total_income = 12300
$payrolls = $period->payrolls()->with(['incomeItems', 'user'])->where('employee_type', 'parttime')->get();

foreach ($payrolls as $payroll) {
    echo "=== Payroll ID: {$payroll->id} ===\n";
    echo "Employee: {$payroll->employee_name}\n";
    echo "Base Salary: {$payroll->base_salary}\n";
    echo "Total Income: {$payroll->total_income}\n";
    echo "Total Deduction: {$payroll->total_deduction}\n";
    echo "Net Pay: {$payroll->net_pay}\n\n";

    echo "Income Items (" . $payroll->incomeItems->count() . "):\n";
    $sum = 0;
    foreach ($payroll->incomeItems as $item) {
        $subtotal = $item->amount * $item->quantity;
        $sum += $subtotal;
        $desc = mb_substr($item->description, 0, 30, 'UTF-8');
        echo "  ID={$item->id} [{$item->income_type}] {$desc}: {$item->amount} x {$item->quantity} = {$subtotal}\n";
    }
    echo "  CALCULATED SUM: {$sum}\n\n";
    echo "---\n\n";
}
