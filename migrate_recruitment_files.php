<?php

use App\Models\RecruitmentApplicant;
use App\Models\RecruitmentApplicantAttachment;
use Illuminate\Support\Facades\Storage;

// ดึงรายการผู้สมัครที่มี resume_path และยังไม่มีใน attachments
$applicants = RecruitmentApplicant::whereNotNull('resume_path')->get();

$count = 0;
foreach ($applicants as $applicant) {
    // ตรวจสอบว่ามีการย้ายไปหรือยัง
    $exists = RecruitmentApplicantAttachment::where('applicant_id', $applicant->id)
        ->where('file_path', $applicant->resume_path)
        ->exists();

    if (!$exists) {
        $fullPath = storage_path('app/public/' . $applicant->resume_path);
        $fileSize = 0;
        if (file_exists($fullPath)) {
            $fileSize = filesize($fullPath);
        }

        RecruitmentApplicantAttachment::create([
            'applicant_id' => $applicant->id,
            'file_name' => basename($applicant->resume_path),
            'original_name' => 'Resume_' . $applicant->first_name . '.pdf',
            'file_path' => $applicant->resume_path,
            'file_type' => 'application/pdf',
            'file_size' => $fileSize,
            'uploaded_by' => $applicant->admin_id,
        ]);
        $count++;
    }
}

echo "Migrated $count applicants successfully.\n";
