<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecruitmentApplicantAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'file_name',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    /**
     * Get the applicant that owns the attachment.
     */
    public function applicant()
    {
        return $this->belongsTo(RecruitmentApplicant::class, 'applicant_id');
    }

    /**
     * Get the user who uploaded the attachment.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
