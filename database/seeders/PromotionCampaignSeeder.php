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
     * Seed one monthly prepay discount Campaign per package for new students.
     *
     * The two packages share subject groups (Play Math is group M, English Sparks
     * is group E), so the package is distinguished by promotion_id, not subject
     * group. Each package is a single Campaign that holds the full month-to-discount
     * table: cash discount is computed per course line by that line's own month
     * count, while the free entrance fee and student bag are receipt-level benefits
     * gated by the highest month commitment on the receipt.
     */
    private function SeedPackageMonthlyDiscountTiers()
    {
        $packages = [
            [
                'name' => 'Promotion Package for Math/English',
                'aliases' => [],
                'tiers' => [2 => 150, 4 => 350, 6 => 600, 12 => 2000],
            ],
            [
                'name' => self::PLAY_MATH_ENGLISH_SPARKS_PROMOTION_NAME,
                'aliases' => [self::LEGACY_PLAY_MATH_PROMOTION_NAME],
                'tiers' => [2 => 100, 4 => 300, 6 => 600],
            ],
        ];

        foreach ($packages as $package_data) {
            $promotion_name = $package_data['name'];
            $promotion = $this->ResolvePackagePromotion($promotion_name, $package_data['aliases']);

            if (!$promotion) {
                $this->command->warn("Skipped package: promotion '{$promotion_name}' not found.");
                continue;
            }

            $month_discount_tiers = collect($package_data['tiers'])
                ->mapWithKeys(function ($discount, $months) {
                    return [(int) $months => (float) $discount];
                })
                ->all();

            $this->DeleteLegacyPackageTierCampaigns($promotion->id, $promotion_name, $package_data['aliases']);

            $campaign = PromotionCampaign::updateOrCreate(
                ['name' => $promotion_name, 'promotion_id' => $promotion->id],
                [
                    'promotion_type' => PromotionCampaign::TYPE_CUSTOM,
                    'is_active' => true,
                    'priority' => 50,
                ]
            );

            $campaign->rules()->delete();
            foreach ($this->BuildPackageRules(array_keys($month_discount_tiers)) as $rule_data) {
                $campaign->rules()->create($rule_data);
            }

            $campaign->rewards()->delete();
            foreach ($this->BuildPackageRewards($month_discount_tiers) as $reward_data) {
                $campaign->rewards()->create($reward_data);
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

    /**
     * Remove the old one-campaign-per-tier records (named "<package> - N เดือน
     * ส่วนลด X") that the single package Campaign replaces. Linked usage snapshots
     * keep their data because the foreign key nulls on delete.
     */
    private function DeleteLegacyPackageTierCampaigns($promotion_id, $promotion_name, array $aliases)
    {
        $name_prefixes = collect(array_merge([$promotion_name], $aliases))
            ->map(function ($name) {
                return $name . ' - ';
            });

        PromotionCampaign::where('promotion_id', $promotion_id)
            ->where(function ($query) use ($name_prefixes) {
                foreach ($name_prefixes as $name_prefix) {
                    $query->orWhere('name', 'like', $name_prefix . '%');
                }
            })
            ->get()
            ->each(function (PromotionCampaign $campaign) {
                $campaign->rules()->delete();
                $campaign->rewards()->delete();
                $campaign->delete();
            });
    }

    private function BuildPackageRules(array $tier_months)
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
                'rule_config_json' => ['months' => array_values(array_map('intval', $tier_months))],
            ],
        ];
    }

    /**
     * Build the bundled rewards for a package Campaign:
     * - cash discount per course line by its own month (PER_COURSE_MONTH_TIER),
     * - free entrance fee when any course line is 2+ months,
     * - student bag when any course line is 4+ months.
     */
    private function BuildPackageRewards(array $month_discount_tiers)
    {
        return [
            [
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 0,
                'reward_config_json' => [
                    PromotionReward::CONFIG_CALCULATION_SCOPE => PromotionReward::CALCULATION_SCOPE_PER_COURSE_MONTH_TIER,
                    PromotionReward::CONFIG_MATCHING_COURSE_TYPE => PromotionReward::DEFAULT_MATCHING_COURSE_TYPE,
                    PromotionReward::CONFIG_MONTH_DISCOUNT_TIERS => $month_discount_tiers,
                ],
            ],
            [
                'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
                'reward_config_json' => [
                    PromotionReward::CONFIG_MATCHING_COURSE_TYPE => PromotionReward::DEFAULT_MATCHING_COURSE_TYPE,
                    PromotionReward::CONFIG_MIN_QUALIFYING_MONTHS => 2,
                ],
            ],
            [
                'reward_type' => PromotionReward::TYPE_GIFT,
                'reward_config_json' => [
                    'gift_name' => 'กระเป๋านักเรียน 1 ใบ',
                    PromotionReward::CONFIG_MATCHING_COURSE_TYPE => PromotionReward::DEFAULT_MATCHING_COURSE_TYPE,
                    PromotionReward::CONFIG_MIN_QUALIFYING_MONTHS => 4,
                ],
            ],
        ];
    }
}
