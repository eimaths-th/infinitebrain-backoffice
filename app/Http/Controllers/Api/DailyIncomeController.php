<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyIncomeReport;
use App\Models\Receipt_School;
use App\Models\SchoolPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyIncomeController extends Controller
{
    /**
     * สรุปรายได้รายวัน
     * รวมถึงตรวจสอบยอดเพิ่มเติมของวันก่อนหน้าที่เข้ามาหลังรายงาน
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dailySummary(Request $request)
    {
        // รับวันที่จาก request หรือใช้วันนี้
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        try {
            $targetDate = Carbon::parse($date);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date format. Please use Y-m-d format.',
            ], 400);
        }

        // ดึงข้อมูลรายได้จาก SchoolPrice โดยอิงวันที่จาก submission_date ของ Receipt_School
        $incomeData = SchoolPrice::join('receipt_school', 'school_price.rid', '=', 'receipt_school.id')
            ->whereDate('receipt_school.submission_date', $targetDate)
            ->where('receipt_school.type', '!=', 2)
            ->select(
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(CAST(school_price.total AS DECIMAL(10,2))) as total_income'),
                DB::raw('SUM(CAST(school_price.price AS DECIMAL(10,2))) as total_price'),
                DB::raw('SUM(CAST(school_price.discount AS DECIMAL(10,2))) as total_discount'),
                DB::raw('SUM(CAST(school_price.entrance_fee AS DECIMAL(10,2))) as total_entrance_fee'),
                DB::raw('SUM(CAST(school_price.bookprice AS DECIMAL(10,2))) as total_book_price'),
                DB::raw('SUM(CAST(school_price.media_price AS DECIMAL(10,2))) as total_media_price')
            )
            ->first();

        // ดึงข้อมูลจำนวน Receipt ที่ submission_date ตรงกับวันที่เลือก
        $receiptCount = Receipt_School::whereDate('submission_date', $targetDate)
            ->where('type', '!=', 2)
            ->count();

        // สรุปตามประเภทการชำระเงิน
        $byPaymentType = Receipt_School::whereDate('submission_date', $targetDate)
            ->where('type', '!=', 2)
            ->select('payment_type', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_type')
            ->get();

        // สรุปตามประเภทคอร์ส
        $byCourseType = Receipt_School::whereDate('submission_date', $targetDate)
            ->select('course_type', DB::raw('COUNT(*) as count'))
            ->groupBy('course_type')
            ->get();

        // รายละเอียดรายได้แต่ละรายการ
        $transactions = SchoolPrice::join('receipt_school', 'school_price.rid', '=', 'receipt_school.id')
            ->whereDate('receipt_school.submission_date', $targetDate)
            ->where('receipt_school.type', '!=', 2)
            ->select(
                'school_price.id',
                'school_price.receipt_number',
                'school_price.subject',
                'school_price.total',
                'school_price.price',
                'school_price.discount',
                'school_price.created_at',
                'receipt_school.first_name',
                'receipt_school.last_name',
                'receipt_school.payment_type',
                'receipt_school.course_type',
                'receipt_school.submission_date'
            )
            ->orderBy('receipt_school.submission_date', 'desc')
            ->get();

        // ตรวจสอบยอดเพิ่มเติมของวันก่อนหน้า (late transactions)
        $previous_day_additional = $this->CheckPreviousDayAdditional($targetDate);

        // สร้างข้อความสำหรับส่ง LINE
        $lineMessage = $this->GenerateLineMessage($targetDate, $incomeData, $receiptCount, $byPaymentType, $previous_day_additional);

        return response()->json([
            'success' => true,
            'date' => $targetDate->format('Y-m-d'),
            'date_thai' => $targetDate->locale('th')->translatedFormat('l j F Y'),
            'summary' => [
                'total_receipts' => $receiptCount,
                'total_transactions' => $incomeData->total_transactions ?? 0,
                'total_income' => number_format((float)($incomeData->total_income ?? 0), 2),
                'total_income_raw' => (float)($incomeData->total_income ?? 0),
                'total_price' => number_format((float)($incomeData->total_price ?? 0), 2),
                'total_discount' => number_format((float)($incomeData->total_discount ?? 0), 2),
                'total_entrance_fee' => number_format((float)($incomeData->total_entrance_fee ?? 0), 2),
                'total_book_price' => number_format((float)($incomeData->total_book_price ?? 0), 2),
                'total_media_price' => number_format((float)($incomeData->total_media_price ?? 0), 2),
            ],
            'previous_day_additional' => $previous_day_additional,
            'by_payment_type' => $byPaymentType,
            'by_course_type' => $byCourseType,
            'transactions' => $transactions,
            'line_message' => $lineMessage,
        ]);
    }

    /**
     * สรุปรายได้วันนี้ (shortcut)
     */
    public function todaySummary()
    {
        return $this->dailySummary(request()->merge(['date' => Carbon::today()->format('Y-m-d')]));
    }

    /**
     * สรุปรายได้เมื่อวาน
     */
    public function yesterdaySummary()
    {
        return $this->dailySummary(request()->merge(['date' => Carbon::yesterday()->format('Y-m-d')]));
    }

    /**
     * สรุปรายได้รายสัปดาห์
     */
    public function weeklySummary(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));

        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date format.',
            ], 400);
        }

        // รายได้รวมในช่วงสัปดาห์ (JOIN กับ receipt_school เพื่อใช้ submission_date)
        $weeklyIncome = SchoolPrice::join('receipt_school', 'school_price.rid', '=', 'receipt_school.id')
            ->whereBetween('receipt_school.submission_date', [$start->startOfDay(), $end->endOfDay()])
            ->where('receipt_school.type', '!=', 2)
            ->select(
                DB::raw('DATE(receipt_school.submission_date) as date'),
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(CAST(school_price.total AS DECIMAL(10,2))) as daily_income')
            )
            ->groupBy(DB::raw('DATE(receipt_school.submission_date)'))
            ->orderBy('date')
            ->get();

        $totalWeeklyIncome = $weeklyIncome->sum('daily_income');

        return response()->json([
            'success' => true,
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'daily_breakdown' => $weeklyIncome,
            'total_weekly_income' => number_format($totalWeeklyIncome, 2),
            'total_weekly_income_raw' => $totalWeeklyIncome,
        ]);
    }

    /**
     * สรุปรายได้รายเดือน
     */
    public function monthlySummary(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // รายได้รวมในเดือน (JOIN กับ receipt_school เพื่อใช้ submission_date)
        $monthlyIncome = SchoolPrice::join('receipt_school', 'school_price.rid', '=', 'receipt_school.id')
            ->whereBetween('receipt_school.submission_date', [$startDate, $endDate])
            ->where('receipt_school.type', '!=', 2)
            ->select(
                DB::raw('DATE(receipt_school.submission_date) as date'),
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(CAST(school_price.total AS DECIMAL(10,2))) as daily_income')
            )
            ->groupBy(DB::raw('DATE(receipt_school.submission_date)'))
            ->orderBy('date')
            ->get();

        $totalMonthlyIncome = $monthlyIncome->sum('daily_income');
        $totalTransactions = $monthlyIncome->sum('transactions');

        return response()->json([
            'success' => true,
            'year' => (int)$year,
            'month' => (int)$month,
            'month_name' => $startDate->locale('th')->translatedFormat('F'),
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'daily_breakdown' => $monthlyIncome,
            'total_transactions' => $totalTransactions,
            'total_monthly_income' => number_format($totalMonthlyIncome, 2),
            'total_monthly_income_raw' => $totalMonthlyIncome,
        ]);
    }

    /**
     * บันทึกยอดที่รายงานไปแล้ว
     * n8n เรียกหลังส่ง LINE สำเร็จ เพื่อบันทึกยอดที่รายงานแล้ว
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function SaveReport(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        try {
            $targetDate = Carbon::parse($date);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date format. Please use Y-m-d format.',
            ], 400);
        }

        // คำนวณยอดรวมปัจจุบันของวันที่ระบุ
        $current_income_data = SchoolPrice::join('receipt_school', 'school_price.rid', '=', 'receipt_school.id')
            ->whereDate('receipt_school.submission_date', $targetDate)
            ->where('receipt_school.type', '!=', 2)
            ->select(
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(CAST(school_price.total AS DECIMAL(10,2))) as total_income')
            )
            ->first();

        $current_receipts = Receipt_School::whereDate('submission_date', $targetDate)
            ->where('type', '!=', 2)
            ->count();

        // บันทึกหรืออัปเดตยอดที่รายงาน (upsert by report_date)
        DailyIncomeReport::updateOrCreate(
            ['report_date' => $targetDate->format('Y-m-d')],
            [
                'reported_income' => (float)($current_income_data->total_income ?? 0),
                'reported_transactions' => (int)($current_income_data->total_transactions ?? 0),
                'reported_receipts' => $current_receipts,
                'reported_at' => Carbon::now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'รายงานถูกบันทึกเรียบร้อยแล้ว',
            'saved_data' => [
                'date' => $targetDate->format('Y-m-d'),
                'income' => (float)($current_income_data->total_income ?? 0),
                'transactions' => (int)($current_income_data->total_transactions ?? 0),
                'receipts' => $current_receipts,
                'saved_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * ตรวจสอบยอดเพิ่มเติมของวันก่อนหน้า (late transactions)
     * เปรียบเทียบยอดปัจจุบันกับยอดที่รายงานไปแล้ว
     *
     * @param Carbon $targetDate วันที่ของรายงานปัจจุบัน (วันนี้)
     * @return array|null ข้อมูลยอดเพิ่มเติม หรือ null ถ้าไม่มี
     */
    private function CheckPreviousDayAdditional(Carbon $targetDate): ?array
    {
        $previous_date = $targetDate->copy()->subDay();

        // ดึง record ที่รายงานไปแล้วของวันก่อนหน้า
        $previous_report = DailyIncomeReport::where('report_date', $previous_date->format('Y-m-d'))->first();

        // ถ้าไม่เคยรายงานวันก่อนหน้า → ไม่มี additional
        if (!$previous_report) {
            return null;
        }

        // คำนวณยอดรวมปัจจุบันของวันก่อนหน้า (อาจมียอดเพิ่มหลังรายงาน)
        $current_income_data = SchoolPrice::join('receipt_school', 'school_price.rid', '=', 'receipt_school.id')
            ->whereDate('receipt_school.submission_date', $previous_date)
            ->where('receipt_school.type', '!=', 2)
            ->select(
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(CAST(school_price.total AS DECIMAL(10,2))) as total_income')
            )
            ->first();

        $current_receipts = Receipt_School::whereDate('submission_date', $previous_date)
            ->where('type', '!=', 2)
            ->count();

        $current_income = (float)($current_income_data->total_income ?? 0);
        $reported_income = (float)$previous_report->reported_income;
        $additional_income = round($current_income - $reported_income, 2);

        $current_transactions = (int)($current_income_data->total_transactions ?? 0);
        $additional_transactions = $current_transactions - $previous_report->reported_transactions;

        $additional_receipts = $current_receipts - $previous_report->reported_receipts;

        // ถ้าไม่มีส่วนต่าง → ไม่มี additional
        if ($additional_income <= 0 && $additional_transactions <= 0) {
            return null;
        }

        return [
            'has_additional' => true,
            'date' => $previous_date->format('Y-m-d'),
            'date_thai' => $previous_date->locale('th')->translatedFormat('l j F Y'),
            'reported_income' => number_format($reported_income, 2),
            'current_income' => number_format($current_income, 2),
            'additional_income' => number_format($additional_income, 2),
            'additional_income_raw' => $additional_income,
            'additional_transactions' => $additional_transactions,
            'additional_receipts' => $additional_receipts,
        ];
    }

    /**
     * สร้างข้อความสำหรับส่ง LINE
     * รวมยอดเพิ่มเติมของวันก่อนหน้า (ถ้ามี)
     *
     * @param Carbon $date
     * @param object $incomeData
     * @param int $receiptCount
     * @param \Illuminate\Support\Collection $byPaymentType
     * @param array|null $previous_day_additional
     * @return string
     */
    private function GenerateLineMessage($date, $incomeData, $receiptCount, $byPaymentType, $previous_day_additional = null)
    {
        $totalIncome = number_format((float)($incomeData->total_income ?? 0), 2);
        $dateThai = $date->locale('th')->translatedFormat('j F Y');

        $message = "📊 สรุปรายได้ประจำวัน\n";
        $message .= "📅 วันที่: {$dateThai}\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "💰 รายได้รวม: {$totalIncome} บาท\n";
        $message .= "🧾 จำนวนใบเสร็จ: {$receiptCount} ใบ\n";
        $message .= "📝 จำนวนรายการ: " . ($incomeData->total_transactions ?? 0) . " รายการ\n";

        if ($byPaymentType->count() > 0) {
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= "💳 แยกตามการชำระ:\n";
            foreach ($byPaymentType as $payment) {
                $paymentLabel = $this->GetPaymentTypeLabel($payment->payment_type);
                $message .= "  • {$paymentLabel}: {$payment->count} รายการ\n";
            }
        }

        // แสดงยอดเพิ่มเติมของวันก่อนหน้า (ถ้ามี)
        if ($previous_day_additional !== null && $previous_day_additional['has_additional']) {
            $message .= "\n━━━━━━━━━━━━━━━\n";
            $message .= "📢 ยอดเพิ่มเติมของ {$previous_day_additional['date_thai']}\n";
            $message .= "(หลังรายงานเมื่อวาน)\n";
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= "💰 ยอดเพิ่ม: {$previous_day_additional['additional_income']} บาท\n";
            $message .= "📝 รายการเพิ่ม: {$previous_day_additional['additional_transactions']} รายการ\n";
            $message .= "🧾 ใบเสร็จเพิ่ม: {$previous_day_additional['additional_receipts']} ใบ\n";
            $message .= "💰 ยอดรวมทั้งหมดของ {$previous_day_additional['date_thai']}: {$previous_day_additional['current_income']} บาท\n";
        }

        return $message;
    }

    /**
     * แปลงประเภทการชำระเงินเป็นข้อความ
     *
     * @param string|null $type
     * @return string
     */
    private function GetPaymentTypeLabel($type)
    {
        $labels = [
            'cash' => 'เงินสด',
            'transfer' => 'โอนเงิน',
            'credit' => 'บัตรเครดิต',
            'qr' => 'QR Code',
        ];

        return $labels[$type] ?? $type ?? 'ไม่ระบุ';
    }

    /**
     * จำลองดึงข้อมูล 3 วัน (24-26 ม.ค. 2026)
     */
    public function simulationTest()
    {
        $dates = ['2026-01-24', '2026-01-25', '2026-01-26'];
        $results = [];

        foreach ($dates as $date) {
            $request = new Request();
            $request->merge(['date' => $date]);

            $summaryResponse = $this->dailySummary($request);
            $data = $summaryResponse->getData(true);

            $results[] = [
                'date' => $date,
                'summary' => $data
            ];
        }

        return response()->json([
            'success' => true,
            'test_results' => $results
        ]);
    }
}
