<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model สำหรับตาราง daily_income_reports
 * เก็บข้อมูลยอดรายได้ที่ถูกรายงานไปแล้วในแต่ละวัน
 * ใช้เปรียบเทียบกับยอดปัจจุบันเพื่อตรวจจับ late transactions
 *
 * @property int $id
 * @property string $report_date
 * @property float $reported_income
 * @property int $reported_transactions
 * @property int $reported_receipts
 * @property string|null $reported_at
 */
class DailyIncomeReport extends Model
{
    /**
     * ชื่อตารางในฐานข้อมูล
     *
     * @var string
     */
    protected $table = 'daily_income_reports';

    /**
     * คอลัมน์ที่อนุญาตให้ mass assignment
     *
     * @var array<string>
     */
    protected $fillable = [
        'report_date',
        'reported_income',
        'reported_transactions',
        'reported_receipts',
        'reported_at',
    ];

    /**
     * กำหนด type casting สำหรับ attributes
     *
     * @var array<string, string>
     */
    protected $casts = [
        'report_date' => 'date',
        'reported_income' => 'decimal:2',
        'reported_transactions' => 'integer',
        'reported_receipts' => 'integer',
        'reported_at' => 'datetime',
    ];
}
