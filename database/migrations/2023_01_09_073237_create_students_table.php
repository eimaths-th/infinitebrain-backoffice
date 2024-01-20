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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('lastname');
            $table->string('nickname');
            $table->string('mobile');
            $table->string('grade');
            $table->timestamps();
        });

        // $table->string('first_name')->nullable();
        // $table->string('last_name')->nullable();
        // $table->string('nick_name')->nullable();
        // $table->string('school_name')->nullable();
        // $table->string('grade')->nullable();
        // $table->string('birth_date')->nullable();
        // $table->string('photo_att')->nullable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
};
