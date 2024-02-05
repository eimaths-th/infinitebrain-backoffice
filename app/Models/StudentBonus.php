<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $student_id
 * @property integer $schoolp_id
 * @property string $bonus
 * @property string $detail
 * @property integer $approv_id
 * @property string $created_at
 * @property string $updated_at
 */
class StudentBonus extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_bonus';

    /**
     * @var array
     */
    protected $fillable = ['student_id', 'schoolp_id', 'bonus', 'detail', 'approv_id', 'created_at', 'updated_at'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
    public function school_p()
    {
        return $this->belongsTo(SchoolPrice::class, 'schoolp_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'approv_id', 'id');
    }
}
