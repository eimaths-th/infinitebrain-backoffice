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
        Schema::create('app_download', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('file_name')->nullable();
            $table->string('type')->nullable();
            $table->string('version')->nullable();
            $table->string('upload_by')->nullable();
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
        Schema::dropIfExists('app_download');
    }
};
