<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('signature')->nullable()->after('disabled_at')->comment('ลายเซ็นของ user (base64 หรือ path)');
            $table->boolean('signature_is_image')->default(false)->after('signature')->comment('ลายเซ็นเป็นรูปภาพ import');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['signature', 'signature_is_image']);
        });
    }
};
