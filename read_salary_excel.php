<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'docs/salary_exam.xlsx';

try {
    $spreadsheet = IOFactory::load($filePath);

    echo "=== ไฟล์: " . $filePath . " ===\n";
    echo "จำนวน Sheets: " . $spreadsheet->getSheetCount() . "\n\n";

    foreach ($spreadsheet->getSheetNames() as $index => $sheetName) {
        echo "--- Sheet " . ($index + 1) . ": " . $sheetName . " ---\n";

        $sheet = $spreadsheet->getSheet($index);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        echo "ขนาด: " . $highestRow . " rows x " . $highestColumn . " columns\n";

        // อ่านข้อมูล 30 แถวแรก
        $maxRows = min($highestRow, 30);

        for ($row = 1; $row <= $maxRows; $row++) {
            $rowData = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cellValue = $sheet->getCell($col . $row)->getValue();
                if ($cellValue !== null && $cellValue !== '') {
                    $rowData[$col] = $cellValue;
                }
            }
            if (!empty($rowData)) {
                echo "Row $row: " . json_encode($rowData, JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
