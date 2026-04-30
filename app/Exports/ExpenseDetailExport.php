<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseDetailExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $year;
    protected $month;
    protected $isEvaluation;

    public function __construct($data, $year, $month, $isEvaluation = false)
    {
        $this->data = $data;
        $this->year = $year;
        $this->month = $month;
        $this->isEvaluation = $isEvaluation;
    }

    public function view(): View
    {
        return view('finance.performance.export-expense', [
            'data' => $this->data,
            'year' => $this->year,
            'month' => $this->month,
            'isEvaluation' => $this->isEvaluation
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
