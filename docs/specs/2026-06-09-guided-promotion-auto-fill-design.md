# Guided Promotion Auto-Fill Design

Date: 2026-06-09

## Goal

เพิ่ม workflow แบบ Guided Auto-Fill ให้หน้า `receipt_school` เพื่อช่วย Staff ที่ยัง key รายการเองทั้งหมด โดยระบบจะช่วยคำนวณ Campaign ที่เข้าเงื่อนไข, ให้ Staff กด Apply, เติมส่วนลดลงรายการใบเสร็จ, แสดงยอดที่ควรเป็น, และเตือนเมื่อยอดที่ Staff key ไม่ตรงกับ Campaign

ระบบต้องยังอนุญาตให้ Staff บันทึกได้แม้ยอดไม่ตรง Campaign เพราะ business ต้องรองรับเคสพิเศษหน้างาน

## Confirmed Requirements

- ใช้แนวทาง Guided Auto-Fill
- Staff ยัง key ข้อมูลและรายการใบเสร็จเองตาม flow เดิม
- Staff เป็นผู้เลือก Campaign เองจากรายการที่ระบบคำนวณว่าเข้าเงื่อนไข
- เพิ่มปุ่ม Apply สำหรับ Campaign ที่เลือก
- เมื่อ Apply แล้ว ระบบช่วยเติมส่วนลดลง line item จริง
- ถ้า Staff แก้ยอดเองหลัง Apply ระบบต้องเตือนว่ายอดไม่ตรง Campaign
- การเตือนต้องไม่ block save
- Backend ต้อง validate Campaign ซ้ำก่อน save ว่า Campaign ยังเข้าเงื่อนไข
- Backend ต้องเก็บ expected value และ actual value เพื่อ audit
- รอบแรกยังไม่ทำ full package pricing engine ใหม่
- รอบแรกยังไม่ lock ช่อง Discount, Price, Qty, Entrance Fee

## Current Context

ระบบมีส่วนที่รองรับอยู่แล้ว:

- `promotion_campaigns`, `promotion_rules`, `promotion_rewards`
- `PromotionEligibilityService`
- `PromotionRewardCalculator`
- `ReceiptPromotionUsageService`
- endpoint preview Campaign ของ `receipt_school`
- UI Campaign panel ใน create/edit receipt modal
- การเลือก Campaign ได้ 1 Campaign ต่อใบเสร็จ
- การบันทึก `receipt_promotion_usages` snapshot

ข้อจำกัดปัจจุบัน:

- Campaign ที่เลือกยังเป็น usage snapshot เป็นหลัก
- ส่วนลดจาก Campaign ยังไม่ได้ถูก apply ลงช่อง Discount ของรายการใบเสร็จอย่างเป็น workflow ชัดเจน
- ไม่มี expected vs actual summary ก่อน save
- ไม่มี warning เมื่อ Staff แก้ยอดเองจนไม่ตรง Campaign
- ยังไม่มี audit field ที่แยก expected discount และ actual discount ชัดเจน

## Recommended Approach

ทำเป็น 3 ชั้น:

1. Frontend Guided Apply
2. Backend Recalculation and Warning
3. Audit Snapshot

แนวทางนี้ลดความผิดพลาดของ Staff ได้ทันที โดยไม่เปลี่ยนระบบให้แข็งเกินไป และยังต่อยอดเป็น strict validation ภายหลังได้

## UX Flow

### Create Receipt

1. Staff กรอก Promotion, Source, นักเรียนใหม่, Parent, Student และรายการเรียนตามปกติ
2. ระบบ preview Campaign ที่เข้าเงื่อนไขอัตโนมัติ
3. Staff เลือก Campaign
4. Staff กด `Apply Campaign`
5. ระบบคำนวณ reward result จาก Campaign ที่เลือก
6. ระบบเติมส่วนลดลงช่อง Discount ของ line item ที่เหมาะสม
7. ระบบแสดง Campaign Summary
8. ถ้า Staff แก้รายการหลัง Apply ระบบคำนวณ expected ใหม่และแสดงสถานะ
9. เมื่อกด Save backend validate Campaign ซ้ำและบันทึก warning snapshot ถ้ายอดไม่ตรง

### Edit Receipt

