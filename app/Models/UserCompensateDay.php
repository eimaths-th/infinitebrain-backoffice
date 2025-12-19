<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCompensateDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'holiday_id',
        'holiday_date',
        'is_used',
        'used_date',
        'year',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'used_date' => 'date',
        'is_used' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function holiday()
    {
        return $this->belongsTo(CompanyHoliday::class, 'holiday_id');
    }
}
