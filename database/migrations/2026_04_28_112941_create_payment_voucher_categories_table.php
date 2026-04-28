<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_voucher_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('รหัสหมวดหมู่ (ภาษาอังกฤษ, ไม่เว้นวรรค) เช่น shop_rent');
            $table->string('label')->comment('ชื่อหมวดหมู่ (แสดงผล) เช่น ค่าเช่าร้าน');
            $table->boolean('is_active')->default(true)->comment('สถานะการใช้งาน 1=เปิด, 0=ปิด');
            $table->integer('sort_order')->default(0)->comment('ลำดับการแสดงผล');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_voucher_categories');
    }
};
