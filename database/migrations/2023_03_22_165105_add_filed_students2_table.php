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
        Schema::table('students', function (Blueprint $table) {
            //
            $table->renameColumn('name','first_name');
            $table->renameColumn('lastname','last_name');
            $table->renameColumn('nickname','nick_name');

             // $table->string('first_name')->after('name');
        // $table->string('last_name')->after('lastname');
        // $table->string('nick_name')->after('nick');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            //
        });
    }
};
