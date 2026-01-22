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
        Schema::create('payment_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_voucher_id');
            $table->integer('item_no')->comment('ลำดับที่');
            $table->string('description')->comment('รายการ');
            $table->decimal('amount', 15, 2)->default(0)->comment('จำนวนเงิน');
            $table->timestamps();
            
            $table->foreign('payment_voucher_id')
                ->references('id')
                ->on('payment_vouchers')
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
        Schema::dropIfExists('payment_voucher_items');
    }
};
