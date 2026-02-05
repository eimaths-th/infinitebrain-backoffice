<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$file_path = __DIR__ . '/docs/example.xlsx';
$output_path = __DIR__ . '/excel_structure.txt';

$output = "";

try {
    $spreadsheet = IOFactory::load($file_path);
    $sheet = $spreadsheet->getActiveSheet();

    $output .= "======================================\n";
    $output .= "EXCEL FILE STRUCTURE ANALYSIS\n";
    $output .= "======================================\n\n";

    $output .= "Sheet Name: " . $sheet->getTitle() . "\n";
    $output .= "Highest Row: " . $sheet->getHighestRow() . "\n";
    $output .= "Highest Column: " . $sheet->getHighestColumn() . "\n\n";

    $output .= "=== CELL CONTENTS (Row by Row) ===\n\n";

    $highest_row = $sheet->getHighestRow();
    $highest_column = $sheet->getHighestColumn();
    $highest_column_index = Coordinate::columnIndexFromString($highest_column);

    for ($row = 1; $row <= min($highest_row, 50); $row++) {
        $row_has_data = false;
        $row_data = [];

        for ($col = 1; $col <= $highest_column_index; $col++) {
            $col_letter = Coordinate::stringFromColumnIndex($col);
            $cell_coordinate = $col_letter . $row;
            $cell_value = $sheet->getCell($cell_coordinate)->getValue();

            if ($cell_value !== null && $cell_value !== '') {
                $row_has_data = true;
                $row_data[] = "$col_letter: \"$cell_value\"";
            }
        }

        if ($row_has_data) {
            $output .= "ROW $row:\n";
            foreach ($row_data as $data) {
                $output .= "  $data\n";
            }
            $output .= "\n";
        }
    }

    $output .= "=== MERGED CELLS ===\n";
    $merged = $sheet->getMergeCells();
    if (empty($merged)) {
        $output .= "No merged cells\n";
    } else {
        foreach ($merged as $merge_range) {
            $output .= "  $merge_range\n";
        }
    }

    file_put_contents($output_path, $output);
    echo "Output saved to: $output_path\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
