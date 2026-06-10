<?php

namespace App\Services;

use App\Models\PromotionReward;

class PromotionRewardCalculator
{
    public function calculate(array $receipt_context, PromotionReward $promotion_reward)
    {
        $subtotal_amount = $this->getAmount($receipt_context, 'subtotal_amount');

        if ($promotion_reward->reward_type === PromotionReward::TYPE_FIXED_DISCOUNT) {
            $fixed_discount_result = $this->CalculateFixedDiscountResult($receipt_context, $promotion_reward);

            return $this->buildRewardResult(
                $promotion_reward,
                $fixed_discount_result['discount_amount'],
                $fixed_discount_result[PromotionReward::CONFIG_CALCULATION_DETAIL]
            );
        }

        if ($promotion_reward->reward_type === PromotionReward::TYPE_PERCENT_DISCOUNT) {
            $discount_percent = $this->getAmount($promotion_reward->getAttributes(), 'discount_percent');
            $discount_amount = $subtotal_amount * ($discount_percent / 100);

            return $this->buildRewardResult($promotion_reward, $discount_amount);
        }

        if ($promotion_reward->reward_type === PromotionReward::TYPE_CAPPED_DISCOUNT) {
            $discount_amount = $this->calculateConfiguredDiscount($receipt_context, $promotion_reward);
            $max_discount_amount = $this->getAmount($promotion_reward->getAttributes(), 'max_discount_amount');

            if ($max_discount_amount > 0) {
                $discount_amount = min($discount_amount, $max_discount_amount);
            }

            return $this->buildRewardResult($promotion_reward, $discount_amount);
        }

        if ($promotion_reward->reward_type === PromotionReward::TYPE_FREE_ENTRANCE_FEE) {
            $entrance_fee_amount = $this->getAmount($receipt_context, 'entrance_fee_amount');
            $max_discount_amount = $this->getAmount($promotion_reward->getAttributes(), 'max_discount_amount');

            if ($max_discount_amount > 0) {
                $entrance_fee_amount = min($entrance_fee_amount, $max_discount_amount);
            }

            return $this->buildRewardResult($promotion_reward, $entrance_fee_amount);
        }

        if ($promotion_reward->reward_type === PromotionReward::TYPE_GIFT) {
            return $this->buildRewardResult($promotion_reward, 0);
        }

        return $this->buildRewardResult($promotion_reward, 0);
    }

    private function calculateConfiguredDiscount(array $receipt_context, PromotionReward $promotion_reward)
    {
        $reward_config = $promotion_reward->reward_config_json ?? [];

        if (isset($reward_config['discount_amount'])) {
            return $this->getAmount($reward_config, 'discount_amount');
        }

        if (isset($reward_config['discount_percent'])) {
            $subtotal_amount = $this->getAmount($receipt_context, 'subtotal_amount');
            $discount_percent = $this->getAmount($reward_config, 'discount_percent');

            return $subtotal_amount * ($discount_percent / 100);
        }

        return $this->getAmount($promotion_reward->getAttributes(), 'discount_amount');
    }

    private function CalculateFixedDiscountResult(array $receipt_context, PromotionReward $promotion_reward)
    {
        $reward_config = $promotion_reward->reward_config_json ?? [];

        if (($reward_config[PromotionReward::CONFIG_CALCULATION_SCOPE] ?? null) === PromotionReward::CALCULATION_SCOPE_PER_MATCHING_COURSE) {
            return $this->CalculatePerMatchingCourseDiscountResult($receipt_context, $promotion_reward, $reward_config);
        }

        return [
            'discount_amount' => $this->getAmount($promotion_reward->getAttributes(), 'discount_amount'),
            PromotionReward::CONFIG_CALCULATION_DETAIL => [],
        ];
    }

