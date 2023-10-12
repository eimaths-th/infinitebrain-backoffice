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
        Schema::table('opening_closing_times', function (Blueprint $table) {
            //
            $table->string('user_id')->nullable()->after('id');
            $table->string('comments')->nullable()->after('closing_time');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opening_closing_times', function (Blueprint $table) {
            //
        });
    }
};
