<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // สร้างตาราง user_tags สำหรับเก็บ tags
        Schema::create('user_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // ชื่อ tag เช่น "สอนคณิต", "สอนวิทย์"
            $table->string('color')->nullable(); // สี badge (hex หรือ class name)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // สร้าง pivot table สำหรับ many-to-many relationship
        Schema::create('user_tag_pivot', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_tag_id')->constrained()->onDelete('cascade');
            $table->primary(['user_id', 'user_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tag_pivot');
        Schema::dropIfExists('user_tags');
    }
};
