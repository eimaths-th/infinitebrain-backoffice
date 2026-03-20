<?php
$all = \App\Models\History::whereNotNull('user_id')->count();
$with = \App\Models\History::whereNotNull('user_id')->whereNotNull('school_price_id')->count();
$without = \App\Models\History::whereNotNull('user_id')->whereNull('school_price_id')->get();

$matched = 0;
foreach($without as $h) {
    $sp = \App\Models\SchoolPrice::where('student_id', $h->student_id)
        ->where('subject', $h->subject_id)
        ->where('total', '>', 0)
        ->orderBy('id', 'desc')
        ->first();
    if ($sp) {
        $matched++;
    }
}

echo "====================================\n";
echo "Total with user_id: " . $all . "\n";
echo "With school_price_id: " . $with . "\n";
echo "Without school_price_id: " . count($without) . "\n";
echo "Out of those without, matched by fallback: " . $matched . "\n";
echo "Unmatched total (ไม่พบใบเสร็จจริงๆ): " . (count($without) - $matched) . "\n";
echo "====================================\n";
