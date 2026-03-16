<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptVoucherLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_voucher_id',
        'action',
        'from_status',
        'to_status',
        'user_id',
        'user_name',
        'remark',
    ];

    public function voucher()
    {
        return $this->belongsTo(ReceiptVoucher::class, 'receipt_voucher_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
