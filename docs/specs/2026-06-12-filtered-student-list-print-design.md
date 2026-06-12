# Filtered Student List Print Design

Date: 2026-06-12

## Objective

เพิ่มความสามารถให้หน้า `report-reciept-student` พิมพ์รายชื่อนักเรียนจากการ์ดวิชาเดียวได้ โดยรายชื่อที่พิมพ์ต้องตรงกับ filter ที่ผู้ใช้เลือกอยู่ในการ์ดนั้น ณ ตอนกดปุ่มพิมพ์

## Confirmed Scope

- เพิ่มปุ่มพิมพ์รายชื่อภายในการ์ดวิชาใน section ข้อมูลประชากรนักเรียน
- พิมพ์เฉพาะวิชาหรือการ์ดที่ผู้ใช้กดปุ่ม
- ใช้ filter ปัจจุบันของการ์ดนั้นเท่านั้น
- ใช้หน้า print เดิม `reportRecieptStdPrint` และ route เดิม `report-reciept-student-print`
- เพิ่ม section print ใหม่สำหรับรายชื่อนักเรียนที่ถูกกรอง
- ไม่เปลี่ยน behavior ของ section print เดิม เช่น graph, timeline, summary, demographics, monthly table, paper table

## Out of Scope

- พิมพ์หลายวิชาพร้อมกันจาก filter คนละชุด
- Export Excel หรือ PDF โดยตรง
- เปลี่ยน query หลักของรายงานรายปีหรือรายเดือน
- เปลี่ยนชื่อ route, file, หรือ URL เดิมที่ใช้คำสะกด `reciept` / `recipt`
- ปรับ redesign หน้า dashboard หรือการ์ดข้อมูลประชากรทั้งหมด

## User Flow

1. ผู้ใช้เปิดหน้า `report-reciept-student`
2. ผู้ใช้เลือกปี และเลือกเดือนใน section ข้อมูลประชากรนักเรียนถ้าต้องการ
3. ผู้ใช้เปิด detail ของการ์ดวิชาที่ต้องการ
4. ผู้ใช้เลือก filter ในการ์ดนั้น เช่น เพศ, เด็กใหม่หรือเด็กเก่า, Promotion, Source
5. ผู้ใช้กดปุ่ม `พิมพ์รายชื่อ` ในการ์ดวิชานั้น
6. ระบบเปิดหน้า print ใน tab ใหม่
7. หน้า print แสดงเฉพาะรายชื่อนักเรียนของวิชานั้นที่ตรงกับ filter ปัจจุบัน
8. ผู้ใช้กดปุ่ม print บนหน้า print เดิม

## Print Data Contract

หน้า report จะส่ง query parameter เพิ่มไปยังหน้า print:

- `sections=filtered-student-list`
- `year`
- `orientation`
- `demo_month`
- `demo_group`
- `demo_sex`
- `demo_enrollment`
- `demo_promotion_filter`
- `demo_source_filter`
- `demo_promotion_name`
- `demo_source_name`

ค่าที่ไม่มีการเลือก filter ให้ส่งเป็นค่าว่างหรือค่า default ที่สื่อว่าไม่กรอง เช่น `all`

## Filter Rules

หน้า print ต้องใช้ logic เดียวกับหน้า report:

- `demo_group` จำกัดรายชื่อให้อยู่ในวิชาที่กดพิมพ์
- `demo_month=all` หมายถึงภาพรวมรายปี
- `demo_month=<1-12>` หมายถึงเฉพาะนักเรียนที่มี period ครอบคลุมเดือนนั้นในปีที่เลือก
- `demo_sex` รองรับ `all`, `male`, `female`, `unknown`
- `demo_enrollment` รองรับ `new`, `old`, `unknown` หรือค่าว่างสำหรับไม่กรอง
- `demo_promotion_filter` รองรับ `all`, `with-promotion`, `without-promotion`
- `demo_source_filter` รองรับ `all`, `with-source`, `without-source`
- `demo_promotion_name` กรองเฉพาะ promotion name ที่เลือก
- `demo_source_name` กรองเฉพาะ source name ที่เลือก

