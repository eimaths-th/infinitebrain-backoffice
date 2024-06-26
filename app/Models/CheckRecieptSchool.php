<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $student_id
 * @property string $note
 * @property string $receipt_school_id
 * @property string $school_price_id
 * @property string $date_check
 * @property string $check_by
 * @property string $created_at
 * @property string $updated_at
 */
class CheckRecieptSchool extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'check_reciept_school';

    /**
     * @var array
     */
    protected $fillable = ['student_id','note', 'receipt_school_id', 'school_price_id', 'date_check', 'check_by', 'created_at', 'updated_at'];

    public function school_price()
    {
        return $this->belongsTo(SchoolPrice::class, 'school_price_id');
    }
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'check_by', 'id');
    }
    public function receiptSchool()
    {
        return $this->belongsTo(Receipt_School::class, 'receipt_school_id', 'id');
    }
}
