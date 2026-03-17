<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SchoolPrice;

$year = 2026;
$month = 3;

$prices = SchoolPrice::where('course_type', 1)
    ->whereHas('rec_school', function($query) use ($year, $month) {
        $query->whereYear('submission_date', $year)
              ->whereMonth('submission_date', $month);
    })->with(['std', 'rec_school', 'school_sub'])->take(1)->get();
    
print_r($prices->toArray());
