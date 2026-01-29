<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('docs/salary_exam.xlsx');

foreach ($spreadsheet->getSheetNames() as $name) {
    echo "=== Sheet: $name ===" . PHP_EOL;
    $sheet = $spreadsheet->getSheetByName($name);
    $highestRow = min($sheet->getHighestRow(), 40);
    $highestColumn = $sheet->getHighestColumn();

    for ($row = 1; $row <= $highestRow; $row++) {
        $cells = [];
        foreach (range('A', $highestColumn) as $col) {
            $val = $sheet->getCell($col . $row)->getCalculatedValue();
            if ($val !== null && $val !== '') {
                $cells[] = "$col: $val";
            }
        }
        if (!empty($cells)) {
            echo "Row $row: " . implode(' | ', $cells) . PHP_EOL;
        }
    }
    echo PHP_EOL;
}
