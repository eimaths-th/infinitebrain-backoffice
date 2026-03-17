<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payroll;
use App\Models\PayrollPeriod;

echo "--- All Payroll Periods ---\n";
$periods = PayrollPeriod::all();
foreach($periods as $per) {
    echo "ID: {$per->id}, Year: {$per->year}, Month: {$per->month}, Users: {$per->payrolls()->count()}\n";
}

echo "--- Recent Payrolls ---\n";
$latestPayrolls = Payroll::with('user', 'payrollPeriod')->take(20)->get();
foreach($latestPayrolls as $p) {
    echo "Payroll ID: {$p->id}, Period: {$p->payrollPeriod->month}/{$p->payrollPeriod->year}, User: {$p->user->name}, Dept: {$p->user->department}, Status: {$p->status}\n";
}
