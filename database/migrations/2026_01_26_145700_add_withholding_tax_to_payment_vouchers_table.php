<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * เพิ่มฟิลด์หัก ณ ที่จ่าย (Withholding Tax)
     */
    public function up(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            // อัตราภาษีหัก ณ ที่จ่าย (เช่น 1, 2, 3, 5)
            $table->decimal('withholding_tax_rate', 5, 2)->nullable()->default(0)->after('total_amount')->comment('อัตราภาษีหัก ณ ที่จ่าย (%)');

            // จำนวนเงินภาษีหัก ณ ที่จ่าย
            $table->decimal('withholding_tax_amount', 15, 2)->nullable()->default(0)->after('withholding_tax_rate')->comment('จำนวนเงินภาษีหัก ณ ที่จ่าย');

            // ยอดสุทธิหลังหักภาษี
            $table->decimal('net_amount', 15, 2)->nullable()->default(0)->after('withholding_tax_amount')->comment('ยอดสุทธิหลังหักภาษี');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropColumn(['withholding_tax_rate', 'withholding_tax_amount', 'net_amount']);
        });
    }
};
