<?php

namespace Tests\Unit;

use App\Models\PromotionReward;
use App\Services\PromotionRewardCalculator;
use PHPUnit\Framework\TestCase;

class PromotionRewardCalculatorTest extends TestCase
{
    public function testFixedDiscountRewardReturnsConfiguredAmount()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = new PromotionReward([
            'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
            'discount_amount' => 200,
        ]);

        $result = $calculator->calculate(['subtotal_amount' => 5800], $promotion_reward);

        $this->assertSame(200.0, $result['discount_amount']);
    }

    public function testPercentDiscountRewardUsesSubtotalAmount()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = new PromotionReward([
            'reward_type' => PromotionReward::TYPE_PERCENT_DISCOUNT,
            'discount_percent' => 10,
        ]);

        $result = $calculator->calculate(['subtotal_amount' => 5800], $promotion_reward);

        $this->assertSame(580.0, $result['discount_amount']);
    }

    public function testCappedDiscountRewardDoesNotExceedMaximumAmount()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = new PromotionReward([
            'reward_type' => PromotionReward::TYPE_CAPPED_DISCOUNT,
            'max_discount_amount' => 900,
            'reward_config_json' => [
                'discount_amount' => 1200,
            ],
        ]);

        $result = $calculator->calculate(['subtotal_amount' => 10000], $promotion_reward);

        $this->assertSame(900.0, $result['discount_amount']);
    }

    public function testFreeEntranceFeeRewardWaivesEntranceFeeAmount()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = new PromotionReward([
            'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
        ]);

        $result = $calculator->calculate(['subtotal_amount' => 5800, 'entrance_fee_amount' => 535], $promotion_reward);

        $this->assertSame(535.0, $result['discount_amount']);
        $this->assertSame('ฟรีค่าลงทะเบียน', $result['label']);
    }

    public function testGiftRewardHasNoDiscountButCarriesLabel()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = new PromotionReward([
            'reward_type' => PromotionReward::TYPE_GIFT,
            'reward_config_json' => [
                'gift_name' => 'กระเป๋านักเรียน 1 ใบ',
            ],
        ]);

        $result = $calculator->calculate(['subtotal_amount' => 5800], $promotion_reward);

        $this->assertSame(0.0, $result['discount_amount']);
        $this->assertSame('กระเป๋านักเรียน 1 ใบ', $result['label']);
    }
}
