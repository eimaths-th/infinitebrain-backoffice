<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$records = App\Models\SchoolPrice::with(['std', 'rec_school'])
    ->whereNotIn('group', ['M', 'E', 'CHN', 'Sci'])
    ->where('course_type', 1)
    ->whereHas('rec_school', function($query) {
        $query->whereIn(DB::raw('YEAR(submission_date)'), [2022, 2023, 2024, 2025, 2026]);
    })
    ->get()
    ->map(function ($p) {
        return [
            'id' => $p->id,
            'student_id' => $p->student_id,
            'student_code' => $p->std->code ?? 'N/A',
            'student_name' => collect([$p->std->nick_name ?? '', $p->std->first_name ?? ''])->filter()->join(' '),
            'receipt_number' => $p->rec_school->receipt_number ?? 'N/A',
            'submission_date' => $p->rec_school->submission_date ?? 'N/A',
            'period' => $p->period ?? 'N/A',
            'total' => $p->total,
            'group' => $p->group
        ];
    });

echo "Found " . $records->count() . " records with unmapped or null group:\n\n";

foreach ($records as $r) {
    echo "ID: {$r['id']} | Std ID: {$r['student_id']} | Code: {$r['student_code']} | Name: {$r['student_name']} | Receipt: {$r['receipt_number']} | Date: {$r['submission_date']} | Period: {$r['period']} | Total: {$r['total']} | Group: '{$r['group']}'\n";
}
