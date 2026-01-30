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
        Schema::table('bank_statements', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_id')->nullable()->after('created_by')->comment('Linked Payroll ID');
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_statements', function (Blueprint $table) {
            $table->dropForeign(['payroll_id']);
            $table->dropColumn('payroll_id');
        });
    }
};
