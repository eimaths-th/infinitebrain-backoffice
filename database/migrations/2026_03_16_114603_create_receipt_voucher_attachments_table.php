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
        Schema::create('receipt_voucher_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receipt_voucher_id');
            $table->unsignedBigInteger('receipt_voucher_item_id')->nullable();
            $table->string('attachment_type', 50)->default('voucher')->comment('voucher, item');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('uploaded_by_name')->nullable();
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
        Schema::dropIfExists('receipt_voucher_attachments');
    }
};
