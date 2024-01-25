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
        Schema::create('student_bonus', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->nullable();
            $table->integer('schoolp_id')->nullable();
            $table->string('bonus')->nullable();
            $table->string('detail')->nullable();
            $table->integer('approv_id')->nullable();

            // $table->integer('subject_id')->nullable();
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
        Schema::dropIfExists('student_bonus');
    }
};
