# Package Discount Per Course Design

Date: 2026-06-10

## Objective

Change package monthly discount calculation from a single flat discount per
receipt to a cash discount per eligible course line, while keeping entrance fee
waiver and student bag rewards limited to one receipt/student benefit.

This spec supersedes the provisional assumption in
`docs/specs/2026-06-09-package-monthly-discount-campaigns-design.md` that package
discounts are flat per receipt.

## Confirmed Requirements

- Package cash discounts apply per eligible course/subject line.
- The rule applies to every tier of every package.
- Math + English 12 months should discount `2,000 + 2,000 = 4,000`.
- Math + English 6 months should discount `600 + 600 = 1,200`.
- Math + English 4 months should discount `350 + 350 = 700`.
- Math + English 2 months should discount `150 + 150 = 300`.
- Play Math + English Sparks 6 months should discount `600 + 600 = 1,200`.
- Play Math + English Sparks 4 months should discount `300 + 300 = 600`.
- Play Math + English Sparks 2 months should discount `100 + 100 = 200`.
- Free entrance fee remains one receipt-level benefit.
- Student bag remains one receipt/student benefit and must not multiply by
  course count.
- Existing rule that only one Campaign can be selected per receipt remains
  unchanged.

## Current Context

The current package tier campaigns are data-driven through
`PromotionCampaignSeeder`:

- Package tier campaigns are linked to the selected `promotion_id`.
- Each tier uses `EXACT_MONTHS`.
- Each tier has a `FIXED_DISCOUNT` reward.
- Each tier has a `FREE_ENTRANCE_FEE` reward.
- Tiers with 4 months or more also have a `GIFT` reward.

The current calculation is receipt-level:

- `PromotionRewardCalculator` returns `FIXED_DISCOUNT` as the configured amount
  once per reward.
- `PromotionEligibilityService` sums all reward result discount amounts.
- `FREE_ENTRANCE_FEE` uses `entrance_fee_amount` from receipt context.
- `GIFT` has no money impact and only contributes a label.

The existing receipt context already contains enough item-level data:

- `receipt_items`
- `course_type`
- `month_count`
- `subject_group`
- `discount_amount`
- `entrance_fee_amount`

## Recommended Approach

Add item-scoped cash discount calculation through `reward_config_json`, without
adding database columns.

Package tier `FIXED_DISCOUNT` rewards should include config similar to:

```json
{
  "calculation_scope": "PER_MATCHING_COURSE",
  "matching_course_type": 1,
  "matching_months": [6],
  "unit_discount_amount": 600
}
```

When a fixed discount reward has `calculation_scope =
PER_MATCHING_COURSE`, the calculator should:

1. Count receipt items where `course_type` equals `matching_course_type`.
2. Keep only items whose `month_count` exactly matches one of
   `matching_months`.
3. Multiply `unit_discount_amount` by that matching item count.
4. Return the multiplied amount as the reward discount.
5. Include calculation details in `reward_config` or result metadata for audit
   and UI summary.

When the config is missing, existing fixed discount behavior remains unchanged.

## Reward Scope Rules

### Cash Discount

Cash discount is course-scoped only when the reward explicitly opts in through
`calculation_scope = PER_MATCHING_COURSE`.

Example:

- Math 12 months + English 12 months
- Matching course count = 2
- Unit discount = 2,000
- Cash discount result = 4,000

### Free Entrance Fee

Entrance fee waiver remains receipt-scoped.

Example:

- Math 12 months + English 12 months
- `entrance_fee_amount = 535`
- Entrance fee waiver result = 535 once

### Gift

Gift remains receipt/student-scoped.

Example:

- Math 12 months + English 12 months
- Gift result = `กระเป๋านักเรียน 1 ใบ`
- The gift label must not become 2 bags.

## Data Flow

1. Staff selects package Promotion and receipt course lines.
2. Preview/save builds `receipt_context` with normalized `receipt_items`.
3. Eligibility still filters campaigns by selected `promotion_id`, date, branch,
   and exact month rules.
4. For the matching tier campaign, reward calculation runs:
   - `FIXED_DISCOUNT` multiplies per matching course when configured.
   - `FREE_ENTRANCE_FEE` waives entrance fee once.
   - `GIFT` remains one label with no discount amount.
