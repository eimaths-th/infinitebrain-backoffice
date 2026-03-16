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
        Schema::create('receipt_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique()->comment('เลขที่ใบสำคัญรับ');
            $table->date('voucher_date')->comment('วันที่');
            $table->string('branch_code')->nullable()->comment('รหัสสาขา');
            $table->string('receive_from')->comment('รับจาก');
            $table->string('receive_from_address')->nullable()->comment('ที่อยู่ผู้จ่าย');
            $table->string('receive_from_account_no')->nullable()->comment('เลขที่บัญชีผู้จ่าย');
            $table->string('receive_from_bank_name')->nullable()->comment('ธนาคารผู้จ่าย');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('จำนวนเงินรวม');
            $table->decimal('total_deduction_amount', 15, 2)->default(0)->comment('ยอดรวมรายการหัก');
            $table->decimal('net_amount', 15, 2)->default(0)->comment('ยอดเงินสุทธิ');
            $table->text('remark')->nullable()->comment('หมายเหตุ');
            
            // Status: draft, pending, verified, completed, rejected
            $table->enum('status', ['draft', 'pending', 'verified', 'completed', 'rejected'])->default('draft');
            
            // ผู้จัดทำ
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_name')->nullable();
            
            // ผู้ตรวจสอบ (Verified)
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->string('verified_by_name')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('verify_remark')->nullable();
            
            // ผู้ทำเสร็จสิ้น (Completed)
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->string('completed_by_name')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('complete_remark')->nullable();
            
            // ข้อมูลการรับเงินจริง
            $table->string('receiver_name')->nullable()->comment('ชื่อผู้รับเงิน');
            $table->date('received_date')->nullable()->comment('วันที่รับเงินจริง');
            
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
        Schema::dropIfExists('receipt_vouchers');
    }
};
