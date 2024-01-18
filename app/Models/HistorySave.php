<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'school_price_id',
        'subject_id',
        'level_id',
        'description',
        'datetime',
        'stime',
        'etime',
        'signature',
    ];

    public function student()
    {
        return $this->hasOne(Student::class, 'id', 'student_id');
    }

    public function subject()
    {
        return $this->hasOne(Subject::class, 'id', 'subject_id');
    }

    public function getFullTimeAttribute()
    {
        return $this->stime . ' - ' . $this->etime;
    }


}
