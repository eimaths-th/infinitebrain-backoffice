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
        Schema::create('lotto_report', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('numbers')->nullable();
            $table->string('type')->nullable();
            $table->string('bet')->nullable();
            $table->string('status')->nullable();
            $table->string('result')->nullable();
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
        Schema::dropIfExists('lotto_report');
    }
};
