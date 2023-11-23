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
        Schema::create('reciept_img_reward', function (Blueprint $table) {
            $table->id();
            $table->string('reciept');
            $table->string('parent_fname');
            $table->string('parent_lname');
            $table->string('std_nick_name');
            $table->string('telp');
            $table->string('submission_date');
            $table->string('file_att');
            $table->string('receipt_number');
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
        Schema::dropIfExists('reciept_img_reward');
    }
};
