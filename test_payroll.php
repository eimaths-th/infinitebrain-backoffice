<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payroll;
use App\Models\PayrollPeriod;

$year = 2026;
$month = 3;

echo "--- Payroll Periods for {$month}/{$year} ---\n";
$periods = PayrollPeriod::where('year', $year)->where('month', $month)->get();
print_r($periods->toArray());

echo "\n--- Payrolls for {$month}/{$year} (All) ---\n";
$payrolls = Payroll::whereHas('payrollPeriod', function($q) use ($year, $month) {
    $q->where('year', $year)->where('month', $month);
})->with('user')->get();
foreach($payrolls as $p) {
    echo "ID: {$p->id}, User: {$p->user->name}, Dept: {$p->user->department}, Status: {$p->status}, NetPay: {$p->net_pay}\n";
}

echo "\n--- Payrolls for {$month}/{$year} (Filtered like UI) ---\n";
$filtered = Payroll::where('status', Payroll::STATUS_PAID)
    ->whereHas('user', function($query) {
        $query->whereIn('department', ['วิชาการ', 'สำนักงาน']);
    })
    ->whereHas('payrollPeriod', function($query) use ($year, $month) {
        $query->where('year', $year)->where('month', $month);
    })->get();
echo "Filtered Count: " . $filtered->count() . "\n";
