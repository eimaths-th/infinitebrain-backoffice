<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Users ที่มี tag นี้
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_tag_pivot');
    }

    /**
     * Scope สำหรับ active tags
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Default colors for tags
     */
    const COLORS = [
        'primary' => 'สีน้ำเงิน',
        'success' => 'สีเขียว',
        'danger' => 'สีแดง',
        'warning' => 'สีเหลือง',
        'info' => 'สีฟ้า',
        'secondary' => 'สีเทา',
    ];
}
