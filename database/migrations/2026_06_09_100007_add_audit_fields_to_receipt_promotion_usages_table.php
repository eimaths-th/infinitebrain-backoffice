<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('receipt_promotion_usages', function (Blueprint $table) {
            $table->decimal('expected_discount_amount_snapshot', 15, 2)->default(0)->after('discount_amount_snapshot');
            $table->decimal('actual_discount_amount_snapshot', 15, 2)->default(0)->after('expected_discount_amount_snapshot');
            $table->decimal('expected_net_amount_snapshot', 15, 2)->default(0)->after('actual_discount_amount_snapshot');
            $table->decimal('actual_net_amount_snapshot', 15, 2)->default(0)->after('expected_net_amount_snapshot');
            $table->string('match_status', 30)->default('NOT_APPLIED')->after('actual_net_amount_snapshot');
            $table->string('mismatch_reason')->nullable()->after('match_status');
        });
    }

    public function down()
    {
        Schema::table('receipt_promotion_usages', function (Blueprint $table) {
            $table->dropColumn([
                'expected_discount_amount_snapshot',
                'actual_discount_amount_snapshot',
                'expected_net_amount_snapshot',
                'actual_net_amount_snapshot',
                'match_status',
                'mismatch_reason',
            ]);
        });
    }
};
