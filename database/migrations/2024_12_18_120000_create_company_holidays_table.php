<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique()->comment('วันที่หยุด');
            $table->string('name')->comment('ชื่อวันหยุด');
            $table->year('year')->comment('ปี');
            $table->timestamps();

            $table->index('year');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_holidays');
    }
};
