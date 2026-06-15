# Campaigns System Current Specification

Date: 2026-06-11

## Purpose

This document is the consolidated current specification for the `receipt_school`
Campaign system. It is the source of truth for the Campaign system as it
currently works.

## Domain Definitions

### Promotion

`Promotion` is the receipt header context selected by staff on a school receipt.

It is stored in `promotions` and referenced by `receipt_school.promotion_id`.
It remains separate from calculated discounts.

Promotion is responsible for:

- Receipt-level promotion category tracking.
- Filtering which linked Campaigns may appear.
- Showing the selected promotion context in receipt create/edit/list flows.

Promotion is not responsible for:

- Rule evaluation.
- Discount calculation.
- Reward calculation.
- Historical Campaign snapshots.

### Campaign

`Campaign` is the calculated rule-and-reward configuration.

It is stored in `promotion_campaigns` with child `promotion_rules`,
`promotion_rewards`, and optional `promotion_branch_eligibilities`.

Campaign is responsible for:

- Deciding whether a receipt draft is eligible.
- Calculating cash discount, entrance-fee waiver, and gift rewards.
- Returning preview results to the receipt modal.
- Providing snapshot data when the receipt is saved.

### Receipt Promotion Usage

`receipt_promotion_usages` stores the selected Campaign snapshot for a saved
receipt. It protects historical receipts from later Campaign configuration
changes.

## Current Business Rules

- The Campaign engine applies only to `receipt_school`.
- Staff must select a `Promotion` on create receipt.
- Campaigns may be linked to one Promotion or left global.
- Global Campaigns are eligible for any selected Promotion if their rules pass.
- Linked Campaigns are eligible only when their `promotion_id` matches the
  selected receipt `promotion_id`.
- Multiple Campaigns may be selected per receipt when their reward categories do
  not overlap.
- The backend validates the selected Campaign again during create/update.
- A Campaign may contain multiple rewards, such as cash discount plus free
  entrance fee plus gift.
- Subject promotion validation still uses `subjects.promotion`.
- Promotion and non-promotion subjects cannot be mixed in one school receipt.
- Campaign expected-vs-actual mismatch warns and audits, but does not block save.

## New Student Context for Campaigns

The backend still calculates the `นักเรียนใหม่` context per subject group, not
globally per student. This context is shown to staff as supporting information.
It no longer blocks Campaign eligibility by itself.

For each course receipt line where `course_type = 1`, the system checks the pair:

- `student_id`
- `subject_group`

A receipt is marked as new-student context only when every course line in the
receipt has no previous `school_price` record for the same
`student_id + subject_group`, excluding the current receipt during edit.

Examples:

- A student who studied Math before and now starts English for the first time is
  new for English.
- A student who already studied Math before is not new for a Math Campaign.
- If a receipt mixes one new subject and one old subject, the receipt-level
  Campaign `is_new_student` context is false, but staff may still select
  Campaigns that use the legacy new-student preset when the other rules pass.
- If a course line is incomplete and has no student or subject group yet, the
  receipt is not treated as new-student eligible.

## Data Model

### `promotions`

Current receipt-level master data.

Important fields:

- `id`
- `name`
- `is_active`

Behavior:

- Active records appear in new receipt selection.
- Existing receipts may keep previously selected promotions.
- Promotion records are not the calculation source.

### `receipt_school`

Important Campaign-related fields:

- `promotion_id`
- `source`
- `referent_parent_id`

Behavior:

- `promotion_id` provides the Promotion context for Campaign filtering.
- `source` and `referent_parent_id` provide referral context.

### `promotion_campaigns`

Important fields:

- `promotion_id`
- `name`
- `promotion_type`
- `is_active`
- `starts_at`
- `ends_at`
- `priority`

Supported `promotion_type` values:

- `TWO_SUBJECTS`
- `REFERRAL`
- `NEW_STUDENT_MULTI_COURSE`
- `NEW_STUDENT_FREE_ENTRANCE_GIFT`
- `NEW_STUDENT_FREE_ENTRANCE`
- `NEW_STUDENT_GIFT`
- `CUSTOM`

