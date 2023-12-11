<?php

namespace App\Imports;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class AttendanceImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        //
    }
    public function model(array $row)
    {
        
        return new Attendance([
            'employee_id' => $row['ID'],
            'date' => $row['วันที่'],
            'day_of_week' => $row['สัปดาห์'],
            'in_time' => $row['เข้า'],
            'out_time' => $row['ออก'],
        ]);
    }
}
