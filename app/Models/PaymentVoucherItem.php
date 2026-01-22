<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentVoucherItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_voucher_id',
        'item_no',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // ลบ attachments ก่อนลบ item
        static::deleting(function ($item) {
            foreach ($item->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment->delete();
            }
        });
    }

    public function paymentVoucher()
    {
        return $this->belongsTo(PaymentVoucher::class);
    }

    public function attachments()
    {
        return $this->hasMany(PaymentVoucherAttachment::class, 'payment_voucher_item_id');
    }
}
