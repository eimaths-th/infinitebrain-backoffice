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
        Schema::table('forms_manage', function (Blueprint $table) {
            $table->string('ref_date_label')->nullable()->after('registration_fee_label')->default('วันที่อ้างอิง');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms_manage', function (Blueprint $table) {
            $table->dropColumn('ref_date_label');
        });
    }
};
