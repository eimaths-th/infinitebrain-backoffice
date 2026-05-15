# Finance Workflow

เอกสารนี้อธิบาย workflow ระบบ finance จากโค้ดในโปรเจกต์ โดยอ้างอิง route, controller และ model ที่เกี่ยวข้องจริงในระบบ จุดประสงค์คือให้เห็นภาพรวมการไหลของข้อมูลการเงิน ตั้งแต่การบันทึกเอกสาร รายรับ รายจ่าย เงินเดือน ไปจนถึง dashboard และผลประกอบการ

## ภาพรวมระบบ

ระบบ finance ในโปรเจกต์นี้ไม่ได้อยู่ใน module เดียว แต่ประกอบด้วยหลายส่วนที่ทำงานร่วมกัน:

| ส่วนระบบ | หน้าที่หลัก | Route หลัก | Controller / Model สำคัญ |
| --- | --- | --- | --- |
| Finance Dashboard | แสดงภาพรวมรายรับ รายจ่าย เงินเดือน และผลประกอบการ | `/finance-dashboard` | `FinanceDashboardController` |
| Performance | คำนวณผลประกอบการรายเดือน/รายปี | `/performance` | `Finance\PerformanceController` |
| Payment Voucher | จัดการใบสำคัญจ่าย | `/payment-voucher` | `PaymentVoucherController`, `PaymentVoucher` |
| Receipt Voucher | จัดการใบสำคัญรับ | `/receipt-voucher` | `ReceiptVoucherController`, `ReceiptVoucher` |
| Payroll | จัดการงวดเงินเดือนและเงินเดือนรายพนักงาน | `/payroll` | `PayrollController`, `PayrollPeriod`, `Payroll` |
| Daily Income API | สรุปรายรับรายวัน/สัปดาห์/เดือน สำหรับ n8n หรือ LINE notification | `/api/income/*` | `Api\DailyIncomeController` |
| School Receipt Revenue | รายรับจากคอร์สเรียน | `/receipt_school` | `ReceiptSchoolController`, `Receipt_School`, `SchoolPrice` |

ภาพรวมการไหลของข้อมูล:

```text
รายรับจากคอร์สเรียน
  SchoolPrice + receipt_school
        +
รายรับอื่น
  ReceiptVoucher.completed
        +
รายรับ EiMaths
  EiMaths API
        ↓
Performance Revenue

รายจ่ายทั่วไป
  PaymentVoucher.completed และ is_salary = false
        +
เงินเดือน
  Payroll.paid
        ↓
Performance Expense

Performance Revenue - Performance Expense
        ↓
กำไร/ขาดทุน
```

หมายเหตุสำคัญ: Finance Dashboard และ Performance ใช้ข้อมูลบางชุดร่วมกัน แต่มีเงื่อนไขการนับยอดไม่เหมือนกัน Dashboard เน้นภาพรวมและสถานะ ส่วน Performance เน้นยอดที่ถือว่าเสร็จสมบูรณ์ตามเงื่อนไขของแต่ละ module

## Finance Dashboard Workflow

หน้า dashboard อยู่ที่ route:

```text
GET /finance-dashboard
GET /finance-dashboard/branch-detail
```

ใช้ `FinanceDashboardController`

Workflow:

```text
ผู้ใช้เปิด Finance Dashboard
        ↓
ระบบรับ filter year/month
        ↓
ถ้าไม่ส่ง filter ใช้ปีและเดือนปัจจุบัน
        ↓
ตรวจสิทธิ์ผู้ใช้
        ↓
ถ้าเป็น external-branch จำกัดข้อมูลตาม branch group ที่ได้รับสิทธิ์
        ↓
ดึงข้อมูล Payment Voucher
        ↓
ดึงข้อมูล Receipt Voucher
        ↓
ดึงข้อมูล Payroll
        ↓
คำนวณ performance summary แยกกลุ่มสาขา
        ↓
ส่งข้อมูลไปแสดงที่ finance_dashboard.index
```

ข้อมูลที่ dashboard สรุป:

