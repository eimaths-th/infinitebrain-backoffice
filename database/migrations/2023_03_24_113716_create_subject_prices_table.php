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
        Schema::create('subject_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('subject_id')->nullable();
            $table->integer('level_id')->nullable();
            $table->integer('week')->nullable();
            $table->integer('old_price')->nullable();
            $table->integer('price')->nullable();
            $table->integer('book')->nullable();
            $table->integer('book_old_price')->nullable();
            $table->integer('book_price')->nullable();
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
        Schema::dropIfExists('subject_prices');
    }
};
