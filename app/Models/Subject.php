<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'title',
        'title_th',
        'book',
        'book_per_month',
    ];

    public function school_pice()
    {
        return $this->hasOne(SchoolPrice::class, 'subject', 'id');
    }
}