- จำนวน Payment Voucher แยกตามสถานะของเดือนที่เลือก
- ยอด Payment Voucher ทั้งหมดและยอดเดือนที่เลือก
- จำนวน Payment Voucher ที่ยังรอดำเนินการ
- ยอด Payment Voucher แยกตามสาขา
- รายการ Payment Voucher ล่าสุด
- จำนวน Receipt Voucher แยกตามสถานะของเดือนที่เลือก
- ยอด Receipt Voucher ทั้งหมดและยอดเดือนที่เลือก
- จำนวน Receipt Voucher ที่ยังรอดำเนินการ
- ยอด Receipt Voucher แยกตามสาขา
- รายการ Receipt Voucher ล่าสุด
- สถานะงวด Payroll ของปีที่เลือก
- Payroll รายเดือนที่เลือก
- รายได้/รายการหักใน Payroll
- Performance summary แยก branch group

การ drill-down รายละเอียดสาขาใช้ `/finance-dashboard/branch-detail` โดยรับ query เช่น `type`, `branch_code`, `period`, `year`, `month`

## Payment Voucher Workflow

Payment Voucher คือระบบใบสำคัญจ่าย ใช้สำหรับบันทึกรายจ่ายทั่วไปและรายการที่ต้องผ่านขั้นตอนตรวจสอบ/อนุมัติ/จ่ายเงิน

Model หลัก: `PaymentVoucher`

สถานะจริงในระบบ:

| Status | ความหมายในระบบ |
| --- | --- |
| `draft` | แบบร่าง |
| `pending` | รอตรวจสอบ |
| `reviewed` | ตรวจสอบแล้ว / รออนุมัติ |
| `approved` | อนุมัติแล้ว / รอจ่ายเงิน |
| `rejected` | ถูกปฏิเสธ |
| `completed` | เสร็จสิ้น |

Workflow หลัก:

```text
สร้างใบสำคัญจ่าย
        ↓
draft หรือ pending
        ↓
review
        ↓
reviewed
        ↓
approve
        ↓
approved
        ↓
complete
        ↓
completed
```

เงื่อนไขความสามารถตาม model:

| Method | เงื่อนไขสถานะ |
| --- | --- |
| `canEdit()` | `draft`, `rejected` |
| `canSubmit()` | `draft`, `rejected` |
| `canReview()` | `pending` |
| `canApprove()` | `reviewed` |
| `canComplete()` | `approved` |
| `canUndoComplete()` | `completed` |

ขั้นตอนสร้างเอกสาร:

```text
เลือกสาขา
        ↓
ระบบหา Branch และ BranchGroup
        ↓
ระบบสร้าง voucher_code จาก branch prefix
        ↓
ระบบ generate voucher_no จาก voucher_code + year + month + running number
        ↓
บันทึกข้อมูลผู้รับเงินผ่าน Payee
        ↓
บันทึกรายการจ่าย
        ↓
บันทึกรายการหัก ถ้ามี
        ↓
คำนวณยอดรวมและยอดสุทธิ
        ↓
บันทึกไฟล์แนบ ถ้ามี
        ↓
บันทึก log
        ↓
ถ้ากด submit ส่งเข้า pending หรือ approved กรณี skip approval
```

การคำนวณยอดใน Payment Voucher:

```text
total_amount = ผลรวมรายการจ่าย
total_deduction_amount = ผลรวมรายการหัก
withholding_tax_amount = ภาษีหัก ณ ที่จ่าย
net_amount = total_amount - total_deduction_amount - withholding_tax_amount
```

ข้อมูลที่เกี่ยวข้อง:

- รายการจ่ายและรายการหักเก็บใน `PaymentVoucherItem`
- รายการหักแยกด้วย `type = deduction`
- ไฟล์แนบเก็บใน `PaymentVoucherAttachment`
- ประวัติการทำรายการเก็บใน `PaymentVoucherLog`
- ผู้รับเงินเก็บหรือ reuse ผ่าน `Payee`

การนับใน Performance:

