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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // ข้อมูล Snapshot ณ ขณะนั้น
            $table->string('employee_code')->nullable();
            $table->string('employee_name');
            $table->string('position')->nullable();
            $table->enum('employee_type', ['fulltime', 'parttime', 'contract']);
            $table->string('bank_account')->nullable();
            $table->string('bank_name')->nullable();

            // สรุปยอด
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('total_income', 12, 2)->default(0);
            $table->decimal('total_deduction', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);

            // Options สำหรับการคำนวณ
            $table->boolean('calc_social_security')->default(true);  // คำนวณ ปกส. หรือไม่
            $table->boolean('calc_tax')->default(true);              // คำนวณภาษีหรือไม่
            $table->decimal('social_security_rate', 5, 2)->default(5.00);  // อัตรา ปกส. %
            $table->decimal('social_security_cap', 12, 2)->default(17500); // เพดาน ปกส.
            $table->decimal('tax_rate', 5, 2)->default(3.00);        // อัตราภาษี %

            $table->text('remark')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'paid'])->default('draft');
            $table->timestamps();

            $table->unique(['payroll_period_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
