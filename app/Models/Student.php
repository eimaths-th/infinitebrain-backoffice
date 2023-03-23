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
        'photo_att',
        'grade',
    ];
}
