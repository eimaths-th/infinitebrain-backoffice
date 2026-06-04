# School Receipt Promotion Design

Date: 2026-05-25

## Goal

Add promotion master data and allow a school receipt to reference one promotion for internal backoffice tracking. The promotion does not calculate discounts, change prices, or appear on printed receipt documents.

## Confirmed Requirements

- A receipt may reference zero or one promotion.
- Promotion selection is stored at the `receipt_school` header level, not per `school_price` line item.
- Promotion selection is optional.
- Existing logic using `subjects.promotion = yes/no` remains the source of whether a receipt is treated as a promotion receipt.
- Existing rule remains: promotion subjects and non-promotion subjects cannot be mixed in the same school receipt.
- Promotion names are shown only in the school receipt list and create/edit receipt modal.
- Promotion names are not shown on receipt view/print pages and are not added to reports in this scope.
- Promotion management supports create, edit, enable, and disable.
- Promotion records are not deleted, to preserve receipt history.
- The promotion management menu is placed near the Subject/Course management area.

## Recommended Approach

Create a dedicated `promotions` master table and add `promotion_id` to `receipt_school`.

This keeps promotion names normalized, supports active/inactive filtering, and avoids storing free text on each receipt. It also keeps the feature independent from pricing and discount logic.

## Data Model

### `promotions`

- `id`
- `name`
- `is_active`
- `created_at`
- `updated_at`

Validation:

- `name` is required.
- `name` is unique.
- `is_active` is stored as a boolean-like value.

### `receipt_school`

Add nullable `promotion_id`.

Behavior:

- Null means no promotion name was selected.
- Existing receipts remain valid after migration because the field is nullable.
- If a promotion is later disabled, existing receipts still display its saved promotion name in the list/edit flow.

## Application Components

### Model

Add a `Promotion` model for the `promotions` table.

Update `Receipt_School`:

- Add `promotion_id` to `$fillable`.
- Add a `promotion()` relationship using `belongsTo(Promotion::class, 'promotion_id')`.

### Controller

Add a `PromotionController` for master data.

Responsibilities:

- List promotions.
- Store new promotion.
- Return promotion data for edit.
- Update promotion name and active status.
- Toggle active/inactive status.
- Do not expose delete behavior.

Update `ReceiptSchoolController`:

- Load active promotions for the create modal.
- Load all promotions needed for edit display, including inactive promotions already selected by older receipts.
- Save nullable `promotion_id` on create and update.
- Include promotion name in the receipt DataTable response.
- Do not change current subject promotion validation logic.

## UI Design

### Promotion Master Page

Create a page under the Subject/Course management area.

Fields:

- Promotion name
- Status: active/inactive

Actions:

- Add promotion
- Edit promotion
- Enable/disable promotion

There is no delete action.

### School Receipt Create/Edit Modal

Add a `Promotion` dropdown in the receipt header section.

Behavior:

- Default option is empty.
- Dropdown lists active promotions.
- Edit mode can show the selected promotion even if it is inactive.
- The field is optional.
- The field does not affect price, discount, receipt total, or receipt type.

### School Receipt List

Add a `Promotion Name` column next to the existing `Promotion` yes/no column.

Behavior:

- Show the promotion name if selected.
- Show `-` when no promotion is selected.

## Data Flow

Create receipt:

1. User selects subjects and optionally selects a promotion.
2. Existing subject promotion validation runs.
3. Receipt header is saved with nullable `promotion_id`.
4. Receipt line items are saved unchanged.

Edit receipt:

1. Existing receipt loads selected promotion.
2. User can keep, clear, or change promotion.
3. Existing subject promotion validation runs.
4. Receipt header updates `promotion_id`.
5. Receipt line item update flow remains unchanged.

Promotion management:

1. Active promotions appear in create receipt.
2. Inactive promotions are hidden from new selections.
3. Inactive promotions remain visible where already referenced by a receipt.

## Error Handling

- Promotion name validation errors are returned to the UI and shown in the page alert/toast pattern already used in this project.
- Receipt create/update accepts empty `promotion_id`.
- Receipt create/update rejects unknown `promotion_id`.
- If an inactive promotion is submitted on create, the request is rejected.
- If an inactive promotion already belongs to an existing receipt, edit can keep that value.

## Impact

Directly touched areas:

- Routes in `routes/web.php`.
- New promotion controller/model/view.
- `receipt_school` migration and model relationship.
- `ReceiptSchoolController` create/update/list/edit data handling.
- `resources/views/receipt_school/receipt.blade.php`.
- Sidebar/menu area near Subject/Course management.

Out of scope:

- Discount calculation.
- Printed receipt output.
- Receipt view output.
- Source report and finance reports.
- Changing `subjects.promotion` behavior.
- Deleting promotion records.

## Testing Plan

- Migration runs successfully.
- Promotion can be added.
- Promotion can be edited.
- Promotion can be disabled and enabled.
- Disabled promotion does not appear in create receipt dropdown.
- Receipt can be created without promotion.
- Promotion receipt can be created with a selected promotion.
- Existing validation still blocks mixing promotion and non-promotion subjects.
- Receipt list shows selected promotion name.
- Edit receipt loads and saves promotion selection.
- View/print receipt output does not show promotion name.
