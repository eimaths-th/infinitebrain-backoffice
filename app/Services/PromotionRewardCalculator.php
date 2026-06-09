<?php

namespace App\Services;

use App\Models\PromotionReward;

class PromotionRewardCalculator
{
    public function calculate(array $receipt_context, PromotionReward $promotion_reward)
    {
        $subtotal_amount = $this->getAmount($receipt_context, 'subtotal_amount');

        if ($promotion_reward->reward_type === PromotionReward::TYPE_FIXED_DISCOUNT) {
            return $this->buildRewardResult(
                $promotion_reward,
                $this->getAmount($promotion_reward->getAttributes(), 'discount_amount')
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

    private function buildRewardResult(PromotionReward $promotion_reward, $discount_amount)
    {
        $discount_amount = round(max(0, (float) $discount_amount), 2);

        return [
            'reward_type' => $promotion_reward->reward_type,
            'discount_amount' => $discount_amount,
            'reward_config' => $promotion_reward->reward_config_json ?? [],
            'label' => $this->buildRewardLabel($promotion_reward, $discount_amount),
        ];
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
