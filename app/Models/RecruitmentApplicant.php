<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentApplicant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'position_applied',
        'resume_path',
        'status',
        'follow_up',
        'reviewer_note',
        'manager_note',
        'admin_id',
        'reviewer_id',
        'manager_id',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REJECTED = 'rejected';

    const FOLLOW_UP_YES = 'yes';
    const FOLLOW_UP_NO = 'no';
    const FOLLOW_UP_UNDECIDED = 'undecided';

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function logs()
    {
        return $this->hasMany(RecruitmentApplicantLog::class, 'applicant_id');
    }

    public function GetFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function GetStatusLabel()
    {
        return [
            self::STATUS_PENDING => 'รอตรวจสอบ',
            self::STATUS_REVIEWED => 'ตรวจสอบแล้ว',
            self::STATUS_VERIFIED => 'อนุมัติแล้ว',
            self::STATUS_REJECTED => 'ปฏิเสธ',
        ][$this->status] ?? $this->status;
    }
}
