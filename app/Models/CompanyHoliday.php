<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'year',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function compensateDays()
    {
        return $this->hasMany(UserCompensateDay::class, 'holiday_id');
    }
}
