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
        Schema::create('receipt_promotion_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_school_id')
                ->constrained('receipt_school')
                ->onDelete('cascade');
            $table->foreignId('promotion_campaign_id')
                ->nullable()
                ->constrained('promotion_campaigns')
                ->nullOnDelete();
            $table->string('promotion_name_snapshot');
            $table->decimal('discount_amount_snapshot', 15, 2)->default(0);
            $table->longText('reward_snapshot_json')->nullable();
            $table->longText('rule_snapshot_json')->nullable();
            $table->foreignId('selected_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('selected_at')->nullable();
            $table->timestamps();

            $table->unique(['receipt_school_id', 'promotion_campaign_id'], 'rpu_receipt_campaign_unique');
            $table->index('promotion_campaign_id');
            $table->index('selected_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receipt_promotion_usages');
    }
};
