<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('wfh_days')->nullable()->after('days_off')->comment('วัน WFH ประจำสัปดาห์ เช่น 1,3 = จันทร์,พุธ');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wfh_days');
        });
    }
};
