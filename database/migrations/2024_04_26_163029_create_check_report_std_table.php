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
        Schema::create('check_report_std', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('receipt_school_id');
            $table->string('school_price_id');
            $table->string('check');
            $table->string('date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('check_report_std');
    }
};
