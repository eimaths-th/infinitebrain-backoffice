<?php

namespace App\Services;

use App\Models\PromotionCampaign;
use App\Models\ReceiptPromotionUsage;
use Illuminate\Support\Facades\DB;

class ReceiptPromotionUsageService
{
    private $comparisonService;

    public function __construct(PromotionUsageComparisonService $comparisonService)
    {
        $this->comparisonService = $comparisonService;
    }

    public function replaceUsage($receipt_school_id, PromotionCampaign $campaign, array $eligible_promotion, $selected_by_user_id = null, array $receipt_context = [])
    {
        return $this->replaceUsages($receipt_school_id, [
            [
                'campaign' => $campaign,
                'eligible_promotion' => $eligible_promotion,
            ],
        ], $selected_by_user_id, $receipt_context);
    }

    /**
     * Replace all calculated promotion usages for a receipt with the given set.
     * Each $selected item is ['campaign' => PromotionCampaign, 'eligible_promotion' => array].
     */
    public function replaceUsages($receipt_school_id, array $selected, $selected_by_user_id = null, array $receipt_context = [])
    {
        return DB::transaction(function () use ($receipt_school_id, $selected, $selected_by_user_id, $receipt_context) {
            ReceiptPromotionUsage::where('receipt_school_id', $receipt_school_id)->delete();

            $created = [];
            foreach ($selected as $item) {
                $campaign = $item['campaign'];
                $eligible_promotion = $item['eligible_promotion'];
                $usage_comparison = $this->comparisonService->Compare($receipt_context, $eligible_promotion);

                $created[] = ReceiptPromotionUsage::create([
                    'receipt_school_id' => $receipt_school_id,
                    'promotion_campaign_id' => $campaign->id,
                    'promotion_name_snapshot' => $eligible_promotion['name'],
                    'discount_amount_snapshot' => $eligible_promotion['discount_amount'] ?? 0,
                    'expected_discount_amount_snapshot' => $usage_comparison['expected_discount_amount'],
                    'actual_discount_amount_snapshot' => $usage_comparison['actual_discount_amount'],
                    'expected_net_amount_snapshot' => $usage_comparison['expected_net_amount'],
                    'actual_net_amount_snapshot' => $usage_comparison['actual_net_amount'],
                    'match_status' => $usage_comparison['match_status'],
                    'mismatch_reason' => $usage_comparison['mismatch_reason'],
                    'reward_snapshot_json' => $eligible_promotion['reward_results'] ?? [],
                    'rule_snapshot_json' => $eligible_promotion['rule_results'] ?? [],
                    'selected_by_user_id' => $selected_by_user_id,
                    'selected_at' => now(),
                ]);
            }

            return $created;
        });
    }

    public function clearUsage($receipt_school_id)
    {
        return ReceiptPromotionUsage::where('receipt_school_id', $receipt_school_id)->delete();
    }
}
