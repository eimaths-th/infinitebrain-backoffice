<?php

namespace App\Services;

use App\Models\PromotionCampaign;
use App\Models\PromotionRule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PromotionEligibilityService
{
    private $rewardCalculator;

    public function __construct(PromotionRewardCalculator $rewardCalculator)
    {
        $this->rewardCalculator = $rewardCalculator;
    }

    public function getEligiblePromotions(array $receipt_context, ?Collection $campaigns = null)
    {
        $campaigns = $campaigns ?? PromotionCampaign::with(['rules', 'rewards', 'branchEligibilities'])
            ->active()
            ->where(function ($query) use ($receipt_context) {
                if (!empty($receipt_context['promotion_id'])) {
                    $query->whereNull('promotion_id')
                        ->orWhere('promotion_id', (int) $receipt_context['promotion_id']);
                    return;
                }

                $query->whereNull('promotion_id');
            })
            ->orderBy('priority', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return $campaigns
            ->filter(function (PromotionCampaign $campaign) use ($receipt_context) {
                return $this->isCampaignEligible($campaign, $receipt_context);
            })
            ->map(function (PromotionCampaign $campaign) use ($receipt_context) {
                return $this->buildEligiblePromotion($campaign, $receipt_context);
            })
            ->values();
    }

    private function isCampaignEligible(PromotionCampaign $campaign, array $receipt_context)
    {
        if (!$campaign->is_active) {
            return false;
        }

        if (!$this->isWithinDateRange($campaign, $receipt_context)) {
            return false;
        }

        if (!$this->isBranchEligible($campaign, $receipt_context)) {
            return false;
        }

        if (!$this->isPromotionEligible($campaign, $receipt_context)) {
            return false;
        }

        foreach ($campaign->rules as $promotion_rule) {
            if (!$this->isRuleEligible($promotion_rule, $receipt_context)) {
                return false;
            }
        }

        return true;
    }

    private function isPromotionEligible(PromotionCampaign $campaign, array $receipt_context)
    {
        if (empty($campaign->promotion_id)) {
            return true;
        }

        if (empty($receipt_context['promotion_id'])) {
            return false;
        }

        return (int) $campaign->promotion_id === (int) $receipt_context['promotion_id'];
    }

    private function isWithinDateRange(PromotionCampaign $campaign, array $receipt_context)
    {
        $evaluation_date = isset($receipt_context['evaluation_date'])
            ? Carbon::parse($receipt_context['evaluation_date'])
            : Carbon::now();

        if ($campaign->starts_at && $evaluation_date->lt($campaign->starts_at)) {
            return false;
        }

        if ($campaign->ends_at && $evaluation_date->gt($campaign->ends_at)) {
            return false;
        }

        return true;
    }

    private function isBranchEligible(PromotionCampaign $campaign, array $receipt_context)
    {
        $branch_eligibilities = $campaign->branchEligibilities ?? collect();

        if ($branch_eligibilities->isEmpty()) {
            return true;
        }

        if (empty($receipt_context['branch_id'])) {
            return false;
        }

        return $branch_eligibilities
            ->contains('branch_id', (int) $receipt_context['branch_id']);
    }

    private function isRuleEligible(PromotionRule $promotion_rule, array $receipt_context)
    {
        if ($promotion_rule->rule_type === PromotionRule::TYPE_SUBJECT_COMBINATION) {
            return $this->hasRequiredSubjectGroups($promotion_rule, $receipt_context);
        }

        if ($promotion_rule->rule_type === PromotionRule::TYPE_REFERRAL) {
            return !empty($receipt_context['has_referral']);
        }

        if ($promotion_rule->rule_type === PromotionRule::TYPE_NEW_STUDENT_MIN_COURSES) {
            return $this->isNewStudentMinimumCoursesEligible($promotion_rule, $receipt_context);
        }

        if ($promotion_rule->rule_type === PromotionRule::TYPE_NEW_STUDENT) {
            return true;
        }

        if ($promotion_rule->rule_type === PromotionRule::TYPE_MIN_COURSES) {
            return $this->isMinimumCoursesEligible($promotion_rule, $receipt_context);
        }

        if ($promotion_rule->rule_type === PromotionRule::TYPE_EXACT_COURSES) {
            return $this->isExactCoursesEligible($promotion_rule, $receipt_context);
        }

        if ($promotion_rule->rule_type === PromotionRule::TYPE_MIN_MONTHS) {
            return $this->isMinimumMonthsEligible($promotion_rule, $receipt_context);
        }

        if ($promotion_rule->rule_type === PromotionRule::TYPE_EXACT_MONTHS) {
            return $this->isExactMonthsEligible($promotion_rule, $receipt_context);
        }

        if ($promotion_rule->rule_type === PromotionRule::TYPE_SUBJECT_GROUP_MONTHS) {
            return $this->hasRequiredSubjectGroupMonths($promotion_rule, $receipt_context);
        }

        if ($promotion_rule->rule_type === PromotionRule::TYPE_EXACT_SUBTOTAL) {
            return $this->hasExactSubtotal($promotion_rule, $receipt_context);
        }

        return false;
    }

    private function hasRequiredSubjectGroups(PromotionRule $promotion_rule, array $receipt_context)
    {
        $rule_config = $promotion_rule->rule_config_json ?? [];
        $required_subject_groups = $rule_config['subject_groups'] ?? [];

        if (empty($required_subject_groups)) {
            return false;
        }

        $selected_subject_groups = collect($receipt_context['receipt_items'] ?? [])
            ->map(function ($receipt_item) {
                return $receipt_item['subject_group'] ?? $receipt_item['group'] ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty(array_diff($required_subject_groups, $selected_subject_groups));
    }

    private function isNewStudentMinimumCoursesEligible(PromotionRule $promotion_rule, array $receipt_context)
    {
        $rule_config = $promotion_rule->rule_config_json ?? [];
        $minimum_courses = (int) ($rule_config['minimum_courses'] ?? 0);

        if ($minimum_courses <= 0) {
            return false;
        }

        $course_count = collect($receipt_context['receipt_items'] ?? [])
            ->filter(function ($receipt_item) {
                return (int) ($receipt_item['course_type'] ?? 1) === 1;
            })
            ->count();

        return $course_count >= $minimum_courses;
    }

    private function isMinimumCoursesEligible(PromotionRule $promotion_rule, array $receipt_context)
    {
        $rule_config = $promotion_rule->rule_config_json ?? [];
        $minimum_courses = (int) ($rule_config['minimum_courses'] ?? 0);

        if ($minimum_courses <= 0) {
            return false;
        }

        $course_count = collect($receipt_context['receipt_items'] ?? [])
            ->filter(function ($receipt_item) {
                return (int) ($receipt_item['course_type'] ?? 1) === 1;
            })
            ->count();

        return $course_count >= $minimum_courses;
    }

    private function isExactCoursesEligible(PromotionRule $promotion_rule, array $receipt_context)
    {
        $rule_config = $promotion_rule->rule_config_json ?? [];
        $exact_courses = (int) ($rule_config['exact_courses'] ?? 0);

        if ($exact_courses <= 0) {
            return false;
        }

        $course_count = collect($receipt_context['receipt_items'] ?? [])
            ->filter(function ($receipt_item) {
                return (int) ($receipt_item['course_type'] ?? 1) === 1;
            })
            ->count();

        return $course_count === $exact_courses;
    }

    private function isMinimumMonthsEligible(PromotionRule $promotion_rule, array $receipt_context)
    {
        $rule_config = $promotion_rule->rule_config_json ?? [];
        $minimum_months = (int) ($rule_config['minimum_months'] ?? 0);

        if ($minimum_months <= 0) {
            return false;
        }

        return collect($receipt_context['receipt_items'] ?? [])
            ->contains(function ($receipt_item) use ($minimum_months) {
                return (int) ($receipt_item['month_count'] ?? 0) >= $minimum_months;
            });
    }

    private function isExactMonthsEligible(PromotionRule $promotion_rule, array $receipt_context)
    {
        $rule_config = $promotion_rule->rule_config_json ?? [];
        $allowed_months = $rule_config['months'] ?? ($rule_config['allowed_months'] ?? []);

        if (!is_array($allowed_months)) {
            $allowed_months = [$allowed_months];
        }

        $allowed_months = collect($allowed_months)
            ->map(function ($month) {
                return (int) $month;
            })
            ->filter(function ($month) {
                return $month > 0;
            })
            ->all();

        if (empty($allowed_months)) {
            return false;
        }

        return collect($receipt_context['receipt_items'] ?? [])
            ->contains(function ($receipt_item) use ($allowed_months) {
                return in_array((int) ($receipt_item['month_count'] ?? 0), $allowed_months, true);
            });
    }

    private function hasRequiredSubjectGroupMonths(PromotionRule $promotion_rule, array $receipt_context)
    {
        $rule_config = $promotion_rule->rule_config_json ?? [];
        $required_subject_groups = $rule_config['subject_groups'] ?? [];
        $allowed_months = $rule_config['months'] ?? [];

        if (empty($required_subject_groups) || empty($allowed_months)) {
            return false;
        }

        $allowed_months = collect(is_array($allowed_months) ? $allowed_months : [$allowed_months])
            ->map(function ($month) {
                return (float) $month;
            })
            ->filter(function ($month) {
                return $month > 0;
            })
            ->values()
            ->all();

        if (empty($allowed_months)) {
            return false;
        }

        $course_type = $rule_config['course_type'] ?? 1;
        $receipt_items = collect($receipt_context['receipt_items'] ?? [])
            ->filter(function ($receipt_item) use ($course_type) {
                return (int) ($receipt_item['course_type'] ?? 1) === (int) $course_type;
            });

        foreach ($required_subject_groups as $required_subject_group) {
            $has_matching_item = $receipt_items->contains(function ($receipt_item) use ($required_subject_group, $allowed_months) {
                $subject_group = $receipt_item['subject_group'] ?? $receipt_item['group'] ?? null;
                $month_count = (float) ($receipt_item['month_count'] ?? 0);

                if ($subject_group !== $required_subject_group) {
                    return false;
                }

                foreach ($allowed_months as $allowed_month) {
                    if (abs($month_count - $allowed_month) < 0.0001) {
                        return true;
                    }
                }

                return false;
            });

            if (!$has_matching_item) {
                return false;
            }
        }

        return true;
    }

    private function hasExactSubtotal(PromotionRule $promotion_rule, array $receipt_context)
    {
        $rule_config = $promotion_rule->rule_config_json ?? [];
        $expected_amount = (float) ($rule_config['amount'] ?? 0);
        $tolerance = (float) ($rule_config['tolerance'] ?? 0.01);

        if ($expected_amount <= 0) {
            return false;
        }

        $subtotal_amount = (float) ($receipt_context['subtotal_amount'] ?? 0);

        return abs($subtotal_amount - $expected_amount) <= $tolerance;
    }

    private function buildEligiblePromotion(PromotionCampaign $campaign, array $receipt_context)
    {
        $reward_results = $campaign->rewards
            ->map(function ($promotion_reward) use ($receipt_context) {
                return $this->rewardCalculator->calculate($receipt_context, $promotion_reward);
            })
            ->values();

        $reward_summary = $reward_results
            ->pluck('label')
            ->filter()
            ->implode(' + ');

        $reward_categories = $campaign->rewards
            ->map(function ($promotion_reward) {
                return \App\Models\PromotionReward::categoryForType($promotion_reward->reward_type);
            })
            ->unique()
            ->values()
            ->all();

        return [
            'promotion_campaign_id' => $campaign->id,
            'name' => $campaign->name,
            'promotion_type' => $campaign->promotion_type,
            'discount_amount' => $reward_results->sum('discount_amount'),
            'reward_summary' => $reward_summary,
            'reward_categories' => $reward_categories,
            'reward_results' => $reward_results->all(),
            'rule_results' => $campaign->rules->map(function ($promotion_rule) {
                return [
                    'rule_type' => $promotion_rule->rule_type,
                    'rule_config' => $promotion_rule->rule_config_json ?? [],
                ];
            })->values()->all(),
        ];
    }
}
