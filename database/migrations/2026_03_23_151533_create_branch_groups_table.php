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
        Schema::create('branch_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('รหัสกลุ่มสาขา เช่น EL, IF');
            $table->string('name', 100)->comment('ชื่อกลุ่มสาขา');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('branch_groups');
    }
};
