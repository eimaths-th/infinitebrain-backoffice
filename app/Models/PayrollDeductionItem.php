<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollDeductionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'deduction_type',
        'description',
        'rate',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    /**
     * Get deduction type label
     */
    public function GetDeductionTypeLabel()
    {
        return Payroll::$deductionTypes[$this->deduction_type] ?? $this->deduction_type;
    }
}