    private function CalculatePerMatchingCourseDiscountResult(array $receipt_context, PromotionReward $promotion_reward, array $reward_config)
    {
        $matching_course_type = (int) ($reward_config[PromotionReward::CONFIG_MATCHING_COURSE_TYPE] ?? PromotionReward::DEFAULT_MATCHING_COURSE_TYPE);
        $matching_months = $this->NormalizeMatchingMonths($reward_config[PromotionReward::CONFIG_MATCHING_MONTHS] ?? []);
        $unit_discount_amount = $this->ResolveUnitDiscountAmount($promotion_reward, $reward_config);
        $matching_course_count = $this->CountMatchingCourseItems(
            $receipt_context['receipt_items'] ?? [],
            $matching_course_type,
            $matching_months
        );

        return [
            'discount_amount' => $unit_discount_amount * $matching_course_count,
            PromotionReward::CONFIG_CALCULATION_DETAIL => [
                PromotionReward::CONFIG_CALCULATION_SCOPE => PromotionReward::CALCULATION_SCOPE_PER_MATCHING_COURSE,
                PromotionReward::CONFIG_MATCHING_COURSE_TYPE => $matching_course_type,
                PromotionReward::CONFIG_MATCHING_MONTHS => $matching_months,
                PromotionReward::CONFIG_UNIT_DISCOUNT_AMOUNT => round($unit_discount_amount, 2),
                PromotionReward::CONFIG_MATCHING_COURSE_COUNT => $matching_course_count,
            ],
        ];
    }

    private function ResolveUnitDiscountAmount(PromotionReward $promotion_reward, array $reward_config)
    {
        $unit_discount_amount = $this->getAmount($reward_config, PromotionReward::CONFIG_UNIT_DISCOUNT_AMOUNT);

        if ($unit_discount_amount > 0) {
            return $unit_discount_amount;
        }

        return $this->getAmount($promotion_reward->getAttributes(), 'discount_amount');
    }

    private function CountMatchingCourseItems(array $receipt_items, $matching_course_type, array $matching_months)
    {
        if (empty($matching_months)) {
            return 0;
        }

        return collect($receipt_items)
            ->filter(function ($receipt_item) use ($matching_course_type, $matching_months) {
                $course_type = (int) ($receipt_item['course_type'] ?? PromotionReward::DEFAULT_MATCHING_COURSE_TYPE);

                if ($course_type !== $matching_course_type) {
                    return false;
                }

                return $this->IsMatchingMonth($receipt_item['month_count'] ?? null, $matching_months);
            })
            ->count();
    }

    private function NormalizeMatchingMonths($matching_months)
    {
        if (!is_array($matching_months)) {
            $matching_months = [$matching_months];
        }

        return collect($matching_months)
            ->map(function ($month) {
                return (int) $month;
            })
            ->filter(function ($month) {
                return $month > 0;
            })
            ->values()
            ->all();
    }

    private function IsMatchingMonth($month_count, array $matching_months)
    {
        if ($month_count === null || $month_count === '') {
            return false;
        }

        return in_array((int) $month_count, $matching_months, true);
    }

    private function buildRewardResult(PromotionReward $promotion_reward, $discount_amount, array $calculation_detail = [])
    {
        $discount_amount = round(max(0, (float) $discount_amount), 2);

        $reward_result = [
            'reward_type' => $promotion_reward->reward_type,
            'discount_amount' => $discount_amount,
            'reward_config' => $promotion_reward->reward_config_json ?? [],
            'label' => $this->buildRewardLabel($promotion_reward, $discount_amount),
        ];

        if (!empty($calculation_detail)) {
            $reward_result[PromotionReward::CONFIG_CALCULATION_DETAIL] = $calculation_detail;
        }

        return $reward_result;
    }

    private function buildRewardLabel(PromotionReward $promotion_reward, $discount_amount)
    {
        $reward_config = $promotion_reward->reward_config_json ?? [];

        if ($promotion_reward->reward_type === PromotionReward::TYPE_FREE_ENTRANCE_FEE) {
            return 'ฟรีค่าลงทะเบียน';
        }

        if ($promotion_reward->reward_type === PromotionReward::TYPE_GIFT) {
            return $reward_config['gift_name'] ?? 'รับของแถม';
        }

        if ($discount_amount > 0) {
            return 'ลด ' . number_format($discount_amount, 2) . ' บาท';
        }

        return '';
    }

    private function getAmount(array $source, $key)
    {
        if (!array_key_exists($key, $source) || $source[$key] === null || $source[$key] === '') {
            return 0;
        }

        return (float) $source[$key];
    }
}
