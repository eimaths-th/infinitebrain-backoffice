# School Receipt Promotion Engine Design

Date: 2026-06-09

## Goal

Design a configurable promotion calculation system for `receipt_school` while keeping the existing `promotions` master data as required receipt-level tracking.

The new promotion engine calculates eligible discounts and rewards for school receipts. It must not replace the existing `promotions` table behavior, because the current `promotions` table is already used as a required receipt header selection.

## Confirmed Requirements

- Use a hybrid design.
- Package prices are treated as standard package pricing, not promotion rules.
- The existing `receipt_school.promotion_id` remains required when creating a receipt.
- The new promotion calculation applies only to `receipt_school`.
- Staff manually selects one eligible calculated promotion from the list returned by the system.
- The system shows all eligible calculated promotions with their discount amounts.
- Calculated promotions cannot be combined with each other.
- The selected calculated promotion must be snapshotted on the receipt so historical receipts do not change when promotion setup changes later.
- Existing subject promotion validation remains unchanged:
  - `subjects.promotion = yes/no` still controls whether a receipt is treated as a promotion receipt.
  - Promotion subjects and non-promotion subjects cannot be mixed in the same school receipt.

## Existing Promotion Tracking

### Current `promotions`

The existing `promotions` table stores the receipt header promotion name selected by staff.

It remains responsible for:

- Receipt-level promotion tracking.
- Active/inactive selection list.
- Showing promotion names in receipt list and edit flow.

It is not responsible for:

- Discount calculation.
- Reward calculation.
- Package pricing.
- Printed receipt pricing logic.

### Current `receipt_school.promotion_id`

The current implementation requires `promotion_id` on receipt creation. This remains required.

This field should continue to reference `promotions.id`.

## Recommended Approach

Create a separate promotion calculation engine.

Keep:

- `promotions`
- `receipt_school.promotion_id`

Add:

- `promotion_campaigns`
- `promotion_rules`
- `promotion_rewards`
- `promotion_branch_eligibilities`
- `receipt_promotion_usages`

This avoids mixing two different meanings into one `promotions` table:

- Existing promotion tracking.
- New discount calculation.

## Data Model

### `promotion_campaigns`

Stores calculated promotion campaigns.

Columns:

- `id`
- `name`
- `promotion_type`
- `is_active`
- `starts_at`
- `ends_at`
- `priority`
- `created_at`
- `updated_at`

Notes:

- `name` is snapshotted when a receipt uses the campaign.
- `promotion_type` should be a stable symbolic value, such as `TWO_SUBJECTS`, `REFERRAL`, or `NEW_STUDENT_MULTI_COURSE`.
- `starts_at` and `ends_at` allow campaign date control.
- `priority` is useful for display ordering only. Staff still chooses the final promotion manually.

### `promotion_rules`

Stores campaign eligibility rules.

Columns:

- `id`
- `promotion_campaign_id`
- `rule_type`
- `rule_config_json`
- `created_at`
- `updated_at`

Example `rule_type` values:

- `SUBJECT_COMBINATION`
- `REFERRAL`
- `NEW_STUDENT_MIN_COURSES`
- `MIN_MONTHS`
- `BRANCH_ELIGIBLE`

### `promotion_rewards`

Stores campaign rewards.

Columns:

- `id`
- `promotion_campaign_id`
- `reward_type`
- `discount_amount`
- `discount_percent`
- `max_discount_amount`
- `reward_config_json`
- `created_at`
- `updated_at`

Example `reward_type` values:

- `FIXED_DISCOUNT`
- `PERCENT_DISCOUNT`
- `CAPPED_DISCOUNT`
- `FREE_ENTRANCE_FEE`
- `GIFT`

### `promotion_branch_eligibilities`

Stores branch eligibility for calculated promotion campaigns.

Columns:

- `id`
- `promotion_campaign_id`
- `branch_id`
- `created_at`
- `updated_at`

Behavior:

- If campaign branch records exist, the receipt branch must match one of them.
- If no campaign branch records exist, the campaign can be treated as global only if the business confirms that global campaigns are allowed.

