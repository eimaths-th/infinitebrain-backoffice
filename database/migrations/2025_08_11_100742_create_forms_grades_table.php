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
        Schema::create('forms_grades', function (Blueprint $table) {
            $table->id();
            $table->string('form_id')->nullable();
            $table->string('sfield')->nullable();
            $table->string('form_name')->nullable();
            $table->string('form_route')->nullable();
            $table->string('grade')->nullable();
            $table->integer('create_by')->nullable();
            $table->integer('update_by')->nullable();

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
        Schema::dropIfExists('forms_grades');
    }
};
