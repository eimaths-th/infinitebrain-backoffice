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
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_code', 20)->unique();      // เช่น 2569-01
            $table->string('period_name');                    // มกราคม 2569
            $table->integer('year');                          // ปี พ.ศ.
            $table->integer('month');                         // เดือน 1-12
            $table->date('start_date');                       // วันเริ่มงวด
            $table->date('end_date');                         // วันสิ้นสุดงวด
            $table->date('payment_date')->nullable();         // วันจ่ายเงิน
            $table->enum('status', ['draft', 'processing', 'approved', 'paid'])->default('draft');
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('total_net_pay', 15, 2)->default(0);
            $table->integer('employee_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->unique(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
