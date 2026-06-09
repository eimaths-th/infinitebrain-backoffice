<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionCampaign extends Model
{
    use HasFactory;

    const TYPE_TWO_SUBJECTS = 'TWO_SUBJECTS';
    const TYPE_REFERRAL = 'REFERRAL';
    const TYPE_NEW_STUDENT_MULTI_COURSE = 'NEW_STUDENT_MULTI_COURSE';
    const TYPE_NEW_STUDENT_FREE_ENTRANCE_GIFT = 'NEW_STUDENT_FREE_ENTRANCE_GIFT';
    const TYPE_NEW_STUDENT_FREE_ENTRANCE = 'NEW_STUDENT_FREE_ENTRANCE';
    const TYPE_NEW_STUDENT_GIFT = 'NEW_STUDENT_GIFT';
    const TYPE_CUSTOM = 'CUSTOM';

    protected $fillable = [
        'promotion_id',
        'name',
        'promotion_type',
        'is_active',
        'starts_at',
        'ends_at',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'priority' => 'integer',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function rules()
    {
        return $this->hasMany(PromotionRule::class);
    }

    public function rewards()
    {
        return $this->hasMany(PromotionReward::class);
    }

    public function branchEligibilities()
    {
        return $this->hasMany(PromotionBranchEligibility::class);
    }

    public function usageReceipts()
    {
        return $this->hasMany(ReceiptPromotionUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
