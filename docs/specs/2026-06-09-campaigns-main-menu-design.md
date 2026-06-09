# Campaigns Main Menu Design

## Objective

Separate Campaigns from Promotion as its own main menu and make one Campaign capable of carrying all rewards required by the current school promotion rules.

## Scope

- Promotion remains the master list used by receipts as the selected promotion context.
- Campaigns becomes a standalone admin page and sidebar menu item.
- Campaigns can be linked to a Promotion or remain global.
- A selected receipt can use only one calculated Campaign.
- One Campaign can contain multiple rewards, such as free entrance fee and school bag gift.

## Campaign Rules

Campaigns must support these rule types:

- Subject combination, such as Math and English.
- Referral source.
- New student.
- Minimum course count.
- Minimum month count.
- Exact month count.

Branch eligibility and date range already exist in the backend model and service. The Campaigns UI should expose them when this change touches the Campaign form.

## Campaign Rewards

Campaigns must support these reward types:

- Fixed discount.
- Percent discount.
- Capped discount.
- Free entrance fee.
- Gift.

Multiple rewards may belong to one Campaign. This is required for "4 months or more: free entrance fee + school bag" while still enforcing one Campaign per receipt.

## Receipt Flow

The receipt preview should show eligible Campaigns based on the selected Promotion, receipt subjects, course counts, month counts, referral source, new student flag, branch, and submission date.

The receipt create/edit flow should validate the selected Campaign again on the server before saving.

## Testing

- PHP syntax checks for changed PHP files.
- Verify route names are valid after splitting Campaigns into a standalone page.
- Verify receipt Campaign selection only allows one selected Campaign.
