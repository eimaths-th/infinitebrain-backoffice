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
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->date('date')->comment('วันที่');
            $table->decimal('amount', 10, 2)->default(0)->comment('ยอดเงิน');
            $table->string('account_in')->nullable()->comment('บัญชีเข้า');
            $table->string('account_out')->nullable()->comment('บัญชีออก');
            $table->string('description')->nullable()->comment('รายการ');
            $table->string('received_from')->nullable()->comment('ได้รับจาก');
            $table->string('reference_no')->nullable()->comment('เลขที่');
            $table->string('paid_by')->nullable()->comment('ชำระโดย');
            $table->decimal('subtotal', 10, 2)->default(0)->comment('ยอดย่อย');
            $table->decimal('branch_eye_level_in', 10, 2)->default(0)->comment('อายเลเวล (เข้า)');
            $table->decimal('branch_eye_level_out', 10, 2)->default(0)->comment('อายเลเวล (ออก)');
            $table->decimal('branch_central_in', 10, 2)->default(0)->comment('ส่วนกลาง (เข้า)');
            $table->decimal('branch_central_out', 10, 2)->default(0)->comment('ส่วนกลาง (ออก)');
            $table->decimal('fee', 10, 2)->default(0)->comment('ธรรมเนียม');
            $table->decimal('tax', 10, 2)->default(0)->comment('ภาษี');
            $table->decimal('total_in', 10, 2)->default(0)->comment('รวมเข้า');
            $table->decimal('total_out', 10, 2)->default(0)->comment('รวมออก');
            $table->text('remark')->nullable()->comment('หมายเหตุ');
            $table->unsignedBigInteger('created_by')->nullable()->comment('ผู้สร้าง'); // Not strict FK to allow flexibility
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_statements');
    }
};
