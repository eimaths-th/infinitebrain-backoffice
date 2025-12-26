<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use App\Services\LeaveRequestPdfService;
use Illuminate\Console\Command;

class TestPdfExport extends Command
{
    protected $signature = 'test:pdf {id?}';
    protected $description = 'ทดสอบ PDF export';

    public function handle()
    {
        $id = $this->argument('id');

        if (!$id) {
            // ทดสอบ basic PDF generation
            $this->info('Testing basic PDF generation...');

            try {
                $pdf = new \setasign\Fpdi\Tcpdf\Fpdi('P', 'mm', 'A4');
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetFont('freeserif', '', 14);
                $pdf->AddPage();
                $pdf->SetXY(20, 20);
                $pdf->Cell(100, 10, 'ทดสอบภาษาไทย - Test Thai', 0, 0, 'L');

                $output_path = storage_path('app/temp/test_pdf.pdf');

                if (!file_exists(dirname($output_path))) {
                    mkdir(dirname($output_path), 0755, true);
                }

                $pdf->Output($output_path, 'F');

                if (file_exists($output_path)) {
                    $this->info('SUCCESS: PDF created at ' . $output_path);
                    $this->info('File size: ' . filesize($output_path) . ' bytes');
                } else {
                    $this->error('FAILED: PDF file not created');
                }
            } catch (\Exception $e) {
                $this->error('ERROR: ' . $e->getMessage());
            }

            return;
        }

        // ทดสอบกับ leave request จริง
        $leave_request = LeaveRequest::with(['user', 'approver'])->find($id);

        if (!$leave_request) {
            $this->error("Leave request #{$id} not found");
            return;
        }

        try {
            $service = new LeaveRequestPdfService();
            $pdf_path = $service->Export($leave_request);

            if (file_exists($pdf_path)) {
                $this->info('SUCCESS: PDF created at ' . $pdf_path);
                $this->info('File size: ' . filesize($pdf_path) . ' bytes');
            } else {
                $this->error('FAILED: PDF file not created');
            }
        } catch (\Exception $e) {
            $this->error('ERROR: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
        }
    }
}
