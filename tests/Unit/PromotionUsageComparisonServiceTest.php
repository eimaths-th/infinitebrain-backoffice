<?php

namespace Tests\Unit;

use App\Services\PromotionUsageComparisonService;
use PHPUnit\Framework\TestCase;

class PromotionUsageComparisonServiceTest extends TestCase
{
    public function testCompareReturnsMatchedWhenActualDiscountEqualsExpected()
    {
        $service = new PromotionUsageComparisonService();

        $receipt_context = [
            'subtotal_amount' => 5600,
            'receipt_items' => [
                ['discount_amount' => 200],
            ],
        ];
        $eligible_promotion = [
            'discount_amount' => 200,
            'reward_results' => [['discount_amount' => 200]],
        ];

        $result = $service->Compare($receipt_context, $eligible_promotion);

        $this->assertSame(PromotionUsageComparisonService::STATUS_MATCHED, $result['match_status']);
        $this->assertSame(200.0, $result['expected_discount_amount']);
        $this->assertSame(200.0, $result['actual_discount_amount']);
        $this->assertNull($result['mismatch_reason']);
        $this->assertNull($result['warning_message']);
    }

    public function testCompareReturnsMismatchedWithWarningButDoesNotBlock()
    {
        $service = new PromotionUsageComparisonService();

        $receipt_context = [
            'subtotal_amount' => 5800,
            'receipt_items' => [
                ['discount_amount' => 0],
            ],
        ];
        $eligible_promotion = [
            'discount_amount' => 200,
            'reward_results' => [['discount_amount' => 200]],
        ];

        $result = $service->Compare($receipt_context, $eligible_promotion);

        $this->assertSame(PromotionUsageComparisonService::STATUS_MISMATCHED, $result['match_status']);
        $this->assertSame(200.0, $result['expected_discount_amount']);
        $this->assertSame(0.0, $result['actual_discount_amount']);
        // Mismatch surfaces a warning but the service still returns a usable snapshot (no exception / no block).
        $this->assertNotNull($result['warning_message']);
        $this->assertSame($result['mismatch_reason'], $result['warning_message']);
    }

    public function testCompareReturnsMatchedForGiftOnlyRewardWithoutDiscount()
    {
        $service = new PromotionUsageComparisonService();

        $receipt_context = [
            'subtotal_amount' => 5800,
            'receipt_items' => [
                ['discount_amount' => 0],
            ],
        ];
        $eligible_promotion = [
            'discount_amount' => 0,
            'reward_results' => [['label' => 'กระเป๋านักเรียน 1 ใบ', 'discount_amount' => 0]],
        ];

        $result = $service->Compare($receipt_context, $eligible_promotion);

        $this->assertSame(PromotionUsageComparisonService::STATUS_MATCHED, $result['match_status']);
        $this->assertNull($result['warning_message']);
    }

    public function testCompareReturnsNotAppliedWhenNoDiscountAndNoReward()
    {
        $service = new PromotionUsageComparisonService();

        $receipt_context = [
            'subtotal_amount' => 5800,
            'receipt_items' => [],
        ];
        $eligible_promotion = [
            'discount_amount' => 0,
        ];

        $result = $service->Compare($receipt_context, $eligible_promotion);

        $this->assertSame(PromotionUsageComparisonService::STATUS_NOT_APPLIED, $result['match_status']);
    }
}
