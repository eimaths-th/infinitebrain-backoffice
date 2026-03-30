<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt_School extends Model
{
    use HasFactory;
    protected $table="receipt_school";

    protected $fillable = [
        'type',
        'submission_date',
        'payment_type',
        'course_type',
        'first_name',
        'last_name',
        'street_address',
        'postcode',
        'name_parent',
        'mobile',
        'package',
        'file_att',
        'receipt_number',
        'pid',
        'source',
        'approv_id',
        'approv_name',
    ];

    public function parentRegister()
    {
        return $this->belongsTo(ParentRegister::class, 'name_parent', 'id');
    }

    public function school_pice()
    {
        return $this->hasOne(SchoolPrice::class, 'rid', 'id');
    }


}
