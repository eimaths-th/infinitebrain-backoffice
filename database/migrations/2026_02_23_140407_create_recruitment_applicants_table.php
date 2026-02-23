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
        Schema::create('recruitment_applicants', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position_applied')->nullable();
            $table->string('resume_path')->nullable(); // PDF File path
            $table->enum('status', ['pending', 'reviewed', 'verified', 'rejected'])->default('pending');
            $table->enum('follow_up', ['yes', 'no', 'undecided'])->default('undecided');
            $table->text('reviewer_note')->nullable();
            $table->text('manager_note')->nullable();
            $table->foreignId('admin_id')->constrained('users'); // Who uploaded
            $table->foreignId('reviewer_id')->nullable()->constrained('users'); // Who reviewed
            $table->foreignId('manager_id')->nullable()->constrained('users'); // Who verified
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_applicants');
    }
};
