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
        Schema::create('receipt_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receipt_voucher_id');
            $table->string('type', 20)->default('item')->comment('item = รายการรับ, deduction = รายการหัก');
            $table->integer('item_no')->comment('ลำดับที่');
            $table->string('description')->comment('รายการ');
            $table->decimal('amount', 15, 2)->default(0)->comment('จำนวนเงิน');
            $table->timestamps();
            
            $table->foreign('receipt_voucher_id')
                ->references('id')
                ->on('receipt_vouchers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receipt_voucher_items');
    }
};
