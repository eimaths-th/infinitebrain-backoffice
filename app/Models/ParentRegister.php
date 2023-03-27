<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
class ParentRegister extends Authenticatable
{
    use HasFactory;
    protected $table="parent";
    protected $guard = 'parent';
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
    protected $hidden = [
        'password', 'remember_token',
    ];
}
