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
        Schema::create('payroll_income_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->string('income_type');       // salary, ot, travel, commission, bonus, other
            $table->string('description');       // คำอธิบาย
            $table->decimal('quantity', 10, 2)->default(1);  // จำนวน (ชม./วัน)
            $table->decimal('rate', 12, 2)->default(0);      // อัตรา
            $table->decimal('amount', 12, 2);                // จำนวนเงิน
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_income_items');
    }
};
