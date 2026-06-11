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
            if (!$this->QualifiesByMinMonths($receipt_context, $promotion_reward)) {
                return $this->buildRewardResult($promotion_reward, 0, [], '');
            }

            $entrance_fee_amount = $this->getAmount($receipt_context, 'entrance_fee_amount');
            $max_discount_amount = $this->getAmount($promotion_reward->getAttributes(), 'max_discount_amount');

            if ($max_discount_amount > 0) {
                $entrance_fee_amount = min($entrance_fee_amount, $max_discount_amount);
            }

            return $this->buildRewardResult($promotion_reward, $entrance_fee_amount);
        }

        if ($promotion_reward->reward_type === PromotionReward::TYPE_GIFT) {
            if (!$this->QualifiesByMinMonths($receipt_context, $promotion_reward)) {
                return $this->buildRewardResult($promotion_reward, 0, [], '');
            }

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
        $calculation_scope = $reward_config[PromotionReward::CONFIG_CALCULATION_SCOPE] ?? null;

        if ($calculation_scope === PromotionReward::CALCULATION_SCOPE_PER_COURSE_MONTH_TIER) {
            return $this->CalculatePerCourseMonthTierDiscountResult($receipt_context, $reward_config);
        }

        if ($calculation_scope === PromotionReward::CALCULATION_SCOPE_PER_MATCHING_COURSE) {
            return $this->CalculatePerMatchingCourseDiscountResult($receipt_context, $promotion_reward, $reward_config);
        }

        return [
            'discount_amount' => $this->getAmount($promotion_reward->getAttributes(), 'discount_amount'),
            PromotionReward::CONFIG_CALCULATION_DETAIL => [],
        ];
    }

    private function CalculatePerCourseMonthTierDiscountResult(array $receipt_context, array $reward_config)
    {
        $matching_course_type = (int) ($reward_config[PromotionReward::CONFIG_MATCHING_COURSE_TYPE] ?? PromotionReward::DEFAULT_MATCHING_COURSE_TYPE);
        $month_discount_tiers = $this->NormalizeMonthDiscountTiers($reward_config[PromotionReward::CONFIG_MONTH_DISCOUNT_TIERS] ?? []);

        $line_discounts = [];
        $discount_amount = 0;

        foreach ($receipt_context['receipt_items'] ?? [] as $receipt_item) {
            if ((int) ($receipt_item['course_type'] ?? PromotionReward::DEFAULT_MATCHING_COURSE_TYPE) !== $matching_course_type) {
                continue;
            }

            $month_count = $this->NormalizeMonthCount($receipt_item['month_count'] ?? null);

            if ($month_count === null || !array_key_exists($month_count, $month_discount_tiers)) {
                continue;
            }

            $line_discount = $month_discount_tiers[$month_count];

            if ($line_discount <= 0) {
                continue;
            }

            $discount_amount += $line_discount;
            $line_discounts[] = [
                'subject_id' => $receipt_item['subject_id'] ?? null,
                'month_count' => $month_count,
                'discount_amount' => round($line_discount, 2),
            ];
        }

        return [
            'discount_amount' => $discount_amount,
            PromotionReward::CONFIG_CALCULATION_DETAIL => [
                PromotionReward::CONFIG_CALCULATION_SCOPE => PromotionReward::CALCULATION_SCOPE_PER_COURSE_MONTH_TIER,
                PromotionReward::CONFIG_MATCHING_COURSE_TYPE => $matching_course_type,
                PromotionReward::CONFIG_MONTH_DISCOUNT_TIERS => $month_discount_tiers,
                PromotionReward::CONFIG_LINE_DISCOUNTS => $line_discounts,
            ],
        ];
    }

    private function NormalizeMonthDiscountTiers($month_discount_tiers)
    {
        if (!is_array($month_discount_tiers)) {
            return [];
        }

        $normalized = [];

        foreach ($month_discount_tiers as $month => $discount) {
            $month_count = (int) $month;
            $discount_amount = (float) $discount;

            if ($month_count <= 0 || $discount_amount <= 0) {
                continue;
            }

            $normalized[$month_count] = $discount_amount;
        }

        return $normalized;
    }

    private function NormalizeMonthCount($month_count)
    {
        if ($month_count === null || $month_count === '') {
            return null;
        }

        return (int) $month_count;
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

    /**
     * Determine whether a receipt meets a reward's minimum-months requirement,
     * based on the highest month commitment among its course lines. Rewards
     * without the requirement always qualify.
     */
    private function QualifiesByMinMonths(array $receipt_context, PromotionReward $promotion_reward)
    {
        $reward_config = $promotion_reward->reward_config_json ?? [];

        if (!array_key_exists(PromotionReward::CONFIG_MIN_QUALIFYING_MONTHS, $reward_config)) {
            return true;
        }

        $min_qualifying_months = (int) $reward_config[PromotionReward::CONFIG_MIN_QUALIFYING_MONTHS];

        if ($min_qualifying_months <= 0) {
            return true;
        }

        $matching_course_type = (int) ($reward_config[PromotionReward::CONFIG_MATCHING_COURSE_TYPE] ?? PromotionReward::DEFAULT_MATCHING_COURSE_TYPE);

        return $this->MaxCourseMonth($receipt_context, $matching_course_type) >= $min_qualifying_months;
    }

    private function MaxCourseMonth(array $receipt_context, $matching_course_type)
    {
        return collect($receipt_context['receipt_items'] ?? [])
            ->filter(function ($receipt_item) use ($matching_course_type) {
                return (int) ($receipt_item['course_type'] ?? PromotionReward::DEFAULT_MATCHING_COURSE_TYPE) === $matching_course_type;
            })
            ->map(function ($receipt_item) {
                return (int) ($receipt_item['month_count'] ?? 0);
            })
            ->max() ?? 0;
    }

    private function buildRewardResult(PromotionReward $promotion_reward, $discount_amount, array $calculation_detail = [], $label_override = null)
    {
        $discount_amount = round(max(0, (float) $discount_amount), 2);

        $reward_result = [
            'reward_type' => $promotion_reward->reward_type,
            'discount_amount' => $discount_amount,
            'reward_config' => $promotion_reward->reward_config_json ?? [],
            'label' => $label_override !== null ? $label_override : $this->buildRewardLabel($promotion_reward, $discount_amount),
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
