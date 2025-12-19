<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('days_off')->nullable()->after('fingerprint_id')->comment('วันหยุดประจำสัปดาห์ เช่น 0,6 = อาทิตย์,เสาร์');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('days_off');
        });
    }
};
