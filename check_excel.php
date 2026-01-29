<?php
require_once __DIR__ . '/vendor/autoload.php';

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$spreadsheet = $reader->load('docs/salary_exam.xlsx');

// ดู Sheet 1 "มค69" - payslip รายบุคคล
echo "=== Sheet 1 (มค69) - Payslip Structure ===\n\n";
$sheet1 = $spreadsheet->getSheet(1);
$rows = $sheet1->toArray();

// แสดง rows 0-30 เพื่อดูโครงสร้าง payslip
for ($i = 0; $i < min(40, count($rows)); $i++) {
    $row = $rows[$i];
    $rowText = implode('', array_filter($row));
    if (empty($rowText)) continue; // ข้ามแถวว่าง

    echo "Row $i: ";
    for ($j = 0; $j < min(12, count($row)); $j++) {
        $val = $row[$j] ?? '';
        $val = mb_substr(trim($val), 0, 15);
        if (!empty($val)) {
            echo "[$j]$val | ";
        }
    }
    echo "\n";
}
