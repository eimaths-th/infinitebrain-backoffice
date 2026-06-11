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

    public function testFixedDiscountRewardCanMultiplyByMatchingCourseCount()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = new PromotionReward([
            'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
            'discount_amount' => 600,
            'reward_config_json' => [
                PromotionReward::CONFIG_CALCULATION_SCOPE => PromotionReward::CALCULATION_SCOPE_PER_MATCHING_COURSE,
                PromotionReward::CONFIG_MATCHING_COURSE_TYPE => 1,
                PromotionReward::CONFIG_MATCHING_MONTHS => [6],
                PromotionReward::CONFIG_UNIT_DISCOUNT_AMOUNT => 600,
            ],
        ]);

        $result = $calculator->calculate([
            'receipt_items' => [
                ['course_type' => 1, 'month_count' => 6],
                ['course_type' => 1, 'month_count' => 6],
                ['course_type' => 1, 'month_count' => 4],
                ['course_type' => 2, 'month_count' => 6],
            ],
        ], $promotion_reward);

        $this->assertSame(1200.0, $result['discount_amount']);
        $this->assertSame(2, $result[PromotionReward::CONFIG_CALCULATION_DETAIL][PromotionReward::CONFIG_MATCHING_COURSE_COUNT]);
        $this->assertSame(600.0, $result[PromotionReward::CONFIG_CALCULATION_DETAIL][PromotionReward::CONFIG_UNIT_DISCOUNT_AMOUNT]);
    }

    public function testPerCourseMonthTierDiscountSumsEachSubjectByItsOwnMonths()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = $this->buildMonthTierReward();

        $result = $calculator->calculate([
            'receipt_items' => [
                ['course_type' => 1, 'month_count' => 12, 'subject_id' => 10],
                ['course_type' => 1, 'month_count' => 2, 'subject_id' => 20],
            ],
        ], $promotion_reward);

        $this->assertSame(2150.0, $result['discount_amount']);

        $line_discounts = $result[PromotionReward::CONFIG_CALCULATION_DETAIL][PromotionReward::CONFIG_LINE_DISCOUNTS];
        $this->assertCount(2, $line_discounts);
        $this->assertSame(2000.0, $line_discounts[0]['discount_amount']);
        $this->assertSame(2, $line_discounts[1]['month_count']);
        $this->assertSame(150.0, $line_discounts[1]['discount_amount']);
    }

    public function testPerCourseMonthTierDiscountWithSameMonthsMultipliesPerSubject()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = $this->buildMonthTierReward();

        $result = $calculator->calculate([
            'receipt_items' => [
                ['course_type' => 1, 'month_count' => 12],
                ['course_type' => 1, 'month_count' => 12],
            ],
        ], $promotion_reward);

        $this->assertSame(4000.0, $result['discount_amount']);
    }

    public function testPerCourseMonthTierDiscountIgnoresMonthsNotInTable()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = $this->buildMonthTierReward();

        $result = $calculator->calculate([
            'receipt_items' => [
                ['course_type' => 1, 'month_count' => 1],
                ['course_type' => 1, 'month_count' => 4],
            ],
        ], $promotion_reward);

        $this->assertSame(350.0, $result['discount_amount']);
        $this->assertCount(1, $result[PromotionReward::CONFIG_CALCULATION_DETAIL][PromotionReward::CONFIG_LINE_DISCOUNTS]);
    }

    public function testPerCourseMonthTierDiscountIgnoresNonCourseLines()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = $this->buildMonthTierReward();

        $result = $calculator->calculate([
            'receipt_items' => [
                ['course_type' => 2, 'month_count' => 12],
                ['course_type' => 1, 'month_count' => 6],
            ],
        ], $promotion_reward);

        $this->assertSame(600.0, $result['discount_amount']);
    }

    public function testPerCourseMonthTierDiscountWithEmptyTableReturnsZero()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = new PromotionReward([
            'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
            'discount_amount' => 2000,
            'reward_config_json' => [
                PromotionReward::CONFIG_CALCULATION_SCOPE => PromotionReward::CALCULATION_SCOPE_PER_COURSE_MONTH_TIER,
                PromotionReward::CONFIG_MATCHING_COURSE_TYPE => 1,
                PromotionReward::CONFIG_MONTH_DISCOUNT_TIERS => [],
            ],
        ]);

        $result = $calculator->calculate([
            'receipt_items' => [
                ['course_type' => 1, 'month_count' => 12],
            ],
        ], $promotion_reward);

        $this->assertSame(0.0, $result['discount_amount']);
    }

    private function buildMonthTierReward()
    {
        return new PromotionReward([
            'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
            'discount_amount' => 2000,
            'reward_config_json' => [
                PromotionReward::CONFIG_CALCULATION_SCOPE => PromotionReward::CALCULATION_SCOPE_PER_COURSE_MONTH_TIER,
                PromotionReward::CONFIG_MATCHING_COURSE_TYPE => 1,
                PromotionReward::CONFIG_MONTH_DISCOUNT_TIERS => [
                    2 => 150,
                    4 => 350,
                    6 => 600,
                    12 => 2000,
                ],
            ],
        ]);
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

    public function testFreeEntranceFeeWaivedWhenMaxCourseMonthsMeetsMinimum()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = new PromotionReward([
            'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
            'reward_config_json' => [
                PromotionReward::CONFIG_MATCHING_COURSE_TYPE => 1,
                PromotionReward::CONFIG_MIN_QUALIFYING_MONTHS => 2,
            ],
        ]);

        $result = $calculator->calculate([
            'entrance_fee_amount' => 535,
            'receipt_items' => [
                ['course_type' => 1, 'month_count' => 12],
                ['course_type' => 1, 'month_count' => 2],
            ],
        ], $promotion_reward);

        $this->assertSame(535.0, $result['discount_amount']);
        $this->assertSame('ฟรีค่าลงทะเบียน', $result['label']);
    }

    public function testFreeEntranceFeeNotWaivedWhenBelowMinimumMonths()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = new PromotionReward([
            'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
            'reward_config_json' => [
                PromotionReward::CONFIG_MATCHING_COURSE_TYPE => 1,
                PromotionReward::CONFIG_MIN_QUALIFYING_MONTHS => 2,
            ],
        ]);

        $result = $calculator->calculate([
            'entrance_fee_amount' => 535,
            'receipt_items' => [
                ['course_type' => 1, 'month_count' => 1],
            ],
        ], $promotion_reward);

        $this->assertSame(0.0, $result['discount_amount']);
        $this->assertSame('', $result['label']);
    }

    public function testGiftGrantedWhenMaxCourseMonthsMeetsMinimum()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = new PromotionReward([
            'reward_type' => PromotionReward::TYPE_GIFT,
            'reward_config_json' => [
                'gift_name' => 'กระเป๋านักเรียน 1 ใบ',
                PromotionReward::CONFIG_MATCHING_COURSE_TYPE => 1,
                PromotionReward::CONFIG_MIN_QUALIFYING_MONTHS => 4,
            ],
        ]);

        $result = $calculator->calculate([
            'receipt_items' => [
                ['course_type' => 1, 'month_count' => 12],
                ['course_type' => 1, 'month_count' => 2],
            ],
        ], $promotion_reward);

        $this->assertSame(0.0, $result['discount_amount']);
        $this->assertSame('กระเป๋านักเรียน 1 ใบ', $result['label']);
    }

    public function testGiftWithheldWhenBelowMinimumMonths()
    {
        $calculator = new PromotionRewardCalculator();
        $promotion_reward = new PromotionReward([
            'reward_type' => PromotionReward::TYPE_GIFT,
            'reward_config_json' => [
                'gift_name' => 'กระเป๋านักเรียน 1 ใบ',
                PromotionReward::CONFIG_MATCHING_COURSE_TYPE => 1,
                PromotionReward::CONFIG_MIN_QUALIFYING_MONTHS => 4,
            ],
        ]);

        $result = $calculator->calculate([
            'receipt_items' => [
                ['course_type' => 1, 'month_count' => 2],
            ],
        ], $promotion_reward);

        $this->assertSame(0.0, $result['discount_amount']);
        $this->assertSame('', $result['label']);
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
