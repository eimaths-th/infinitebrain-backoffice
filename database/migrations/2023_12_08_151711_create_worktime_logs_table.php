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
        Schema::create('worktime_logs', function (Blueprint $table) {
            $table->id();

            $table->integer('user_id')->nullable();
            $table->string('type_user')->nullable();
            $table->date('old_date')->nullable();
            $table->string('old_stime')->nullable();
            $table->string('old_etime')->nullable();
            $table->string('old_status')->nullable();
            $table->string('old_status_name')->nullable();

            $table->date('new_date')->nullable();
            $table->string('new_stime')->nullable();
            $table->string('new_etime')->nullable();
            $table->string('new_status')->nullable();
            $table->string('new_status_name')->nullable();

            $table->string('action')->nullable();
            $table->integer('approv_id')->nullable();
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
        Schema::dropIfExists('worktime_logs');
    }
};
