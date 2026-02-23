<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecruitmentApplicantLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'note',
    ];

    public function applicant()
    {
        return $this->belongsTo(RecruitmentApplicant::class, 'applicant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
