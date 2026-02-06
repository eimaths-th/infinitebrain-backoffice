# Mobile App Requirements: Teacher Selection

เพื่อให้รองรับฟีเจอร์การระบุชื่อครูผู้สอนในประวัติการเรียนทางฝั่ง Mobile App จะต้องมีการปรับปรุงดังนี้:

## 1. ดึงข้อมูลรายชื่อครู (Get Teachers List)
เรียกใช้ API ใหม่เพื่อนำรายชื่อครูมาแสดงให้เลือก
- **Endpoint**: `GET /api/get-teachers`
- **Response Format**:
  ```json
  [
    {
      "id": 1,
      "name": "Teacher Name",
      "code": "T001",
      "nickname": "Nick"
    },
    ...
  ]
  ```

## 2. หน้าบันทึกประวัติการเรียน (Save History Form)
ในการบันทึกข้อมูล (`saveHistories`) เพิ่มการส่ง `user_id` ไปด้วย
- **UI Changes**: เพิ่ม Dropdown หรือ Picker ให้เลือก "ชื่อครู" (Teacher) โดยใช้ข้อมูลจากข้อ 1
- **API Call**: ส่ง `user_id` ไปพร้อมกับข้อมูลอื่นๆ ไปที่ `POST /api/saveHistories`

### ตัวอย่าง Payload
```json
{
  "student": "student_id",
  "subject": "subject_id",
  "date": "2024-02-06",
  "stime": "10:00:00",
  "hour": 2,
  "file": (image_file),
  "user_id": 15  <-- (New) ID ของครูที่เลือก
}
```

> [!NOTE]
> หากไม่ส่ง `user_id` ไป ระบบจะบันทึกเป็น `NULL` หรือไม่ได้ระบุชื่อครู
