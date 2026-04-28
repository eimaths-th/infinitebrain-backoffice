<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * เพิ่ม field is_evaluation เพื่อ flag ว่าใบสำคัญจ่ายนี้เข้าใบประเมินหรือไม่
     */
    public function up(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->boolean('is_evaluation')->default(false)->after('is_salary')
                ->comment('Flag ว่าใบสำคัญจ่ายนี้เข้าใบประเมินหรือไม่');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('is_evaluation');
        });
    }
};