- นับเฉพาะ `status = completed`
- นับเฉพาะ `is_salary = false`
- ใช้ยอด `net_amount`
- filter ตาม `branch_code` หรือ `group_branch_code`

## Receipt Voucher Workflow

Receipt Voucher คือระบบใบสำคัญรับ ใช้สำหรับบันทึกรายรับที่ไม่ได้มาจาก school receipt โดยตรง หรือรายรับอื่นที่ต้องมีเอกสารรับเงิน

Model หลัก: `ReceiptVoucher`

สถานะจริงในระบบ:

| Status | ความหมายในระบบ |
| --- | --- |
| `draft` | แบบร่าง |
| `pending` | รอตรวจสอบ |
| `verified` | ตรวจสอบแล้ว |
| `completed` | เสร็จสิ้น |
| `rejected` | ถูกปฏิเสธ |

Workflow หลัก:

```text
สร้างใบสำคัญรับ
        ↓
draft หรือ pending
        ↓
verify
        ↓
verified
        ↓
complete
        ↓
completed
```

เงื่อนไขความสามารถตาม model:

| Method | เงื่อนไขสถานะ |
| --- | --- |
| `canEdit()` | `draft`, `rejected` |
| `canSubmit()` | `draft`, `rejected` |
| `canVerify()` | `pending` |
| `canComplete()` | `verified` |

ขั้นตอนสร้างเอกสาร:

```text
เลือกสาขา
        ↓
ระบบหา Branch และ BranchGroup
        ↓
ระบบสร้าง voucher_code จาก rv_prefix ของ branch
        ↓
ระบบ generate voucher_no
        ↓
บันทึกข้อมูลผู้จ่าย/ผู้โอนผ่าน Payee
        ↓
บันทึกรายการรับ
        ↓
บันทึกรายการหัก ถ้ามี
        ↓
คำนวณยอดสุทธิ
        ↓
บันทึกไฟล์แนบ ถ้ามี
        ↓
บันทึก log
```

การคำนวณยอดใน Receipt Voucher:

```text
total_amount = ผลรวมรายการรับ
total_deduction_amount = ผลรวมรายการหัก
net_amount = total_amount - total_deduction_amount
```

การนับใน Performance:

- นับเฉพาะ `status = completed`
- ใช้ยอด `net_amount`
- filter ตาม `branch_code` หรือ `group_branch_code`

## Payroll Workflow

ระบบ Payroll แบ่งข้อมูลเป็น 2 ชั้น:

| Model | หน้าที่ |
| --- | --- |
| `PayrollPeriod` | งวดเงินเดือน |
| `Payroll` | เงินเดือนรายพนักงานในงวดนั้น |

สถานะของ `PayrollPeriod`:

| Status | ความหมาย |
| --- | --- |
| `draft` | ร่าง |
| `processing` | กำลังประมวลผล |
| `approved` | อนุมัติแล้ว |
| `paid` | จ่ายแล้ว |

สถานะของ `Payroll`:

| Status | ความหมาย |
| --- | --- |
| `draft` | ร่าง |
| `confirmed` | ยืนยันแล้ว |
| `paid` | จ่ายแล้ว |

Workflow หลัก:

```text
สร้าง PayrollPeriod
        ↓
เลือก year, month, frequency
        ↓
generate payrolls จาก users ที่ active
        ↓
ระบบสร้าง Payroll รายพนักงาน
        ↓
ระบบสร้าง income item และ deduction item เริ่มต้น
        ↓
แก้ไขรายบุคคลหรือ bulk edit
        ↓
sync attendance เพื่อคำนวณมาสาย/ขาดงาน
        ↓
approve งวด
        ↓
mark as paid
```

การสร้างงวด:

- `year` ใช้ปี พ.ศ.
- `month` คือเดือนของงวด
- `frequency` รองรับ `monthly` และ `weekly`
- รายเดือนใช้วันแรกถึงวันสุดท้ายของเดือน
- รายสัปดาห์ใช้ `start_date` และ `end_date`

