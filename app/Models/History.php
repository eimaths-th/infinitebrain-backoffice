<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $student_id
 * @property integer $subject_id
 * @property integer $school_price_id
 * @property integer $status
 * @property integer $check_school_price_id
 * @property integer $level_id
 * @property string $datetime
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property string $stime
 * @property string $etime
 * @property string $signature
 */
class History extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['student_id', 'subject_id', 'school_price_id', 'status', 'check_school_price_id', 'level_id', 'datetime', 'description', 'created_at', 'updated_at', 'stime', 'etime', 'signature', 'user_id'];

    // protected $casts = [
    //     'datetime' => 'datetime:Y-m-d',
    // ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

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
