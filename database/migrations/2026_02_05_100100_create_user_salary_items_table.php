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
        // สร้างตารางสำหรับเก็บรายการรายได้ของ User (สำหรับ Part-time)
        Schema::create('user_salary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('description');           // ชื่อรายการ เช่น "สอนคณิต ป.6"
            $table->decimal('rate', 12, 2);          // เรท เช่น 500 บาท/ชั่วโมง
            $table->string('unit')->default('ชม.'); // หน่วย: ชม., วัน, ครั้ง
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_salary_items');
    }
};
