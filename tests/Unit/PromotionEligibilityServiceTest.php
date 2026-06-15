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

    public function testTwoSubjectDiscountRequiresOneMonthEachAndExactSubtotal()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());
        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_SUBJECT_COMBINATION,
                'rule_config_json' => [
                    'subject_groups' => ['M', 'E'],
                ],
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_SUBJECT_GROUP_MONTHS,
                'rule_config_json' => [
                    'subject_groups' => ['M', 'E'],
                    'months' => [1],
                    'course_type' => 1,
                ],
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_COURSES,
                'rule_config_json' => [
                    'exact_courses' => 2,
                ],
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_SUBTOTAL,
                'rule_config_json' => [
                    'amount' => 5800,
                    'tolerance' => 0.01,
                ],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 200,
            ]),
        ]);

        $eligible = $service->getEligiblePromotions([
            'subtotal_amount' => 5800,
            'receipt_items' => [
                ['subject_group' => 'M', 'course_type' => 1, 'month_count' => 1],
                ['subject_group' => 'E', 'course_type' => 1, 'month_count' => 1],
            ],
        ], collect([$campaign]));

        $this->assertCount(1, $eligible);
        $this->assertSame(200.0, $eligible->first()['discount_amount']);

        $wrongMonth = $service->getEligiblePromotions([
            'subtotal_amount' => 11600,
            'receipt_items' => [
                ['subject_group' => 'M', 'course_type' => 1, 'month_count' => 2],
                ['subject_group' => 'E', 'course_type' => 1, 'month_count' => 2],
            ],
        ], collect([$campaign]));

        $this->assertCount(0, $wrongMonth);

        $wrongSubtotal = $service->getEligiblePromotions([
            'subtotal_amount' => 6335,
            'receipt_items' => [
                ['subject_group' => 'M', 'course_type' => 1, 'month_count' => 1],
                ['subject_group' => 'E', 'course_type' => 1, 'month_count' => 1],
            ],
        ], collect([$campaign]));

        $this->assertCount(0, $wrongSubtotal);

        $extraCourse = $service->getEligiblePromotions([
            'subtotal_amount' => 5800,
            'receipt_items' => [
                ['subject_group' => 'M', 'course_type' => 1, 'month_count' => 1],
                ['subject_group' => 'E', 'course_type' => 1, 'month_count' => 1],
                ['subject_group' => 'M', 'course_type' => 1, 'month_count' => 1],
            ],
        ], collect([$campaign]));

        $this->assertCount(0, $extraCourse);
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

    public function testNewStudentMinimumCoursesPromotionAllowsExistingStudents()
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
            'is_new_student' => false,
            'receipt_items' => [
                ['course_type' => 1],
                ['course_type' => 1],
                ['course_type' => 1],
            ],
        ], collect([$campaign]));

        $this->assertCount(1, $result);
        $this->assertSame(900.0, $result->first()['discount_amount']);
    }

    public function testDynamicRulesCanUseNewStudentPresetForAnyStudent()
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
            'is_new_student' => false,
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

    public function testPackageTierDiscountIsDistinguishedByLinkedPromotion()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());

        $mathTier = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [4]],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 350,
            ]),
        ], 1);

        $playTier = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [4]],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 300,
            ]),
        ], 2);

        $campaigns = collect([$mathTier, $playTier]);
        $items = [
            'is_new_student' => true,
            'receipt_items' => [['subject_group' => 'M', 'month_count' => 4]],
        ];

        $math = $service->getEligiblePromotions(['promotion_id' => 1] + $items, $campaigns);
        $this->assertCount(1, $math);
        $this->assertSame(350.0, $math->first()['discount_amount']);

        $play = $service->getEligiblePromotions(['promotion_id' => 2] + $items, $campaigns);
        $this->assertCount(1, $play);
        $this->assertSame(300.0, $play->first()['discount_amount']);
    }

    public function testPackageTierDiscountAllowsExistingStudentContext()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());

        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [4]],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 350,
            ]),
        ], 1);

        $result = $service->getEligiblePromotions([
            'promotion_id' => 1,
            'is_new_student' => false,
            'receipt_items' => [['subject_group' => 'M', 'month_count' => 4]],
        ], collect([$campaign]));

        $this->assertCount(1, $result);
        $this->assertSame(350.0, $result->first()['discount_amount']);
    }

    public function testPackageTierCampaignCanCombineDiscountEntranceAndGiftRewards()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());

        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [4]],
            ]),
        ], [
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => 350,
            ]),
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
            ]),
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_GIFT,
                'reward_config_json' => ['gift_name' => 'กระเป๋านักเรียน 1 ใบ'],
            ]),
        ], 1);

        $result = $service->getEligiblePromotions([
            'promotion_id' => 1,
            'is_new_student' => true,
            'entrance_fee_amount' => 535,
            'receipt_items' => [['subject_group' => 'M', 'month_count' => 4]],
        ], collect([$campaign]));

        $this->assertCount(1, $result);
        $this->assertSame(885.0, $result->first()['discount_amount']);
        $this->assertSame(['CASH', 'ENTRANCE', 'GIFT'], $result->first()['reward_categories']);
        $this->assertSame('ลด 350.00 บาท + ฟรีค่าลงทะเบียน + กระเป๋านักเรียน 1 ใบ', $result->first()['reward_summary']);
    }

    public function testPackageTierPerCourseDiscountMultipliesCashOnly()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());

        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [4]],
            ]),
        ], [
            $this->MakePerCourseFixedReward(4, 350),
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
            ]),
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_GIFT,
                'reward_config_json' => ['gift_name' => 'กระเป๋านักเรียน 1 ใบ'],
            ]),
        ], 1);

        $result = $service->getEligiblePromotions([
            'promotion_id' => 1,
            'is_new_student' => true,
            'entrance_fee_amount' => 535,
            'receipt_items' => [
                ['subject_group' => 'M', 'course_type' => 1, 'month_count' => 4],
                ['subject_group' => 'E', 'course_type' => 1, 'month_count' => 4],
            ],
        ], collect([$campaign]));

        $reward_results = $result->first()['reward_results'];

        $this->assertCount(1, $result);
        $this->assertSame(1235.0, $result->first()['discount_amount']);
        $this->assertSame(700.0, $reward_results[0]['discount_amount']);
        $this->assertSame(2, $reward_results[0][PromotionReward::CONFIG_CALCULATION_DETAIL][PromotionReward::CONFIG_MATCHING_COURSE_COUNT]);
        $this->assertSame('ลด 700.00 บาท + ฟรีค่าลงทะเบียน + กระเป๋านักเรียน 1 ใบ', $result->first()['reward_summary']);
        $this->assertSame(['CASH', 'ENTRANCE', 'GIFT'], $result->first()['reward_categories']);
    }

    public function testPackageTierPerCourseDiscountCountsOnlyMatchingCourseLines()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());

        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [6]],
            ]),
        ], [
            $this->MakePerCourseFixedReward(6, 600),
        ], 1);

        $result = $service->getEligiblePromotions([
            'promotion_id' => 1,
            'receipt_items' => [
                ['subject_group' => 'M', 'course_type' => 1, 'month_count' => 6],
                ['subject_group' => 'E', 'course_type' => 1, 'month_count' => 4],
                ['subject_group' => 'M', 'course_type' => 2, 'month_count' => 6],
            ],
        ], collect([$campaign]));

        $reward_results = $result->first()['reward_results'];

        $this->assertCount(1, $result);
        $this->assertSame(600.0, $result->first()['discount_amount']);
        $this->assertSame(1, $reward_results[0][PromotionReward::CONFIG_CALCULATION_DETAIL][PromotionReward::CONFIG_MATCHING_COURSE_COUNT]);
    }

    public function testPackageTierPerCourseDiscountUsesLinkedPackageUnitAmount()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());

        $mathTier = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [4]],
            ]),
        ], [
            $this->MakePerCourseFixedReward(4, 350),
        ], 1);

        $playTier = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [4]],
            ]),
        ], [
            $this->MakePerCourseFixedReward(4, 300),
        ], 2);

        $campaigns = collect([$mathTier, $playTier]);
        $receipt_context = [
            'receipt_items' => [
                ['subject_group' => 'M', 'course_type' => 1, 'month_count' => 4],
                ['subject_group' => 'E', 'course_type' => 1, 'month_count' => 4],
            ],
        ];

        $math = $service->getEligiblePromotions(['promotion_id' => 1] + $receipt_context, $campaigns);
        $play = $service->getEligiblePromotions(['promotion_id' => 2] + $receipt_context, $campaigns);

        $this->assertCount(1, $math);
        $this->assertSame(700.0, $math->first()['discount_amount']);

        $this->assertCount(1, $play);
        $this->assertSame(600.0, $play->first()['discount_amount']);
    }

    public function testPackageMonthTierDiscountIsPerSubjectByItsOwnMonths()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());

        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [12]],
            ]),
        ], $this->MakePackageBundleRewards([2 => 150, 4 => 350, 6 => 600, 12 => 2000]), 1);

        $result = $service->getEligiblePromotions([
            'promotion_id' => 1,
            'is_new_student' => true,
            'entrance_fee_amount' => 535,
            'receipt_items' => [
                ['subject_group' => 'M', 'course_type' => 1, 'month_count' => 12],
                ['subject_group' => 'E', 'course_type' => 1, 'month_count' => 2],
            ],
        ], collect([$campaign]));

        $reward_results = $result->first()['reward_results'];

        $this->assertCount(1, $result);
        $this->assertSame(2685.0, $result->first()['discount_amount']);
        $this->assertSame(2150.0, $reward_results[0]['discount_amount']);
        $this->assertSame('ลด 2,150.00 บาท + ฟรีค่าลงทะเบียน + กระเป๋านักเรียน 1 ใบ', $result->first()['reward_summary']);

        $line_discounts = $reward_results[0][PromotionReward::CONFIG_CALCULATION_DETAIL][PromotionReward::CONFIG_LINE_DISCOUNTS];
        $this->assertCount(2, $line_discounts);
        $this->assertSame(2000.0, $line_discounts[0]['discount_amount']);
        $this->assertSame(150.0, $line_discounts[1]['discount_amount']);
    }

    public function testPackageBundleGivesEntranceButNoGiftForTwoMonthOnlyReceipt()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());

        $campaign = $this->makeCampaign([
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
            ]),
            new PromotionRule([
                'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                'rule_config_json' => ['months' => [2, 4, 6, 12]],
            ]),
        ], $this->MakePackageBundleRewards([2 => 150, 4 => 350, 6 => 600, 12 => 2000]), 1);

        $result = $service->getEligiblePromotions([
            'promotion_id' => 1,
            'is_new_student' => true,
            'entrance_fee_amount' => 535,
            'receipt_items' => [
                ['subject_group' => 'M', 'course_type' => 1, 'month_count' => 2],
                ['subject_group' => 'E', 'course_type' => 1, 'month_count' => 2],
            ],
        ], collect([$campaign]));

        $this->assertCount(1, $result);
        $this->assertSame(835.0, $result->first()['discount_amount']);
        $this->assertSame('ลด 300.00 บาท + ฟรีค่าลงทะเบียน', $result->first()['reward_summary']);
    }

    public function testExactMonthTiersDoNotStackForSamePackage()
    {
        $service = new PromotionEligibilityService(new PromotionRewardCalculator());

        $tiers = collect([2 => 150, 4 => 350, 6 => 600])
            ->map(function ($discount, $months) {
                return $this->makeCampaign([
                    new PromotionRule([
                        'rule_type' => PromotionRule::TYPE_EXACT_MONTHS,
                        'rule_config_json' => ['months' => [$months]],
                    ]),
                ], [
                    new PromotionReward([
                        'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                        'discount_amount' => $discount,
                    ]),
                ], 1);
            })
            ->values();

        $result = $service->getEligiblePromotions([
            'promotion_id' => 1,
            'receipt_items' => [['subject_group' => 'M', 'month_count' => 6]],
        ], $tiers);

        $this->assertCount(1, $result);
        $this->assertSame(600.0, $result->first()['discount_amount']);
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

    private function MakePerCourseFixedReward($months, $unit_discount_amount)
    {
        return new PromotionReward([
            'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
            'discount_amount' => $unit_discount_amount,
            'reward_config_json' => [
                PromotionReward::CONFIG_CALCULATION_SCOPE => PromotionReward::CALCULATION_SCOPE_PER_MATCHING_COURSE,
                PromotionReward::CONFIG_MATCHING_COURSE_TYPE => PromotionReward::DEFAULT_MATCHING_COURSE_TYPE,
                PromotionReward::CONFIG_MATCHING_MONTHS => [(int) $months],
                PromotionReward::CONFIG_UNIT_DISCOUNT_AMOUNT => $unit_discount_amount,
            ],
        ]);
    }

    private function MakeMonthTierReward(array $month_discount_tiers)
    {
        return new PromotionReward([
            'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
            'discount_amount' => 0,
            'reward_config_json' => [
                PromotionReward::CONFIG_CALCULATION_SCOPE => PromotionReward::CALCULATION_SCOPE_PER_COURSE_MONTH_TIER,
                PromotionReward::CONFIG_MATCHING_COURSE_TYPE => PromotionReward::DEFAULT_MATCHING_COURSE_TYPE,
                PromotionReward::CONFIG_MONTH_DISCOUNT_TIERS => $month_discount_tiers,
            ],
        ]);
    }

    private function MakePackageBundleRewards(array $month_discount_tiers)
    {
        return [
            $this->MakeMonthTierReward($month_discount_tiers),
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
                'reward_config_json' => [
                    PromotionReward::CONFIG_MATCHING_COURSE_TYPE => PromotionReward::DEFAULT_MATCHING_COURSE_TYPE,
                    PromotionReward::CONFIG_MIN_QUALIFYING_MONTHS => 2,
                ],
            ]),
            new PromotionReward([
                'reward_type' => PromotionReward::TYPE_GIFT,
                'reward_config_json' => [
                    'gift_name' => 'กระเป๋านักเรียน 1 ใบ',
                    PromotionReward::CONFIG_MATCHING_COURSE_TYPE => PromotionReward::DEFAULT_MATCHING_COURSE_TYPE,
                    PromotionReward::CONFIG_MIN_QUALIFYING_MONTHS => 4,
                ],
            ]),
        ];
    }
}
