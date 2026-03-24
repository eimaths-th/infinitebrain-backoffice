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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('branch_groups')->onDelete('cascade');
            $table->string('code', 20)->unique()->comment('รหัสสาขา เช่น EL, IF, EiRP');
            $table->string('name', 100)->comment('ชื่อสาขา');
            $table->string('rv_prefix', 20)->nullable()->comment('Prefix ใบสำคัญรับ (เช่น ELRV)');
            $table->string('pv_prefix', 20)->nullable()->comment('Prefix ใบสำคัญจ่าย (เช่น ELPV)');
            $table->string('bf_prefix', 20)->nullable()->comment('Prefix ค่าธรรมเนียมธนาคาร (เช่น ELBF)');
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('branches');
    }
};
