<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'account_no',
        'bank_name',
        'tax_id',
        'phone',
        'email',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship to PaymentVoucher
     */
    public function paymentVouchers()
    {
        return $this->hasMany(PaymentVoucher::class);
    }

    /**
     * Scope for active payees
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Search by name or account number
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'LIKE', "%{$keyword}%")
              ->orWhere('account_no', 'LIKE', "%{$keyword}%");
        });
    }

    /**
     * Find or create a payee
     */
    public static function findOrCreateByName($name, $attributes = [])
    {
        $payee = static::where('name', $name)->first();
        
        if (!$payee) {
            $payee = static::create(array_merge(['name' => $name], $attributes));
        } else {
            // Update with new info if provided
            if (!empty($attributes)) {
                $payee->update(array_filter($attributes));
            }
        }
        
        return $payee;
    }
}
