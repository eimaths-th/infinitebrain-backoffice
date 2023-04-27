<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolPrice extends Model
{
    use HasFactory;
    protected $table="school_price";

   
    protected $fillable = [
        'subject',
        'level',
        'week',
        'qty',
        'book',
        'bookprice',
        'discount',
        'price',
        'total',
        'rid',
        'type',
        'receipt_number',
        'period',
    ];
}
