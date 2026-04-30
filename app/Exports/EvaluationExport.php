<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluationExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $year;
    protected $month;
    protected $isYearly;
    protected $isEvaluation;

    public function __construct($data, $year, $month = null, $isYearly = false, $isEvaluation = true)
    {
        $this->data = $data;
        $this->year = $year;
        $this->month = $month;
        $this->isYearly = $isYearly;
        $this->isEvaluation = $isEvaluation;
    }

    public function view(): View
    {
        $viewName = $this->isYearly ? 'finance.performance.export-yearly' : 'finance.performance.export-monthly';
        
        return view($viewName, [
            'data' => $this->data,
            'year' => $this->year,
            'month' => $this->month,
            'isEvaluation' => $this->isEvaluation
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
