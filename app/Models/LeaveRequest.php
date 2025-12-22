<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'submitted_date',
        'start_date',
        'end_date',
        'total_days',
        'start_time',
        'end_time',
        'reason',
        'location',
        'contact_phone',
        'contact_address',
        'compensate_for_date',
        'swap_from_date',
        'half_day',
        'employee_signature',
        'signature_is_image',
        'status',
        'approved_by',
        'approved_at',
        'approver_signature',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'submitted_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'compensate_for_date' => 'date',
        'swap_from_date' => 'date',
        'approved_at' => 'datetime',
        'signature_is_image' => 'boolean',
        'total_days' => 'decimal:1',
    ];

    const LEAVE_TYPES = [
        'sick' => 'ลาป่วย',
        'personal' => 'ลากิจ',
        'maternity' => 'ลาคลอด',
        'ordination' => 'ลาบวช',
        'military' => 'ลารับราชการทหาร',
        'vacation' => 'ลาพักผ่อน',
        'compensate' => 'ใช้วันหยุดชดเชย',
        'swap_day' => 'สลับวันหยุด',
        'remote_work' => 'ทำงานนอกสถานที่',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const STATUSES = [
        'pending' => 'รอพิจารณา',
        'approved' => 'อนุมัติ',
        'rejected' => 'ไม่อนุมัติ',
    ];

    const HALF_DAY_OPTIONS = [
        'none' => 'เต็มวัน',
        'morning' => 'ครึ่งวันเช้า',
        'afternoon' => 'ครึ่งวันบ่าย',
    ];

    const LEAVE_QUOTAS = [
        'vacation' => 6,    // ลาพักร้อน 6 วัน/ปี
        'personal' => 3,    // ลากิจ 3 วัน/ปี
        'sick' => 30,       // ลาป่วย 30 วัน/ปี
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attachments()
    {
        return $this->hasMany(LeaveRequestAttachment::class);
    }

    public function GetLeaveTypeName()
    {
        return self::LEAVE_TYPES[$this->leave_type] ?? $this->leave_type;
    }

    public function GetStatusName()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function GetStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function IsPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function IsApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function IsRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function GetDateRangeText()
    {
        if ($this->start_date->eq($this->end_date)) {
            return $this->start_date->format('d/m/Y');
        }
        return $this->start_date->format('d/m/Y') . ' - ' . $this->end_date->format('d/m/Y');
    }

    public static function GetUserLeaveStats($user_id, $year = null)
    {
        $year = $year ?? now()->year;

        $stats = [];

        foreach (self::LEAVE_QUOTAS as $leave_type => $quota) {
            $used = self::where('user_id', $user_id)
                ->where('leave_type', $leave_type)
                ->where('status', self::STATUS_APPROVED)
                ->whereYear('start_date', $year)
                ->sum('total_days');

            $stats[$leave_type] = [
                'name' => self::LEAVE_TYPES[$leave_type],
                'quota' => $quota,
                'used' => (float) $used,
                'remaining' => $quota - (float) $used,
            ];
        }

        return $stats;
    }

    public static function GetUserLeaveUsedByType($user_id, $leave_type, $year = null)
    {
        $year = $year ?? now()->year;

        return (float) self::where('user_id', $user_id)
            ->where('leave_type', $leave_type)
            ->where('status', self::STATUS_APPROVED)
            ->whereYear('start_date', $year)
            ->sum('total_days');
    }
}