Behavior:

- `promotion_id = null` means global Campaign.
- `promotion_id` set means Campaign is linked to that Promotion.
- `is_active`, date range, branch eligibility, linked Promotion, and rules must
  all pass before the Campaign is returned.

### `promotion_rules`

Important fields:

- `promotion_campaign_id`
- `rule_type`
- `rule_config_json`

Supported rule types:

- `SUBJECT_COMBINATION`
- `REFERRAL`
- `NEW_STUDENT_MIN_COURSES`
- `NEW_STUDENT`
- `MIN_COURSES`
- `EXACT_COURSES`
- `MIN_MONTHS`
- `EXACT_MONTHS`
- `SUBJECT_GROUP_MONTHS`
- `EXACT_SUBTOTAL`
- `BRANCH_ELIGIBLE`

Rule behavior:

- All rules on a Campaign must pass.
- Subject rules use `receipt_items[].subject_group`.
- Month rules use normalized `receipt_items[].month_count`.
- New-student rules are treated as staff-selectable presets and do not block
  eligibility by `is_new_student`.
- Referral rules use the receipt context `has_referral`.

### `promotion_rewards`

Important fields:

- `promotion_campaign_id`
- `reward_type`
- `discount_amount`
- `discount_percent`
- `max_discount_amount`
- `reward_config_json`

Supported reward types:

- `FIXED_DISCOUNT`
- `PERCENT_DISCOUNT`
- `CAPPED_DISCOUNT`
- `FREE_ENTRANCE_FEE`
- `GIFT`

Reward categories:

- Cash rewards: `FIXED_DISCOUNT`, `PERCENT_DISCOUNT`, `CAPPED_DISCOUNT`
- Entrance rewards: `FREE_ENTRANCE_FEE`
- Gift rewards: `GIFT`

Supported fixed-discount scopes:

- Default receipt-level fixed discount.
- `PER_MATCHING_COURSE`
- `PER_COURSE_MONTH_TIER`

### `promotion_branch_eligibilities`

Optional branch eligibility table.

Behavior:

- If a Campaign has branch eligibility records, the receipt branch must match.
- If a Campaign has no branch records, it is treated as global across branches.

### `receipt_promotion_usages`

Stores selected Campaign snapshots.

Important fields:

- `receipt_school_id`
- `promotion_campaign_id`
- `promotion_name_snapshot`
- `discount_amount_snapshot`
- `expected_discount_amount_snapshot`
- `actual_discount_amount_snapshot`
- `expected_net_amount_snapshot`
- `actual_net_amount_snapshot`
- `match_status`
- `mismatch_reason`
- `reward_snapshot_json`
- `rule_snapshot_json`
- `selected_by_user_id`
- `selected_at`

Behavior:

- Existing usages are replaced when staff touches Campaign selection on edit.
- Snapshots preserve historical calculation details.
- Expected and actual values support audit when staff manually keys different
  discount values.

## Receipt Context

The backend builds a normalized `receipt_context` before preview/save.

Important fields:

- `promotion_id`
- `branch_id`
- `subtotal_amount`
- `entrance_fee_amount`
- `has_referral`
- `is_new_student`
- `evaluation_date`
- `receipt_items`

Each normalized `receipt_item` includes:

- `student_id`
- `subject_id`
- `subject_group`
- `course_type`
- `month_count`
- `net_amount`
- `discount_amount`

Month count:

- If explicit `month_count` is sent, use it.
- Otherwise derive from `week * qty / 4`.
- `week = 4` means one month.

## Eligibility Flow

1. Staff edits receipt fields in create/edit modal.
2. Frontend sends receipt context to `POST /receipt_school/promotions/preview`.
3. Backend normalizes receipt items and computes derived context:
   - subject groups
   - month counts
   - referral status
   - new-student-per-subject status
   - entrance fee amount
