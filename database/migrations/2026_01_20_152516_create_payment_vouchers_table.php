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
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique()->comment('เลขที่ใบสำคัญจ่าย');
            $table->date('voucher_date')->comment('วันที่');
            $table->string('pay_to')->comment('จ่ายให้');
            $table->string('pay_to_address')->nullable()->comment('ที่อยู่ผู้รับ');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('รวมทั้งสิ้น');
            $table->text('remark')->nullable()->comment('หมายเหตุ');
            
            // Status: draft, pending, approved, rejected, reviewed, completed
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'reviewed', 'completed'])->default('draft');
            
            // ผู้จัดทำ (Finance)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_name')->nullable();
            
            // ผู้อนุมัติ (Approver)
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('approved_by_name')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approve_remark')->nullable();
            
            // ผู้ตรวจสอบ (Reviewer)
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('reviewed_by_name')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_remark')->nullable();
            
            // ผู้รับเงิน
            $table->string('receiver_name')->nullable()->comment('ผู้รับเงิน');
            $table->date('received_date')->nullable()->comment('วันที่รับเงิน');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_vouchers');
    }
};