การ generate payroll:

```text
ดึง user ที่ is_disabled = false
        ↓
ถ้าเป็น weekly ดึง user ที่ payroll_frequency = weekly
        ↓
ถ้าเป็น monthly ดึง user ที่ payroll_frequency = monthly หรือ null
        ↓
สร้าง Payroll ต่อ user หากยังไม่มีในงวด
        ↓
เพิ่ม income item เงินเดือน ถ้ามี base_salary
        ↓
คำนวณประกันสังคม
        ↓
คำนวณภาษี
        ↓
คำนวณ total_income, total_deduction, net_pay
        ↓
อัปเดตยอดรวมของ PayrollPeriod
```

รายการรายได้และรายการหัก:

- รายได้เก็บใน `PayrollIncomeItem`
- รายการหักเก็บใน `PayrollDeductionItem`
- `Payroll::CalculateTotals()` รวมยอดจาก item แล้วคำนวณ `net_pay`

สูตรที่พบใน model:

```text
total_income = sum(quantity * amount ของ income items)
total_deduction = sum(amount ของ deduction items)
net_pay = total_income - total_deduction
```

รายการหักอัตโนมัติที่พบ:

- ประกันสังคม
- ภาษีเงินได้
- ภาษีหัก ณ ที่จ่าย 3% จาก income item ที่ระบุ flag
- หักมาสาย
- หักขาดงาน

การ sync attendance:

```text
เลือก PayrollPeriod
        ↓
คำนวณรอบเวลา 26 เดือนก่อนหน้า ถึง 25 เดือนปัจจุบัน
        ↓
วน payroll รายพนักงาน
        ↓
รวม late_time จาก Recordtime
        ↓
นับวันที่ status = 13 เป็นขาดงาน
        ↓
สร้าง/อัปเดต deduction item
        ↓
คำนวณยอด payroll ใหม่
        ↓
คำนวณยอด PayrollPeriod ใหม่
```

การนับใน Performance:

- นับเฉพาะ `Payroll.status = paid`
- ใช้ยอด `net_pay`
- filter ตาม `PayrollPeriod.year` และ `PayrollPeriod.month`
- mapping เข้ากลุ่มสาขาด้วย department ของ user:
  - `EL` ใช้ department `วิชาการ`, `สำนักงาน`
  - `IF` ใช้ department `คอมพิวเตอร์`
  - `META` และ `EiMaths` ไม่พบการ map payroll ใน logic ปัจจุบัน

## Performance Calculation Workflow

Performance อยู่ที่ route:

```text
GET /performance
GET /performance/monthly/{year}/{month}
GET /performance/yearly/{year}
```

ใช้ `Finance\PerformanceController`

Workflow รายเดือน:

```text
ผู้ใช้เปิดหน้า Performance
        ↓
ระบบรับ year, month, groups, branches
        ↓
ตรวจ allowed groups จาก role ผู้ใช้
        ↓
คำนวณรายรับ
        ↓
คำนวณรายจ่าย
        ↓
คำนวณ profit
        ↓
ถ้าต้องการรายละเอียด ดึงรายการประกอบ
        ↓
ส่งไป view สำหรับแสดงผล/พิมพ์/export
```

สูตรหลัก:

```text
total_revenue = school_revenue + api_school_revenue + rv_revenue
total_expense = pv_expense + payroll_expense
profit = total_revenue - total_expense
```

แหล่งรายรับ:

| แหล่งรายรับ | เงื่อนไข |
| --- | --- |
| School Receipt | `SchoolPrice` join กับ `receipt_school` ผ่าน `rid`, อิง `receipt_school.submission_date` |
| Receipt Voucher | `ReceiptVoucher.status = completed`, ใช้ `net_amount` |
| EiMaths API | ดึงจาก API รายได้รายเดือนตามสาขา |

แหล่งรายจ่าย:

| แหล่งรายจ่าย | เงื่อนไข |
| --- | --- |
| Payment Voucher | `PaymentVoucher.status = completed`, `is_salary = false`, ใช้ `net_amount` |
| Payroll | `Payroll.status = paid`, ใช้ `net_pay` |

