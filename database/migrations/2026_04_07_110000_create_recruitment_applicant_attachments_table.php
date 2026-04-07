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
        Schema::create('recruitment_applicant_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('file_name');           // ชื่อไฟล์ในระบบ
            $table->string('original_name');       // ชื่อไฟล์เดิมที่อัปโหลด
            $table->string('file_path');           // พาธที่เก็บไฟล์
            $table->string('file_type')->nullable(); // ประเภทไฟล์ (MIME)
            $table->unsignedBigInteger('file_size')->nullable(); // ขนาดไฟล์ (Bytes)
            $table->foreignId('uploaded_by')->nullable()->constrained('users'); // ผู้ที่อัปโหลด
            $table->timestamps();

            // เชื่อมความสัมพันธ์กับตาราง recruitment_applicants
            $table->foreign('applicant_id')
                ->references('id')
                ->on('recruitment_applicants')
                ->onDelete('cascade');

            $table->index('applicant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_applicant_attachments');
    }
};
