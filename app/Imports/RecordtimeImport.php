<?php

namespace App\Imports;

use App\Models\CompanyHoliday;
use App\Models\Recordtime;
use App\Models\Statuswork;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class RecordtimeImport implements ToCollection, WithStartRow
{
    protected $import_results = [];
    protected $success_count = 0;
    protected $skip_count = 0;
    protected $error_count = 0;
    protected $errors = [];
    protected $headers = [];
    protected $header_map = [];

    protected $current_fingerprint_id = null;
    protected $current_year = null;
    protected $current_month = null;
    protected $column_sets = [];
    protected $employees_from_file = [];

    public function startRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        $rows_array = $rows->toArray();

        foreach ($rows_array as $row_index => $row) {
            if ($this->IsEmptyRow($row)) {
                continue;
            }

            $first_cell = trim($row[0] ?? '');

            if ($this->IsEmployeeHeader($first_cell)) {
                $this->ParseEmployeeHeader($row);
                continue;
            }

            if ($this->IsColumnHeader($row)) {
                $this->ParseColumnHeaders($row);
                continue;
            }

            if ($this->IsSubHeader($row)) {
                continue;
            }

            if ($this->IsSummaryRow($row)) {
                continue;
            }

            if ($this->current_fingerprint_id && count($this->column_sets) > 0) {
                $this->ProcessDataRow($row);
            }
        }
    }

    protected function IsEmployeeHeader($first_cell)
    {
        return strpos($first_cell, 'ID:') !== false || strpos($first_cell, 'id:') !== false;
    }

    protected function ParseEmployeeHeader($row)
    {
        $emp_name = '';
        $emp_id = '';

        foreach ($row as $cell) {
            if (empty($cell)) continue;
            $cell_str = trim($cell);

            if (preg_match('/ID[:\s]*(\d+)/i', $cell_str, $matches)) {
                $this->current_fingerprint_id = $matches[1];
                $emp_id = $matches[1];
            }

            if (preg_match('/ชื่อ[:\s]*(.+?)(?:\s*,|$)/u', $cell_str, $matches)) {
                $emp_name = trim($matches[1]);
            }

            if (empty($emp_name) && preg_match('/ชื่อ[:\s]*(\S+)/u', $cell_str, $matches)) {
                $emp_name = trim($matches[1]);
            }

            if (preg_match('/วันที่[:\s]*(\d+)\/(\d+)\/(\d+)/u', $cell_str, $matches)) {
                $this->current_month = intval($matches[2]);
                $year = intval($matches[3]);
                if ($year < 100) {
                    $this->current_year = 2000 + $year;
                } elseif ($year > 2500) {
                    $this->current_year = $year - 543;
                } else {
                    $this->current_year = $year;
                }
            }
        }

        if ($emp_id && !isset($this->employees_from_file[$emp_id])) {
            $this->employees_from_file[$emp_id] = $emp_name ?: "ไม่ระบุชื่อ";
        }

        $this->column_sets = [];
        $this->headers = [];
    }

    protected function IsColumnHeader($row)
    {
        $row_text = implode(' ', array_filter($row));
        return mb_strpos($row_text, 'วันที่') !== false && mb_strpos($row_text, 'สัปดาห์') !== false;
    }

    protected function ParseColumnHeaders($row)
    {
        $this->headers = $row;
        $this->column_sets = [];
        $current_set = null;

        foreach ($row as $index => $header) {
            $header_lower = mb_strtolower(trim($header ?? ''));

            if ($header_lower === 'วันที่') {
                if ($current_set !== null) {
                    $this->column_sets[] = $current_set;
                }
                $current_set = ['date' => $index, 'stime' => null, 'etime' => null];
            }

            if ($current_set !== null) {
                if ($header_lower === 'เข้า' && $current_set['stime'] === null) {
                    $current_set['stime'] = $index;
                }
                if ($header_lower === 'ออก' && $current_set['etime'] === null) {
                    $current_set['etime'] = $index;
                }
            }
        }

        if ($current_set !== null) {
            $this->column_sets[] = $current_set;
        }
    }

    protected function IsSubHeader($row)
    {
        $row_text = implode(' ', array_filter($row));
        return mb_strpos($row_text, 'รอบ1') !== false || mb_strpos($row_text, 'รอบ2') !== false;
    }

    protected function IsSummaryRow($row)
    {
        $row_text = implode(' ', array_filter($row));
        return mb_strpos($row_text, 'วันทำงาน:') !== false ||
               mb_strpos($row_text, 'ลาป่วย') !== false ||
               mb_strpos($row_text, 'เงินสุทธิ') !== false;
    }

    protected function IsEmptyRow($row)
    {
        foreach ($row as $cell) {
            if (!empty($cell) && trim($cell) !== '') {
                return false;
            }
        }
        return true;
    }

    protected function ProcessDataRow($row)
    {
        foreach ($this->column_sets as $set) {
            $date_raw = $row[$set['date']] ?? null;

            if (empty($date_raw) || trim($date_raw) === '') {
                continue;
            }

            $date = $this->ParseScheduleDate($date_raw);
            if (!$date) {
                continue;
            }

            $stime_raw = isset($set['stime']) ? ($row[$set['stime']] ?? null) : null;
            $etime_raw = isset($set['etime']) ? ($row[$set['etime']] ?? null) : null;

            $stime = $this->ParseTime($stime_raw);
            $etime = $this->ParseTime($etime_raw);

            $this->SaveRecord($this->current_fingerprint_id, $date, $stime, $etime);
        }
    }

    protected function ParseScheduleDate($date_raw)
    {
        if (empty($date_raw)) {
            return null;
        }

        try {
            $date_str = trim($date_raw);

            if (preg_match('/^(\d+)\.(\d+)$/', $date_str, $matches)) {
                $month = intval($matches[1]);
                $day = intval($matches[2]);
                $year = $this->current_year ?? date('Y');

                if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                    return Carbon::create($year, $month, $day);
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected $update_count = 0;

    protected function SaveRecord($fingerprint_id, $date, $stime, $etime)
    {
        $user = User::where('fingerprint_id', $fingerprint_id)->first();

        if (!$user) {
            $this->skip_count++;
            if (!in_array("ไม่พบพนักงานที่มี fingerprint_id = {$fingerprint_id}", $this->errors)) {
                $this->errors[] = "ไม่พบพนักงานที่มี fingerprint_id = {$fingerprint_id}";
            }
            return;
        }

        if ($user->is_disabled) {
            $this->skip_count++;
            return;
        }

        $status_data = $this->CalculateStatus($user, $date, $stime, $etime);

        $existing_record = Recordtime::where('user_id', $user->id)
            ->where('date', $date->format('Y-m-d'))
            ->first();

        if ($existing_record) {
            $existing_record->update([
                'stime' => $stime,
                'etime' => $etime,
                'status' => $status_data['status_id'],
                'status_name' => $status_data['status_name'],
                'late_time' => $status_data['late_time'],
                'explain' => $status_data['explain'],
            ]);
            $this->update_count++;
            return;
        }

        $record = new Recordtime([
            'user_id' => $user->id,
            'type_user' => $user->type_user,
            'date' => $date->format('Y-m-d'),
            'stime' => $stime,
            'etime' => $etime,
            'status' => $status_data['status_id'],
            'status_name' => $status_data['status_name'],
            'late_time' => $status_data['late_time'],
            'explain' => $status_data['explain'],
        ]);
        $record->save();

        $this->success_count++;
    }

    protected function ParseDate($date_raw)
    {
        if (empty($date_raw)) {
            return null;
        }

        try {
            if (is_numeric($date_raw)) {
                return Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date_raw)->format('Y-m-d'));
            }

            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $date_raw);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return Carbon::parse($date_raw);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function IsDayOff($user, $day_of_week_num)
    {
        if (empty($user->days_off)) {
            return false;
        }

        $days_off = explode(',', $user->days_off);
        return in_array((string)$day_of_week_num, $days_off);
    }

    protected function IsWfhDay($user, $day_of_week_num)
    {
        if (empty($user->wfh_days)) {
            return false;
        }

        $wfh_days = explode(',', $user->wfh_days);
        return in_array((string)$day_of_week_num, $wfh_days);
    }

    protected function IsCompanyHoliday($date)
    {
        return CompanyHoliday::where('date', $date->format('Y-m-d'))->exists();
    }

    protected function GetCompanyHoliday($date)
    {
        return CompanyHoliday::where('date', $date->format('Y-m-d'))->first();
    }

    protected function ParseTime($time_raw)
    {
        if ($time_raw === null || $time_raw === '') {
            return null;
        }

        $time_str = trim($time_raw);
        if ($time_str === '' || $time_str === '-' || $time_str === '--') {
            return null;
        }

        try {
            if (is_numeric($time_str)) {
                $total_seconds = $time_str * 86400;
                $hours = floor($total_seconds / 3600);
                $minutes = floor(($total_seconds % 3600) / 60);
                return sprintf('%02d:%02d', $hours, $minutes);
            }

            if (preg_match('/^\d{1,2}:\d{2}$/', $time_str)) {
                return $time_str;
            }

            $time = Carbon::parse($time_str);
            return $time->format('H:i');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function CalculateStatus($user, $date, $stime, $etime)
    {
        $status_id = 1;
        $explain = '';
        $late_time = 0;
        $late_threshold = 35;

        $today = Carbon::today();
        $is_future_date = $date->gt($today);

        $day_of_week_num = $date->dayOfWeek;
        $day_of_week = $date->dayName;
        $type = $user->type_user;

        $is_day_off = $this->IsDayOff($user, $day_of_week_num);
        $is_wfh_day = $this->IsWfhDay($user, $day_of_week_num);
        $is_company_holiday = $this->IsCompanyHoliday($date);
        $company_holiday = $is_company_holiday ? $this->GetCompanyHoliday($date) : null;

        if ($is_company_holiday && !$stime && !$etime) {
            $status_id = 14;
            $explain = 'วันหยุดบริษัท: ' . ($company_holiday ? $company_holiday->name : '');

            $status = Statuswork::find($status_id);
            $status_name = $status ? $status->name : 'วันหยุด';

            return [
                'status_id' => $status_id,
                'status_name' => $status_name,
                'late_time' => 0,
                'explain' => $explain,
                'is_company_holiday' => true,
            ];
        }

        if ($is_day_off && !$stime && !$etime) {
            $status_id = 14;
            $explain = 'วันหยุดประจำสัปดาห์';

            $status = Statuswork::find($status_id);
            if (!$status) {
                $status_id = 1;
            }
            $status_name = $status ? $status->name : 'วันหยุด';

            return [
                'status_id' => $status_id,
                'status_name' => $status_name,
                'late_time' => 0,
                'explain' => $explain,
                'is_day_off' => true,
            ];
        }

        if ($is_wfh_day && !$stime && !$etime) {
            $status_id = 15;
            $explain = 'Work From Home';

            $status = Statuswork::find($status_id);
            if (!$status) {
                $status_id = 1;
                $status_name = 'WFH';
            } else {
                $status_name = $status->name;
            }

            return [
                'status_id' => $status_id,
                'status_name' => $status_name,
                'late_time' => 0,
                'explain' => $explain,
                'is_wfh' => true,
            ];
        }

        if (($type == 1) || ($day_of_week == "Saturday" || $day_of_week == "Sunday")) {
            $start_of_workday = Carbon::createFromTime(9, 10, 0);
            $end_of_workday = Carbon::createFromTime(16, 59, 59);
        } else {
            $start_of_workday = Carbon::createFromTime(10, 10, 0);
            $end_of_workday = Carbon::createFromTime(17, 59, 59);
        }

        if ($stime && $etime) {
            $starttime = Carbon::parse($stime);
            $endtime = Carbon::parse($etime);

            if (!$endtime->gt($end_of_workday)) {
                if ($endtime->diffInMinutes($end_of_workday) > $late_threshold) {
                    $status_id = 8;
                    $explain .= 'ลาครึ่งวันบ่าย';
                } else {
                    $status_id = 9;
                }
            } elseif ($starttime->gt($start_of_workday)) {
                if ($starttime->diffInMinutes($start_of_workday) > $late_threshold) {
                    $status_id = 5;
                } else {
                    $status_id = 5;
                }
                $time_difference = $starttime->diff($start_of_workday);
                $late_time = ($time_difference->h * 60) + $time_difference->i;
            }
        } elseif (!$stime && $etime) {
            $status_id = 6;
        } elseif ($stime && !$etime) {
            $status_id = 7;
        } elseif (!$stime && !$etime) {
            if ($is_future_date) {
                $status_id = 16;
                $explain = 'กำลังรอข้อมูล';
            } else {
                $status_id = 13;
            }
        }

        $status = Statuswork::find($status_id);
        $status_name = $status ? $status->name : 'ไม่ระบุ';

        return [
            'status_id' => $status_id,
            'status_name' => $status_name,
            'late_time' => $late_time,
            'explain' => $explain,
        ];
    }

    public function GetResults()
    {
        return [
            'success_count' => $this->success_count,
            'update_count' => $this->update_count,
            'skip_count' => $this->skip_count,
            'error_count' => $this->error_count,
            'errors' => array_slice($this->errors, 0, 20),
            'detected_headers' => array_filter($this->headers),
            'header_map' => $this->header_map,
            'employees_from_file' => $this->employees_from_file,
        ];
    }
}
