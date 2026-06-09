<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionReward extends Model
{
    use HasFactory;

    const TYPE_FIXED_DISCOUNT = 'FIXED_DISCOUNT';
    const TYPE_PERCENT_DISCOUNT = 'PERCENT_DISCOUNT';
    const TYPE_CAPPED_DISCOUNT = 'CAPPED_DISCOUNT';
    const TYPE_FREE_ENTRANCE_FEE = 'FREE_ENTRANCE_FEE';
    const TYPE_GIFT = 'GIFT';

    const CATEGORY_CASH = 'CASH';
    const CATEGORY_ENTRANCE = 'ENTRANCE';
    const CATEGORY_GIFT = 'GIFT';

    /**
     * Map a reward type to its reward category. Campaigns that share a reward
     * category cannot be combined on the same receipt (e.g. two cash discounts).
     */
    public static function categoryForType($reward_type)
    {
        if ($reward_type === self::TYPE_FREE_ENTRANCE_FEE) {
            return self::CATEGORY_ENTRANCE;
        }

        if ($reward_type === self::TYPE_GIFT) {
            return self::CATEGORY_GIFT;
        }

        return self::CATEGORY_CASH;
    }

    protected $fillable = [
        'promotion_campaign_id',
        'reward_type',
        'discount_amount',
        'discount_percent',
        'max_discount_amount',
        'reward_config_json',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'reward_config_json' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(PromotionCampaign::class, 'promotion_campaign_id');
    }
}
