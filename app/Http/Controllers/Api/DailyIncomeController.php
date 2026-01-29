<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receipt_School;
use App\Models\SchoolPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyIncomeController extends Controller
{
    /**
     * สรุปรายได้รายวัน
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

        // ดึงข้อมูลรายได้จาก SchoolPrice ตามวันที่
        // ดึงข้อมูลรายได้จาก SchoolPrice ตามวันที่
        $incomeData = SchoolPrice::join('receipt_school', 'school_price.rid', '=', 'receipt_school.id')
            ->whereDate('school_price.created_at', $targetDate)
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

        // ดึงข้อมูลจำนวน Receipt ที่สร้างในวันนั้น
        // ดึงข้อมูลจำนวน Receipt ที่สร้างในวันนั้น
        $receiptCount = Receipt_School::whereDate('created_at', $targetDate)
            ->where('type', '!=', 2)
            ->count();

        // สรุปตามประเภทการชำระเงิน
        // สรุปตามประเภทการชำระเงิน
        $byPaymentType = Receipt_School::whereDate('created_at', $targetDate)
            ->where('type', '!=', 2)
            ->select('payment_type', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_type')
            ->get();

        // สรุปตามประเภทคอร์ส
        $byCourseType = Receipt_School::whereDate('created_at', $targetDate)
            ->select('course_type', DB::raw('COUNT(*) as count'))
            ->groupBy('course_type')
            ->get();

        // รายละเอียดรายได้แต่ละรายการ
        // รายละเอียดรายได้แต่ละรายการ
        $transactions = SchoolPrice::join('receipt_school', 'school_price.rid', '=', 'receipt_school.id')
            ->whereDate('school_price.created_at', $targetDate)
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
                'receipt_school.course_type'
            )
            ->orderBy('school_price.created_at', 'desc')
            ->get();

        // สร้างข้อความสำหรับส่ง LINE
        $lineMessage = $this->generateLineMessage($targetDate, $incomeData, $receiptCount, $byPaymentType);

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

        // รายได้รวมในช่วงสัปดาห์
        $weeklyIncome = SchoolPrice::whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(CAST(total AS DECIMAL(10,2))) as daily_income')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
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

        // รายได้รวมในเดือน
        $monthlyIncome = SchoolPrice::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(CAST(total AS DECIMAL(10,2))) as daily_income')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
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
     * สร้างข้อความสำหรับส่ง LINE
     */
    private function generateLineMessage($date, $incomeData, $receiptCount, $byPaymentType)
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
                $paymentLabel = $this->getPaymentTypeLabel($payment->payment_type);
                $message .= "  • {$paymentLabel}: {$payment->count} รายการ\n";
            }
        }

        return $message;
    }

    /**
     * แปลงประเภทการชำระเงินเป็นข้อความ
     */
    private function getPaymentTypeLabel($type)
    {
        $labels = [
            'cash' => 'เงินสด',
            'transfer' => 'โอนเงิน',
            'credit' => 'บัตรเครดิต',
            'qr' => 'QR Code',
            // เพิ่มเติมตามที่ใช้ในระบบ
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
