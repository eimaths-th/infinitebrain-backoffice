<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentVoucherAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_voucher_id',
        'payment_voucher_item_id',
        'attachment_type',
        'file_name',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'uploaded_by',
        'uploaded_by_name',
    ];

    const TYPE_VOUCHER = 'voucher';
    const TYPE_ITEM = 'item';

    // Relationships
    public function paymentVoucher()
    {
        return $this->belongsTo(PaymentVoucher::class);
    }

    public function paymentVoucherItem()
    {
        return $this->belongsTo(PaymentVoucherItem::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helpers
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    public function isImage()
    {
        return in_array($this->file_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function isPdf()
    {
        return $this->file_type === 'application/pdf';
    }
}
