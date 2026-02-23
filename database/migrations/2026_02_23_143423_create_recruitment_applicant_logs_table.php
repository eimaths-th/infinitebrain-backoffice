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
        Schema::create('recruitment_applicant_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('recruitment_applicants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // Who performed the action
            $table->string('action'); // e.g., 'uploaded', 'reviewed', 'verified', 'rejected'
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_applicant_logs');
    }
};
