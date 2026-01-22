<?php

namespace App\Exports;

use App\Models\PaymentVoucher;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Contracts\View\View;

class PaymentVoucherExport implements FromView, WithStyles, WithColumnWidths, WithEvents
{
    protected $voucher;

    public function __construct(PaymentVoucher $voucher)
    {
        $this->voucher = $voucher;
    }

    public function view(): View
    {
        return view('payment_voucher.export_excel', [
            'voucher' => $this->voucher
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 45,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title style
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Header style
        $sheet->getStyle('A3:F3')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();

                // Set borders for items table
                $itemsStartRow = 8;
                $itemsEndRow = $itemsStartRow + $this->voucher->items->count();

                $sheet->getStyle("A{$itemsStartRow}:D{$itemsEndRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Total row style
                $totalRow = $itemsEndRow + 1;
                $sheet->getStyle("A{$totalRow}:D{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}
