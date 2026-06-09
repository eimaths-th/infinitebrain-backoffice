<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptPromotionUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_school_id',
        'promotion_campaign_id',
        'promotion_name_snapshot',
        'discount_amount_snapshot',
        'expected_discount_amount_snapshot',
        'actual_discount_amount_snapshot',
        'expected_net_amount_snapshot',
        'actual_net_amount_snapshot',
        'match_status',
        'mismatch_reason',
        'reward_snapshot_json',
        'rule_snapshot_json',
        'selected_by_user_id',
        'selected_at',
    ];

    protected $casts = [
        'discount_amount_snapshot' => 'decimal:2',
        'expected_discount_amount_snapshot' => 'decimal:2',
        'actual_discount_amount_snapshot' => 'decimal:2',
        'expected_net_amount_snapshot' => 'decimal:2',
        'actual_net_amount_snapshot' => 'decimal:2',
        'reward_snapshot_json' => 'array',
        'rule_snapshot_json' => 'array',
        'selected_at' => 'datetime',
    ];

    public function receiptSchool()
    {
        return $this->belongsTo(Receipt_School::class, 'receipt_school_id');
    }

    public function campaign()
    {
        return $this->belongsTo(PromotionCampaign::class, 'promotion_campaign_id');
    }

    public function selectedByUser()
    {
        return $this->belongsTo(User::class, 'selected_by_user_id');
    }
}
