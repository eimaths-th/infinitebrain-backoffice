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
        Schema::table('receipt_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('payee_id')->nullable()->after('branch_code')->comment('ID ผู้รับเงิน/ผู้จ่ายเงิน');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receipt_vouchers', function (Blueprint $table) {
            $table->dropColumn('payee_id');
        });
    }
};
