<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $student_id
 * @property string $subject
 * @property string $group
 * @property string $level
 * @property string $week
 * @property string $media_name
 * @property string $qty
 * @property string $book
 * @property string $entrance_fee
 * @property string $bookprice
 * @property string $media_price
 * @property string $discount
 * @property string $price
 * @property string $total
 * @property integer $rid
 * @property string $created_at
 * @property string $updated_at
 * @property string $type
 * @property string $receipt_number
 * @property string $period
 * @property integer $course_type
 */
class SchoolPrice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'school_price';

    /**
     * @var array
     */
    protected $fillable = ['student_id', 'subject', 'group', 'level', 'week', 'media_name', 'qty', 'book', 'entrance_fee', 'bookprice', 'media_price', 'discount', 'price', 'total', 'rid', 'created_at', 'updated_at', 'type', 'receipt_number', 'period', 'course_type'];

    public function rec_school()
    {
        return $this->belongsTo(Receipt_School::class, 'rid', 'id');
    }

    public function school_sub()
    {
        return $this->hasOne(Subject::class, 'id', 'subject');
    }

    public function std()
    {
        return $this->hasOne(Student::class, 'id', 'student_id');
    }
}
