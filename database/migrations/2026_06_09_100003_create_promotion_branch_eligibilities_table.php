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
        Schema::create('promotion_branch_eligibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_campaign_id')
                ->constrained('promotion_campaigns')
                ->onDelete('cascade');
            $table->foreignId('branch_id')
                ->constrained('branches')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['promotion_campaign_id', 'branch_id'], 'promotion_branch_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotion_branch_eligibilities');
    }
};
