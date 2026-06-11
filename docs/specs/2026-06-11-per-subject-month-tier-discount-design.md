# Per-Subject Month-Tier Discount Design

Date: 2026-06-11

## Objective

Change package cash-discount calculation so each course line is discounted by
its own month count, looked up from the package's full month-to-discount tier
table — instead of only discounting course lines whose month count matches the
single selected tier.

This supersedes the assumption in
`docs/specs/2026-06-10-package-discount-per-course-design.md` that every course
line on a package receipt shares the same month count.

## Motivating Case

Receipt 1290 mixes month counts within one Math/English package receipt:

- Math Level 1 — 12 months
- English Pre-A I Level 1 — 2 months

The selected campaign is the 12-month tier (`matching_months = [12]`), so only
the Math line is discounted (2,000). The English line (2 months) receives no
discount. Expected behavior: English should also be discounted by its own
2-month rate (150), for a total cash discount of 2,150.

## Confirmed Requirements

- Cash discount is calculated per course line by that line's own month count.
- The per-month rate comes from the package's full tier table:
  - Math/English: `{2:150, 4:350, 6:600, 12:2000}`
  - Play Math/English Sparks: `{2:100, 4:300, 6:600}`
- A course line whose month count is not in the table receives 0 cash discount
  (e.g. 1 month).
- Only `course_type = 1` lines receive the cash discount.
- Total cash discount = sum of per-line discounts.
- The receipt view shows the per-line discount under each subject (already
  implemented in this branch) and shows no combined total line when every
  discount is attributed per line.
- Free entrance fee and student bag rewards are unchanged and out of scope.
- Existing receipts whose stored snapshot uses the old per-tier format must keep
  rendering correctly (combined total at the bottom).

## Current Context

- Package tier campaigns are seeded one campaign per month tier per package,
  linked by `promotion_id` (`PromotionCampaignSeeder::SeedPackageMonthlyDiscountTiers`).
- Each tier's `FIXED_DISCOUNT` reward uses `reward_config_json` with
  `calculation_scope = PER_MATCHING_COURSE`, a single `matching_months` array,
  and `unit_discount_amount`.
- `PromotionRewardCalculator::CalculatePerMatchingCourseDiscountResult` counts
  course lines matching the single tier months and multiplies by the unit
  amount.
- `ReceiptSchoolController::show()` already distributes the snapshot discount per
  course line, but matches a single unit amount against a single set of
  `matching_months`, so it cannot attribute different rates to subjects with
  different month counts.
- The receipt views `receipt/view1.blade.php` and `receipt/print1.blade.php`
  already render a `promo_discount` line under each course line and fall back to
  `campaign_discount_remaining` at the bottom.

## Recommended Approach

Collapse the per-tier campaigns into a single package Campaign per package,
whose rewards are bundled:

- One `FIXED_DISCOUNT` reward holding the full month-to-discount tier table,
  with a new calculation scope `PER_COURSE_MONTH_TIER` that looks up each course
  line's own month in that table and sums the result.
