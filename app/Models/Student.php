<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'first_name',
        'last_name',
        'nick_name',
        'school_name',
        'birth_date',
        'street_address',
        'mobile',
        'status',
        'photo_att',
        'grade',
        'sex',
        'reason',
        'pid'
    ];
    public function parent()
    {
        return $this->hasOne(ParentRegister::class, 'id', 'pid');
    }


    public function rec_school()
    {
        return $this->hasMany(Receipt_School::class, 'pid','id');
        // return $this->hasOne(Receipt_School::class, 'pid','id');
    }

    public function school_p()
    {
        return $this->hasMany(SchoolPrice::class, 'student_id','id');
        // return $this->hasOne(Receipt_School::class, 'pid','id');
    }

    public function status_log()
    {
        return $this->hasMany(Statuslog::class, 'student_id','id');
        // return $this->hasOne(Receipt_School::class, 'pid','id');
    }


    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }


    protected $primaryKey = 'id';

    public static function generateNextStudentCode()
    {
        $lastStudent = self::orderBy('id', 'desc')->first();

        if (!$lastStudent) {
            // If no students in the database, start with 1
            return '00001';
        }

        return str_pad($lastStudent->code + 1, 5, '0', STR_PAD_LEFT);
    }




}
