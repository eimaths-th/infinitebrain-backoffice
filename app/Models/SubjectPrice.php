<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'level_id',
        'week',
        'old_price',
        'price',
        'book',
        'book_old_price',
        'book_price',
        'type',
    ];
}