### `receipt_promotion_usages`

Stores the calculated promotion selected by staff for a receipt.

Columns:

- `id`
- `receipt_school_id`
- `promotion_campaign_id`
- `promotion_name_snapshot`
- `discount_amount_snapshot`
- `reward_snapshot_json`
- `rule_snapshot_json`
- `selected_by_user_id`
- `selected_at`
- `created_at`
- `updated_at`

Behavior:

- One `receipt_school` can have zero or one `receipt_promotion_usages` record.
- `promotion_name_snapshot` protects historical display when the campaign name changes later.
- `discount_amount_snapshot` protects financial history.
- `reward_snapshot_json` stores non-cash rewards, such as school bag or free entrance fee.
- `rule_snapshot_json` stores the rule result used at the time of selection.

## Initial Campaign Mapping

### Math and English two-subject discount

Business rule:

- Register Math and English together.
- Discount 200 baht.

Suggested config:

- `promotion_type`: `TWO_SUBJECTS`
- `rule_type`: `SUBJECT_COMBINATION`
- `rule_config_json`: subject groups include `M` and `E`
- `reward_type`: `FIXED_DISCOUNT`
- `discount_amount`: `200`

### Referral discount

Business rule:

- Friend referral discount.
- Discount 10 percent.

Suggested config:

- `promotion_type`: `REFERRAL`
- `rule_type`: `REFERRAL`
- `reward_type`: `PERCENT_DISCOUNT`
- `discount_percent`: `10`

Required later:

- Confirm who receives the discount.
- Confirm whether referral evidence is stored on the receipt, parent, or student.

### New student multi-course discount

Business rule:

- New student.
- Register 3 or more courses.
- Discount up to 900 baht.

Suggested config:

- `promotion_type`: `NEW_STUDENT_MULTI_COURSE`
- `rule_type`: `NEW_STUDENT_MIN_COURSES`
- `rule_config_json`: minimum courses `3`
- `reward_type`: `CAPPED_DISCOUNT`
- `max_discount_amount`: `900`

Required later:

- Confirm how to detect a new student from existing data.
- Confirm the formula before applying the 900 baht cap.

Package price records should store:

- Month count.
- Entrance fee amount.
- Tuition amount.
- Package discount amount.
- Net package amount.
- Included rewards, if these rewards are standard package benefits.

Important:

- Discounts already embedded in the package table are treated as standard package pricing.
- Calculated promotions are applied after package price selection.

## Calculation Flow

1. Staff selects receipt parent, student, subject, level, package, and period.
2. System calculates package or line item pricing.
3. System evaluates active promotion campaigns for the selected receipt context.
4. System returns all eligible calculated promotions.
5. Staff selects one eligible calculated promotion.
6. System subtracts only the selected calculated promotion discount from the payable amount.
7. System stores:
   - `receipt_school.promotion_id` for existing required promotion tracking.
   - `receipt_promotion_usages` for the selected calculated promotion.
   - Snapshot data for name, reward, rules, and discount.

## Service Design

Recommended services:

### `PromotionEligibilityService`

Responsibilities:

- Load active campaigns.
- Check campaign date range.
- Check branch eligibility.
- Evaluate rules against the receipt draft.
- Return eligible campaigns with calculated reward previews.

### `PromotionRewardCalculator`

Responsibilities:

- Calculate fixed discount rewards.
- Calculate percent discount rewards.
- Apply max discount caps.
- Return normalized reward output.

### `ReceiptPromotionUsageService`

Responsibilities:

- Validate that the selected campaign is still eligible.
- Create or replace a receipt promotion usage record.
- Store snapshots.

## Controller Impact

### `ReceiptSchoolController`

Expected changes:

- Add an endpoint to preview eligible calculated promotions for a receipt draft.
- Validate selected calculated promotion on receipt store.
- Save `receipt_promotion_usages` after `receipt_school` is created.
- Validate selected calculated promotion on receipt update.
- Replace or clear `receipt_promotion_usages` during receipt update.

Existing behavior to keep:

