<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolPriceSave extends Model
{
    use HasFactory;

    protected $table="school_price";


    protected $fillable = [
        'subject',
        'group',
        'course_type',
        'student_id',
        'level',
        'week',
        'qty',
        'book',
        'media_name',
        'media_price',
        'entrance_fee',
        'bookprice',
        'discount',
        'price',
        'total',
        'rid',
        'type',
        'receipt_number',
        'period',
    ];

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
