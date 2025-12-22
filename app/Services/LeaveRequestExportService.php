<?php

namespace App\Services;

use App\Models\LeaveRequest;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveRequestExportService
{
    protected $template_map = [
        'sick' => 'ใบลา_อินฟินิท2023.docx',
        'personal' => 'ใบลา_อินฟินิท2023.docx',
        'maternity' => 'ใบลา_อินฟินิท2023.docx',
        'ordination' => 'ใบลา_อินฟินิท2023.docx',
        'military' => 'ใบลา_อินฟินิท2023.docx',
        'vacation' => 'ใบลาพักผ่อน_อินฟินิท(1).docx',
        'compensate' => 'ใบขอใช้วันหยุดชดเชย สลับวันหยุด.docx',
        'swap_day' => 'ใบขอใช้วันหยุดชดเชย สลับวันหยุด.docx',
        'remote_work' => 'ใบขขออนุญาตทำงานนอกสถานที่.docx',
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

    public function Export(LeaveRequest $leave_request)
    {
        $template_file = $this->GetTemplateFile($leave_request->leave_type);

        if (!file_exists($template_file)) {
            throw new \Exception("ไม่พบไฟล์ต้นฉบับ: {$template_file}");
        }

        $template = new TemplateProcessor($template_file);

        $this->FillCommonData($template, $leave_request);

        switch ($leave_request->leave_type) {
            case 'sick':
            case 'personal':
            case 'maternity':
            case 'ordination':
            case 'military':
                $this->FillGeneralLeave($template, $leave_request);
                break;
            case 'vacation':
                $this->FillVacationLeave($template, $leave_request);
                break;
            case 'compensate':
            case 'swap_day':
                $this->FillCompensateLeave($template, $leave_request);
                break;
            case 'remote_work':
                $this->FillRemoteWorkLeave($template, $leave_request);
                break;
        }

        $this->FillSignatures($template, $leave_request);
        $this->FillApproval($template, $leave_request);

        $output_path = storage_path('app/temp/leave_request_' . $leave_request->id . '.docx');

        if (!file_exists(dirname($output_path))) {
            mkdir(dirname($output_path), 0755, true);
        }

        $template->saveAs($output_path);

        return $output_path;
    }

    protected function GetTemplateFile($leave_type)
    {
        $template_name = $this->template_map[$leave_type] ?? 'ใบลา_อินฟินิท2023.docx';
        return base_path('docs/' . $template_name);
    }

    protected function FillCommonData(TemplateProcessor $template, LeaveRequest $leave_request)
    {
        $submitted_date = $leave_request->submitted_date ?? $leave_request->created_at;
        $start_date = $leave_request->start_date;
        $end_date = $leave_request->end_date;

        $template->setValue('date_day', $submitted_date->day);
        $template->setValue('date_month', $this->thai_months[$submitted_date->month]);
        $template->setValue('date_year', $submitted_date->year + 543);

        $template->setValue('prefix_employee_name', $leave_request->user->GetDisplayName() ?? '');
        $template->setValue('employee_name', $leave_request->user->GetFullName() ?? '');
        $template->setValue('title_prefix', $leave_request->user->title_prefix ?? '');
        $template->setValue('position', $leave_request->user->position ?? 'พนักงาน');

        $template->setValue('start_day', $start_date->day);
        $template->setValue('start_month', $this->thai_months[$start_date->month]);
        $template->setValue('start_year', $start_date->year + 543);

        $template->setValue('end_day', $end_date->day);
        $template->setValue('end_month', $this->thai_months[$end_date->month]);
        $template->setValue('end_year', $end_date->year + 543);

        $template->setValue('total_days', $leave_request->total_days);
        $template->setValue('reason', $leave_request->reason ?? '');
        $template->setValue('contact_phone', $leave_request->contact_phone ?? '');
        $template->setValue('contact_address', $leave_request->contact_address ?? '');

        $this->FillLeaveStats($template, $leave_request);
    }

    protected function FillLeaveStats(TemplateProcessor $template, LeaveRequest $leave_request)
    {
        $year = $leave_request->start_date->year;
        $user_id = $leave_request->user_id;

        $vacation_used = LeaveRequest::GetUserLeaveUsedByType($user_id, 'vacation', $year);
        $personal_used = LeaveRequest::GetUserLeaveUsedByType($user_id, 'personal', $year);
        $sick_used = LeaveRequest::GetUserLeaveUsedByType($user_id, 'sick', $year);

        $vacation_quota = LeaveRequest::LEAVE_QUOTAS['vacation'] ?? 6;
        $personal_quota = LeaveRequest::LEAVE_QUOTAS['personal'] ?? 3;
        $sick_quota = LeaveRequest::LEAVE_QUOTAS['sick'] ?? 30;

        $template->setValue('vacation_quota', $vacation_quota);
        $template->setValue('vacation_used', $vacation_used);
        $template->setValue('vacation_remaining', $vacation_quota - $vacation_used);

        $template->setValue('personal_quota', $personal_quota);
        $template->setValue('personal_used', $personal_used);
        $template->setValue('personal_remaining', $personal_quota - $personal_used);

        $template->setValue('sick_quota', $sick_quota);
        $template->setValue('sick_used', $sick_used);
        $template->setValue('sick_remaining', $sick_quota - $sick_used);

        $template->setValue('stats_year', $year + 543);

        $template->setValue('sum_total_days_sick', $leave_request->total_days + $sick_used);
        $template->setValue('sum_total_days_personal', $leave_request->total_days + $personal_used);
        $template->setValue('sum_total_days_vacation', $leave_request->total_days + $vacation_used);

        $template->setValue('total_days_sick', $leave_request->leave_type === 'sick' ?  $leave_request->total_days : '');
        $template->setValue('total_days_personal', $leave_request->leave_type === 'personal' ?  $leave_request->total_days : '');
        $template->setValue('total_days_vacation', $leave_request->leave_type === 'vacation' ?  $leave_request->total_days : '');
    }

    protected function FillGeneralLeave(TemplateProcessor $template, LeaveRequest $leave_request)
    {
        $template->setValue('check_sick', $leave_request->leave_type === 'sick' ? '☑' : '☐');
        $template->setValue('check_personal', $leave_request->leave_type === 'personal' ? '☑' : '☐');
        $template->setValue('check_maternity', $leave_request->leave_type === 'maternity' ? '☑' : '☐');
        $template->setValue('check_ordination', $leave_request->leave_type === 'ordination' ? '☑' : '☐');
        $template->setValue('check_military', $leave_request->leave_type === 'military' ? '☑' : '☐');

    }

    protected function FillVacationLeave(TemplateProcessor $template, LeaveRequest $leave_request)
    {
        // Vacation specific fields
    }

    protected function FillCompensateLeave(TemplateProcessor $template, LeaveRequest $leave_request)
    {
        $template->setValue('check_compensate', $leave_request->leave_type === 'compensate' ? '☑' : '☐');
        $template->setValue('check_swap', $leave_request->leave_type === 'swap_day' ? '☑' : '☐');

        if ($leave_request->compensate_for_date) {
            $comp_date = Carbon::parse($leave_request->compensate_for_date);
            $template->setValue('compensate_day', $comp_date->day);
            $template->setValue('compensate_month', $this->thai_months[$comp_date->month]);
            $template->setValue('compensate_year', $comp_date->year + 543);
        } else {
            $template->setValue('compensate_day', '');
            $template->setValue('compensate_month', '');
            $template->setValue('compensate_year', '');
        }

        if ($leave_request->swap_from_date) {
            $swap_date = Carbon::parse($leave_request->swap_from_date);
            $template->setValue('swap_day', $swap_date->day);
            $template->setValue('swap_month', $this->thai_months[$swap_date->month]);
            $template->setValue('swap_year', $swap_date->year + 543);
        } else {
            $template->setValue('swap_day', '');
            $template->setValue('swap_month', '');
            $template->setValue('swap_year', '');
        }
    }

    protected function FillRemoteWorkLeave(TemplateProcessor $template, LeaveRequest $leave_request)
    {
        $template->setValue('location', $leave_request->location ?? '');
        $template->setValue('start_time', $leave_request->start_time ?? '');
        $template->setValue('end_time', $leave_request->end_time ?? '');
    }

    protected function FillSignatures(TemplateProcessor $template, LeaveRequest $leave_request)
    {
        $sig_path = null;

        if ($leave_request->employee_signature) {
            if ($leave_request->signature_is_image) {
                $sig_path = storage_path('app/public/' . $leave_request->employee_signature);

                if (!file_exists($sig_path)) {
                    $sig_path = public_path('storage/' . $leave_request->employee_signature);
                }

                if ($sig_path && file_exists($sig_path)) {
                    $sig_path = $this->ConvertToPng($sig_path, 'emp_sig_' . $leave_request->id);
                }
            } elseif (strpos($leave_request->employee_signature, 'data:image') === 0) {
                $sig_path = $this->SaveBase64ToTemp($leave_request->employee_signature, 'emp_sig_' . $leave_request->id);
            } else {
                $sig_path = storage_path('app/public/' . $leave_request->employee_signature);
                if (!file_exists($sig_path)) {
                    $sig_path = public_path('storage/' . $leave_request->employee_signature);
                }

                if ($sig_path && file_exists($sig_path)) {
                    $sig_path = $this->ConvertToPng($sig_path, 'emp_sig_' . $leave_request->id);
                }
            }

            if ($sig_path && file_exists($sig_path)) {
                try {
                    $template->setImageValue('employee_signature', [
                        'path' => $sig_path,
                        'width' => 100,
                        'height' => 50,
                    ]);
                } catch (\Exception $e) {
                    $template->setValue('employee_signature', '(ลายเซ็น)');
                }
            } else {
                $template->setValue('employee_signature', '');
            }
        } else {
            $template->setValue('employee_signature', '');
        }
    }

    protected function ConvertToPng($source_path, $filename)
    {
        try {
            $temp_path = storage_path('app/temp/' . $filename . '.png');

            if (!file_exists(dirname($temp_path))) {
                mkdir(dirname($temp_path), 0755, true);
            }

            $image = \Intervention\Image\Facades\Image::make($source_path)->encode('png');
            $image->save($temp_path);

            return $temp_path;
        } catch (\Exception $e) {
            return $source_path;
        }
    }

    protected function FillApproval(TemplateProcessor $template, LeaveRequest $leave_request)
    {
        $template->setValue('check_approved', $leave_request->IsApproved() ? '☑' : '☐');
        $template->setValue('check_rejected', $leave_request->IsRejected() ? '☑' : '☐');
        $template->setValue('rejection_reason', $leave_request->rejection_reason ?? '');
        $template->setValue('approver_name', $leave_request->approver->name ?? 'มานะ อินทราภรณ์');

        if ($leave_request->approved_at) {
            $approved_date = $leave_request->approved_at;
            $template->setValue('approved_day', $approved_date->day);
            $template->setValue('approved_month', $this->thai_months[$approved_date->month]);
            $template->setValue('approved_year', $approved_date->year + 543);
        } else {
            $template->setValue('approved_day', '');
            $template->setValue('approved_month', '');
            $template->setValue('approved_year', '');
        }

        if ($leave_request->approver_signature) {
            $sig_path = $this->SaveBase64ToTemp($leave_request->approver_signature, 'approver_sig_' . $leave_request->id);
            if ($sig_path && file_exists($sig_path)) {
                try {
                    $template->setImageValue('approver_signature', [
                        'path' => $sig_path,
                        'width' => 100,
                        'height' => 50,
                    ]);
                } catch (\Exception $e) {
                    $template->setValue('approver_signature', '(ลายเซ็นผู้อนุมัติ)');
                }
            } else {
                $template->setValue('approver_signature', '');
            }
        } else {
            $template->setValue('approver_signature', '');
        }
    }

    protected function SaveBase64ToTemp($base64_data, $filename)
    {
        if (strpos($base64_data, 'data:image') === 0) {
            $parts = explode(',', $base64_data);
            if (count($parts) === 2) {
                $image_data = base64_decode($parts[1]);
                $extension = 'png';
                if (strpos($parts[0], 'jpeg') !== false) {
                    $extension = 'jpg';
                }

                $temp_path = storage_path('app/temp/' . $filename . '.' . $extension);

                if (!file_exists(dirname($temp_path))) {
                    mkdir(dirname($temp_path), 0755, true);
                }

                file_put_contents($temp_path, $image_data);
                return $temp_path;
            }
        }
        return null;
    }
}
