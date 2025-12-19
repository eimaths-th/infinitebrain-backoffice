<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_disabled')->default(false)->after('wfh_days')->comment('พนักงานลาออก/ปิดใช้งาน');
            $table->date('disabled_at')->nullable()->after('is_disabled')->comment('วันที่ลาออก');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_disabled', 'disabled_at']);
        });
    }
};
