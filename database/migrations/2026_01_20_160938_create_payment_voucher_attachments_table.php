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
        Schema::create('payment_voucher_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_voucher_id')->nullable();
            $table->unsignedBigInteger('payment_voucher_item_id')->nullable();
            $table->string('attachment_type')->default('voucher'); // 'voucher' หรือ 'item'
            $table->string('file_name');           // ชื่อไฟล์ที่เก็บในระบบ
            $table->string('original_name');       // ชื่อไฟล์ต้นฉบับ
            $table->string('file_path');           // path ของไฟล์
            $table->string('file_type')->nullable(); // mime type
            $table->unsignedBigInteger('file_size')->nullable(); // ขนาดไฟล์ (bytes)
            $table->string('description')->nullable(); // คำอธิบายไฟล์
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('uploaded_by_name')->nullable();
            $table->timestamps();

            $table->foreign('payment_voucher_id')
                ->references('id')
                ->on('payment_vouchers')
                ->onDelete('cascade');

            $table->foreign('payment_voucher_item_id')
                ->references('id')
                ->on('payment_voucher_items')
                ->onDelete('cascade');

            $table->index(['payment_voucher_id', 'attachment_type'], 'pv_attach_voucher_type_idx');
            $table->index('payment_voucher_item_id', 'pv_attach_item_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_voucher_attachments');
    }
};
