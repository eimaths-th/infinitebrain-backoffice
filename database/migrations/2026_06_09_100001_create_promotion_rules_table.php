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
        Schema::create('promotion_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_campaign_id')
                ->constrained('promotion_campaigns')
                ->onDelete('cascade');
            $table->string('rule_type', 100)->index();
            $table->longText('rule_config_json')->nullable();
            $table->timestamps();

            $table->index(['promotion_campaign_id', 'rule_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotion_rules');
    }
};
