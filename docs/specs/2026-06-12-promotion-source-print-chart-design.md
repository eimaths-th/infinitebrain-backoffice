# Promotion Source Print Chart Design

Date: 2026-06-12

## Objective

เพิ่ม section กราฟ Promotion / Source ใน modal `พิมพ์รายงาน` ของหน้า `report-reciept-student` เพื่อให้ผู้ใช้พิมพ์ภาพรวมช่องทาง Promotion และ Source ของนักเรียนทุกวิชา โดยภาพรวมต้อง **อิงตาม filter ที่ผู้ใช้เลือก (ปี และเดือน)** และนำเสนอในรูปแบบที่อ่านแล้วเข้าใจทันที (โดนัทสัดส่วน + อันดับ)

## Revision History

- 2026-06-12 (v1) — ออกแบบเริ่มต้น: summary card + กราฟแท่งแนวนอน Top Promotion/Source ของ "ปีล่าสุด"
- 2026-06-12 (v2) — ปรับให้ section อิงตาม filter จริง (ปี + เดือน `demo_month`) แทน "ปีล่าสุด" ที่ fix จากข้อมูล
- 2026-06-12 (v3) — เปลี่ยน UI เป็น **โดนัทสัดส่วน + อันดับแบบแท่งแนวนอน (HTML)** เพื่อให้เข้าใจง่ายขึ้น และกำหนดให้ `No Promotion` ไม่ถูกนับเป็น "มี Promotion"

เอกสารนี้สะท้อนสถานะปัจจุบัน (v3)

## Confirmed Scope

- เพิ่ม checkbox `กราฟ Promotion / Source` ใน modal `พิมพ์รายงาน` ส่ง section `promotion-source-chart`
- ใช้ route เดิม `reportRecieptStdPrint` (`/report-reciept-student-print`)
- section อิงตาม filter:
  - **ปี** จาก query `year`
  - **เดือน** จาก query `demo_month` (`all` = ทั้งปี, `1`–`12` = เฉพาะเดือนนั้น) — ค่าเดียวกับที่ section demographics ใช้
- ข้อมูลมาจาก `$channel_school_prices` (จาก `GetPromotionSourceChannelSchoolPrices()` ใน controller) โดยอิง `rec_school->submission_date` เป็นตัวกำหนดเดือน/ปี
- นำเสนอ Promotion และ Source ในหน้าเดียวกัน 2 panel
- แต่ละ panel ประกอบด้วย **โดนัทสรุปสัดส่วน (Chart.js)** + **อันดับแบบแท่งแนวนอนที่เป็น HTML/CSS** (ไม่ใช่ Chart.js)
- ปิด animation ของ Chart.js เพื่อให้เหมาะกับหน้า print

## Out of Scope

- ไม่ทำกราฟแยกตามวิชา
- ไม่เพิ่ม filter เฉพาะ Promotion หรือ Source ใน modal (ใช้ filter ปี/เดือนร่วมกับ section อื่น)
- ไม่ทำกราฟแบบ interactive นอกเหนือจาก tooltip พื้นฐานของ Chart.js
- ไม่ทำ export Excel หรือ PDF โดยตรง
- ไม่เปลี่ยน route เดิม
- ไม่เปลี่ยน logic ของ section print เดิม เช่น all-month chart, timeline, summary table, demographics, paper table

## User Flow

1. ผู้ใช้เปิดหน้า `report-reciept-student`
2. ผู้ใช้เลือกปีที่ต้องการ และ (ถ้าต้องการ) เลือกเดือนใน section ข้อมูลประชากร
3. ผู้ใช้กดปุ่ม `พิมพ์รายงาน`
4. ผู้ใช้ติ๊ก checkbox `กราฟ Promotion / Source`
5. ผู้ใช้เลือกแนวกระดาษ แล้วกด `พิมพ์`
6. ระบบเปิดหน้า print เดิมพร้อม section `promotion-source-chart` และส่ง `year` + `demo_month` ไปด้วย
7. หน้า print แสดงภาพรวม Promotion / Source ของปี/เดือนที่เลือก

## Filter Behavior

