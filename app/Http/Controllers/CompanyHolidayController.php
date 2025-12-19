<?php

namespace App\Http\Controllers;

use App\Imports\CompanyHolidayImport;
use App\Models\CompanyHoliday;
use App\Models\User;
use App\Models\UserCompensateDay;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class CompanyHolidayController extends Controller
{
    public function Index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $holidays = CompanyHoliday::where('year', $year)
            ->orderBy('date')
            ->get();

        $years = CompanyHoliday::selectRaw('DISTINCT year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (!in_array($year, $years)) {
            $years[] = $year;
            rsort($years);
        }

        return view('company_holidays.index', compact('holidays', 'year', 'years'));
    }

    public function Store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|unique:company_holidays,date',
            'name' => 'required|string|max:255',
        ]);

        $date = Carbon::parse($request->date);

        $holiday = CompanyHoliday::create([
            'date' => $date->format('Y-m-d'),
            'name' => $request->name,
            'year' => $date->year,
        ]);

        $this->CalculateCompensateDays($holiday);

        return response()->json([
            'success' => true,
            'message' => 'เพิ่มวันหยุดสำเร็จ',
            'holiday' => $holiday,
        ]);
    }

    public function Destroy($id)
    {
        $holiday = CompanyHoliday::findOrFail($id);
        $holiday->compensateDays()->delete();
        $holiday->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบวันหยุดสำเร็จ',
        ]);
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

    public function RecalculateAll(Request $request)
    {
        $year = $request->get('year', date('Y'));

        UserCompensateDay::where('year', $year)->delete();

        $holidays = CompanyHoliday::where('year', $year)->get();
        foreach ($holidays as $holiday) {
            $this->CalculateCompensateDays($holiday);
        }

        return response()->json([
            'success' => true,
            'message' => 'คำนวณวันชดเชยใหม่สำเร็จ',
        ]);
    }

    public function GetUserCompensateDays(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $users = User::with(['compensateDays' => function($query) use ($year) {
            $query->where('year', $year)->with('holiday');
        }])->whereHas('compensateDays', function($query) use ($year) {
            $query->where('year', $year);
        })->get();

        return view('company_holidays.compensate_days', compact('users', 'year'));
    }

    public function Import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'year' => 'nullable|integer|min:2020|max:2100',
        ]);

        try {
            $year = $request->get('year');
            $import = new CompanyHolidayImport($year);
            Excel::import($import, $request->file('file'));

            $results = $import->GetResults();

            return response()->json([
                'success' => true,
                'message' => "Import สำเร็จ! เพิ่ม {$results['imported']} วัน (ข้าม {$results['skipped']} วัน)",
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function DownloadTemplate()
    {
        $template_path = public_path('templates/holiday_template.xlsx');

        if (file_exists($template_path)) {
            return response()->download($template_path, 'วันหยุดตามประเพณี_template.xlsx');
        }

        return response()->json([
            'success' => false,
            'message' => 'ไม่พบไฟล์ template',
        ], 404);
    }
}