5. `PromotionEligibilityService` returns total `discount_amount`, summary, and
   reward results.
6. UI renders the returned summary and amount without needing to select multiple
   campaigns.
7. Save re-validates the selected campaign and stores usage snapshot with the
   multiplied expected discount.

## Impacted Files

### `database/seeders/PromotionCampaignSeeder.php`

Update package tier fixed discount rewards so they include per-course
calculation config:

- `calculation_scope`
- `matching_course_type`
- `matching_months`
- `unit_discount_amount`

Keep `FREE_ENTRANCE_FEE` and `GIFT` reward definitions unchanged.

### `app/Services/PromotionRewardCalculator.php`

Extend `FIXED_DISCOUNT` handling:

- If reward config requests per-course calculation, calculate by matching
  receipt items.
- Otherwise keep existing fixed amount behavior.
- Preserve existing behavior for percent discount, capped discount, entrance
  fee, and gift.

### `app/Services/PromotionEligibilityService.php`

No major responsibility change is expected. It should continue to build eligible
promotion results from reward calculator outputs.

Optional small enhancement:

- Preserve reward result metadata so UI/audit can show matching course count and
  unit discount.

### `resources/views/receipt_school/receipt.blade.php`

No required structural change if the backend returns the correct
`reward_summary` and `discount_amount`.

Optional UX improvement:

- Show a short breakdown in campaign cards, such as `ลด 600 x 2 วิชา = 1,200
  บาท`, when reward result metadata is present.

### `tests/Unit/PromotionRewardCalculatorTest.php`

Add tests for per-course fixed discount calculation.

### `tests/Unit/PromotionEligibilityServiceTest.php`

Update or add package tier tests so totals reflect per-course cash discounts
while entrance and gift stay receipt-scoped.

## Validation and Edge Cases

- If no course item matches the tier months, the campaign should not be eligible
  because the existing `EXACT_MONTHS` rule should fail.
- If one course line matches and another has a different month count, the cash
  discount should multiply only by the matching line count.
- Matching `month_count` in reward calculation must stay consistent with the
  existing `EXACT_MONTHS` eligibility behavior, so preview eligibility and reward
  multiplication cannot disagree.
- If `course_type` is not course type `1`, the line should not multiply package
  cash discount.
- Valid `0`, `false`, and empty string handling should follow existing amount
  parsing rules and should not be treated as eligible course counts.
- Entrance fee waiver should cap to actual `entrance_fee_amount` once.
- Gift should never affect money totals.

## Testing Plan

### Unit Tests

- Fixed discount without config still returns the configured amount once.
- Fixed discount with `PER_MATCHING_COURSE` returns unit discount multiplied by
  matching course count.
- Math/English 12 months with two eligible course lines returns cash discount
  4,000.
- Math/English 6 months with two eligible course lines returns cash discount
  1,200.
- Math/English 4 months with two eligible course lines plus entrance fee and
  gift returns total discount `700 + 535 = 1,235`, with gift label once.
- Play Math/English Sparks 4 months with two eligible course lines returns cash
  discount 600.
- One matching course plus one non-matching month only multiplies the matching
  course.
- Package tier campaign with only one eligible course still returns the existing
  one-course discount amount.

### Manual Tests

- Create receipt: Math + English 12 months, select package tier campaign, verify
  expected campaign discount is 4,000 cash plus one entrance fee waiver.
- Create receipt: Play Math + English Sparks 4 months, verify cash discount is
  600 and gift is one bag.
- Edit an existing receipt with selected campaign and save without changing the
  campaign; validation should still pass.
- Save with staff-keyed discount lower than expected; save should still work but
  warning snapshot should mark mismatch.

## Out of Scope

- Allowing multiple campaign selection on one receipt.
- Changing referral discount behavior.
- Changing new-student 3-course discount behavior.
- Changing printed receipt layout.
- Building a full package pricing engine.
- Auto-selecting the best campaign.
- Multiplying entrance fee waiver or gift.

## Approval Gate

Development can start after this spec is reviewed and approved.