1. เปิด modal แล้วแสดง Campaign เดิมจาก snapshot
2. ถ้า Staff ไม่แตะ Campaign เดิม ให้เก็บ usage เดิมไว้
3. ถ้า Staff เลือก Campaign ใหม่หรือกด Apply ใหม่ ให้ระบบคำนวณ expected ใหม่
4. ถ้า Staff แก้ยอดจนไม่ตรง ให้เตือนแต่ยัง save ได้
5. เมื่อ save ให้ backend เก็บ actual value ล่าสุดเทียบกับ expected value

## UI Components

### Campaign Option

แสดง Campaign ที่เข้าเงื่อนไขอยู่แล้ว แต่เพิ่ม action:

- radio เลือก Campaign
- ปุ่ม `Apply Campaign`
- badge สถานะ:
  - `ยังไม่ได้ Apply`
  - `Applied`
  - `ยอดตรงกับ Campaign`
  - `ยอดไม่ตรงกับ Campaign`

### Campaign Summary

แสดงใต้ Campaign panel:

- Campaign name
- ยอดก่อนลด
- ส่วนลดที่ Campaign คาดหวัง
- ค่าลงทะเบียนที่ฟรี ถ้ามี
- ของแถม ถ้ามี
- ยอดสุทธิที่ควรชำระ
- ส่วนลดที่ Staff key จริง
- ยอดสุทธิที่ Staff key จริง
- สถานะความตรงกัน

### Warning Message

ถ้ายอดไม่ตรง Campaign:

ข้อความแนะนำ:

> ยอดที่ key ไม่ตรงกับ Campaign ที่ Apply ระบบจะบันทึกต่อได้ แต่จะเก็บสถานะเป็นยอดไม่ตรง Campaign เพื่อใช้ตรวจสอบย้อนหลัง

Warning นี้ควรแสดงทั้งในหน้า create/edit ก่อน save และส่งกลับจาก backend ตอน save response ถ้า backend ตรวจพบอีกครั้ง

## Discount Apply Rules

### Fixed, Percent, and Capped Discount

ระบบควร apply ส่วนลดลง line item แบบ conservative:

- ถ้ามี line item course type = course ให้ลงส่วนลดที่ course line แรกที่เข้าเงื่อนไข
- ถ้า Campaign ผูกกับ subject combination เช่น Math + English ให้ลงส่วนลดที่ course line แรกในกลุ่มที่เกี่ยวข้อง
- ถ้าไม่สามารถจับ line ที่เหมาะสมได้ ให้ไม่ auto-fill และแสดง warning ให้ Staff key เอง

### Free Entrance Fee

ระบบควรใช้วิธีที่ไม่ทำลายข้อมูลเดิม:

- เก็บ entrance fee เดิมไว้เป็น original value ใน calculation context
- แสดง expected entrance fee waiver ใน summary
- รอบแรกไม่ set entrance fee display เป็น 0 อัตโนมัติ
- รอบแรกให้แสดง waiver ใน Campaign Summary และเก็บ expected entrance fee discount ใน snapshot
- ถ้า Staff ต้องการให้ยอด actual ตรง Campaign ให้ Staff ปรับ field ที่เกี่ยวข้องเองตาม flow เดิม

### Gift

Gift ไม่มีผลกับยอดเงิน:

- แสดงใน summary
- เก็บใน reward snapshot
- ไม่แก้ line item total

## Expected vs Actual Calculation

### Expected

คำนวณจาก Campaign reward:

- `expected_discount_amount`
- `expected_entrance_fee_discount_amount`
- `expected_gift_summary`
- `expected_net_amount`

### Actual

คำนวณจากค่าที่ Staff key จริงในรายการใบเสร็จ:

- `actual_discount_amount`
- `actual_entrance_fee_amount`
- `actual_net_amount`

### Match Status

เพิ่มสถานะสำหรับ snapshot:

- `MATCHED`
- `MISMATCHED`
- `NOT_APPLIED`

`MISMATCHED` ไม่ block save

## Backend Behavior

### Preview Endpoint

ยังใช้ endpoint เดิม:

- `POST /receipt_school/promotions/preview`

ควรเพิ่มข้อมูลใน response สำหรับ UI apply:

- reward categories
- expected discount amount
- reward results
- suggested apply target ถ้าระบบระบุได้

### Store and Update

ตอน save backend ต้อง:

1. สร้าง receipt context จาก request ล่าสุด
2. validate Campaign ที่เลือกซ้ำด้วย `PromotionEligibilityService`
3. calculate expected reward ด้วย `PromotionRewardCalculator`
4. calculate actual discount จาก request line items
5. compare expected vs actual
6. save receipt ตาม flow เดิม
7. save `receipt_promotion_usages` พร้อม expected/actual/warning snapshot
8. return warning message ถ้า mismatch แต่ไม่ return validation error

