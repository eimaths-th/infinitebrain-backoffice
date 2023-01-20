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
        //
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('submission_date')->nullable();;
            $table->string('first_name')->nullable();;
            $table->string('last_name')->nullable();;
            $table->text('street_address')->nullable();;
            $table->text('street_address2')->nullable();;
            $table->string('city')->nullable();;
            $table->string('province')->nullable();;
            $table->string('postcode')->nullable();;
            $table->string('name_parent')->nullable();;
            $table->string('mobile')->nullable();;
            $table->string('package')->nullable();;
            $table->string('file_att')->nullable();;
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
        //
        Schema::dropIfExists('receipts');
    }
};
