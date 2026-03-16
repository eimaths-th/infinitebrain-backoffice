<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptVoucherAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_voucher_id',
        'receipt_voucher_item_id',
        'attachment_type',
        'file_name',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
        'uploaded_by_name',
    ];

    public function voucher()
    {
        return $this->belongsTo(ReceiptVoucher::class, 'receipt_voucher_id');
    }

    public function item()
    {
        return $this->belongsTo(ReceiptVoucherItem::class, 'receipt_voucher_item_id');
    }
}