เงื่อนไขตาม branch group:

- ถ้าเลือก branch เจาะจง จะ filter ด้วย `branch_code`
- ถ้าไม่เลือก branch แต่เลือก group จะ filter ด้วย `group_branch_code`
- School Receipt แสดงเฉพาะเมื่อเลือก `EL`
- Payroll แสดงตาม department mapping ของแต่ละ group
- EiMaths revenue ใช้เมื่อเลือกกลุ่มหรือสาขาที่เกี่ยวข้องกับ `EiMaths`

Workflow รายปี:

```text
รับ year
        ↓
วนคำนวณรายเดือน 1 ถึง 12
        ↓
รวมยอด revenue, expense, profit
        ↓
รวมรายละเอียดรายการ
        ↓
group รายละเอียดซ้ำอีกครั้งสำหรับรายปี
        ↓
ส่งไป view รายปี
```

## Evaluation Sheet Workflow

Evaluation Sheet ใช้ข้อมูลจาก Payment Voucher ที่ถูก mark ด้วย `is_evaluation = true`

Workflow:

```text
มี Payment Voucher อยู่ในระบบ
        ↓
ผู้ใช้กด evaluate หรือ toggle evaluation
        ↓
ระบบตั้ง is_evaluation = true
        ↓
หน้า Performance เรียกโหมด evaluation
        ↓
ระบบดึงเฉพาะ Payment Voucher ที่ is_evaluation = true
        ↓
พิมพ์หรือ export ใบประเมิน
```

Route ที่เกี่ยวข้อง:

```text
GET /performance/monthly/{year}/{month}/print-evaluation
GET /performance/monthly/{year}/{month}/export-evaluation
GET /performance/yearly/{year}/print-evaluation
GET /performance/yearly/{year}/export-evaluation
```

หมายเหตุจาก logic ปัจจุบัน:

- Evaluation ใช้ flag `is_evaluation`
- ในโหมด evaluation ระบบเน้นรายการ Payment Voucher ที่ถูกเลือกเข้าใบประเมิน
- โหมด evaluation ไม่ใช้รายรับ School Receipt, Receipt Voucher และ Payroll เหมือน performance ปกติ

## Daily Income API Workflow

Daily Income API อยู่ใน `Api\DailyIncomeController`

Route:

```text
GET /api/income/daily
GET /api/income/today
GET /api/income/yesterday
GET /api/income/weekly
GET /api/income/monthly
GET /api/income/simulation-test
POST /api/income/save-report
```

Workflow รายวัน:

```text
รับ date หรือใช้วันที่ปัจจุบัน
        ↓
parse date
        ↓
join SchoolPrice กับ receipt_school
        ↓
filter ด้วย receipt_school.submission_date
        ↓
ไม่นับ receipt_school.type = 2
        ↓
รวมยอดรายได้และจำนวนรายการ
        ↓
แยกตาม payment_type
        ↓
แยกตาม course_type
        ↓
ดึง transaction detail
        ↓
ตรวจยอดเพิ่มเติมของวันก่อนหน้า
        ↓
สร้าง line_message
        ↓
ส่ง JSON response
```

Workflow บันทึกยอดที่รายงานแล้ว:

```text
n8n หรือระบบภายนอกส่ง POST /api/income/save-report
        ↓
ระบบคำนวณยอดล่าสุดของ date ที่ระบุ
        ↓
updateOrCreate DailyIncomeReport ตาม report_date
        ↓
เก็บยอด income, transactions, receipts และ reported_at
```

Workflow ตรวจยอดเพิ่มเติมของวันก่อนหน้า:

```text
หา DailyIncomeReport ของวันก่อนหน้า
        ↓
ถ้าไม่พบ ให้ถือว่าไม่มีข้อมูลเปรียบเทียบ
        ↓
คำนวณยอดปัจจุบันของวันก่อนหน้า
        ↓
เปรียบเทียบกับยอดที่เคย report
        ↓
ถ้ามียอดเพิ่ม ส่ง previous_day_additional กลับไปใน response
```

