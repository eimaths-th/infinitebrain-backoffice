<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'file_name',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
    ];

    public function LeaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function IsImage()
    {
        return in_array($this->file_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function IsPdf()
    {
        return $this->file_type === 'application/pdf';
    }

    public function GetFileSizeFormatted()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function GetFileIcon()
    {
        if ($this->IsImage()) {
            return 'fa-file-image';
        } elseif ($this->IsPdf()) {
            return 'fa-file-pdf';
        } elseif (str_contains($this->file_type, 'word') || str_contains($this->original_name, '.doc')) {
            return 'fa-file-word';
        } elseif (str_contains($this->file_type, 'excel') || str_contains($this->original_name, '.xls')) {
            return 'fa-file-excel';
        }
        return 'fa-file';
    }
}
