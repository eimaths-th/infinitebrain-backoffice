<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\PromotionCampaign;
use App\Models\PromotionReward;
use App\Models\PromotionRule;
use Illuminate\Database\Seeder;

class PromotionCampaignSeeder extends Seeder
{
    const PLAY_MATH_ENGLISH_SPARKS_PROMOTION_NAME = 'Promotion Package for Play Math / English Sparks';
    const LEGACY_PLAY_MATH_PROMOTION_NAME = 'Promotion Package for Play Math';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $campaigns = [
            [
                'name' => 'Math and English two-subject discount',
                'promotion_type' => PromotionCampaign::TYPE_TWO_SUBJECTS,
                'priority' => 30,
                'rules' => [
                    [
                        'rule_type' => PromotionRule::TYPE_SUBJECT_COMBINATION,
                        'rule_config_json' => [
                            'subject_groups' => ['M', 'E'],
                        ],
                    ],
                    [
                        'rule_type' => PromotionRule::TYPE_SUBJECT_GROUP_MONTHS,
                        'rule_config_json' => [
                            'subject_groups' => ['M', 'E'],
                            'months' => [1],
                            'course_type' => 1,
                        ],
                    ],
                    [
                        'rule_type' => PromotionRule::TYPE_EXACT_COURSES,
                        'rule_config_json' => [
                            'exact_courses' => 2,
                        ],
                    ],
                    [
                        'rule_type' => PromotionRule::TYPE_EXACT_SUBTOTAL,
                        'rule_config_json' => [
                            'amount' => 5800,
                            'tolerance' => 0.01,
                        ],
                    ],
                ],
                'rewards' => [
                    [
                        'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                        'discount_amount' => 200,
                    ],
                ],
            ],
            [
                'name' => 'Referral discount',
                'promotion_type' => PromotionCampaign::TYPE_REFERRAL,
                'priority' => 20,
                'rules' => [
                    [
                        'rule_type' => PromotionRule::TYPE_REFERRAL,
                        'rule_config_json' => [
                            'requires_referral' => true,
                        ],
                    ],
                ],
                'rewards' => [
                    [
                        'reward_type' => PromotionReward::TYPE_PERCENT_DISCOUNT,
                        'discount_percent' => 10,
                    ],
                ],
            ],
            [
                'name' => 'New student 3 courses discount',
                'promotion_type' => PromotionCampaign::TYPE_NEW_STUDENT_MULTI_COURSE,
                'priority' => 10,
                'rules' => [
                    [
                        'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
                        'rule_config_json' => [
                            'requires_new_student' => true,
                        ],
                    ],
                    [
                        'rule_type' => PromotionRule::TYPE_MIN_COURSES,
                        'rule_config_json' => [
                            'minimum_courses' => 3,
                        ],
                    ],
                ],
                'rewards' => [
                    [
                        'reward_type' => PromotionReward::TYPE_CAPPED_DISCOUNT,
                        'discount_amount' => 900,
                        'max_discount_amount' => 900,
                        'reward_config_json' => [
                            'discount_amount' => 900,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'นักเรียนใหม่ 4 เดือนขึ้นไป ฟรีค่าลงทะเบียนและรับกระเป๋านักเรียน',
                'promotion_type' => PromotionCampaign::TYPE_NEW_STUDENT_FREE_ENTRANCE_GIFT,
                'priority' => 40,
                'rules' => [
                    [
                        'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
                        'rule_config_json' => [
                            'requires_new_student' => true,
                        ],
                    ],
                    [
                        'rule_type' => PromotionRule::TYPE_MIN_MONTHS,
                        'rule_config_json' => [
                            'minimum_months' => 4,
                        ],
                    ],
                ],
                'rewards' => [
                    [
                        'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
                    ],
                    [
                        'reward_type' => PromotionReward::TYPE_GIFT,
                        'reward_config_json' => [
                            'gift_name' => 'กระเป๋านักเรียน 1 ใบ',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'นักเรียนใหม่ 2 เดือน ฟรีค่าลงทะเบียน',
                'promotion_type' => PromotionCampaign::TYPE_NEW_STUDENT_FREE_ENTRANCE,
                'priority' => 35,
                'rules' => [
                    [
                        'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
                        'rule_config_json' => [
                            'requires_new_student' => true,
                        ],
                    ],
                    [
                        'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                        'rule_config_json' => [
                            'months' => [2],
                        ],
                    ],
                ],
                'rewards' => [
                    [
                        'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
                    ],
                ],
            ],
            [
                'name' => 'นักเรียนใหม่ 1 เดือน รับกระเป๋านักเรียน',
                'promotion_type' => PromotionCampaign::TYPE_NEW_STUDENT_GIFT,
                'priority' => 15,
                'rules' => [
                    [
                        'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
                        'rule_config_json' => [
                            'requires_new_student' => true,
                        ],
                    ],
                    [
                        'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                        'rule_config_json' => [
                            'months' => [1],
                        ],
                    ],
                ],
                'rewards' => [
                    [
                        'reward_type' => PromotionReward::TYPE_GIFT,
                        'reward_config_json' => [
                            'gift_name' => 'กระเป๋านักเรียน 1 ใบ',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($campaigns as $campaign_data) {
            $campaign = PromotionCampaign::updateOrCreate(
                ['promotion_type' => $campaign_data['promotion_type']],
                [
                    'name' => $campaign_data['name'],
                    'is_active' => true,
                    'priority' => $campaign_data['priority'],
                ]
            );

            $campaign->rules()->delete();
            foreach ($campaign_data['rules'] as $rule_data) {
                $campaign->rules()->create($rule_data);
            }

            $campaign->rewards()->delete();
            foreach ($campaign_data['rewards'] as $reward_data) {
                $campaign->rewards()->create($reward_data);
            }
        }

        $this->SeedPackageMonthlyDiscountTiers();

        $this->command->info('Promotion campaigns seeded successfully.');
    }

    /**
     * Seed per-package monthly prepay discount tiers for new students.
     *
     * The two packages share subject groups (Play Math is group M, English Sparks
     * is group E), so the package is distinguished by promotion_id, not subject
     * group. Each tier uses EXACT_MONTHS so a given month count maps to exactly
     * one tier (no stacking of lower tiers). Each tier is one complete Campaign
     * because receipts can select only one Campaign.
     */
    private function SeedPackageMonthlyDiscountTiers()
    {
        $package_tiers = [
            [
                'name' => 'Promotion Package for Math/English',
                'aliases' => [],
                'tiers' => [
                    ['months' => 2, 'discount' => 150],
                    ['months' => 4, 'discount' => 350],
                    ['months' => 6, 'discount' => 600],
                    ['months' => 12, 'discount' => 2000],
                ],
            ],
            [
                'name' => self::PLAY_MATH_ENGLISH_SPARKS_PROMOTION_NAME,
                'aliases' => [self::LEGACY_PLAY_MATH_PROMOTION_NAME],
                'tiers' => [
                    ['months' => 2, 'discount' => 100],
                    ['months' => 4, 'discount' => 300],
                    ['months' => 6, 'discount' => 600],
                ],
            ],
        ];

        foreach ($package_tiers as $package_data) {
            $promotion_name = $package_data['name'];
            $promotion = $this->ResolvePackagePromotion($promotion_name, $package_data['aliases']);

            if (!$promotion) {
                $this->command->warn("Skipped tiers: promotion '{$promotion_name}' not found.");
                continue;
            }

            foreach ($package_data['tiers'] as $tier) {
                $name = $this->BuildPackageCampaignName($promotion_name, $tier);
                $legacy_names = collect($package_data['aliases'])
                    ->map(function ($alias_name) use ($tier) {
                        return $this->BuildPackageCampaignName($alias_name, $tier);
                    })
                    ->all();

                $campaign = PromotionCampaign::whereIn('name', array_merge([$name], $legacy_names))->first();

                if ($campaign) {
                    $campaign->update([
                        'name' => $name,
                        'promotion_id' => $promotion->id,
                        'promotion_type' => PromotionCampaign::TYPE_CUSTOM,
                        'is_active' => true,
                        'priority' => 50,
                    ]);
                } else {
                    $campaign = PromotionCampaign::create([
                        'name' => $name,
                        'promotion_id' => $promotion->id,
                        'promotion_type' => PromotionCampaign::TYPE_CUSTOM,
                        'is_active' => true,
                        'priority' => 50,
                    ]);
                }

                $campaign->rules()->delete();
                foreach ($this->BuildPackageTierRules($tier['months']) as $rule_data) {
                    $campaign->rules()->create($rule_data);
                }

                $campaign->rewards()->delete();
                foreach ($this->BuildPackageTierRewards($tier) as $reward_data) {
                    $campaign->rewards()->create($reward_data);
                }
            }
        }
    }

    private function ResolvePackagePromotion($promotion_name, array $aliases)
    {
        $promotion = Promotion::where('name', $promotion_name)->first();

        if (!$promotion && !empty($aliases)) {
            $promotion = Promotion::whereIn('name', $aliases)->first();
        }

        if ($promotion && $promotion->name !== $promotion_name) {
            $promotion->update(['name' => $promotion_name]);
        }

        return $promotion;
    }

    private function BuildPackageCampaignName($promotion_name, array $tier)
    {
        return $promotion_name . ' - ' . $tier['months'] . ' เดือน ส่วนลด ' . $tier['discount'];
    }

    private function BuildPackageTierRules($month_count)
    {
        return [
            [
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
                'rule_config_json' => [
                    'requires_new_student' => true,
                ],
            ],
            [
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [(int) $month_count]],
            ],
        ];
    }

    private function BuildPackageTierRewards(array $tier)
    {
        $rewards = [
            [
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => $tier['discount'],
                'reward_config_json' => [
                    PromotionReward::CONFIG_CALCULATION_SCOPE => PromotionReward::CALCULATION_SCOPE_PER_MATCHING_COURSE,
                    PromotionReward::CONFIG_MATCHING_COURSE_TYPE => PromotionReward::DEFAULT_MATCHING_COURSE_TYPE,
                    PromotionReward::CONFIG_MATCHING_MONTHS => [(int) $tier['months']],
                    PromotionReward::CONFIG_UNIT_DISCOUNT_AMOUNT => $tier['discount'],
                ],
            ],
            [
                'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
            ],
        ];

        if ((int) $tier['months'] >= 4) {
            $rewards[] = [
                'reward_type' => PromotionReward::TYPE_GIFT,
                'reward_config_json' => [
                    'gift_name' => 'กระเป๋านักเรียน 1 ใบ',
                ],
            ];
        }

        return $rewards;
    }
}
