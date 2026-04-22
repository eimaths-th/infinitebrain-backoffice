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
        if (Schema::hasTable('histories')) {
            Schema::table('histories', function (Blueprint $table) {
                $table->index('student_id'); // สำหรับดึงประวัติของนักเรียนแต่ละคน

                // ตรวจสอบว่ามีคอลัมน์ user_id (พนักงาน/แอดมินที่ทำรายการ) ค่อยสร้าง
                if (Schema::hasColumn('histories', 'user_id')) {
                    $table->index('user_id');
                }

                $table->index('datetime'); // สำหรับการจัดเรียงหรือค้นหาตามวันที่
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
        if (Schema::hasTable('histories')) {
            Schema::table('histories', function (Blueprint $table) {
                $table->dropIndex(['student_id']);

                if (Schema::hasColumn('histories', 'user_id')) {
                    $table->dropIndex(['user_id']);
                }

                $table->dropIndex(['datetime']);
            });
        }
    }
};
