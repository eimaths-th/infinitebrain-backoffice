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
        Schema::create('record_time', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('type_user')->nullable();
            $table->date('date')->nullable();
            $table->string('stime')->nullable();
            $table->string('etime')->nullable();
            $table->string('status')->nullable();

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
        Schema::dropIfExists('record_time');
    }
};
