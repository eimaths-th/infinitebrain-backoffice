# Linked Promotion Campaigns Auto Calculation Design

## Objective

Connect the existing required `Promotion` selection on `receipt_school` with calculated `Campaigns`, so staff can select a Promotion first, then choose from eligible Campaigns calculated automatically by the system.

## Confirmed Requirements

- The existing `promotion_id` field remains required on school receipts.
- The calculated promotion engine applies only to `receipt_school`.
- Staff selects the final Campaign from the system-calculated eligible list.
- Staff should not need to click a manual Preview button.
- Promotion and Campaign should remain separate concepts.
- One Promotion can have many Campaigns.
- A Campaign can optionally be global by leaving it unlinked from a Promotion.

## Domain Model

### Promotion

Promotion is the main receipt-level promotion category selected by staff. It remains the required tracking field already used by the receipt flow.

### Campaign

Campaign is a calculated rule-and-reward configuration. It determines whether a discount applies and how much discount should be calculated.

### Relationship

Add nullable `promotion_id` to `promotion_campaigns`.

- `promotion_campaigns.promotion_id = promotions.id`: Campaign appears only when that Promotion is selected.
- `promotion_campaigns.promotion_id = null`: Campaign is global and can appear with any selected Promotion.

## Receipt Flow

1. Staff selects a Promotion in the receipt modal.
2. The frontend sends receipt context to the existing calculated promotion preview endpoint automatically.
3. The context includes:
   - selected `promotion_id`
   - selected branch
   - receipt items
   - subject group
   - month count
   - course type
   - referral source status
   - new student status
   - subtotal amount
4. The backend filters Campaigns by:
   - active status
   - date range
   - branch eligibility
   - linked Promotion or global Campaign
   - rule eligibility
5. The frontend renders only eligible Campaigns.
6. Staff selects one eligible Campaign.
7. The selected Campaign is submitted with the receipt.
8. The backend validates the Campaign again before saving.
9. The backend writes or replaces `receipt_promotion_usages`.

## Admin Flow

The Promotion admin page keeps the existing Promotion tracking list and the calculated Campaign list.

Campaign create/edit gains a `Linked Promotion` dropdown:

- Empty value means global Campaign.
- Selected Promotion means the Campaign only appears when that Promotion is selected in `receipt_school`.

Campaign rules remain template-based and dynamic:

- subject combination
- referral
- new student
- minimum courses
- minimum months

Campaign rewards remain:

- fixed discount
- percent discount
- capped discount

## Example Configuration

### Package Math / English

Linked Campaigns:

- 12 months: minimum months 12, fixed discount 2000
- 6 months: minimum months 6, fixed discount 600
- 4 months: minimum months 4, fixed discount 350
- 2 months: minimum months 2, fixed discount 150

### Package Play Math / English Sparks

Linked Campaigns:

- 6 months: minimum months 6, fixed discount 600
- 4 months: minimum months 4, fixed discount 300
- 2 months: minimum months 2, fixed discount 100

### Referral

Can be configured as a global Campaign or linked to a specific Promotion, depending on business usage.

## Data Integrity

- `promotion_campaigns.promotion_id` must be nullable for global Campaign support.
- The foreign key should use `nullOnDelete` so deleting a Promotion does not delete Campaign history.
- Receipt save/update must re-check Campaign eligibility server-side.
- If staff submits an ineligible Campaign, the API returns validation errors and does not save usage.

## Backward Compatibility

- Existing Campaigns remain valid because `promotion_id` defaults to null.
- Existing receipts remain valid because `promotion_id` remains required and unchanged.
- The existing preview endpoint can remain, but the UI calls it automatically instead of using a manual Preview button.
- Existing `receipt_promotion_usages` records do not need migration.

## UI Behavior

- The manual Preview button should be removed or hidden from the normal receipt workflow.
- Auto calculation should run when any relevant field changes:
  - Promotion
  - source/referral
  - new student checkbox
  - receipt item list
  - subject
  - month count
  - amount
- While calculating, show a loading state in the Campaign panel.
- If no eligible Campaign exists, show a clear empty state.
- If the selected Campaign becomes ineligible after staff changes receipt details, clear the selected Campaign.

## Testing

Unit tests:

- Campaign linked to selected Promotion is eligible.
- Campaign linked to another Promotion is not eligible.
- Global Campaign is eligible with any selected Promotion.
- Missing `promotion_id` only returns global Campaigns.
- Server-side selected Campaign validation rejects a Campaign linked to another Promotion.

Manual tests:

- Create receipt with Package Math / English and verify only linked Campaigns appear.
- Change Promotion and verify Campaign list refreshes automatically.
- Change month count and verify eligible Campaign list recalculates automatically.
- Save receipt and verify `receipt_promotion_usages` records the selected Campaign and discount amount.
- Edit receipt without touching Campaign and verify existing usage is not unexpectedly changed.

## Out of Scope

- Combining Promotion and Campaign into one table.
- Auto-selecting the best discount without staff confirmation.
- Rebuilding package pricing or entrance fee calculation beyond Campaign discount eligibility.
- Applying the new Campaign engine outside `receipt_school`.

## Approval Gate

Development can begin after this spec is approved.