- One `FREE_ENTRANCE_FEE` reward gated by a minimum-months requirement (waived
  when the receipt's highest course month is at least 2).
- One `GIFT` reward gated by a minimum-months requirement (granted when the
  receipt's highest course month is at least 4).

The single Campaign removes the duplicate tier options from the selection UI:
the staff selects one package Campaign and the system applies the correct
per-subject discount plus the entrance/gift benefits earned by the highest month
commitment on the receipt.

The old per-tier campaigns ("<package> - N เดือน ส่วนลด X") are deleted by the
seeder; their linked usage snapshots are preserved because the foreign key nulls
on delete.

### Reward config shape

```json
{
  "calculation_scope": "PER_COURSE_MONTH_TIER",
  "matching_course_type": 1,
  "month_discount_tiers": { "2": 150, "4": 350, "6": 600, "12": 2000 }
}
```

`unit_discount_amount` and `matching_months` may remain for backward
compatibility but are not used by the new scope.

## Components

### `app/Models/PromotionReward.php`

Add constants:

- `CALCULATION_SCOPE_PER_COURSE_MONTH_TIER = 'PER_COURSE_MONTH_TIER'`
- `CONFIG_MONTH_DISCOUNT_TIERS = 'month_discount_tiers'`
- `CONFIG_LINE_DISCOUNTS = 'line_discounts'` (per-line breakdown key in
  calculation detail)

### `app/Services/PromotionRewardCalculator.php`

- In `CalculateFixedDiscountResult`, route `PER_COURSE_MONTH_TIER` to a new
  method `CalculatePerCourseMonthTierDiscountResult`.
- The new method:
  1. Reads `matching_course_type` (default 1) and the `month_discount_tiers`
     map (normalized to integer month keys, float amounts).
  2. Iterates receipt items; for each `course_type = matching_course_type` line
     whose integer month count is a key in the map, takes that month's discount.
  3. Sums all per-line discounts as the reward discount amount.
  4. Returns `calculation_detail` with the scope, `matching_course_type`, the
     normalized `month_discount_tiers`, and a `line_discounts` array of
     `{ month, discount_amount }` (and `subject_id` when available) for audit
     and for the receipt view.
- `PER_MATCHING_COURSE` behavior and all other reward types remain unchanged.

### `database/seeders/PromotionCampaignSeeder.php`

- In `SeedPackageMonthlyDiscountTiers`, build a single month-to-discount map per
  package from its tier list.
- In `BuildPackageTierRewards`, set the `FIXED_DISCOUNT` reward config to use
  `PER_COURSE_MONTH_TIER` with `matching_course_type` and the full
  `month_discount_tiers` map. Keep `FREE_ENTRANCE_FEE` and `GIFT` rewards
  unchanged.

### `app/Http/Controllers/ReceiptSchoolController.php`

- In `show()`, when a reward snapshot's `calculation_detail` scope is
  `PER_COURSE_MONTH_TIER`, attribute the per-line discount by looking up each
  course line's own integer month count in the snapshot's `month_discount_tiers`
  map, rather than matching one unit amount.
- Keep the existing `PER_MATCHING_COURSE` distribution path for old snapshots.
- `campaign_discount_remaining` continues to hold any discount not attributed to
  a line, so legacy snapshots still show a combined total at the bottom.

### `resources/views/receipt/view1.blade.php` and `receipt/print1.blade.php`

No change required. The per-line `promo_discount` row and the
`campaign_discount_remaining` bottom row already exist.

### Tests

`tests/Unit/PromotionRewardCalculatorTest.php`:

- Two subjects with different months (12 and 2) return summed per-line discounts
  (2,000 + 150 = 2,150) with a `line_discounts` breakdown.
- Two subjects with the same month (12 and 12) return 4,000.
- A 1-month line (not in the table) contributes 0.
- A non-`course_type = 1` line contributes 0.
- Empty or missing `month_discount_tiers` returns 0.

`tests/Unit/PromotionEligibilityServiceTest.php`:

- Package tier campaign with mixed-month course lines returns the summed
  per-subject cash discount, with entrance fee and gift unchanged.

## Data Flow

1. Staff selects the package `promotion_id` and course lines (months may differ
   per subject).
2. Preview/save builds `receipt_context` with normalized `receipt_items`
   (each carries `course_type` and `month_count`).
3. Eligibility filters campaigns by `promotion_id`, date, branch, new-student,
   and the tier's existing rules (unchanged).
4. For the selected tier campaign, `FIXED_DISCOUNT` now computes the per-subject
   month-tier discount and returns the per-line breakdown.
5. `ReceiptPromotionUsageService` stores the reward results (with
   `calculation_detail`) in `reward_snapshot_json`.
6. `ReceiptSchoolController::show()` reads the snapshot, attributes each line's
   discount from `month_discount_tiers`, and renders one discount row per
   subject.

## Validation and Edge Cases

- Month-count matching uses integer comparison, consistent with the existing
  per-course calculation.
- A line whose month is absent from the table contributes 0 and shows no
  discount row.
- If no course line maps to a tier, the cash discount is 0 (campaign still
  selectable; reward simply yields 0).
- Entrance fee waiver and gift rewards are unaffected.
- Old snapshots without `month_discount_tiers` keep the previous distribution
  and bottom-total fallback.

## Entrance Fee and Gift Gating

The single package Campaign bundles entrance fee and gift, gated by the highest
month commitment on the receipt (`min_qualifying_months` in the reward config,
evaluated against the maximum `month_count` among `course_type = 1` lines):

- Free entrance fee: highest course month >= 2.
- Student bag: highest course month >= 4.

When a reward does not qualify, it contributes no discount and an empty label so
it does not appear in the receipt summary. The standalone new-student
entrance/gift campaigns remain for non-package scenarios, including the 1-month
bag case.

## Per-Line Label

For `PER_COURSE_MONTH_TIER` snapshots, the receipt shows each course line's
discount labelled with its own months and amount, e.g.
`Promotion Package for Math/English - 2 เดือน ส่วนลด 150`. Legacy
`PER_MATCHING_COURSE` snapshots keep the stored campaign name unchanged.

## Out of Scope

- Changing the standalone new-student entrance/gift campaigns.
- Changing the promotion selection UI components themselves.
- Referral and new-student 3-course discount behavior.

## Approval Gate

Development can start after this spec is reviewed and approved.
