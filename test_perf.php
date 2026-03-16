<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SchoolPrice;
use App\Models\PaymentVoucher;
use Carbon\Carbon;

$year = 2026;
$month = 3; 

// 1. WhereBetween Logic (My original logic)
$start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
$end = Carbon::createFromDate($year, $month, 1)->endOfMonth();

$schoolRevenue1 = SchoolPrice::where('course_type', 1)
    ->whereHas('rec_school', function($query) use ($start, $end) {
        $query->whereBetween('submission_date', [$start, $end]);
    })->sum('total');

// 2. WhereMonth/WhereYear Logic (Report Month Student logic)
$reciept_school = \App\Models\Receipt_School::whereMonth('submission_date', $month)
    ->whereYear('submission_date', $year)->get();
$receiptSchoolIds = $reciept_school->pluck('id')->toArray();
$schoolRevenue2 = SchoolPrice::whereIn('rid', $receiptSchoolIds)
    ->where('course_type', 1)->sum('total');

echo "--- March 2026 School Revenue ---\n";
echo "My Logic (whereBetween submission_date): " . $schoolRevenue1 . "\n";
echo "Report Logic (whereMonth/Year, whereIn rid): " . $schoolRevenue2 . "\n";

// Let's find exactly which records are different
$myRecords = SchoolPrice::where('course_type', 1)
    ->whereHas('rec_school', function($query) use ($start, $end) {
        $query->whereBetween('submission_date', [$start, $end]);
    })->pluck('id')->toArray();

$reportRecords = SchoolPrice::whereIn('rid', $receiptSchoolIds)
    ->where('course_type', 1)->pluck('id')->toArray();

$diff = array_diff($reportRecords, $myRecords);
echo "Records in Report but NOT in My Logic: " . implode(', ', $diff) . "\n";
if (!empty($diff)) {
    $missingPrices = SchoolPrice::whereIn('id', $diff)->with('rec_school')->get();
    foreach ($missingPrices as $p) {
        echo "Missing Price ID: {$p->id}, Receipt ID: {$p->rid}, Submission Date: " . ($p->rec_school ? $p->rec_school->submission_date : 'NULL') . ", Total: {$p->total}\n";
    }
}

// Payment Vouchers for March 2026
echo "\n--- March 2026 PVs (Branch EL, ELPV, ELBF) ---\n";
$pvs = PaymentVoucher::where('status', 'approved')
    ->whereIn('branch_code', ['EL', 'ELPV', 'ELBF'])
    ->whereBetween('voucher_date', [$start, $end])
    ->get(['id', 'voucher_no', 'voucher_date', 'net_amount']);
    
$totalPv = 0;
foreach ($pvs as $pv) {
    echo "ID: {$pv->id}, No: {$pv->voucher_no}, Date: {$pv->voucher_date->toDateString()}, Amount: {$pv->net_amount}\n";
    $totalPv += $pv->net_amount;
}
echo "Total PV: $totalPv\n";
