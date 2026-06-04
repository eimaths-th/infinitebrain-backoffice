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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });

        Schema::table('receipt_school', function (Blueprint $table) {
            $table->unsignedBigInteger('promotion_id')->nullable()->after('remark')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receipt_school', function (Blueprint $table) {
            $table->dropIndex(['promotion_id']);
            $table->dropColumn('promotion_id');
        });

        Schema::dropIfExists('promotions');
    }
};
