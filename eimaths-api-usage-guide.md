# คู่มือการใช้งาน API: Revenue Summary By Branch Monthly

เอกสารนี้สรุปรายละเอียดและวิธีใช้งาน API สำหรับดึงข้อมูลสรุปรายได้แยกตามสาขาแบบรายเดือน

## 1. ข้อมูลพื้นฐาน (API Endpoint)
- **URL:** `{{base_url}}/api/revenue-summary-by-branch-monthly`
- **Method:** `GET`
- **Content-Type:** `application/json`

> [!NOTE]
> `{{base_url}}` โดยปกติคือ `http://localhost:8000` (หากรันผ่าน `php artisan serve`)
> [!NOTE] 
### Domain ที่ใช้งานจริง
> `{{base_url}}` โดยปกติคือ `https://app.eimaths-th.com/`

## 2. Parameter ที่ต้องส่ง (Query Parameters)
คุณต้องส่ง Parameter ดังต่อไปนี้ผ่าน Query String:

| Parameter | ประเภท | คำอธิบาย | ตัวอย่าง |
| :--- | :--- | :--- | :--- |
| `month` | Integer | ลำดับเดือนที่ต้องการ (1-12) | `10` |
| `year` | Integer | ปี ค.ศ. (4 หลัก) | `2025` |

## 3. วิธีการทดสอบด้วย Postman
1. เปิด Postman และสร้าง Request ใหม่
2. เลือก Method เป็น **GET**
3. กรอก URL: `http://localhost:8000/api/revenue-summary-by-branch-monthly`
4. ไปที่แท็บ **Params** และเพิ่ม Key-Value:
   - Key: `month` , Value: `10`
   - Key: `year` , Value: `2025`
5. กดปุ่ม **Send**

## 4. โครงสร้างข้อมูลตอบกลับ (Response Format)
API จะตอบกลับเป็น JSON ดังนี้:

```json
{
    "success": true,
    "message": "สรุปรายได้รายเดือนแยกตามสาขาสำเร็จ",
    "data": {
        "date": "02-2026",
        "grand_total": 391500,
        "total_receipts": 19,
        "branches": [
            {
                "centre": 2,
                "branch_name": "Ratchaphruek",
                "branch_code": "RP",
                "total_receipts": 18,
                "total_revenue": "381000.00"
            },
            {
                "centre": 3,
                "branch_name": "Bangkae",
                "branch_code": "BK",
                "total_receipts": 1,
                "total_revenue": "10500.00"
            }
        ]
    }
}
```

### รายละเอียดข้อมูลใน Response:
- `grand_total`: ยอดรวมรายได้ทั้งหมดของทุกสาขาในเดือนนั้น
- `total_receipts`: จำนวนใบเสร็จทั้งหมด
- `branches`: รายการข้อมูลแยกตามสาขา (ชื่อสาขา, รหัสสาขา, จำนวนใบเสร็จ และรายได้ของสาขานั้น)

## 5. การจัดการข้อผิดพลาด (Error Handling)
หากเกิดข้อผิดพลาด ระบบจะตอบกลับด้วย Status Code `500` พร้อมรายละเอียด:
```json
{
    "success": false,
    "message": "An error occurred fetching data.",
    "error": "..." 
}
```

---
> [!IMPORTANT]
> API นี้จะข้ามข้อมูลจากสาขาที่มี ID เป็น 4, 5 และ 6 และจะดึงเฉพาะรายการที่มี `type = 0` (รายได้ปกติ) เท่านั้น
