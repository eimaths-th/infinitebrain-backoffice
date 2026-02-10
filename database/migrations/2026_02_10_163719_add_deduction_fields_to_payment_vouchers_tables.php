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
        // เพิ่ม type column ใน payment_voucher_items เพื่อแยกรายการเบิก/หัก
        Schema::table('payment_voucher_items', function (Blueprint $table) {
            $table->string('type', 20)->default('item')->after('payment_voucher_id')
                ->comment('item = รายการเบิก, deduction = รายการหัก');
        });

        // เพิ่ม total_deduction_amount ใน payment_vouchers
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->decimal('total_deduction_amount', 12, 2)->default(0)->after('total_amount')
                ->comment('ยอดรวมรายการหัก');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_voucher_items', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('total_deduction_amount');
        });
    }
};