## Print Layout

เพิ่ม section ใหม่ใน `resources/views/students/print_report_recipt_std.blade.php`

หัวรายงานควรแสดง:

- ชื่อรายงาน: รายชื่อนักเรียนตามตัวกรอง
- ปี
- เดือนหรือภาพรวมรายปี
- วิชา
- คำอธิบาย filter ที่ใช้อยู่
- วันที่พิมพ์

ส่วนสรุป Promotion / Source ควรแสดงก่อนตาราง:

- จำนวนนักเรียนที่มี Promotion
- จำนวนนักเรียนที่ไม่มี Promotion
- รายชื่อ Promotion พร้อมจำนวน
- จำนวนนักเรียนที่มี Source
- จำนวนนักเรียนที่ไม่มี Source
- รายชื่อ Source พร้อมจำนวน

ตารางควรมีคอลัมน์:

- ลำดับ
- Code
- ชื่อเล่น
- สถานะรายวิชา
- เพศ
- Promotion
- Source

หากไม่พบข้อมูล ให้แสดงข้อความว่าไม่พบนักเรียนตามเงื่อนไขที่เลือก

## Implementation Boundaries

แก้เฉพาะไฟล์หลัก:

- `resources/views/students/report_recipt_std.blade.php`
- `resources/views/students/print_report_recipt_std.blade.php`

แก้ controller เฉพาะเมื่อจำเป็นต้อง validate หรือ normalize query parameter ในฝั่ง server เท่านั้น

## Error Handling

- ถ้า `demo_group` ว่างหรือไม่พบ group ให้หน้า print แสดง empty state แทน error
- ถ้า `demo_month` ไม่ใช่ `all` และไม่ใช่เลข 1-12 ให้ถือเป็น `all`
- ถ้า filter parameter ไม่ตรงชุดค่าที่รองรับ ให้ถือเป็นค่า default ไม่กรอง
- ชื่อ Promotion และ Source ต้องแสดงแบบ escaped ผ่าน Blade default output

## Security Considerations

- ห้ามนำ query parameter ไปต่อ raw SQL
- ใช้ข้อมูลจาก collection ที่ query ด้วยระบบเดิมเท่านั้น
- แสดงค่า query parameter ด้วย Blade escaped output
- ไม่เพิ่มข้อมูลส่วนบุคคลที่ไม่เคยแสดงอยู่ในหน้ารายงานเดิม

## Performance Considerations

- ใช้ `$reportYear_schoolPrice` ที่หน้า print มีอยู่แล้ว
- ไม่เพิ่ม query ต่อ student รายคนใน loop
- คงการ group และ filter ใน PHP collection ตาม pattern เดิมของ view
- หลีกเลี่ยงการโหลด dataset ใหม่ถ้า controller ส่งข้อมูลเดิมเพียงพอ

## Manual Test Checklist

- กดพิมพ์รายชื่อจากการ์ด M โดยไม่เลือก filter แล้วเห็นเฉพาะรายชื่อ M
- เลือกเดือนแล้วกดพิมพ์ รายชื่อใน print ต้องตรงกับเดือนนั้น
- filter เพศชายแล้วพิมพ์ ต้องไม่มีนักเรียนหญิงในตาราง
- filter เด็กใหม่แล้วพิมพ์ ต้องแสดงเฉพาะสถานะรายวิชาใหม่
- filter Promotion แบบมีหรือไม่มี แล้วพิมพ์ต้องตรงกับหน้าจอ
- เลือก Promotion name เฉพาะชื่อแล้วพิมพ์ต้องตรงกับหน้าจอ
- filter Source แบบมีหรือไม่มี แล้วพิมพ์ต้องตรงกับหน้าจอ
- เลือก Source name เฉพาะชื่อแล้วพิมพ์ต้องตรงกับหน้าจอ
- กรณี filter แล้วไม่พบข้อมูล ต้องเห็น empty state
- modal พิมพ์รายงานเดิมยังเปิดและพิมพ์ section เดิมได้เหมือนเดิม
