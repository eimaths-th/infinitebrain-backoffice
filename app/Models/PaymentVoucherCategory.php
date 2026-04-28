<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentVoucherCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key',
        'label',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }

    /**
     * Get label map key => label
     */
    public static function getLabelMap()
    {
        // Cache the result for performance if necessary, or just query
        return self::active()->pluck('label', 'key')->toArray();
    }
}
