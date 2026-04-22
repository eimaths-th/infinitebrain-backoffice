<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('record_time')) {
            Schema::table('record_time', function (Blueprint $table) {
                // ค้นหาประวัติการเข้างานของพนักงานแต่ละคน
                $table->index('user_id');
                // ค้นหาตามวันที่ / ช่วงเวลา
                $table->index('date');
                // ค้นหาประเภท หรือ สถานะการเข้างาน
                $table->index('status');
                // ดึงข้อมูลการเข้างานของพนักงาน ในช่วงวันที่กำหนด (เจอบ่อยสุดในการทำสรุปเงินเดือน/วันลา)
                $table->index(['user_id', 'date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('record_time')) {
            Schema::table('record_time', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropIndex(['date']);
                $table->dropIndex(['status']);
                $table->dropIndex(['user_id', 'date']);
            });
        }
    }
};
