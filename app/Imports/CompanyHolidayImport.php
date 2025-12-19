<?php

namespace App\Imports;

use App\Models\CompanyHoliday;
use App\Models\User;
use App\Models\UserCompensateDay;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CompanyHolidayImport implements ToCollection, WithStartRow
{
    protected $year;
    protected $imported = 0;
    protected $skipped = 0;
    protected $errors = [];

    protected $thai_months = [
        'มกราคม' => 1, 'กุมภาพันธ์' => 2, 'มีนาคม' => 3, 'เมษายน' => 4,
        'พฤษภาคม' => 5, 'มิถุนายน' => 6, 'กรกฎาคม' => 7, 'กรกฏาคม' => 7,
        'สิงหาคม' => 8, 'กันยายน' => 9, 'ตุลาคม' => 10,
        'พฤศจิกายน' => 11, 'ธันวาคม' => 12
    ];

    public function __construct($year = null)
    {
        $this->year = $year ?? date('Y');
    }

    public function startRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        $detected_year = $this->DetectYear($rows);
        if ($detected_year) {
            $this->year = $detected_year;
        }

        foreach ($rows as $index => $row) {
            $row_array = $row->toArray();

            if (!$this->IsDataRow($row_array)) {
                continue;
            }

            $result = $this->ProcessRow($row_array);
            if ($result === true) {
                $this->imported++;
            } elseif ($result === false) {
                $this->skipped++;
            }
        }
    }

    protected function DetectYear($rows)
    {
        foreach ($rows as $row) {
            $row_str = implode(' ', $row->toArray());

            if (preg_match('/ประจำปี\s*(\d{4})/u', $row_str, $matches)) {
                $year = intval($matches[1]);
                if ($year > 2500) {
                    return $year - 543;
                }
                return $year;
            }

            if (preg_match('/ปี\s*(\d{4})/u', $row_str, $matches)) {
                $year = intval($matches[1]);
                if ($year > 2500) {
                    return $year - 543;
                }
                return $year;
            }
        }
        return null;
    }

    protected function IsDataRow($row)
    {
        $first_cell = trim($row[0] ?? '');
        if (!is_numeric($first_cell)) {
            return false;
        }

        $date_cell = trim($row[3] ?? '');
        $name_cell = trim($row[6] ?? '');

        if (empty($date_cell) || empty($name_cell)) {
            return false;
        }

        foreach ($this->thai_months as $month_name => $month_num) {
            if (strpos($date_cell, $month_name) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function ProcessRow($row)
    {
        $date_str = trim($row[3] ?? '');
        $name = trim($row[6] ?? '');

        if (empty($date_str) || empty($name)) {
            return null;
        }

        $parsed = $this->ParseThaiDate($date_str);
        if (!$parsed) {
            $this->errors[] = "ไม่สามารถแปลงวันที่: {$date_str}";
            return false;
        }

        $date = sprintf('%04d-%02d-%02d', $this->year, $parsed['month'], $parsed['day']);

        if (CompanyHoliday::where('date', $date)->exists()) {
            return false;
        }

        $holiday = CompanyHoliday::create([
            'date' => $date,
            'name' => $name,
            'year' => $this->year,
        ]);

        $this->CalculateCompensateDays($holiday);

        return true;
    }

    protected function ParseThaiDate($date_str)
    {
        foreach ($this->thai_months as $month_name => $month_num) {
            if (strpos($date_str, $month_name) !== false) {
                $day_part = str_replace($month_name, '', $date_str);
                $day = intval(preg_replace('/[^0-9]/', '', $day_part));

                if ($day >= 1 && $day <= 31) {
                    return ['day' => $day, 'month' => $month_num];
                }
            }
        }
        return null;
    }

    protected function CalculateCompensateDays(CompanyHoliday $holiday)
    {
        $day_of_week = Carbon::parse($holiday->date)->dayOfWeek;
        $users = User::whereNotNull('days_off')->get();

        foreach ($users as $user) {
            $days_off = explode(',', $user->days_off);

            if (in_array((string)$day_of_week, $days_off)) {
                UserCompensateDay::firstOrCreate([
                    'user_id' => $user->id,
                    'holiday_id' => $holiday->id,
                ], [
                    'holiday_date' => $holiday->date,
                    'year' => $holiday->year,
                    'is_used' => false,
                ]);
            }
        }
    }

    public function GetResults()
    {
        return [
            'year' => $this->year,
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }
}
