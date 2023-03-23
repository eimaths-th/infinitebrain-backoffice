<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    use HasFactory;
    protected $table="register_form";

    protected $fillable = [
        'email',
        'password',
        'name_parent',
        'mobile',
        // 'first_name',
        // 'last_name',
        // 'nick_name',
        // 'school_name',
        // 'birth_date',
        'street_address',
        // 'street_address2',
        // 'city',
        // 'province',
        'postcode',
        // 'grade',
        'code',
        
    ];
}
