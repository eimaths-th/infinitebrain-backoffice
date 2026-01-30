<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollIncomeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'income_type',
        'description',
        'quantity',
        'rate',
        'amount',
        'is_wht_3_percent',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'is_wht_3_percent' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    /**
     * Get income type label
     */
    public function GetIncomeTypeLabel()
    {
        return Payroll::$incomeTypes[$this->income_type] ?? $this->income_type;
    }
}
