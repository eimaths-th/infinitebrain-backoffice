<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSalaryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'rate',
        'unit',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * หน่วยที่รองรับ
     */
    const UNITS = [
        'ชม.' => 'ชั่วโมง',
        'วัน' => 'วัน',
        'ครั้ง' => 'ครั้ง',
    ];

    /**
     * User ที่เป็นเจ้าของรายการนี้
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope สำหรับ active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * คำนวณจำนวนเงินตาม quantity
     */
    public function CalculateAmount($quantity)
    {
        return round($this->rate * $quantity, 2);
    }
}
