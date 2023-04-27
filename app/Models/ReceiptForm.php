<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptForm extends Model
{
    use HasFactory;

    protected $table="receipt_forms";

    protected $fillable = [
        'sfield',
        'submission_date',
        'first_name',
        'last_name',
        'nick_name',
        'email',
        'school_name',
        'birth_date',
        'street_address',
        'street_address2',
        'city',
        'province',
        'postcode',
        'name_parent',
        'mobile',
        'package',
        'photo_att',
        'file_att',
        'news_form',
        'generate',
        'receipt_number',
        'receipt_customer_number',
        'grade',
        'description',
        'description2',
        'contest',
    ];
}
