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
        return $this->hasMany(Receipt_School::class, 'pid', 'id');
        // return $this->hasOne(Receipt_School::class, 'pid','id');
    }

    public function school_p()
    {
        return $this->hasMany(SchoolPrice::class, 'student_id', 'id');
        // return $this->hasOne(Receipt_School::class, 'pid','id');
    }

    public function status_log()
    {
        return $this->hasMany(Statuslog::class, 'student_id', 'id');
        // return $this->hasOne(Receipt_School::class, 'pid','id');
    }


    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }


    protected $primaryKey = 'id';

    public static function generateNextStudentCode()
    {

        $lastStudent = self::where(function ($query) {
            $query->where('code', 'like', '0%');
        })->orderBy('id', 'desc')->first();

        $lastStudentWithThree = self::where(function ($query) {
            $query->where('code', 'like', '3%');
        })->orderBy('id', 'desc')->first();
        $lastStudentWithTwo = self::where(function ($query) {
            $query->where('code', 'like', '2%');
        })->orderBy('id', 'desc')->first();
        $lastStudentWithFour = self::where(function ($query) {
            $query->where('code', 'like', '1%');
        })->orderBy('id', 'desc')->first();
        $lastStudentWithDummy = self::where(function ($query) {
            $query->where('code', 'like', '8%');
        })->orderBy('id', 'desc')->first();

        if (!$lastStudent) {
            $nextCode = ['code'=>'00001','type'=> 'default'];
        }else {
            $nextCode = ['code' =>str_pad($lastStudent->code + 1, 5, '0', STR_PAD_LEFT),'type' => 'default' ];
        }
        if (!$lastStudentWithThree) {
            $nextCodeForThree = ['code'=>'30001','type'=> 'Science'];
        } else {
            $nextCodeForThree = ['code' =>$lastStudentWithThree->code[0] === '3' ? $lastStudentWithThree->code + 1 : '30001','type' => 'Science' ];
        }

        if (!$lastStudentWithFour) {
            $nextCodeForFour = ['code'=>'10001','type'=> 'Chinese'];
        }else {
            $nextCodeForFour = ['code' => $lastStudentWithFour->code[0] === '1' ? $lastStudentWithFour->code + 1 : '10001','type' => 'Chinese' ];

        }
        if (!$lastStudentWithDummy) {
            $nextCodeForDummy = ['code'=>'80001','type'=> 'dummy'];
        }else {
            $nextCodeForDummy = ['code' => $lastStudentWithDummy->code[0] === '8' ? $lastStudentWithDummy->code + 1 : '80001','type' => 'dummy' ];

        }
        if (!$lastStudentWithTwo) {
            $nextCodeForTwo = ['code'=>'20001','type'=> 'Thai'];
        }else {
            $nextCodeForTwo = ['code' => $lastStudentWithTwo->code[0] === '2' ? $lastStudentWithTwo->code + 1 : '20001','type' => 'Thai' ];

        }




        return [
            $nextCode,
            $nextCodeForThree,
            $nextCodeForFour,
            $nextCodeForDummy,
            $nextCodeForTwo
        ];
    }
}
