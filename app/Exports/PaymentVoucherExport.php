<?php

namespace App\Exports;

use App\Models\PaymentVoucher;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Carbon\Carbon;

class PaymentVoucherExport
{
    protected $voucher;

    public function __construct(PaymentVoucher $voucher)
    {
        $this->voucher = $voucher;
    }

    /**
     * Export Payment Voucher to Excel using template
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    /**
     * Export Payment Voucher to Excel using template
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function Download()
    {
        $spreadsheet = $this->prepareSpreadsheet();

        // Create temp file
        $temp_file = tempnam(sys_get_temp_dir(), 'pv_excel_');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($temp_file);

        // Generate filename
        $filename = 'payment_voucher_' . $this->voucher->voucher_no . '.xlsx';

        return response()->download($temp_file, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Prepare Spreadsheet object with data
     *
     * @return Spreadsheet
     */
    public function prepareSpreadsheet()
    {
        // Load template file
        $template_path = base_path('docs/example.xlsx');
        $spreadsheet = IOFactory::load($template_path);
        $sheet = $spreadsheet->getActiveSheet();

        // Fill voucher data
        $this->FillVoucherData($sheet);

        // Fill items data
        $this->FillItemsData($sheet);

        // Fill signatures
        $this->FillSignatures($sheet);

        return $spreadsheet;
    }

    /**
     * Fill voucher header data
     */
    protected function FillVoucherData($sheet)
    {
        // Voucher number (J10)
        $sheet->setCellValue('J10', $this->voucher->voucher_no);

        // Voucher date (J11)
        $voucher_date = Carbon::parse($this->voucher->voucher_date)->format('d/m/Y');
        $sheet->setCellValue('J11', $voucher_date);

        // Payment Cycle (J12) - Assuming user added this field in template
        // Note: Please adjust the cell coordinate if it's different in the actual template
        if ($this->voucher->payment_cycle) {
            $sheet->setCellValue('J12', $this->voucher->payment_cycle);
        }

        // Pay to (C10)
        $sheet->setCellValue('C10', $this->voucher->pay_to);

        // Bank account info (C12)
        $bank_info = '';
        if ($this->voucher->pay_to_account_no) {
            $bank_info = $this->voucher->pay_to_account_no;
            if ($this->voucher->pay_to_bank_name) {
                $bank_info .= ' ' . $this->voucher->pay_to_bank_name;
            }
        }
        $sheet->setCellValue('C12', $bank_info);

        // Address (C14)
        $sheet->setCellValue('C14', $this->voucher->pay_to_address ?? '');
    }

    /**
     * Fill items data in the table
     */
    /**
     * Fill items data in the table
     */
    protected function FillItemsData($sheet)
    {
        // Items starts at row 19, up to row 31 (13 items max in template)
        $start_row = 19;
        $max_items = 13;

        $items = $this->voucher->items;

        // Prepare items list to render
        $render_items = [];
        foreach ($items as $item) {
            $render_items[] = [
                'no' => $item->item_no,
                'description' => $item->description,
                'amount' => $item->amount
            ];
        }

        // Add Withholding Tax as a negative item
        if ($this->voucher->withholding_tax_amount > 0) {
            $tax_label = 'ภาษีเงินได้หัก ณ ที่จ่าย';
            if ($this->voucher->withholding_tax_rate > 0) {
                $tax_label .= ' ' . ($this->voucher->withholding_tax_rate + 0) . ' %'; // Remove trailing zero if any
            }

            $render_items[] = [
                'no' => count($render_items) + 1,
                'description' => $tax_label,
                'amount' => -$this->voucher->withholding_tax_amount
            ];
        }

        // Limit items to template capacity
        $items_to_show = array_slice($render_items, 0, $max_items);

        // Fill items
        foreach ($items_to_show as $index => $item_data) {
            $row = $start_row + $index;

            // Item number (Column A)
            $sheet->setCellValue('A' . $row, $item_data['no']);

            // Description (Column C)
            $sheet->setCellValue('C' . $row, $item_data['description']);

            // Amount (Column I)
            $sheet->setCellValue('I' . $row, $item_data['amount']);

            // Check if this is the Tax item (last item and amount is negative)
            // Or easier: check if description contains "หัก ณ ที่จ่าย"
            if ($item_data['amount'] < 0 && str_contains($item_data['description'], 'หัก ณ ที่จ่าย')) {
                // Style for Tax Item: Red Text
                $sheet->getStyle('C' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                $sheet->getStyle('I' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));

                // Number Format: No brackets, show minus sign
                // Format: Positive; Negative; Zero
                $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
            } else {
                // Reset style to default (Black) just in case template has formatting
                $sheet->getStyle('C' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));
                $sheet->getStyle('I' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));
                // Standard format
                $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }

        // Clear remaining item rows
        for ($i = count($items_to_show); $i < $max_items; $i++) {
            $row = $start_row + $i;
            $sheet->setCellValue('A' . $row, '');
            // Clear multiple columns to ensure no artifacts from template (Text was in E in template)
            $sheet->setCellValue('C' . $row, '');
            $sheet->setCellValue('D' . $row, '');
            $sheet->setCellValue('E' . $row, '');
            $sheet->setCellValue('I' . $row, '');
        }

        // Total amount (row 32)
        // Since we included the negative tax in the items, SUM(I19:I31) would calculate Net Amount.
        // But to be safe and explicit, let's put the Net Amount value.
        // Or if the template relies on formula, we can verify if we wrote to I32.
        // User template has formula in I32. If we don't write to it, it uses formula.
        // However, we should ensure the displayed value is Net Amount.
        // Writing value overwrites formula, which is safer for export file.
        $net_amount = $this->voucher->total_amount - $this->voucher->withholding_tax_amount;
        $sheet->setCellValue('I32', $net_amount);
    }

    /**
     * Fill signature section
     */
    protected function FillSignatures($sheet)
    {
        // Approver signature (A35 area)
        $approver_text = "ผู้อนุมัติ  ";
        if ($this->voucher->approved_by_name) {
            $approver_text .= $this->voucher->approved_by_name;
        } else {
            $approver_text .= "...........................................................................";
        }
        $sheet->setCellValue('A35', $approver_text);

        // Creator signature (D35 area)
        $creator_text = "ผู้จัดทำ  ";
        if ($this->voucher->created_by_name) {
            $creator_text .= $this->voucher->created_by_name;
        } else {
            $creator_text .= "....................................................................";
        }
        $sheet->setCellValue('D35', $creator_text);

        // Receiver signature (H35 area)
        $receiver_text = "ผู้รับเงิน ";
        if ($this->voucher->receiver_name) {
            $receiver_text .= $this->voucher->receiver_name;
        } else {
            $receiver_text .= "............................................................................";
        }
        $sheet->setCellValue('H35', $receiver_text);

        // Date line (A37)
        $date_text = "วันที่ ";
        if ($this->voucher->approved_at) {
            $date_text .= Carbon::parse($this->voucher->approved_at)->format('d/m/Y');
        } else {
            $date_text .= ".....................................................................";
        }
        $sheet->setCellValue('A37', $date_text);

        // Check amount (D37)
        $check_text = "เช็คจำนวน  ";
        if ($this->voucher->net_amount) {
            $check_text .= number_format($this->voucher->net_amount, 2) . " บาท";
        } elseif ($this->voucher->total_amount) {
            $check_text .= number_format($this->voucher->total_amount, 2) . " บาท";
        } else {
            $check_text .= "................................................บาท";
        }
        $sheet->setCellValue('D37', $check_text);

        // Bank line (H37) - keep template default
        // Check number line (H39) - keep template default
    }
}
