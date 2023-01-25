<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;
    protected $table="receipts";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'submission_date',
        'first_name',
        'last_name',
        'street_address',
        'street_address2',
        'city',
        'province',
        'postcode',
        'name_parent',
        'mobile',
        'package',
        'file_att',
        'sfield',
        'receipt_number',
        'receipt_customer_number',
        'grade',
        'description',
        'description2',
    ];
}