- `$latest_channel_year` = `(int) $year` (ไม่ใช่ "ปีล่าสุด" ที่หาจากข้อมูลอีกต่อไป)
- `$channel_demo_month` parse จาก `demo_month`; ถ้าไม่ใช่ `all` และไม่อยู่ในช่วง 1–12 ให้ถือเป็น `all`
- เมื่อเลือกเดือนเจาะจง: สร้าง month bucket เฉพาะเดือนนั้น และข้าม receipt ที่ `submission_date` ไม่ตรงเดือน
- ป้ายแสดงผล `$channel_period_label`:
  - `all` → `ปี <year>`
  - เดือนเจาะจง → `เดือน <ชื่อเดือนไทย> <year>`
  - ใช้ใน badge, หัวข้อ section, note ของแต่ละ panel, empty state และ title โดนัทใน JS

## Data Rules

นับนักเรียนแบบ unique ต่อ student ภายในปี/เดือนที่เลือก (อิง `submission_date`)

Promotion:

- **`No Promotion` (ชื่อที่มีคำว่า "no promotion" แบบ case-insensitive) ไม่ถือเป็นการใช้โปรโมชั่น** จึงไม่นับเป็น `มี Promotion` และไม่ปรากฏในอันดับ
- นักเรียนที่มีอย่างน้อยหนึ่ง promotion จริง (ไม่ใช่ค่าว่างและไม่ใช่ `No Promotion`) นับเป็น `มี Promotion`
- ที่เหลือ (รวมคนที่มีแต่ `No Promotion` หรือไม่มี promotion เลย) นับเป็น `ไม่มี Promotion`
- `มี Promotion` + `ไม่มี Promotion` = จำนวนนักเรียน unique ในช่วงที่เลือก
- อันดับ Promotion นับ **จำนวนนักเรียน unique ต่อชื่อโปร** (dedupe ต่อ student ต่อชื่อ)
- ถ้านักเรียนหนึ่งคนใช้หลายโปร จะถูกนับในหลายแถวของอันดับ → **ผลรวมของอันดับอาจมากกว่าจำนวนคน `มี Promotion`** (นี่ไม่ใช่ bug; เป็นคนละหน่วยนับ และมีหมายเหตุกำกับใต้หัวข้ออันดับ)

Source: ใช้กติกาเดียวกับ Promotion แต่ไม่มีการ exclude ค่าพิเศษ

- นักเรียนที่มีอย่างน้อยหนึ่ง `rec_school->source` (ไม่ว่าง) นับเป็น `มี Source`
- ที่เหลือนับเป็น `ไม่มี Source`
- อันดับ Source นับจำนวนนักเรียน unique ต่อชื่อ source และผลรวมอาจมากกว่าจำนวนคน `มี Source` ได้เช่นกัน

## Print Layout

อยู่ใน `resources/views/students/print_report_recipt_std.blade.php` ภายใต้ `@if (in_array('promotion-source-chart', $sections))`

หัวรายงานแสดง:

- ชื่อรายงาน: กราฟ Promotion / Source
- badge ช่วงเวลา = `$channel_period_label`
- วันที่พิมพ์

แต่ละ panel (Promotion, Source) แสดง:

- **โดนัทสรุป**: วงโดนัท Chart.js (cutout 70%) + ตัวเลข % ตรงกลางวง (เช่น `33% มี Promotion`) + legend บอกจำนวนจริง มี/ไม่มี + บรรทัด `จากนักเรียนทั้งหมด N คน`
- **อันดับ**: หัวข้อ + หมายเหตุตัวเล็ก + รายการแท่งแนวนอน (HTML) เรียงมาก→น้อย แต่ละแถวมีเลขอันดับ, ชื่อ, แท่งความยาวตามสัดส่วนของค่าสูงสุด, และตัวเลขจำนวน
- กรณีไม่มีรายการ ให้ใช้ `@forelse ... @empty` แสดง empty state

Canvas ids (โดนัทเท่านั้น):

- `promotionDonutChart`
- `sourceDonutChart`

Chart.js behavior (โดนัท):

