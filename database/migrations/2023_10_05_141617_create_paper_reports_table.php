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
        Schema::create('paper_reports', function (Blueprint $table) {
            $table->id();
            $table->string('no')->nullable();
            $table->integer('std_id')->nullable();
            $table->integer('rec_id')->nullable();
            $table->integer('schp_id')->nullable();
            $table->string('receipt_number')->nullable();
            $table->date('submission_date')->nullable();
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
        Schema::dropIfExists('paper_reports');
    }
};
