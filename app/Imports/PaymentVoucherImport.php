<?php

namespace App\Imports;

use App\Models\PaymentVoucher;
use App\Models\PaymentVoucherItem;
use App\Models\PaymentVoucherLog;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentVoucherImport
{
    /**
     * โครงสร้างไฟล์ Excel:
     * - Row 10, Col J: เลขที่ใบสำคัญจ่าย (Voucher No) - ไม่ใช้ ระบบสร้างให้
     * - Row 10, Col C: ผู้รับ (Pay To)
     * - Row 11, Col J: วันที่ (Date)
     * - Row 12, Col C: เลขที่บัญชี + ธนาคาร
     * - Row 14, Col C: ที่อยู่
     * - Row 19-31: รายการ (Col A=ลำดับ, Col C=รายการ, Col I=จำนวนเงิน)
     */

    protected $file_path;
    protected $branch_code;
    protected $errors = [];
    protected $imported_count = 0;

    public function __construct(string $file_path, string $branch_code = 'EL')
    {
        $this->file_path = $file_path;
        $this->branch_code = $branch_code;
    }

    /**
     * Preview ข้อมูลจากไฟล์ Excel (ไม่บันทึก)
     */
    public function preview(): array
    {
        try {
            $spreadsheet = IOFactory::load($this->file_path);
            $sheet = $spreadsheet->getActiveSheet();

            // ดึงข้อมูลหลัก
            $excel_voucher_no = $this->GetCellValue($sheet, 'J', 10); // เก็บไว้แสดง แต่ไม่ใช้
            $pay_to = $this->GetCellValue($sheet, 'C', 10);
            $date_value = $sheet->getCell('J11')->getValue();
            $bank_info = $this->GetCellValue($sheet, 'C', 12);
            $pay_to_address = $this->GetCellValue($sheet, 'C', 14);

            // แยกเลขบัญชีและชื่อธนาคาร
            $account_no = null;
            $bank_name = null;
            if ($bank_info) {
                if (preg_match('/^([\d\-]+)\s+(.+)$/', $bank_info, $matches)) {
                    $account_no = trim($matches[1]);
                    $bank_name = trim($matches[2]);
                } else {
                    $account_no = $bank_info;
                }
            }

            // แปลงวันที่
            $voucher_date = $this->ParseDate($date_value);

            // ดึงรายการ
            $items = $this->ParseItems($sheet);

            // คำนวณยอดรวม
            $total_amount = array_sum(array_column($items, 'amount'));

            // สร้าง voucher_no ใหม่จากระบบ
            $generated_voucher_no = PaymentVoucher::generateVoucherNo($this->branch_code);

            // Validate
            $warnings = [];
            if (empty($pay_to)) {
                $this->errors[] = 'ไม่พบชื่อผู้รับ (Cell C10)';
            }
            if (!$voucher_date) {
                $this->errors[] = 'ไม่พบวันที่หรือรูปแบบวันที่ไม่ถูกต้อง (Cell J11)';
            }
            if (empty($items)) {
                $this->errors[] = 'ไม่พบรายการในใบสำคัญจ่าย';
            }

            // แจ้งเตือนถ้ามี voucher_no ในไฟล์
            if ($excel_voucher_no) {
                $warnings[] = "พบเลขที่ในไฟล์: $excel_voucher_no (ระบบจะสร้างใหม่)";
            }

            return [
                'success' => empty($this->errors),
                'errors' => $this->errors,
                'warnings' => $warnings,
                'data' => [
                    'excel_voucher_no' => $excel_voucher_no,
                    'generated_voucher_no' => $generated_voucher_no,
                    'voucher_date' => $voucher_date ? $voucher_date->format('Y-m-d') : null,
                    'voucher_date_display' => $voucher_date ? $voucher_date->format('d/m/Y') : null,
                    'pay_to' => $pay_to,
                    'pay_to_address' => $pay_to_address,
                    'pay_to_account_no' => $account_no,
                    'pay_to_bank_name' => $bank_name,
                    'items' => $items,
                    'total_amount' => $total_amount,
                    'items_count' => count($items),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['เกิดข้อผิดพลาดในการอ่านไฟล์: ' . $e->getMessage()],
                'warnings' => [],
                'data' => null,
            ];
        }
    }

    /**
     * บันทึกข้อมูลจาก Preview (รับ voucher_no จาก user)
     */
    public function importFromPreview(array $data): array
    {
        // Validate voucher_no
        $voucher_no = $data['voucher_no'] ?? null;
        if (empty($voucher_no)) {
            return [
                'success' => false,
                'errors' => ['กรุณาระบุเลขที่ใบสำคัญจ่าย'],
            ];
        }

        // ตรวจสอบซ้ำ
        $existing = PaymentVoucher::where('voucher_no', $voucher_no)->first();
        if ($existing) {
            return [
                'success' => false,
                'errors' => ["เลขที่ใบสำคัญจ่าย $voucher_no มีอยู่ในระบบแล้ว"],
            ];
        }

        DB::beginTransaction();

        try {
            // สร้าง Payee
            $payee = null;
            if (!empty($data['pay_to'])) {
                $payee = \App\Models\Payee::findOrCreateByName($data['pay_to'], [
                    'address' => $data['pay_to_address'] ?? null,
                    'account_no' => $data['pay_to_account_no'] ?? null,
                    'bank_name' => $data['pay_to_bank_name'] ?? null,
                    'created_by' => Auth::id(),
                ]);
            }

            // สร้างใบสำคัญจ่าย
            $voucher = PaymentVoucher::create([
                'voucher_no' => $voucher_no,
                'branch_code' => $data['branch_code'] ?? $this->branch_code,
                'voucher_date' => $data['voucher_date'],
                'pay_to' => $data['pay_to'],
                'pay_to_address' => $data['pay_to_address'] ?? null,
                'pay_to_account_no' => $data['pay_to_account_no'] ?? null,
                'pay_to_bank_name' => $data['pay_to_bank_name'] ?? null,
                'payee_id' => $payee?->id,
                'total_amount' => $data['total_amount'] ?? 0,
                'status' => PaymentVoucher::STATUS_DRAFT,
                'created_by' => Auth::id(),
                'created_by_name' => Auth::user()->name ?? 'System',
            ]);

            // สร้างรายการย่อย
            if (!empty($data['items'])) {
                foreach ($data['items'] as $index => $item) {
                    PaymentVoucherItem::create([
                        'payment_voucher_id' => $voucher->id,
                        'item_no' => $index + 1,
                        'description' => $item['description'],
                        'amount' => $item['amount'],
                    ]);
                }
            }

            // บันทึก Log
            PaymentVoucherLog::create([
                'payment_voucher_id' => $voucher->id,
                'action' => PaymentVoucherLog::ACTION_CREATE,
                'from_status' => null,
                'to_status' => PaymentVoucher::STATUS_DRAFT,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'System',
                'remark' => 'Import จากไฟล์ Excel',
            ]);

            DB::commit();

            return [
                'success' => true,
                'voucher_id' => $voucher->id,
                'voucher_no' => $voucher_no,
                'message' => 'Import สำเร็จ!',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'errors' => ['เกิดข้อผิดพลาด: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * ดึงค่าจาก Cell
     */
    protected function GetCellValue($sheet, string $col, int $row): ?string
    {
        $value = $sheet->getCell($col . $row)->getValue();
        if ($value === null || $value === '') {
            return null;
        }
        return trim((string) $value);
    }

    /**
     * แปลงวันที่
     */
    protected function ParseDate($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject($value));
            } catch (\Exception $e) {
                return null;
            }
        }

        $value = trim((string) $value);

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
            try {
                return Carbon::createFromFormat('d/m/Y', $value);
            } catch (\Exception $e) {
                return null;
            }
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            try {
                return Carbon::parse($value);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * ดึงรายการจาก Excel (Row 19-31)
     */
    protected function ParseItems($sheet): array
    {
        $items = [];

        for ($row = 19; $row <= 31; $row++) {
            $item_no = $this->GetCellValue($sheet, 'A', $row);
            $description = $this->GetCellValue($sheet, 'C', $row);
            $amount_value = $sheet->getCell('I' . $row)->getCalculatedValue();

            if (empty($description) || empty($amount_value)) {
                continue;
            }

            if (!is_numeric($amount_value)) {
                continue;
            }

            $amount = (float) $amount_value;

            if ($amount <= 0) {
                continue;
            }

            $items[] = [
                'item_no' => $item_no,
                'description' => $description,
                'amount' => $amount,
            ];
        }

        return $items;
    }

    public function GetErrors(): array
    {
        return $this->errors;
    }

    public function GetImportedCount(): int
    {
        return $this->imported_count;
    }
}
