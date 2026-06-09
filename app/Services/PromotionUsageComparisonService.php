<?php

namespace App\Services;

class PromotionUsageComparisonService
{
    const STATUS_MATCHED = 'MATCHED';
    const STATUS_MISMATCHED = 'MISMATCHED';
    const STATUS_NOT_APPLIED = 'NOT_APPLIED';

    public function Compare(array $receipt_context, array $eligible_promotion)
    {
        $expected_discount_amount = $this->GetAmount($eligible_promotion, 'discount_amount');
        $actual_discount_amount = $this->CalculateActualDiscountAmount($receipt_context);
        $actual_net_amount = $this->GetAmount($receipt_context, 'subtotal_amount');
        $expected_net_amount = max(0, $actual_net_amount + $actual_discount_amount - $expected_discount_amount);

        $match_status = self::STATUS_NOT_APPLIED;
        $mismatch_reason = null;

        if ($expected_discount_amount > 0) {
            $match_status = $this->AmountsMatch($expected_discount_amount, $actual_discount_amount)
                ? self::STATUS_MATCHED
                : self::STATUS_MISMATCHED;
        } elseif (!empty($eligible_promotion['reward_results'])) {
            $match_status = self::STATUS_MATCHED;
        }

        if ($match_status === self::STATUS_MISMATCHED) {
            $mismatch_reason = 'ยอดส่วนลดที่ Staff key ไม่ตรงกับ Campaign ที่ Apply';
        }

        return [
            'expected_discount_amount' => round($expected_discount_amount, 2),
            'actual_discount_amount' => round($actual_discount_amount, 2),
            'expected_net_amount' => round($expected_net_amount, 2),
            'actual_net_amount' => round($actual_net_amount, 2),
            'match_status' => $match_status,
            'mismatch_reason' => $mismatch_reason,
            'warning_message' => $mismatch_reason,
        ];
    }

    private function CalculateActualDiscountAmount(array $receipt_context)
    {
        return collect($receipt_context['receipt_items'] ?? [])
            ->sum(function ($receipt_item) {
                return $this->GetAmount($receipt_item, 'discount_amount');
            });
    }

    private function AmountsMatch($expected_amount, $actual_amount)
    {
        return abs(round((float) $expected_amount, 2) - round((float) $actual_amount, 2)) < 0.01;
    }

    private function GetAmount(array $source, $key)
    {
        if (!array_key_exists($key, $source) || $source[$key] === null || $source[$key] === '') {
            return 0;
        }

        return (float) $source[$key];
    }
}