## Permission และ Branch Filtering

ระบบใช้ทั้ง middleware, permission และ role

Permission ที่เกี่ยวข้องกับ finance พบในระบบ เช่น:

```text
payment-voucher.view
payment-voucher.create
payment-voucher.edit
payment-voucher.review
payment-voucher.approve
payment-voucher.export
payment-voucher.delete
receipt-voucher.view
receipt-voucher.create
receipt-voucher.edit
receipt-voucher.verify
receipt-voucher.complete
receipt-voucher.export
receipt-voucher.delete
payroll.view
performance.view
```

พฤติกรรมตาม role ที่พบ:

| Role | ผลต่อการมองเห็นข้อมูล |
| --- | --- |
| `account` | ใน Payment Voucher และ Receipt Voucher จะเห็นเฉพาะรายการที่ตัวเองสร้าง |
| `external-branch` | จำกัด branch/group ตาม `user->branches` |

การ resolve branch:

```text
user->branches
        ↓
BranchGroup.code
        ↓
Branch.code
        ↓
filter voucher ด้วย branch_code
```

จุดที่ใช้ branch filtering:

- Payment Voucher list/show/create/update
- Receipt Voucher list/show/create/update
- Finance Dashboard
- Performance
- Finance Dashboard branch detail

## ข้อมูลที่ถูกนับในผลประกอบการ

ข้อมูลที่ถูกนับเป็นรายรับ:

| รายการ | เงื่อนไข |
| --- | --- |
| รายรับคอร์สเรียน | `SchoolPrice` ที่ผูกกับ `receipt_school.submission_date` ในเดือน/ปีนั้น |
| Receipt Voucher | `status = completed`, ใช้ `net_amount` |
| EiMaths API | รายได้รายเดือนจาก API ภายนอก |

ข้อมูลที่ถูกนับเป็นรายจ่าย:

| รายการ | เงื่อนไข |
| --- | --- |
| Payment Voucher | `status = completed`, `is_salary = false`, ใช้ `net_amount` |
| Payroll | `status = paid`, ใช้ `net_pay` |

ข้อมูลที่ไม่ถูกนับใน Performance หลักตาม logic ที่ตรวจสอบ:

- Payment Voucher ที่ยังไม่ `completed`
- Payment Voucher ที่ `is_salary = true`
- Receipt Voucher ที่ยังไม่ `completed`
- Payroll ที่ยังไม่ `paid`
- Voucher ที่เป็น `draft`, `pending`, `reviewed`, `approved`, `verified` แต่ยังไม่ถึงสถานะจบตามเงื่อนไขของ Performance

## สรุป Workflow รวม

```text
1. รายรับคอร์สเรียนถูกบันทึกใน receipt_school และ SchoolPrice
2. รายรับอื่นถูกบันทึกผ่าน Receipt Voucher
3. รายจ่ายทั่วไปถูกบันทึกผ่าน Payment Voucher
4. เงินเดือนถูกจัดการผ่าน PayrollPeriod และ Payroll
5. Finance Dashboard แสดงภาพรวมตามเดือน ปี สถานะ และสาขา
6. Performance คำนวณผลประกอบการจากรายการที่ถือว่าสมบูรณ์แล้ว
7. Evaluation Sheet ใช้ Payment Voucher ที่ถูก mark is_evaluation
8. Daily Income API สรุปรายรับคอร์สเรียนเพื่อส่งรายงานภายนอก
```

เอกสารนี้สะท้อนพฤติกรรมจากโค้ดที่ตรวจสอบใน controller และ model หลักของระบบ finance หาก workflow ธุรกิจจริงมีขั้นตอนนอกระบบหรือมีเงื่อนไขจากฐานข้อมูล production เพิ่มเติม ควรตรวจสอบร่วมกับข้อมูลจริงก่อนนำไปใช้เป็นเอกสาร process ทางบัญชีอย่างเป็นทางการ
