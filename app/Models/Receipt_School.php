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
    ];
}
