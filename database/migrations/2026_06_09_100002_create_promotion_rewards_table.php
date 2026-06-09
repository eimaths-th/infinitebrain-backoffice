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
        Schema::create('promotion_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_campaign_id')
                ->constrained('promotion_campaigns')
                ->onDelete('cascade');
            $table->string('reward_type', 100)->index();
            $table->decimal('discount_amount', 15, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            $table->longText('reward_config_json')->nullable();
            $table->timestamps();

            $table->index(['promotion_campaign_id', 'reward_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotion_rewards');
    }
};
