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
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('payee_id')->nullable()->after('pay_to_address')->comment('FK to payees table');
            $table->string('pay_to_account_no')->nullable()->after('pay_to_address')->comment('เลขที่บัญชีผู้รับเงิน');
            $table->string('pay_to_bank_name')->nullable()->after('pay_to_account_no')->comment('ชื่อธนาคาร');
            
            $table->index('payee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropIndex(['payee_id']);
            $table->dropColumn(['payee_id', 'pay_to_account_no', 'pay_to_bank_name']);
        });
    }
};
