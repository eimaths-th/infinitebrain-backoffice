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
        if (!Schema::hasColumn('receipt_school', 'referent_parent_name')) {
            Schema::table('receipt_school', function (Blueprint $table) {
                $table->string('referent_parent_name')->nullable()->after('referent_parent_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('receipt_school', 'referent_parent_name')) {
            Schema::table('receipt_school', function (Blueprint $table) {
                $table->dropColumn('referent_parent_name');
            });
        }
    }
};
