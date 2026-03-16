<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptVoucherItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_voucher_id',
        'type',
        'item_no',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function voucher()
    {
        return $this->belongsTo(ReceiptVoucher::class, 'receipt_voucher_id');
    }

    public function attachments()
    {
        return $this->hasMany(ReceiptVoucherAttachment::class, 'receipt_voucher_item_id');
    }
}
