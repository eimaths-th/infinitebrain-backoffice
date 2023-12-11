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
        Schema::table('worktime_logs', function (Blueprint $table) {
            //
            $table->string('old_explain')->nullable()->after('old_status_name');
            $table->string('new_explain')->nullable()->after('new_status_name');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('worktime_logs', function (Blueprint $table) {
            //
        });
    }
};
