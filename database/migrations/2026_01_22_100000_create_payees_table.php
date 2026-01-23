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
        Schema::create('payees', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('ชื่อผู้รับเงิน/บริษัท');
            $table->text('address')->nullable()->comment('ที่อยู่');
            $table->string('account_no')->nullable()->comment('เลขที่บัญชีธนาคาร');
            $table->string('bank_name')->nullable()->comment('ชื่อธนาคาร');
            $table->string('tax_id')->nullable()->comment('เลขประจำตัวผู้เสียภาษี');
            $table->string('phone')->nullable()->comment('เบอร์โทร');
            $table->string('email')->nullable()->comment('อีเมล');
            $table->boolean('is_active')->default(true)->comment('สถานะใช้งาน');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->index('name');
            $table->index('account_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payees');
    }
};
