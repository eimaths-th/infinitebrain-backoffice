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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_probation')->default(false)->after('employee_type');
            $table->date('probation_end_date')->nullable()->after('is_probation');
            $table->enum('payroll_frequency', ['monthly', 'weekly'])->default('monthly')->after('probation_end_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_probation', 'probation_end_date', 'payroll_frequency']);
        });
    }
};