- `promotion_id` remains required on store.
- Current subject promotion validation remains unchanged.
- Current period overlap validation remains unchanged.
- Current receipt running number behavior remains unchanged.

## UI Impact

### Create receipt modal

Add a calculated promotion section below receipt line items or near summary totals.

Behavior:

- Staff clicks preview or the system refreshes after pricing changes.
- Eligible calculated promotions are shown with:
  - Promotion name.
  - Discount amount.
  - Reward description.
  - Reason or condition summary.
- Staff selects one calculated promotion.
- Staff may leave calculated promotion empty only if the business allows no calculated promotion on a receipt.

### Edit receipt modal

Behavior:

- Show the selected calculated promotion snapshot.
- Allow staff to re-preview and select a different eligible calculated promotion.
- Replacing a calculated promotion updates `receipt_promotion_usages` and keeps audit logs.

## API Shape

### Preview eligible calculated promotions

Suggested route:

- `POST /receipt_school/promotions/preview`

Request shape:

```json
{
  "promotion_id": 1,
  "parent_id": 10,
  "student_ids": [100, 101],
  "branch_id": 2,
  "receipt_items": [
    {
      "student_id": 100,
      "subject_id": 1,
      "course_type": 1,
      "month_count": 2,
      "net_amount": 5650
    }
  ]
}
```

Response shape:

```json
{
  "eligible_promotions": [
    {
      "promotion_campaign_id": 1,
      "name": "Math and English two-subject discount",
      "discount_amount": 200,
      "reward_summary": "Discount 200 baht",
      "rule_summary": "Math and English selected"
    }
  ]
}
```

## Validation Rules

- Reject selected calculated promotion if campaign is inactive.
- Reject selected calculated promotion if the campaign date is invalid.
- Reject selected calculated promotion if branch is not eligible.
- Reject selected calculated promotion if receipt items no longer match campaign rules.
- Reject selected calculated promotion if another calculated promotion usage already exists and the request does not explicitly replace it.
- Store money values as integer baht or decimal columns with fixed precision. Do not use floating point.

## Audit and History

Receipt promotion usage should be included in receipt create/update logs.

Snapshot fields must be included in audit records:

- Promotion campaign id.
- Promotion name snapshot.
- Discount amount snapshot.
- Reward snapshot.
- Rule snapshot.
- Selected user id.
- Selected timestamp.

## Impact

Directly touched areas:

- New migrations for promotion engine tables.
- New models for promotion campaign, rules, rewards, branch eligibility, and usage.
- New promotion services.
- `ReceiptSchoolController` preview, store, and update flow.
- `resources/views/receipt_school/receipt.blade.php` promotion preview and selection UI.
- Receipt create/update audit payload.

Indirectly affected areas:

- Receipt totals.
- Receipt edit behavior.
- Any report that later needs net amount after calculated promotion.

Out of scope for first implementation:

- Printed receipt output changes.
- Finance reports.
- Source reports.
- Referral master data.
- Automatic best-promotion selection.
- Applying more than one calculated promotion to the same receipt.

## Testing Plan

- Migration runs successfully.
- Existing `promotions` management still works.
- Receipt create still requires `promotion_id`.
- Receipt create works without a calculated promotion if business allows it.
- Eligible calculated promotions are previewed correctly.
- Ineligible calculated promotions are not returned.
- Staff can select one eligible calculated promotion.
- Selected calculated promotion is saved in `receipt_promotion_usages`.
- Receipt update can keep the existing calculated promotion usage.
- Receipt update can replace calculated promotion usage.
- Receipt update rejects a selected calculated promotion if the edited receipt no longer meets the rules.
- Historical receipt promotion snapshots do not change when campaign setup changes later.
- Existing subject promotion mix validation still works.
- Existing period overlap validation still works.

## Open Decisions Before Implementation

- Confirm whether a receipt may have no calculated promotion usage.
- Confirm how branch is determined for `receipt_school`.
- Confirm how to identify a new student from current data.
- Confirm referral evidence storage and who receives the referral discount.
- Confirm whether package pricing should be implemented in the same phase or after the calculated promotion engine.