## Data Model Impact

### Chosen Approach: Extend `receipt_promotion_usages`

เพิ่ม columns:

- `expected_discount_amount_snapshot`
- `actual_discount_amount_snapshot`
- `expected_net_amount_snapshot`
- `actual_net_amount_snapshot`
- `match_status`
- `mismatch_reason`

เหตุผล:

- query/report ง่าย
- เหมาะกับ audit และ filter รายการ mismatch
- เหมาะกับ policy ที่ให้ Staff override ได้แต่ต้องตรวจสอบย้อนหลังได้
- ลดการ parse JSON สำหรับรายงานในอนาคต

## Validation and Error Handling

กรณีที่ต้อง block save:

- Campaign id ไม่มีอยู่จริง
- Campaign inactive
- Campaign ไม่เข้าเงื่อนไขกับ receipt context ล่าสุด
- Campaign ถูก link กับ Promotion อื่นที่ไม่ได้เลือก
- request structure ผิดจนคำนวณไม่ได้

กรณีที่เตือนแต่ยัง save:

- Staff แก้ discount ไม่ตรง expected
- Staff แก้ price/qty หลัง Apply ทำให้ net amount ไม่ตรง
- Staff ไม่ apply Campaign แต่เลือก Campaign ไว้
- Campaign มี gift แต่ไม่มีผลกับยอดเงิน

## Audit and Reporting

ควรเก็บข้อมูลต่อไปนี้:

- receipt school id
- selected campaign id
- selected campaign name snapshot
- expected discount
- actual discount
- expected net amount
- actual net amount
- reward snapshot
- rule snapshot
- match status
- mismatch reason
- selected by user id
- selected at

ภายหลังควรเพิ่ม filter/report:

- ใบเสร็จที่ Campaign matched
- ใบเสร็จที่ Campaign mismatched
- ใบเสร็จที่ Staff override discount

Report นี้เป็น follow-up ไม่ใช่ scope รอบแรก

## Testing Plan

Unit tests:

- fixed discount expected value
- percent discount expected value
- capped discount expected value
- free entrance fee expected value
- gift reward expected value
- compare expected vs actual returns matched
- compare expected vs actual returns mismatched without blocking

Feature/manual tests:

- create receipt, select eligible Campaign, apply discount, save matched
- create receipt, apply Campaign, edit Discount manually, save mismatched warning
- edit receipt without touching Campaign keeps old usage
- edit receipt, re-apply Campaign, save new expected/actual snapshot
- selected Campaign becomes ineligible after Qty change and backend blocks save
- gift Campaign shows reward summary but does not change total
- free entrance fee Campaign shows expected waiver

## Out of Scope

- Full package pricing engine
- Auto-select best Campaign
- Locking discount fields
- Blocking save when expected and actual mismatch
- Referral master data
- Auto-detect new student from historical receipts
- Changing printed receipt layout
- Finance report changes

## Implementation Defaults

ใช้ default ต่อไปนี้ในรอบ implementation แรก:

- Fixed, percent, and capped discount apply ลง course line แรกที่เข้าเงื่อนไข
- ถ้า Campaign เป็น subject combination ให้ apply ลง course line แรกของ subject group ที่อยู่ใน rule
- Free entrance fee ไม่แก้ค่า entrance fee อัตโนมัติ แต่แสดง expected waiver และเก็บ snapshot
- Gift แสดงใน summary และ snapshot เท่านั้น
- เพิ่ม columns ใหม่ใน `receipt_promotion_usages` สำหรับ expected/actual/match status
- Warning แสดงใน Campaign Summary panel ก่อน save
- ถ้า backend พบ mismatch ตอน save ให้ response กลับมาพร้อม warning message และ frontend แสดง alert ใน modal
- Backend block เฉพาะ Campaign ที่ไม่ valid หรือ request ที่คำนวณไม่ได้ ไม่ block เพราะยอด expected/actual ไม่ตรงกัน

## Approval Gate

Spec นี้ออกแบบเฉพาะ Guided Auto-Fill phase หลังจาก promotion engine และ linked campaign flow มีอยู่แล้ว

Development เริ่มได้หลังผู้ใช้ตรวจและยืนยัน spec นี้
