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
            $table->string('submission_date');
            $table->string('first_name');
            $table->string('last_name');
            $table->text('street_address');
            $table->text('street_address2');
            $table->string('city');
            $table->string('province');
            $table->string('postcode');
            $table->string('name_parent');
            $table->string('mobile');
            $table->string('package');
            $table->string('file_att');
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
