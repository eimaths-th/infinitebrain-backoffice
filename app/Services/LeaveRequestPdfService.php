<?php

namespace App\Services;

use App\Models\LeaveRequest;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveRequestPdfService
{
    protected $pdf;

    protected $template_map = [
        'sick' => 'ใบลา_อินฟินิท2023.pdf',
        'personal' => 'ใบลา_อินฟินิท2023.pdf',
        'maternity' => 'ใบลา_อินฟินิท2023.pdf',
        'ordination' => 'ใบลา_อินฟินิท2023.pdf',
        'military' => 'ใบลา_อินฟินิท2023.pdf',
        'vacation' => 'ใบลาพักผ่อน_อินฟินิท.pdf',
        'compensate' => 'ใบขอใช้วันหยุดชดเชย สลับวันหยุด.pdf',
        'swap_day' => 'ใบขอใช้วันหยุดชดเชย สลับวันหยุด.pdf',
        'remote_work' => 'ใบขขออนุญาตทำงานนอกสถานที่.pdf',
    ];

    protected $thai_months = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม',
    ];

    // พิกัดสำหรับ template ใบลาทั่วไป (sick, personal, maternity, ordination, military)
    protected $general_leave_positions = [
        // Header - วันที่ยื่น
        'date_day' => ['x' => 125, 'y' => 55, 'w' => 10, 'size' => 10, 'bold' => false],
        'date_month' => ['x' => 145, 'y' => 55, 'w' => 25, 'size' => 10, 'bold' => false],
        'date_year' => ['x' => 175, 'y' => 55, 'w' => 15, 'size' => 10, 'bold' => false],

        // ข้อมูลพนักงาน
        'prefix_employee_name' => ['x' => 65, 'y' => 84.5, 'w' => 60, 'size' => 10, 'bold' => false],
        'position' => ['x' => 130, 'y' => 84.5, 'w' => 50, 'size' => 10, 'bold' => false],

        // Checkboxes - ประเภทการลา (พิกัดสำหรับ ☑)
        'check_sick' => [
            ['x' => 54, 'y' => 64.5],
            ['x' => 50, 'y' => 93],
        ], //54
        'check_personal' => [
            ['x' => 70, 'y' => 64.5],
            ['x' => 50, 'y' => 101],
        ],
        'check_maternity' => [
            ['x' => 95, 'y' => 64.5],
            ['x' => 50, 'y' => 108],
        ],
        // 'check_ordination' => [
        //     ['x' => 145, 'y' => 64.5],
        // ],
        // 'check_military' => [
        //     ['x' => 25, 'y' => 64.5],
        // ],

        // วันที่ลา
        'start_day' => ['x' => 40, 'y' => 117.5, 'w' => 10, 'size' => 10, 'bold' => false],
        'start_month' => ['x' => 45, 'y' => 117.5, 'w' => 25, 'size' => 10, 'bold' => false],
        'start_year' => ['x' => 65, 'y' => 117.5, 'w' => 15, 'size' => 10, 'bold' => false],
        'end_day' => ['x' => 90, 'y' => 117.5, 'w' => 10, 'size' => 10, 'bold' => false],
        'end_month' => ['x' => 100, 'y' => 117.5, 'w' => 25, 'size' => 10, 'bold' => false],
        'end_year' => ['x' => 115, 'y' => 117.5, 'w' => 15, 'size' => 10, 'bold' => false],
        'total_days' => ['x' => 160, 'y' => 117.5, 'w' => 20, 'size' => 10, 'bold' => false],

        // เหตุผล
        'reason' => ['x' => 85, 'y' => 101, 'w' => 150, 'size' => 10, 'bold' => false],

        // ติดต่อ
        // ติดต่อ
        'contact_phone' => ['x' => 70, 'y' => 125, 'w' => 50, 'size' => 10, 'bold' => false],
        'contact_address' => [
            ['x' => 165, 'y' => 124],
            ['x' => 125, 'y' => 124],
            ['x' => 55, 'y' => 131],
            ['x' => 100, 'y' => 131],
            ['x' => 150, 'y' => 131],
        ],

        // สถิติการลา
        'sick_used' => ['x' => 138, 'y' => 234, 'w' => 15, 'size' => 10, 'bold' => false],
        'personal_used' => ['x' => 138, 'y' => 243, 'w' => 15, 'size' => 10, 'bold' => false],
        'maternity_used' => ['x' => 138, 'y' => 252, 'w' => 15, 'size' => 10, 'bold' => false],

        'total_days_sick' => ['x' => 155, 'y' => 234, 'w' => 15, 'size' => 10, 'bold' => false],
        'total_days_personal' => ['x' => 155, 'y' => 243, 'w' => 15, 'size' => 10, 'bold' => false],
        'total_days_maternity' => ['x' => 155, 'y' => 252, 'w' => 15, 'size' => 10, 'bold' => false],


        'sum_total_days_sick' => ['x' => 172, 'y' => 234, 'w' => 15, 'size' => 10, 'bold' => false],
        'sum_total_days_personal' => ['x' => 172, 'y' => 243, 'w' => 15, 'size' => 10, 'bold' => false],
        'sum_total_days_maternity' => ['x' => 172, 'y' => 252, 'w' => 15, 'size' => 10, 'bold' => false],

        // ลายเซ็น
        'employee_name' => ['x' => 155, 'y' => 177, 'w' => 60, 'size' => 10, 'bold' => false],
        'employee_signature' => ['x' => 157, 'y' => 163, 'w' => 26, 'h' => 8],
        'approver_signature' => ['x' => 35, 'y' => 203, 'w' =>  26, 'h' => 8],

        'check_approved' => ['x' => 25, 'y' => 184.5],
        'check_rejected' => ['x' => 25, 'y' => 193],
        // 'rejection_reason' => ['x' => 35, 'y' => 212, 'w' => 100],
        // 'approver_name' => ['x' => 115, 'y' => 210, 'w' => 50],
        // 'approved_date' => ['x' => 115, 'y' => 218, 'w' => 50],
    ];

    // พิกัดสำหรับ template ลาพักผ่อน
    protected $vacation_leave_positions = [
        'date_day' => ['x' => 145, 'y' => 35, 'w' => 10],
        'date_month' => ['x' => 158, 'y' => 35, 'w' => 25],
        'date_year' => ['x' => 185, 'y' => 35, 'w' => 15],

        'prefix_employee_name' => ['x' => 55, 'y' => 48, 'w' => 60],
        'employee_name' => ['x' => 55, 'y' => 200, 'w' => 60],
        'position' => ['x' => 145, 'y' => 48, 'w' => 50],

        'start_day' => ['x' => 55, 'y' => 70, 'w' => 10],
        'start_month' => ['x' => 70, 'y' => 70, 'w' => 25],
        'start_year' => ['x' => 100, 'y' => 70, 'w' => 15],
        'end_day' => ['x' => 130, 'y' => 70, 'w' => 10],
        'end_month' => ['x' => 145, 'y' => 70, 'w' => 25],
        'end_year' => ['x' => 175, 'y' => 70, 'w' => 15],
        'total_days' => ['x' => 55, 'y' => 78, 'w' => 20],

        'reason' => ['x' => 45, 'y' => 90, 'w' => 150],

        'contact_phone' => ['x' => 75, 'y' => 105, 'w' => 50],
        'contact_address' => ['x' => 55, 'y' => 113, 'w' => 140],

        'vacation_quota' => ['x' => 85, 'y' => 135, 'w' => 15],
        'vacation_used' => ['x' => 85, 'y' => 143, 'w' => 15],
        'vacation_remaining' => ['x' => 85, 'y' => 151, 'w' => 15],

        'employee_signature' => ['x' => 30, 'y' => 175, 'w' => 40, 'h' => 20],
        'approver_signature' => ['x' => 120, 'y' => 175, 'w' => 40, 'h' => 20],
        'check_approved' => ['x' => 110, 'y' => 175],
        'check_rejected' => ['x' => 110, 'y' => 185],
        'rejection_reason' => ['x' => 120, 'y' => 185, 'w' => 50],
        'approver_name' => ['x' => 115, 'y' => 200, 'w' => 50],
        'approved_date' => ['x' => 115, 'y' => 208, 'w' => 50],
    ];

    // พิกัดสำหรับ template ชดเชย/สลับวัน
    protected $compensate_leave_positions = [
        'date_day' => ['x' => 145, 'y' => 35, 'w' => 10],
        'date_month' => ['x' => 158, 'y' => 35, 'w' => 25],
        'date_year' => ['x' => 185, 'y' => 35, 'w' => 15],

        'employee_name' => ['x' => 55, 'y' => 48, 'w' => 60],
        'position' => ['x' => 145, 'y' => 48, 'w' => 50],

        'check_compensate' => ['x' => 25, 'y' => 65],
        'check_swap' => ['x' => 100, 'y' => 65],

        'compensate_day' => ['x' => 70, 'y' => 78, 'w' => 10],
        'compensate_month' => ['x' => 85, 'y' => 78, 'w' => 25],
        'compensate_year' => ['x' => 115, 'y' => 78, 'w' => 15],

        'start_day' => ['x' => 55, 'y' => 93, 'w' => 10],
        'start_month' => ['x' => 70, 'y' => 93, 'w' => 25],
        'start_year' => ['x' => 100, 'y' => 93, 'w' => 15],
        'end_day' => ['x' => 130, 'y' => 93, 'w' => 10],
        'end_month' => ['x' => 145, 'y' => 93, 'w' => 25],
        'end_year' => ['x' => 175, 'y' => 93, 'w' => 15],
        'total_days' => ['x' => 55, 'y' => 101, 'w' => 20],

        'reason' => ['x' => 45, 'y' => 115, 'w' => 150],

        'employee_signature' => ['x' => 120, 'y' => 160, 'w' => 40, 'h' => 20],
        'approver_signature' => ['x' => 120, 'y' => 160, 'w' => 40, 'h' => 20],
        'approver_name' => ['x' => 115, 'y' => 185, 'w' => 50],
        'approved_date' => ['x' => 115, 'y' => 193, 'w' => 50],
    ];

    // พิกัดสำหรับ template ทำงานนอกสถานที่
    protected $remote_work_positions = [
        'date_day' => ['x' => 145, 'y' => 35, 'w' => 10],
        'date_month' => ['x' => 158, 'y' => 35, 'w' => 25],
        'date_year' => ['x' => 185, 'y' => 35, 'w' => 15],

        'employee_name' => ['x' => 55, 'y' => 48, 'w' => 60],
        'position' => ['x' => 145, 'y' => 48, 'w' => 50],

        'start_day' => ['x' => 55, 'y' => 68, 'w' => 10],
        'start_month' => ['x' => 70, 'y' => 68, 'w' => 25],
        'start_year' => ['x' => 100, 'y' => 68, 'w' => 15],
        'end_day' => ['x' => 130, 'y' => 68, 'w' => 10],
        'end_month' => ['x' => 145, 'y' => 68, 'w' => 25],
        'end_year' => ['x' => 175, 'y' => 68, 'w' => 15],

        'start_time' => ['x' => 55, 'y' => 78, 'w' => 20],
        'end_time' => ['x' => 100, 'y' => 78, 'w' => 20],

        'location' => ['x' => 55, 'y' => 90, 'w' => 140],
        'reason' => ['x' => 45, 'y' => 105, 'w' => 150],

        'employee_signature' => ['x' => 30, 'y' => 150, 'w' => 40, 'h' => 20],
        'approver_signature' => ['x' => 120, 'y' => 150, 'w' => 40, 'h' => 20],
        'approver_name' => ['x' => 115, 'y' => 175, 'w' => 50],
        'approved_date' => ['x' => 115, 'y' => 183, 'w' => 50],
    ];

    public function Export(LeaveRequest $leave_request): string
    {
        $template_file = $this->GetTemplateFile($leave_request->leave_type);

        if (!file_exists($template_file)) {
            throw new \Exception("ไม่พบไฟล์ต้นฉบับ PDF: {$template_file}");
        }

        // สร้าง FPDI instance
        $this->pdf = new Fpdi('P', 'mm', 'A4');

        // ตั้งค่า PDF
        $this->pdf->SetCreator('InfiniteBrain Backoffice');
        $this->pdf->SetAuthor('InfiniteBrain');
        $this->pdf->SetTitle('ใบลา');
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->SetAutoPageBreak(false, 0);

        // เพิ่ม Thai font - ใช้ THSarabunNew ถ้ามี หรือ fallback เป็น freeserif
        $font_path = public_path('font/THSarabunNew.ttf');
        if (file_exists($font_path)) {
            // เพิ่ม custom font
            $font_name = \TCPDF_FONTS::addTTFfont($font_path, 'TrueTypeUnicode', '', 96);
            if ($font_name) {
                $this->pdf->SetFont($font_name, '', 14);
            } else {
                // Fallback to freeserif ซึ่งรองรับ Thai
                $this->pdf->SetFont('freeserif', '', 14);
            }
        } else {
            // Fallback to freeserif ซึ่งรองรับ Thai
            $this->pdf->SetFont('freeserif', '', 14);
        }

        // โหลด template
        $page_count = $this->pdf->setSourceFile($template_file);
        $template_id = $this->pdf->importPage(1);

        // เพิ่มหน้าใหม่และใช้ template เป็น background
        $this->pdf->AddPage();
        $this->pdf->useTemplate($template_id, 0, 0, 210, 297);

        // ตั้ง font อีกครั้งหลัง AddPage (TCPDF reset font บางกรณี)
        $font_path = public_path('font/THSarabunNew.ttf');
        if (file_exists($font_path)) {
            $font_name = \TCPDF_FONTS::addTTFfont($font_path, 'TrueTypeUnicode', '', 96);
            if ($font_name) {
                $this->pdf->SetFont($font_name, '', 14);
            } else {
                $this->pdf->SetFont('freeserif', '', 14);
            }
        } else {
            $this->pdf->SetFont('freeserif', '', 14);
        }

        // วางข้อมูลตามประเภทใบลา
        switch ($leave_request->leave_type) {
            case 'sick':
            case 'personal':
            case 'maternity':
            case 'ordination':
            case 'military':
                $this->FillGeneralLeave($leave_request);
                break;
            case 'vacation':
                $this->FillVacationLeave($leave_request);
                break;
            case 'compensate':
            case 'swap_day':
                $this->FillCompensateLeave($leave_request);
                break;
            case 'remote_work':
                $this->FillRemoteWorkLeave($leave_request);
                break;
        }

        // สร้างไฟล์ output
        $output_path = storage_path('app/temp/leave_request_' . $leave_request->id . '.pdf');

        if (!file_exists(dirname($output_path))) {
            mkdir(dirname($output_path), 0755, true);
        }

        $this->pdf->Output($output_path, 'F');

        return $output_path;
    }

    protected function GetTemplateFile(string $leave_type): string
    {
        $template_name = $this->template_map[$leave_type] ?? 'ใบลา_อินฟินิท2023.pdf';
        return base_path('docs/' . $template_name);
    }

    protected function FillGeneralLeave(LeaveRequest $leave_request): void
    {
        $pos = $this->general_leave_positions;

        $submitted_date = $leave_request->submitted_date ?? $leave_request->created_at;
        $start_date = $leave_request->start_date;
        $end_date = $leave_request->end_date;

        // วันที่ยื่น
        $this->WriteText($pos['date_day'], $submitted_date->day);
        $this->WriteText($pos['date_month'], $this->thai_months[$submitted_date->month]);
        $this->WriteText($pos['date_year'], $submitted_date->year + 543);

        // ข้อมูลพนักงาน
        if (isset($pos['prefix_employee_name'])) {
            $this->WriteText($pos['prefix_employee_name'], $leave_request->user->GetDisplayName() ?? '');
        }
        if (isset($pos['employee_name'])) {
            $this->WriteText($pos['employee_name'], $leave_request->user->GetFullName() ?? '');
        }
        if (isset($pos['title_prefix'])) {
            $this->WriteText($pos['title_prefix'], $leave_request->user->title_prefix ?? '');
        }
        $this->WriteText($pos['position'], $leave_request->user->position ?? 'พนักงาน');

        // Checkbox ประเภทการลา - เขียนทุกประเภท: ☑ สำหรับที่เลือก, ☐ สำหรับที่ไม่เลือก
        $leave_types = ['sick', 'personal', 'maternity', 'ordination', 'military'];
        foreach ($leave_types as $type) {
            $checkbox_key = 'check_' . $type;
            if (isset($pos[$checkbox_key])) {
                if ($leave_request->leave_type === $type) {
                    $this->WriteCheckbox($pos[$checkbox_key]);
                } else {
                    $this->WriteUncheckbox($pos[$checkbox_key]);
                }
            }
        }

        // วันที่ลา
        $this->WriteText($pos['start_day'], $start_date->day);
        $this->WriteText($pos['start_month'], $this->thai_months[$start_date->month]);
        $this->WriteText($pos['start_year'], $start_date->year + 543);
        $this->WriteText($pos['end_day'], $end_date->day);
        $this->WriteText($pos['end_month'], $this->thai_months[$end_date->month]);
        $this->WriteText($pos['end_year'], $end_date->year + 543);
        $this->WriteText($pos['total_days'], $leave_request->total_days);

        // เหตุผล
        $this->WriteText($pos['reason'], $leave_request->reason ?? '');

        // ข้อมูลติดต่อ
        $this->WriteText($pos['contact_phone'], $leave_request->contact_phone ?? '');
        $this->WriteText($pos['contact_address'], $leave_request->contact_address ?? '');

        // สถิติการลา
        $this->FillLeaveStats($pos, $leave_request);

        // ผลการอนุมัติ
        $this->FillApprovalStatus($pos, $leave_request);

        // ลายเซ็น
        $this->PlaceSignature($pos['employee_signature'], $leave_request->employee_signature, $leave_request->signature_is_image);

        if ($leave_request->IsApproved()) {
            $this->PlaceApproverSignature($pos['approver_signature'], $leave_request->approver_signature);
            if (isset($pos['approver_name'])) {
                $this->WriteText($pos['approver_name'], $leave_request->approver->name ?? '');
            }

            if ($leave_request->approved_at && isset($pos['approved_date'])) {
                $approved_date = $leave_request->approved_at->format('d/m/') . ($leave_request->approved_at->year + 543);
                $this->WriteText($pos['approved_date'], $approved_date);
            }
        }
    }

    protected function FillVacationLeave(LeaveRequest $leave_request): void
    {
        $pos = $this->vacation_leave_positions;

        $submitted_date = $leave_request->submitted_date ?? $leave_request->created_at;
        $start_date = $leave_request->start_date;
        $end_date = $leave_request->end_date;

        // วันที่ยื่น
        $this->WriteText($pos['date_day'], $submitted_date->day);
        $this->WriteText($pos['date_month'], $this->thai_months[$submitted_date->month]);
        $this->WriteText($pos['date_year'], $submitted_date->year + 543);

        // ข้อมูลพนักงาน
        if (isset($pos['prefix_employee_name'])) {
            $this->WriteText($pos['prefix_employee_name'], $leave_request->user->GetDisplayName() ?? '');
        }
        if (isset($pos['employee_name'])) {
            $this->WriteText($pos['employee_name'], $leave_request->user->name ?? '');
        }
        if (isset($pos['title_prefix'])) {
            $this->WriteText($pos['title_prefix'], $leave_request->user->title_prefix ?? '');
        }
        $this->WriteText($pos['position'], $leave_request->user->position ?? 'พนักงาน');

        // วันที่ลา
        $this->WriteText($pos['start_day'], $start_date->day);
        $this->WriteText($pos['start_month'], $this->thai_months[$start_date->month]);
        $this->WriteText($pos['start_year'], $start_date->year + 543);
        $this->WriteText($pos['end_day'], $end_date->day);
        $this->WriteText($pos['end_month'], $this->thai_months[$end_date->month]);
        $this->WriteText($pos['end_year'], $end_date->year + 543);
        $this->WriteText($pos['total_days'], $leave_request->total_days);

        // เหตุผล
        $this->WriteText($pos['reason'], $leave_request->reason ?? '');

        // ข้อมูลติดต่อ
        $this->WriteText($pos['contact_phone'], $leave_request->contact_phone ?? '');
        $this->WriteText($pos['contact_address'], $leave_request->contact_address ?? '');

        // สถิติการลาพักผ่อน (ใช้ FillLeaveStats แทน)
        $this->FillLeaveStats($pos, $leave_request);

        // ผลการอนุมัติ
        $this->FillApprovalStatus($pos, $leave_request);

        // ลายเซ็น
        $this->PlaceSignature($pos['employee_signature'], $leave_request->employee_signature, $leave_request->signature_is_image);

        if ($leave_request->IsApproved()) {
            $this->PlaceApproverSignature($pos['approver_signature'], $leave_request->approver_signature);
            if (isset($pos['approver_name'])) {
                $this->WriteText($pos['approver_name'], $leave_request->approver->name ?? '');
            }

            if ($leave_request->approved_at && isset($pos['approved_date'])) {
                $approved_date = $leave_request->approved_at->format('d/m/') . ($leave_request->approved_at->year + 543);
                $this->WriteText($pos['approved_date'], $approved_date);
            }
        }
    }

    protected function FillCompensateLeave(LeaveRequest $leave_request): void
    {
        $pos = $this->compensate_leave_positions;

        $submitted_date = $leave_request->submitted_date ?? $leave_request->created_at;
        $start_date = $leave_request->start_date;
        $end_date = $leave_request->end_date;

        // วันที่ยื่น
        $this->WriteText($pos['date_day'], $submitted_date->day);
        $this->WriteText($pos['date_month'], $this->thai_months[$submitted_date->month]);
        $this->WriteText($pos['date_year'], $submitted_date->year + 543);

        // ข้อมูลพนักงาน
        if (isset($pos['prefix_employee_name'])) {
            $this->WriteText($pos['prefix_employee_name'], $leave_request->user->GetDisplayName() ?? '');
        }
        if (isset($pos['employee_name'])) {
            $this->WriteText($pos['employee_name'], $leave_request->user->name ?? '');
        }
        if (isset($pos['title_prefix'])) {
            $this->WriteText($pos['title_prefix'], $leave_request->user->title_prefix ?? '');
        }
        $this->WriteText($pos['position'], $leave_request->user->position ?? 'พนักงาน');

        // Checkbox
        if ($leave_request->leave_type === 'compensate') {
            $this->WriteCheckbox($pos['check_compensate']);
        } else {
            $this->WriteCheckbox($pos['check_swap']);
        }

        // วันที่ชดเชย
        if ($leave_request->compensate_for_date) {
            $comp_date = Carbon::parse($leave_request->compensate_for_date);
            $this->WriteText($pos['compensate_day'], $comp_date->day);
            $this->WriteText($pos['compensate_month'], $this->thai_months[$comp_date->month]);
            $this->WriteText($pos['compensate_year'], $comp_date->year + 543);
        }

        // วันที่ลา
        $this->WriteText($pos['start_day'], $start_date->day);
        $this->WriteText($pos['start_month'], $this->thai_months[$start_date->month]);
        $this->WriteText($pos['start_year'], $start_date->year + 543);
        $this->WriteText($pos['end_day'], $end_date->day);
        $this->WriteText($pos['end_month'], $this->thai_months[$end_date->month]);
        $this->WriteText($pos['end_year'], $end_date->year + 543);
        $this->WriteText($pos['total_days'], $leave_request->total_days);

        // เหตุผล
        $this->WriteText($pos['reason'], $leave_request->reason ?? '');

        // สถิติการลา
        $this->FillLeaveStats($pos, $leave_request);

        // ผลการอนุมัติ
        $this->FillApprovalStatus($pos, $leave_request);

        // ลายเซ็น
        $this->PlaceSignature($pos['employee_signature'], $leave_request->employee_signature, $leave_request->signature_is_image);

        if ($leave_request->IsApproved()) {
            $this->PlaceApproverSignature($pos['approver_signature'], $leave_request->approver_signature);
            if (isset($pos['approver_name'])) {
                $this->WriteText($pos['approver_name'], $leave_request->approver->name ?? '');
            }

            if ($leave_request->approved_at && isset($pos['approved_date'])) {
                $approved_date = $leave_request->approved_at->format('d/m/') . ($leave_request->approved_at->year + 543);
                $this->WriteText($pos['approved_date'], $approved_date);
            }
        }
    }

    protected function FillRemoteWorkLeave(LeaveRequest $leave_request): void
    {
        $pos = $this->remote_work_positions;

        $submitted_date = $leave_request->submitted_date ?? $leave_request->created_at;
        $start_date = $leave_request->start_date;
        $end_date = $leave_request->end_date;

        // วันที่ยื่น
        $this->WriteText($pos['date_day'], $submitted_date->day);
        $this->WriteText($pos['date_month'], $this->thai_months[$submitted_date->month]);
        $this->WriteText($pos['date_year'], $submitted_date->year + 543);

        // ข้อมูลพนักงาน
        if (isset($pos['prefix_employee_name'])) {
            $this->WriteText($pos['prefix_employee_name'], $leave_request->user->GetDisplayName() ?? '');
        }
        if (isset($pos['employee_name'])) {
            $this->WriteText($pos['employee_name'], $leave_request->user->name ?? '');
        }
        if (isset($pos['title_prefix'])) {
            $this->WriteText($pos['title_prefix'], $leave_request->user->title_prefix ?? '');
        }
        $this->WriteText($pos['position'], $leave_request->user->position ?? 'พนักงาน');

        // วันที่
        $this->WriteText($pos['start_day'], $start_date->day);
        $this->WriteText($pos['start_month'], $this->thai_months[$start_date->month]);
        $this->WriteText($pos['start_year'], $start_date->year + 543);
        $this->WriteText($pos['end_day'], $end_date->day);
        $this->WriteText($pos['end_month'], $this->thai_months[$end_date->month]);
        $this->WriteText($pos['end_year'], $end_date->year + 543);

        // เวลา
        $this->WriteText($pos['start_time'], $leave_request->start_time ?? '');
        $this->WriteText($pos['end_time'], $leave_request->end_time ?? '');

        // สถานที่และเหตุผล
        $this->WriteText($pos['location'], $leave_request->location ?? '');
        $this->WriteText($pos['reason'], $leave_request->reason ?? '');

        // สถิติการลา
        $this->FillLeaveStats($pos, $leave_request);

        // ผลการอนุมัติ
        $this->FillApprovalStatus($pos, $leave_request);

        // ลายเซ็น
        $this->PlaceSignature($pos['employee_signature'], $leave_request->employee_signature, $leave_request->signature_is_image);

        if ($leave_request->IsApproved()) {
            $this->PlaceApproverSignature($pos['approver_signature'], $leave_request->approver_signature);
            $this->WriteText($pos['approver_name'], $leave_request->approver->name ?? '');

            if ($leave_request->approved_at) {
                $approved_date = $leave_request->approved_at->format('d/m/') . ($leave_request->approved_at->year + 543);
                $this->WriteText($pos['approved_date'], $approved_date);
            }
        }
    }

    protected function FillApprovalStatus(array $pos, LeaveRequest $leave_request): void
    {
        if (isset($pos['check_approved'])) {
            if ($leave_request->IsApproved()) {
                $this->WriteCheckbox($pos['check_approved']);
            } else {
                $this->WriteUncheckbox($pos['check_approved']);
            }
        }

        if (isset($pos['check_rejected'])) {
            if ($leave_request->IsRejected()) {
                $this->WriteCheckbox($pos['check_rejected']);
            } else {
                $this->WriteUncheckbox($pos['check_rejected']);
            }
        }

        if (isset($pos['rejection_reason'])) {
            $this->WriteText($pos['rejection_reason'], $leave_request->rejection_reason ?? '');
        }
    }

    protected function FillLeaveStats(array $pos, LeaveRequest $leave_request): void
    {
        $year = $leave_request->start_date->year;
        $user_id = $leave_request->user_id;

        $vacation_used = LeaveRequest::GetUserLeaveUsedByType($user_id, 'vacation', $year);
        $personal_used = LeaveRequest::GetUserLeaveUsedByType($user_id, 'personal', $year);
        $sick_used = LeaveRequest::GetUserLeaveUsedByType($user_id, 'sick', $year);

        $vacation_quota = LeaveRequest::LEAVE_QUOTAS['vacation'] ?? 6;
        $personal_quota = LeaveRequest::LEAVE_QUOTAS['personal'] ?? 3;
        $sick_quota = LeaveRequest::LEAVE_QUOTAS['sick'] ?? 30;

        // Quota / Used / Remaining
        if (isset($pos['vacation_quota'])) $this->WriteText($pos['vacation_quota'], $vacation_quota);
        if (isset($pos['vacation_used'])) $this->WriteText($pos['vacation_used'], $vacation_used);
        if (isset($pos['vacation_remaining'])) $this->WriteText($pos['vacation_remaining'], $vacation_quota - $vacation_used);

        if (isset($pos['personal_quota'])) $this->WriteText($pos['personal_quota'], $personal_quota);
        if (isset($pos['personal_used'])) $this->WriteText($pos['personal_used'], $personal_used);
        if (isset($pos['personal_remaining'])) $this->WriteText($pos['personal_remaining'], $personal_quota - $personal_used);

        if (isset($pos['sick_quota'])) $this->WriteText($pos['sick_quota'], $sick_quota);
        if (isset($pos['sick_used'])) $this->WriteText($pos['sick_used'], $sick_used);
        if (isset($pos['sick_remaining'])) $this->WriteText($pos['sick_remaining'], $sick_quota - $sick_used);

        // Stats Year
        if (isset($pos['stats_year'])) $this->WriteText($pos['stats_year'], $year + 543);

        // Sum Total Days (Current Request + Used)
        if (isset($pos['sum_total_days_sick'])) $this->WriteText($pos['sum_total_days_sick'], ($leave_request->total_days + $sick_used) ?: 0);
        if (isset($pos['sum_total_days_personal'])) $this->WriteText($pos['sum_total_days_personal'], ($leave_request->total_days + $personal_used) ?: 0);
        if (isset($pos['sum_total_days_vacation'])) $this->WriteText($pos['sum_total_days_vacation'], ($leave_request->total_days + $vacation_used) ?: 0);

        // Total Days (Current Request Only)
        if (isset($pos['total_days_sick'])) $this->WriteText($pos['total_days_sick'], $leave_request->leave_type === 'sick' ? $leave_request->total_days : 0);
        if (isset($pos['total_days_personal'])) $this->WriteText($pos['total_days_personal'], $leave_request->leave_type === 'personal' ? $leave_request->total_days : 0);
        if (isset($pos['total_days_vacation'])) $this->WriteText($pos['total_days_vacation'], $leave_request->leave_type === 'vacation' ? $leave_request->total_days : 0);
    }

    protected function WriteText(array $position, $text, int $font_size = 14): void
    {
        if (empty($text) && $text !== 0) {
            return;
        }

        // รองรับทั้ง single position และ array ของ positions
        if (isset($position[0]) && is_array($position[0])) {
            // เป็น array ของ positions - วนลูปเขียนทุกตำแหน่ง
            foreach ($position as $pos) {
                $x = $pos['x'] ?? 0;
                $y = $pos['y'] ?? 0;
                $w = $pos['w'] ?? 50;
                $size = $pos['size'] ?? $font_size;
                $bold = $pos['bold'] ?? false;

                $style = $bold ? 'B' : '';
                $this->pdf->SetFont('', $style, $size);
                $this->pdf->SetXY($x, $y);
                $this->pdf->Cell($w, 5, $text, 0, 0, 'L');
            }
        } else {
            // single position
            $x = $position['x'] ?? 0;
            $y = $position['y'] ?? 0;
            $w = $position['w'] ?? 50;
            $size = $position['size'] ?? $font_size;
            $bold = $position['bold'] ?? false;

            $style = $bold ? 'B' : '';
            $this->pdf->SetFont('', $style, $size);
            $this->pdf->SetXY($x, $y);
            $this->pdf->Cell($w, 5, $text, 0, 0, 'L');
        }

        // Reset font กลับ
        $this->pdf->SetFont('', '', 14);
    }

    protected function WriteCheckbox(?array $position): void
    {
        if (!$position) {
            return;
        }

        // รองรับทั้ง single position และ array ของ positions
        // ตรวจสอบว่าเป็น array of positions หรือ single position
        if (isset($position[0]) && is_array($position[0])) {
            // เป็น array ของ positions - วนลูปเขียนทุกตำแหน่ง
            foreach ($position as $pos) {
                $x = $pos['x'] ?? 0;
                $y = $pos['y'] ?? 0;
                $this->pdf->SetXY($x, $y);
                $this->pdf->Cell(5, 5, '☑', 0, 0, 'C');
            }
        } else {
            // single position
            $x = $position['x'] ?? 0;
            $y = $position['y'] ?? 0;
            $this->pdf->SetXY($x, $y);
            $this->pdf->Cell(5, 5, '☑', 0, 0, 'C');
        }
    }

    protected function WriteUncheckbox(?array $position): void
    {
        if (!$position) {
            return;
        }

        // รองรับทั้ง single position และ array ของ positions
        if (isset($position[0]) && is_array($position[0])) {
            foreach ($position as $pos) {
                $x = $pos['x'] ?? 0;
                $y = $pos['y'] ?? 0;
                $this->pdf->SetXY($x, $y);
                $this->pdf->Cell(5, 5, '☐', 0, 0, 'C');
            }
        } else {
            $x = $position['x'] ?? 0;
            $y = $position['y'] ?? 0;
            $this->pdf->SetXY($x, $y);
            $this->pdf->Cell(5, 5, '☐', 0, 0, 'C');
        }
    }

    protected function PlaceSignature(array $position, ?string $signature, bool $is_image = false): void
    {
        if (empty($signature)) {
            return;
        }

        $x = $position['x'] ?? 0;
        $y = $position['y'] ?? 0;
        $w = $position['w'] ?? 40;
        $h = $position['h'] ?? 20;

        $original_path = $this->GetSignatureImagePath($signature, $is_image);
        $image_path = null;

        // แปลงรูปให้เป็น Transparent PNG (แก้ปัญหา background ดำ หรือ ขาวทับเส้น)
        if ($original_path && file_exists($original_path)) {
            $image_path = $this->ConvertImageToTransparentPng($original_path, 'emp_pdf_sig_' . time());
        }

        if ($image_path && file_exists($image_path)) {
            try {
                $this->pdf->Image($image_path, $x, $y, $w, $h, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            } catch (\Exception $e) {
                // Ignore image errors
            }
        }
    }

    protected function PlaceApproverSignature(array $position, ?string $signature): void
    {
        if (empty($signature)) {
            return;
        }

        $x = $position['x'] ?? 0;
        $y = $position['y'] ?? 0;
        $w = $position['w'] ?? 40;
        $h = $position['h'] ?? 20;

        $image_path = null;
        $original_path = null;

        // กรณี base64 data URL
        if (strpos($signature, 'data:image') === 0) {
            $image_path = $this->SaveBase64ToTemp($signature, 'approver_pdf_sig_' . time());
        }
        // กรณี /storage path
        elseif (strpos($signature, '/storage') === 0) {
            $relative_path = str_replace('/storage/', '', $signature);
            $original_path = storage_path('app/public/' . $relative_path);

            if (!file_exists($original_path)) {
                $original_path = public_path('storage/' . $relative_path);
            }
        }
        // กรณี storage path โดยตรง
        else {
            $original_path = storage_path('app/public/' . $signature);

            if (!file_exists($original_path)) {
                $original_path = public_path('storage/' . $signature);
            }
        }

        // แปลงรูปให้เป็น Transparent PNG (แก้ปัญหา background ดำ หรือ ขาวทับเส้น)
        if ($original_path && file_exists($original_path)) {
            $image_path = $this->ConvertImageToTransparentPng($original_path, 'approver_pdf_sig_' . time());
        }

        if ($image_path && file_exists($image_path)) {
            try {
                $this->pdf->Image($image_path, $x, $y, $w, $h, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            } catch (\Exception $e) {
                // Ignore image errors
            }
        }
    }

    protected function GetSignatureImagePath(?string $signature, bool $is_image = false): ?string
    {
        if (empty($signature)) {
            return null;
        }

        // กรณี base64 data URL
        if (strpos($signature, 'data:image') === 0) {
            return $this->SaveBase64ToTemp($signature, 'emp_pdf_sig_' . time());
        }

        // กรณีเป็น path
        if ($is_image || !str_starts_with($signature, 'data:')) {
            $path = storage_path('app/public/' . $signature);

            if (file_exists($path)) {
                return $path;
            }

            $path = public_path('storage/' . $signature);

            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function SaveBase64ToTemp(string $base64_data, string $filename): ?string
    {
        if (strpos($base64_data, 'data:image') !== 0) {
            return null;
        }

        $parts = explode(',', $base64_data);

        if (count($parts) !== 2) {
            return null;
        }

        $temp_path = storage_path('app/temp/' . $filename . '.png');

        if (!file_exists(dirname($temp_path))) {
            mkdir(dirname($temp_path), 0755, true);
        }

        try {
            // ใช้ Intervention Image เพื่อแปลงรูปให้มี background สีขาว
            // (TCPDF มีปัญหากับ transparency จะแสดงเป็นพื้นดำ)
            $image = \Intervention\Image\Facades\Image::make($base64_data);

            $width = $image->width();
            $height = $image->height();

            // สร้าง canvas ใส (Transparent)
            $canvas = \Intervention\Image\Facades\Image::canvas($width, $height);
            $canvas->insert($image);

            // บันทึกเป็น PNG
            $canvas->encode('png')->save($temp_path);

            return $temp_path;
        } catch (\Exception $e) {
            // Fallback: บันทึกแบบเดิม
            $image_data = base64_decode($parts[1]);
            file_put_contents($temp_path, $image_data);
            return $temp_path;
        }
    }


    /**
     * แปลงรูปเป็น PNG แบบ Transparent (แก้ไขชื่อ function จาก ConvertImageWithWhiteBackground)
     */
    protected function ConvertImageToTransparentPng(string $source_path, string $filename): ?string
    {
        try {
            $temp_path = storage_path('app/temp/' . $filename . '.png');

            if (!file_exists(dirname($temp_path))) {
                mkdir(dirname($temp_path), 0755, true);
            }

            $image = \Intervention\Image\Facades\Image::make($source_path);

            // สร้าง Canvas ใส (Transparent) เพื่อรับประกันว่าไม่มีพื้นหลัง
            $canvas = \Intervention\Image\Facades\Image::canvas($image->width(), $image->height());
            $canvas->insert($image);

            // บันทึกเป็น PNG
            $canvas->encode('png')->save($temp_path);

            return $temp_path;
        } catch (\Exception $e) {
            // Fallback: ใช้ไฟล์เดิม
            return $source_path;
        }
    }
}
