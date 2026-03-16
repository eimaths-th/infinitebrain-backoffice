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
        // Check by file extension first as a fallback if file_type is not accurately set
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        
        return in_array($this->file_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']) || 
               in_array($extension, $imageExtensions);
    }

    public function isPdf()
    {
        return $this->file_type === 'application/pdf' || 
               strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)) === 'pdf';
    }
}