- ใช้ `type: 'doughnut'`, `cutout: '70%'`
- ใช้ `maintainAspectRatio: false` คุมขนาดด้วย container `.channel-donut-canvas` (118x118)
- ใช้ `animation: false`
- ซ่อน legend (ใช้ legend HTML ข้าง ๆ แทน)
- tooltip แสดงจำนวนคนและเปอร์เซ็นต์ (กัน division by zero)

อันดับ (HTML/CSS): ความกว้างแท่ง = `count / max * 100%`; รองรับ modifier `.is-muted` (ตัวเอียงสีเทา) เผื่อกรณีต้องการ down-weight รายการ (ปัจจุบัน `No Promotion` ถูกตัดออกตั้งแต่ data layer แล้ว จึงไม่ถึงชั้นนี้)

## Implementation Boundaries

แก้เฉพาะ:

- `resources/views/students/report_recipt_std.blade.php` — checkbox + ส่ง `year` & `demo_month`
- `resources/views/students/print_report_recipt_std.blade.php` — section, CSS, data aggregation, donut JS
- `app/Http/Controllers/StdlogController.php` — `GetPromotionSourceChannelSchoolPrices()` และส่ง `channel_school_prices` เข้า view print

## Error Handling

- ถ้าไม่มีนักเรียนในช่วงที่เลือก (`$channel_student_total === 0`) แสดง empty state แทนทั้ง section
- ถ้า promotion/source เป็นค่าว่าง ไม่ต้องนับเป็นชื่อในอันดับ
- ชื่อ Promotion/Source แสดงด้วย Blade escaped output
- กันหารด้วยศูนย์ทั้งใน % โดนัท, ความกว้างแท่งอันดับ และ tooltip
- ถ้าไม่พบ canvas ของโดนัท ไม่ต้องสร้าง Chart.js instance

## Security Considerations

- ไม่ใช้ query parameter ต่อ raw SQL
- ไม่เพิ่มข้อมูลส่วนบุคคลใหม่ใน section นี้
- ไม่แสดงข้อมูล internal debug หรือ technical detail บนหน้า print

## Performance Considerations

- ใช้ collection จาก controller (`$channel_school_prices`) ที่ eager-load `std`, `rec_school.promotion` แล้ว
- parse และ aggregate ใน loop เดียวต่อ school price
- ไม่ query student, receipt, promotion, source ซ้ำรายคน
- ปิด animation ของ Chart.js
- ส่งค่าเข้า JavaScript เฉพาะตัวเลขโดนัทที่คำนวณไว้แล้ว (อันดับเป็น HTML ฝั่ง server ไม่ผ่าน JS)

## Manual Test Checklist

- modal `พิมพ์รายงาน` มี checkbox `กราฟ Promotion / Source`
- เมื่อเลือก checkbox แล้ว URL print มี `promotion-source-chart` และมี `year` + `demo_month`
- เลือกปีอื่น → section แสดงข้อมูลตามปีนั้น (ไม่ fix เป็นปีล่าสุด)
- เลือกเดือน (เช่น 6) → badge/หัวข้อเป็น `เดือน มิถุนายน <ปี>` และข้อมูลนับเฉพาะเดือนนั้น
- โดนัท Promotion: % ตรงกลาง = `มี / ทั้งหมด`, และ `มี + ไม่มี` = จำนวนนักเรียน unique ในช่วงที่เลือก
- โดนัท Source: เงื่อนไขเดียวกับ Promotion
- นักเรียนที่มีแต่ `No Promotion` ต้องถูกนับเป็น `ไม่มี Promotion` และ `No Promotion` ต้องไม่อยู่ในอันดับ
- อันดับเรียงจากมาก→น้อย, แท่งยาวตามสัดส่วน, มีเลขอันดับและจำนวน
- มีหมายเหตุใต้หัวข้ออันดับอธิบายว่า 1 คนใช้ได้หลายรายการ ผลรวมจึงมากกว่าจำนวนคนได้
- กรณีไม่มี Promotion/Source ในช่วงที่เลือก แสดง empty state ของฝั่งนั้น
- กรณีไม่มีนักเรียนเลยในช่วงที่เลือก แสดง empty state ทั้ง section
- section print เดิม (all-month, timeline, summary table, demographics, paper) ยังทำงานเหมือนเดิม
