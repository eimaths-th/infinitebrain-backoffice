<?php

namespace App\Imports;

use App\Models\BankStatement;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BankStatementImport implements ToModel, WithStartRow
{
    /**
     * @return int
     */
    public function startRow(): int
    {
        return 4; // Start reading from row 4 based on analysis (Row 2 is header, data starts after)
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip empty rows or summary rows
        if (empty($row[0]) && empty($row[1])) {
            return null;
        }

        // Format: ["วันที่","ยอดเงิน","บัญชีเข้า","บัญชีออก","รายการ","ได้รับจาก","เลขที่","ชำระโดย","ยอดย่อย","อายเลเวล",null,"ส่วนกลาง",null,"ธรรมเนียม","ภาษี","รวมเข้า","รวมออก"]
        // Index: 0=Date, 1=Amount, 2=AccIn, 3=AccOut, 4=Desc, 5=RecFrom, 6=Ref, 7=PaidBy, 8=Subtotal, 9=EyeIn, 10=EyeOut, 11=CenIn, 12=CenOut, 13=Fee, 14=Tax, 15=TotalIn, 16=TotalOut

        // Note: The index might shift if there are merged cells or nulls in the array read by Laravel Excel.
        // Based on "Row 2: ["วันที่",...]" it seems straightforward.

        $date = $this->transformDate($row[0]);
        if (!$date) return null; // Skip invalid date rows

        return new BankStatement([
            'date' => $date,
            'amount' => $this->transformNumber($row[1]),
            'account_in' => $row[2],
            'account_out' => $row[3],
            'description' => $row[4],
            'received_from' => $row[5],
            'reference_no' => $row[6],
            'paid_by' => $row[7],
            'subtotal' => $this->transformNumber($row[8]),
            'branch_eye_level_in' => $this->transformNumber($row[9]),
            'branch_eye_level_out' => $this->transformNumber($row[10]), // Assuming next col
            'branch_central_in' => $this->transformNumber($row[11]),
            'branch_central_out' => $this->transformNumber($row[12]), // Assuming next col
            'fee' => $this->transformNumber($row[13]),
            'tax' => $this->transformNumber($row[14]),
            'total_in' => $this->transformNumber($row[15]),
            'total_out' => $this->transformNumber($row[16]),
            'created_by' => Auth::id(),
        ]);
    }

    private function transformDate($value)
    {
        if (!$value) return null;
        try {
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject($value));
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function transformNumber($value)
    {
        return is_numeric($value) ? $value : 0;
    }
}
