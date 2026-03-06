<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * สร้างตาราง daily_income_reports สำหรับเก็บยอดที่รายงานไปแล้ว
     * ใช้ตรวจสอบยอดเพิ่มเติม (late transactions) ในรายงานวันถัดไป
     */
    public function up(): void
    {
        Schema::create('daily_income_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->unique()->comment('วันที่ของรายงาน');
            $table->decimal('reported_income', 12, 2)->default(0)->comment('ยอดรายได้ที่รายงานไปแล้ว');
            $table->integer('reported_transactions')->default(0)->comment('จำนวนรายการที่รายงาน');
            $table->integer('reported_receipts')->default(0)->comment('จำนวนใบเสร็จที่รายงาน');
            $table->timestamp('reported_at')->nullable()->comment('เวลาที่รายงาน');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_income_reports');
    }
};
