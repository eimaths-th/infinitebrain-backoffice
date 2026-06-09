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
        Schema::table('promotion_campaigns', function (Blueprint $table) {
            $table->foreignId('promotion_id')
                ->nullable()
                ->after('id')
                ->constrained('promotions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promotion_campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
        });
    }
};
