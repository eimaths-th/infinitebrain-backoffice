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
        Schema::create('lotto_results', function (Blueprint $table) {
            $table->id();
            $table->string('date');
            $table->string('1st');
            $table->string('3front_1');
            $table->string('3front_2');
            $table->string('3back_1');
            $table->string('3back_2');
            $table->string('2numbers');
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
        Schema::dropIfExists('lotto_results');
    }
};
