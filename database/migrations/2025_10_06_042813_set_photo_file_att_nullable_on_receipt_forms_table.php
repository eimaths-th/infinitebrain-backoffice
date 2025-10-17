<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipt_forms', function (Blueprint $table) {
            $table->string('photo_att')->nullable()->default(null)->change();
            $table->longText('file_att')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('receipt_forms', function (Blueprint $table) {
            $table->string('photo_att')->nullable(false)->change();
            $table->longText('file_att')->nullable(false)->change();
        });
    }
};
