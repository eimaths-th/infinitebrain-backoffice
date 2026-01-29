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
        Schema::create('payroll_deduction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->string('deduction_type');     // social_security, tax, loan, advance, other
            $table->string('description');        // คำอธิบาย
            $table->decimal('rate', 5, 2)->nullable();  // อัตรา %
            $table->decimal('amount', 12, 2);           // จำนวนเงิน
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_deduction_items');
    }
};
