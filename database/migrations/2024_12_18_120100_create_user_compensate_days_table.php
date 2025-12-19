<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_compensate_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('holiday_id')->constrained('company_holidays')->onDelete('cascade');
            $table->date('holiday_date')->comment('วันหยุดบริษัทที่ทับซ้อน');
            $table->boolean('is_used')->default(false)->comment('ใช้สิทธิ์แล้วหรือยัง');
            $table->date('used_date')->nullable()->comment('วันที่ใช้สิทธิ์');
            $table->year('year')->comment('ปี');
            $table->timestamps();

            $table->unique(['user_id', 'holiday_id']);
            $table->index(['user_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_compensate_days');
    }
};
