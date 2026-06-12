# Promotion Source Print Chart Design

Date: 2026-06-12

## Objective

เพิ่ม section กราฟ Promotion / Source ใน modal `พิมพ์รายงาน` ของหน้า `report-reciept-student` เพื่อให้ผู้ใช้สามารถพิมพ์ภาพรวมช่องทาง Promotion และ Source ของนักเรียนทุกวิชาในปีที่เลือกได้

## Confirmed Scope

- เพิ่ม checkbox ใหม่ใน modal `พิมพ์รายงาน`
- checkbox ใช้ label `กราฟ Promotion / Source`
- เมื่อเลือก checkbox ให้ส่ง section ใหม่ `promotion-source-chart` ไปยังหน้า print เดิม
- หน้า print ใช้ route เดิม `reportRecieptStdPrint`
- กราฟต้องเป็นภาพรวมทุกวิชาในปีที่เลือก
- ใช้ข้อมูลจาก `$reportYear_schoolPrice` ที่หน้า print มีอยู่แล้ว
- แสดงข้อมูล Promotion และ Source ในหน้าเดียวกัน
- ใช้กราฟแท่งแนวนอนด้วย Chart.js canvas
- ปิด animation ของ Chart.js เพื่อให้เหมาะกับหน้า print

## Out of Scope

- ไม่ทำกราฟแยกตามวิชา
- ไม่เพิ่ม filter เฉพาะ Promotion หรือ Source ใน modal
- ไม่ทำกราฟแบบ interactive นอกเหนือจาก tooltip พื้นฐานของ Chart.js
- ไม่ทำ export Excel หรือ PDF โดยตรง
- ไม่เปลี่ยน route เดิม
- ไม่เปลี่ยน logic ของ section print เดิม เช่น all-month chart, timeline, summary table, demographics, paper table

## User Flow

1. ผู้ใช้เปิดหน้า `report-reciept-student`
2. ผู้ใช้เลือกปีที่ต้องการ
3. ผู้ใช้กดปุ่ม `พิมพ์รายงาน`
4. ผู้ใช้ติ๊ก checkbox `กราฟ Promotion / Source`
5. ผู้ใช้เลือกแนวกระดาษ
6. ผู้ใช้กด `พิมพ์`
7. ระบบเปิดหน้า print เดิมพร้อม section `promotion-source-chart`
8. หน้า print แสดงกราฟ Promotion / Source รวมทุกวิชาในปีที่เลือก

## Data Rules

นับนักเรียนแบบ unique ต่อ student จาก `$reportYear_schoolPrice`

Promotion:

- นักเรียนที่มีอย่างน้อยหนึ่ง `rec_school->promotion->name` นับเป็น `มี Promotion`
- นักเรียนที่ไม่มี Promotion ใด ๆ นับเป็น `ไม่มี Promotion`
- รายชื่อ Promotion นับจำนวนนักเรียนที่เกี่ยวข้องกับ Promotion นั้น
- ถ้านักเรียนหนึ่งคนมีหลาย Promotion ในปีเดียวกัน ให้นับได้หลายชื่อใน breakdown แต่ยังนับเป็นนักเรียน `มี Promotion` เพียงหนึ่งคน

Source:

- นักเรียนที่มีอย่างน้อยหนึ่ง `rec_school->source` นับเป็น `มี Source`
- นักเรียนที่ไม่มี Source ใด ๆ นับเป็น `ไม่มี Source`
- รายชื่อ Source นับจำนวนนักเรียนที่เกี่ยวข้องกับ Source นั้น
- ถ้านักเรียนหนึ่งคนมีหลาย Source ในปีเดียวกัน ให้นับได้หลายชื่อใน breakdown แต่ยังนับเป็นนักเรียน `มี Source` เพียงหนึ่งคน

## Print Layout

เพิ่ม section ใหม่ใน `resources/views/students/print_report_recipt_std.blade.php`

หัวรายงานควรแสดง:

- ชื่อรายงาน: กราฟ Promotion / Source
- ปี
- วันที่พิมพ์

เนื้อหา section ควรแสดง:

