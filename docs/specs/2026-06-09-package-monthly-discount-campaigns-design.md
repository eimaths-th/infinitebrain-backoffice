# Package Monthly Discount Campaigns Design

## Objective

Make the calculated promotion engine produce the correct per-month prepay discount
for each package on `receipt_school`. Today the discount cannot be distinguished
between the two packages, so a 4-month receipt gives the same discount for
`Math/English` and `Play Math/Sparks` even though the tables differ.

## Problem / Root Cause

The two packages share the same subject groups, so a campaign keyed on subject
group matches both packages:

| Subject     | group |
| ----------- | ----- |
| Math, Play Math, Private Math | M |
| English Pre-A, English 5-8, English Sparks | E |

`Play Math` is group `M` and `English Sparks` is group `E`, identical to the
`Math/English` package. Subject group therefore **cannot** distinguish the two
packages. The field that distinguishes them is `promotion_id`:

- Promotion `Promotion Package for Math/English` (2,900/month)
- Promotion `Promotion Package for Play Math` (3,500/month)

In addition, the per-month discount tiers do not exist in the database yet (the
seeder only contains the two-subject / referral / new-student campaigns).

A secondary bug: on the **edit/update** save path the request only sends
`rrweek` / `rrqty` arrays. `NormalizePromotionReceiptItems` does not read them, so
`month_count` is `null` and `EXACT_MONTHS` re-validation fails — the selected
campaign is rejected on save. The **create/store** path already sends
`receipt[]` containing `week` + `qty`, so it computes `month_count` correctly.

## Confirmed Requirements

- Discount tiers are configured **per package** via `promotion_id` (not subject group).
- Tiers match an **exact** month count (no stacking of lower tiers).
- The discount applies to **anyone who buys that package** — no new-student rule.
- `month_count = (week × qty) ÷ 4` (4 weeks = 1 month), decimals kept.

## Provisional Assumption (to confirm later)

The package tables are priced **per subject**, but the reward is modeled as a
**single flat discount per receipt**. Example: Math + English, both 6 months →
discount **600 once**, not 600 × 2. The user approved proceeding with this for now
("ทำแบบนั้นไปก่อน"). Revisit if per-subject multiplication is required.

## Design

### Part 1 — Data: per-package tier campaigns (PromotionCampaignSeeder)

Add tier campaigns linked to each package promotion. Promotions are looked up by
**name** (IDs are environment-specific). Each campaign:

- `promotion_id` = the package promotion
- `promotion_type` = `CUSTOM`
- rule `EXACT_MONTHS` with `{ "months": [N] }`
- reward `FIXED_DISCOUNT` with the tier amount
- `priority` = 50 (ranks above the global campaigns)
- idempotent via `updateOrCreate` keyed on the campaign `name`

| Promotion (by name)                 | exact months | fixed discount |
| ----------------------------------- | ------------ | -------------- |
| Promotion Package for Math/English  | 2            | 150            |
| Promotion Package for Math/English  | 4            | 350            |
| Promotion Package for Math/English  | 6            | 600            |
| Promotion Package for Math/English  | 12           | 2000           |
| Promotion Package for Play Math     | 2            | 100            |
| Promotion Package for Play Math     | 4            | 300            |
| Promotion Package for Play Math     | 6            | 600            |

Because each tier is linked to its package and uses `EXACT_MONTHS`, selecting a
package promotion shows only that package's tiers, and a given month count maps to
exactly one tier — no overlap, no stacking.

### Part 2 — Backend: month_count on all paths (ReceiptSchoolController)

1. `NormalizePromotionReceiptItems` rr* fallback branch: also read `rrweek` and
   `rrqty` and pass `week` / `qty` into each normalized item (edit path).
2. `NormalizePromotionReceiptItem`: derive `month_count` via a new helper
   `ResolvePromotionMonthCount()`:
   - if an explicit `month_count` is present (preview payload), use it;
   - else if `week` and `qty` are present, return `(week × qty) ÷ 4`;
   - else `null`.

No change is needed to `PromotionEligibilityService` or
`PromotionRewardCalculator` — `promotion_id`, `EXACT_MONTHS`, and `FIXED_DISCOUNT`
are already supported.

## Data Flow After Fix

1. Staff selects a package Promotion (#1 or #2) and a month count on the receipt.
2. Preview/store/update all resolve `month_count` consistently
   (preview: explicit; store/update: `week × qty ÷ 4`).
3. Eligibility filters campaigns by `promotion_id` + `EXACT_MONTHS`, returning the
   single matching tier for that package and month count.
4. The fixed discount for that tier is applied; save re-validates and passes.

## Testing

Unit tests (extend `PromotionEligibilityServiceTest`):

- Promotion #1, 4 months → eligible tier discount **350**.
- Promotion #2, 4 months → eligible tier discount **300** (package distinction).
- Promotion #1, 6 months → exactly **600**, and the 2- and 4-month tiers are NOT
  eligible (no stacking).
- A receipt with no `month_count` but `week=4`, `qty=6` resolves to 6 months.

Manual tests:

- Create receipt: Math/English package, 6 months → discount 600.
- Create receipt: Play Math package, 4 months → discount 300.
- Edit the saved receipt without changing the campaign → save succeeds
  (no "ไม่เข้าเงื่อนไข" rejection).

## Out of Scope

- Per-subject multiplication of the discount (flat per receipt for now).
- Free entrance fee / student-bag rewards (handled by existing campaigns).
- Frontend preview payload changes (it already sends a usable `month_count`).
- Changing eligibility comparison rounding behavior.
