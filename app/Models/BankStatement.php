<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'amount',
        'account_in',
        'account_out',
        'description',
        'received_from',
        'reference_no',
        'paid_by',
        'subtotal',
        'branch_eye_level_in',
        'branch_eye_level_out',
        'branch_central_in',
        'branch_central_out',
        'fee',
        'tax',
        'total_in',
        'total_out',
        'remark',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'branch_eye_level_in' => 'decimal:2',
        'branch_eye_level_out' => 'decimal:2',
        'branch_central_in' => 'decimal:2',
        'branch_central_out' => 'decimal:2',
        'fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_in' => 'decimal:2',
        'total_out' => 'decimal:2',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
