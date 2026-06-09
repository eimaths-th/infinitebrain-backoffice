<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\PromotionCampaign;
use App\Models\PromotionReward;
use App\Models\PromotionRule;
use Illuminate\Database\Seeder;

class PromotionCampaignSeeder extends Seeder
{
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

        $this->seedPackageMonthlyDiscountTiers();

        $this->command->info('Promotion campaigns seeded successfully.');
    }

    /**
     * Seed per-package monthly prepay discount tiers.
     *
     * The two packages share subject groups (Play Math is group M, English Sparks
     * is group E), so the package is distinguished by promotion_id, not subject
     * group. Each tier uses EXACT_MONTHS so a given month count maps to exactly
     * one tier (no stacking of lower tiers).
     */
    private function seedPackageMonthlyDiscountTiers()
    {
        $package_tiers = [
            'Promotion Package for Math/English' => [
                ['months' => 2, 'discount' => 150],
                ['months' => 4, 'discount' => 350],
                ['months' => 6, 'discount' => 600],
                ['months' => 12, 'discount' => 2000],
            ],
            'Promotion Package for Play Math' => [
                ['months' => 2, 'discount' => 100],
                ['months' => 4, 'discount' => 300],
                ['months' => 6, 'discount' => 600],
            ],
        ];

        foreach ($package_tiers as $promotion_name => $tiers) {
            $promotion = Promotion::where('name', $promotion_name)->first();

            if (!$promotion) {
                $this->command->warn("Skipped tiers: promotion '{$promotion_name}' not found.");
                continue;
            }

            foreach ($tiers as $tier) {
                $name = $promotion_name . ' - ' . $tier['months'] . ' เดือน ส่วนลด ' . $tier['discount'];

                $campaign = PromotionCampaign::updateOrCreate(
                    ['name' => $name],
                    [
                        'promotion_id' => $promotion->id,
                        'promotion_type' => PromotionCampaign::TYPE_CUSTOM,
                        'is_active' => true,
                        'priority' => 50,
                    ]
                );

                $campaign->rules()->delete();
                $campaign->rules()->create([
                    'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                    'rule_config_json' => ['months' => [$tier['months']]],
                ]);

                $campaign->rewards()->delete();
                $campaign->rewards()->create([
                    'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                    'discount_amount' => $tier['discount'],
                ]);
            }
        }
    }
}
