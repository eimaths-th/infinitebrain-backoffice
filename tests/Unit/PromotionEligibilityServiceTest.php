<?php

namespace Tests\Unit;

use App\Models\PromotionCampaign;
use App\Models\PromotionReward;
use App\Models\PromotionRule;
use App\Services\PromotionEligibilityService;
use App\Services\PromotionRewardCalculator;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class PromotionEligibilityServiceTest extends TestCase
{
    public function testSubjectCombinationPromotionIsEligibleWhenRequiredGroupsExist()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_SUBJECT_COMBINATION,
                'rule_config_json' => [
                    'subject_groups' => ['M', 'E'],
                ],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 200,
            ]),
        ]);

        $result = $service->getEligiblePromotions([
            'subtotal_amount' => 5800,
            'receipt_items' => [
                ['subject_group' => 'M'],
                ['subject_group' => 'E'],
            ],
        ], collect([$campaign]));

        $this->assertCount(1, $result);
        $this->assertSame(200.0, $result->first()['discount_amount']);
    }

    public function testReferralPromotionIsNotEligibleWithoutReferralContext()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_REFERRAL,
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_PERCENT_DISCOUNT,
                'discount_percent' => 10,
            ]),
        ]);

        $result = $service->getEligiblePromotions([
            'subtotal_amount' => 5800,
            'has_referral' => false,
        ], collect([$campaign]));

        $this->assertCount(0, $result);
    }

    public function testNewStudentMinimumCoursesPromotionRequiresNewStudentFlag()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT_MIN_COURSES,
                'rule_config_json' => [
                    'minimum_courses' => 3,
                ],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_CAPPED_DISCOUNT,
                'max_discount_amount' => 900,
                'reward_config_json' => [
                    'discount_amount' => 900,
                ],
            ]),
        ]);

        $result = $service->getEligiblePromotions([
            'subtotal_amount' => 10000,
            'is_new_student' => true,
            'receipt_items' => [
                ['course_type' => 1],
                ['course_type' => 1],
                ['course_type' => 1],
            ],
        ], collect([$campaign]));

        $this->assertCount(1, $result);
        $this->assertSame(900.0, $result->first()['discount_amount']);
    }

    public function testDynamicRulesCanCombineNewStudentAndMinimumCourses()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
                'rule_config_json' => [
                    'requires_new_student' => true,
                ],
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_MIN_COURSES,
                'rule_config_json' => [
                    'minimum_courses' => 3,
                ],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 900,
            ]),
        ]);

        $result = $service->getEligiblePromotions([
            'subtotal_amount' => 10000,
            'is_new_student' => true,
            'receipt_items' => [
                ['course_type' => 1],
                ['course_type' => 1],
                ['course_type' => 1],
            ],
        ], collect([$campaign]));

        $this->assertCount(1, $result);
        $this->assertSame(900.0, $result->first()['discount_amount']);
    }

    public function testMinimumMonthsRuleRequiresAtLeastOneMatchingReceiptItem()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_MIN_MONTHS,
                'rule_config_json' => [
                    'minimum_months' => 4,
                ],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 300,
            ]),
        ]);

        $result = $service->getEligiblePromotions([
            'subtotal_amount' => 14000,
            'receipt_items' => [
                ['month_count' => 2],
                ['month_count' => 4],
            ],
        ], collect([$campaign]));

        $this->assertCount(1, $result);
        $this->assertSame(300.0, $result->first()['discount_amount']);
    }

    public function testCampaignLinkedToSelectedPromotionIsEligible()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_MIN_MONTHS,
                'rule_config_json' => [
                    'minimum_months' => 6,
                ],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 600,
            ]),
        ], 10);

        $result = $service->getEligiblePromotions([
            'promotion_id' => 10,
            'subtotal_amount' => 17400,
            'receipt_items' => [
                ['month_count' => 6],
            ],
        ], collect([$campaign]));

        $this->assertCount(1, $result);
        $this->assertSame(600.0, $result->first()['discount_amount']);
    }

    public function testCampaignLinkedToAnotherPromotionIsNotEligible()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_MIN_MONTHS,
                'rule_config_json' => [
                    'minimum_months' => 6,
                ],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 600,
            ]),
        ], 10);

        $result = $service->getEligiblePromotions([
            'promotion_id' => 20,
            'subtotal_amount' => 17400,
            'receipt_items' => [
                ['month_count' => 6],
            ],
        ], collect([$campaign]));

        $this->assertCount(0, $result);
    }

    public function testGlobalCampaignIsEligibleWithSelectedPromotion()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_REFERRAL,
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_PERCENT_DISCOUNT,
                'discount_percent' => 10,
            ]),
        ]);

        $result = $service->getEligiblePromotions([
            'promotion_id' => 20,
            'subtotal_amount' => 5800,
            'has_referral' => true,
        ], collect([$campaign]));

        $this->assertCount(1, $result);
        $this->assertSame(580.0, $result->first()['discount_amount']);
    }

    public function testLinkedCampaignIsNotEligibleWhenPromotionContextIsMissing()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_MIN_MONTHS,
                'rule_config_json' => [
                    'minimum_months' => 2,
                ],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 150,
            ]),
        ], 10);

        $result = $service->getEligiblePromotions([
            'subtotal_amount' => 5800,
            'receipt_items' => [
                ['month_count' => 2],
            ],
        ], collect([$campaign]));

        $this->assertCount(0, $result);
    }

    public function testExactMonthsRuleMatchesOnlyAllowedMonths()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
                'rule_config_json' => ['requires_new_student' => true],
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [2, 3]],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
            ]),
        ]);

        $eligible = $service->getEligiblePromotions([
            'subtotal_amount' => 5800,
            'entrance_fee_amount' => 535,
            'is_new_student' => true,
            'receipt_items' => [['month_count' => 2]],
        ], collect([$campaign]));

        $this->assertCount(1, $eligible);
        $this->assertSame(535.0, $eligible->first()['discount_amount']);
        $this->assertSame('ฟรีค่าลงทะเบียน', $eligible->first()['reward_summary']);

        $notEligible = $service->getEligiblePromotions([
            'subtotal_amount' => 5800,
            'entrance_fee_amount' => 535,
            'is_new_student' => true,
            'receipt_items' => [['month_count' => 4]],
        ], collect([$campaign]));

        $this->assertCount(0, $notEligible);
    }

    public function testFreeEntranceAndGiftCampaignSummarisesBothRewards()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
                'rule_config_json' => ['requires_new_student' => true],
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_MIN_MONTHS,
                'rule_config_json' => ['minimum_months' => 4],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
            ]),
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_GIFT,
                'reward_config_json' => ['gift_name' => 'กระเป๋านักเรียน 1 ใบ'],
            ]),
        ]);

        $result = $service->getEligiblePromotions([
            'subtotal_amount' => 11600,
            'entrance_fee_amount' => 535,
            'is_new_student' => true,
            'receipt_items' => [['month_count' => 4]],
        ], collect([$campaign]));

        $this->assertCount(1, $result);
        $this->assertSame(535.0, $result->first()['discount_amount']);
        $this->assertSame('ฟรีค่าลงทะเบียน + กระเป๋านักเรียน 1 ใบ', $result->first()['reward_summary']);
        $this->assertSame(['ENTRANCE', 'GIFT'], $result->first()['reward_categories']);
    }

    public function testCashCampaignExposesCashRewardCategory()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_REFERRAL,
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_PERCENT_DISCOUNT,
                'discount_percent' => 10,
            ]),
        ]);

        $result = $service->getEligiblePromotions([
            'subtotal_amount' => 5800,
            'has_referral' => true,
        ], collect([$campaign]));

        $this->assertSame(['CASH'], $result->first()['reward_categories']);
    }

    private function makeCampaign(array $rules, array $rewards, $promotion_id = null)
    {
        $campaign = new PromotionCampaign([
            'promotion_id' => $promotion_id,
            'name' => 'Test campaign',
            'promotion_type' => PromotionCampaign::TYPE_TWO_SUBJECTS,
            'is_active' => true,
        ]);
        $campaign->id = 1;
        $campaign->setRelation('rules', new Collection($rules));
        $campaign->setRelation('rewards', new Collection($rewards));
        $campaign->setRelation('branchEligibilities', new Collection());

        return $campaign;
    }
}