- Summary card สำหรับ Promotion: ทั้งหมด, มี Promotion, ไม่มี Promotion
- กราฟแท่งแนวนอน Chart.js สำหรับ Top Promotion
- Summary card สำหรับ Source: ทั้งหมด, มี Source, ไม่มี Source
- กราฟแท่งแนวนอน Chart.js สำหรับ Top Source

Canvas ids:

- `promotionSourcePromotionChart`
- `promotionSourceSourceChart`

Chart.js behavior:

- ใช้ `type: 'bar'`
- ใช้ `indexAxis: 'y'` เพื่อแสดงแท่งแนวนอน
- ใช้ `maintainAspectRatio: false` เพื่อควบคุมความสูงด้วย container สำหรับ print
- ใช้ `animation: false`
- ซ่อน legend เพราะแต่ละกราฟมี dataset เดียว
- tooltip แสดงจำนวนคนและเปอร์เซ็นต์เทียบกับจำนวนนักเรียน unique ทั้งปี
- แกน X ต้องเริ่มที่ 0 และ tick เป็นจำนวนเต็ม

ถ้าไม่มี Promotion หรือ Source ในปีนั้น ให้แสดงข้อความ empty state ของส่วนนั้นแทนกราฟ

## Implementation Boundaries

แก้เฉพาะไฟล์หลัก:

- `resources/views/students/report_recipt_std.blade.php`
- `resources/views/students/print_report_recipt_std.blade.php`

ไม่แก้ controller เว้นแต่พบว่าหน้า print ไม่มีข้อมูลที่จำเป็นจริง ๆ

## Error Handling

- ถ้าไม่มีข้อมูลใน `$reportYear_schoolPrice` ให้แสดง empty state แทน error
- ถ้า Promotion หรือ Source เป็นค่าว่าง ให้ไม่นับเป็นชื่อใน breakdown
- แสดงชื่อ Promotion และ Source ด้วย Blade escaped output
- หลีกเลี่ยงการหารด้วยศูนย์เมื่อคำนวณเปอร์เซ็นต์ใน tooltip
- ถ้า labels ของ Promotion หรือ Source ว่าง ไม่ต้องสร้าง Chart.js instance สำหรับ canvas นั้น

## Security Considerations

- ไม่ใช้ query parameter ต่อ raw SQL
- ไม่เพิ่มข้อมูลส่วนบุคคลใหม่ใน section นี้
- ไม่แสดงข้อมูล internal debug หรือ technical detail บนหน้า print

## Performance Considerations

- ใช้ collection ที่มีอยู่แล้วในหน้า print
- รวมข้อมูลใน loop เดียวต่อ `$reportYear_schoolPrice`
- ไม่ query student, receipt, promotion, หรือ source ซ้ำรายคน
- ปิด animation ของ Chart.js เพื่อให้เหมาะกับหน้า print
- ส่ง labels และ values เข้า JavaScript ผ่าน `json_encode` จาก collection ที่คำนวณไว้แล้ว

## Manual Test Checklist

- modal `พิมพ์รายงาน` มี checkbox `กราฟ Promotion / Source`
- เมื่อเลือก checkbox แล้ว URL print มี `promotion-source-chart`
- หน้า print แสดง section กราฟ Promotion / Source
- ตัวเลข `มี Promotion` และ `ไม่มี Promotion` รวมเท่าจำนวนนักเรียน unique ในปีนั้น
- ตัวเลข `มี Source` และ `ไม่มี Source` รวมเท่าจำนวนนักเรียน unique ในปีนั้น
- canvas `promotionSourcePromotionChart` ถูก render เป็นกราฟแท่งแนวนอน
- canvas `promotionSourceSourceChart` ถูก render เป็นกราฟแท่งแนวนอน
- กราฟ Promotion แสดงชื่อ Promotion พร้อมจำนวน และ tooltip แสดงเปอร์เซ็นต์
- กราฟ Source แสดงชื่อ Source พร้อมจำนวน และ tooltip แสดงเปอร์เซ็นต์
- กรณีไม่มีข้อมูล Promotion หรือ Source ต้องแสดง empty state
- section print เดิมยังทำงานเหมือนเดิม