4. `PromotionEligibilityService` loads active Campaigns.
5. Campaigns are filtered by:
   - selected `promotion_id` or global Campaign
   - active status
   - `starts_at` / `ends_at`
   - branch eligibility
   - all configured rules
6. `PromotionRewardCalculator` calculates reward results.
7. Frontend renders eligible Campaign options.
8. Staff selects one or more Campaigns.
9. Store/update validates the selected Campaign again using latest request data.
10. `ReceiptPromotionUsageService` replaces the usage snapshot after receipt
    save.

## Reward Calculation

### Default Fixed Discount

If `FIXED_DISCOUNT` has no special calculation scope, the configured
`discount_amount` applies once at receipt level.

### Percent Discount

`PERCENT_DISCOUNT` calculates from `subtotal_amount`.

### Capped Discount

`CAPPED_DISCOUNT` uses configured discount amount or calculation result but caps
the final cash amount by `max_discount_amount`.

### Free Entrance Fee

`FREE_ENTRANCE_FEE` waives actual `entrance_fee_amount` once.

If reward config has `min_qualifying_months`, it qualifies only when the highest
course month on the receipt meets or exceeds that value.

### Gift

`GIFT` has no money impact.

If reward config has `min_qualifying_months`, it qualifies only when the highest
course month on the receipt meets or exceeds that value.

### Per Matching Course

`PER_MATCHING_COURSE` multiplies a fixed unit discount by the number of matching
course lines.

Relevant config:

```json
{
  "calculation_scope": "PER_MATCHING_COURSE",
  "matching_course_type": 1,
  "matching_months": [6],
  "unit_discount_amount": 600
}
```

### Per Course Month Tier

`PER_COURSE_MONTH_TIER` calculates each course line by its own month count.

Relevant config:

```json
{
  "calculation_scope": "PER_COURSE_MONTH_TIER",
  "matching_course_type": 1,
  "month_discount_tiers": {
    "2": 150,
    "4": 350,
    "6": 600,
    "12": 2000
  }
}
```

Behavior:

- Only `course_type = 1` lines qualify.
- A line whose month count is missing from the tier map receives 0.
- Total cash discount is the sum of all per-line discounts.
- Calculation detail stores `line_discounts` for audit and receipt display.

## Package Campaign Behavior

Package Campaigns are linked to package Promotions through
`promotion_campaigns.promotion_id`.

### Math/English Package

Current tier table:

- 2 months: 150 per course line
- 4 months: 350 per course line
- 6 months: 600 per course line
- 12 months: 2,000 per course line

### Play Math/English Sparks Package

Current tier table:

- 2 months: 100 per course line
- 4 months: 300 per course line
- 6 months: 600 per course line

### Entrance Fee and Gift Gating

Package Campaigns may include:

- Cash discount via `PER_COURSE_MONTH_TIER`
- Free entrance fee when highest course month is at least 2
- Gift when highest course month is at least 4

Entrance fee and gift do not multiply by course count.

## Receipt Display

When a saved receipt has Campaign usage:

- Receipt view/print uses `receipt_promotion_usages` snapshot.
- Per-line discount rows are rendered from `line_discounts` when available.
- Legacy snapshots that cannot be attributed per line fall back to
  `campaign_discount_remaining` at the bottom.
- Campaign display does not recalculate from current Campaign configuration for
  old receipts.

## Expected vs Actual Audit

The system calculates:

- expected discount from Campaign rewards
- actual discount from staff-keyed receipt lines
- expected net amount
- actual net amount

`match_status` values:

- `MATCHED`
- `MISMATCHED`
- `NOT_APPLIED`

Mismatch behavior:

- Save is not blocked when the selected Campaign is valid.
- Warning message is returned and/or displayed when expected and actual discount
  differ.
- Snapshot keeps mismatch details for audit.

## UI Behavior

### Campaign Admin

Campaign management is separate from Promotion management.

Campaign admin supports:

- Creating/editing Campaigns.
- Linking Campaign to a Promotion or leaving it global.
- Configuring campaign type.
- Configuring rule templates.
- Configuring one or more rewards.
- Enabling/disabling Campaigns.

### Receipt Create/Edit Modal

Campaign panel behavior:

- Shows after receipt header/line item context is available.
- Calculates automatically from the selected Promotion and receipt lines.
- Shows current context flags, including `นักเรียนใหม่`.
- `นักเรียนใหม่` is read-only and calculated by the backend.
- Shows eligible Campaign options.
- Allows staff to select multiple Campaigns.
- Prevents selecting multiple Campaigns that share the same reward category,
  such as two cash-discount Campaigns.
- Clears selected Campaign if the receipt context changes and the Campaign is no
  longer eligible.
- On edit, existing Campaign usage is kept unless staff touches/replaces
  Campaign selection.

Campaign panel text should make clear that staff may choose multiple eligible
Campaigns.

## API Contract

### Preview Eligible Campaigns

Route:

- `POST /receipt_school/promotions/preview`

Request accepts:

- `promotion_id`
- `branch_id`
- `source`
- `name_parent`
- `referent_parent_id`
- `receipt_school_id`
- `submission_date`
- `entrance_fee_amount`
- `receipt_items`

Response includes:

- `eligible_promotions`
- `promotion_context.has_referral`
- `promotion_context.is_new_student`

Each eligible Campaign includes:

- `promotion_campaign_id`
- `name`
- `promotion_type`
- `discount_amount`
- `reward_summary`
- `reward_results`
- `rule_results`
- `reward_categories`

## Save and Update Policy

During store/update:

- Validate standard receipt fields.
- Validate subject promotion mixing.
- Validate period overlap.
- Rebuild receipt context from request.
- Revalidate selected Campaign.
- Reject unknown, inactive, date-invalid, branch-invalid, or rule-invalid
  Campaigns.
- Reject selected Campaign combinations that share the same reward category.
- Apply special policy for `NEW_STUDENT_GIFT`: the one-month new-student gift
  Campaign requires entrance fee amount 535.
- Save receipt and line items.
- Replace `receipt_promotion_usages` when Campaign selection is provided.
- Return warning messages for mismatch without blocking save.

## Out of Scope

The current Campaign system does not include:

- Full automatic package pricing engine.
- Auto-selecting the best Campaign without staff confirmation.
- Applying Campaigns outside `receipt_school`.
- Deleting Promotion records as normal workflow.
- Recomputing historical receipt Campaign amounts after Campaign setup changes.
- Combining Campaigns that share the same reward category on one receipt.

## Current Test Coverage

Primary unit coverage:

- `tests/Unit/PromotionEligibilityServiceTest.php`
- `tests/Unit/PromotionRewardCalculatorTest.php`
- `tests/Unit/PromotionUsageComparisonServiceTest.php`

Important tested behaviors:

- linked Promotion Campaign eligibility
- global Campaign eligibility
- new-student preset eligibility for existing and new students
- exact/min month rules
- subject combination rules
- referral rules
- package tier per-course/month-tier discount
- free entrance fee and gift summaries
- expected vs actual comparison

## Manual Regression Checklist

- Create receipt with package Promotion and mixed course months; verify per-line
  cash discount follows each line's own month.
- Create receipt for a student who is new to English but old to Math; verify
  Campaigns using the legacy new-student preset can still appear when the other
  rules pass.
- Create receipt with old subject history; verify `นักเรียนใหม่` is unchecked.
- Change Promotion; verify Campaign list recalculates.
- Change receipt item month count; verify Campaign list and reward amount
  recalculates.
- Edit saved receipt without touching Campaign; verify existing usage remains.
- Edit saved receipt and choose a new Campaign; verify usage snapshot is
  replaced.
- Key a manual discount that differs from Campaign expected amount; verify save
  succeeds with mismatch warning and audit snapshot.
- View/print receipt; verify per-line Campaign discount appears and legacy
  remaining discount still renders.
