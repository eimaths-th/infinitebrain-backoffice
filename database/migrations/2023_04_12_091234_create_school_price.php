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
        Schema::create('school_price', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->nullable();
            $table->string('level')->nullable();
            $table->string('week')->nullable();
            $table->string('qty')->nullable();
            $table->string('book')->nullable();
            $table->string('bookprice')->nullable();
            $table->string('discount')->nullable();
            $table->string('price')->nullable();
            $table->string('total')->nullable();
            $table->string('rid')->nullable();

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
        Schema::dropIfExists('school_price');
    }
};
