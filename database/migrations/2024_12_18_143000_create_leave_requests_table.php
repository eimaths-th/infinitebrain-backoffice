<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->enum('leave_type', [
                'sick',           // ลาป่วย
                'personal',       // ลากิจ
                'maternity',      // ลาคลอด
                'ordination',     // ลาบวช
                'military',       // ลารับราชการทหาร
                'vacation',       // ลาพักผ่อน
                'compensate',     // ใช้วันหยุดชดเชย
                'swap_day',       // สลับวันหยุด
                'remote_work',    // ทำงานนอกสถานที่
            ])->comment('ประเภทการลา');

            $table->date('start_date')->comment('วันที่เริ่มลา');
            $table->date('end_date')->comment('วันที่สิ้นสุดลา');
            $table->decimal('total_days', 5, 1)->comment('จำนวนวันลา');

            $table->time('start_time')->nullable()->comment('เวลาเริ่ม (สำหรับทำงานนอกสถานที่)');
            $table->time('end_time')->nullable()->comment('เวลาสิ้นสุด (สำหรับทำงานนอกสถานที่)');

            $table->text('reason')->comment('เหตุผลการลา');
            $table->string('location')->nullable()->comment('สถานที่ (สำหรับทำงานนอกสถานที่)');
            $table->string('contact_phone')->nullable()->comment('เบอร์โทรติดต่อระหว่างลา');
            $table->text('contact_address')->nullable()->comment('ที่อยู่ติดต่อระหว่างลา');

            $table->date('compensate_for_date')->nullable()->comment('วันที่ต้องการใช้ชดเชย (สำหรับวันหยุดชดเชย)');
            $table->date('swap_from_date')->nullable()->comment('สลับจากวันที่ (สำหรับสลับวันหยุด)');

            $table->enum('half_day', ['none', 'morning', 'afternoon'])->default('none')->comment('ลาครึ่งวัน');

            $table->text('employee_signature')->nullable()->comment('ลายเซ็นพนักงาน (base64 หรือ path)');
            $table->boolean('signature_is_image')->default(false)->comment('ลายเซ็นเป็นรูปภาพ import');

            $table->enum('status', [
                'pending',    // รอพิจารณา
                'approved',   // อนุมัติ
                'rejected',   // ไม่อนุมัติ
            ])->default('pending')->comment('สถานะการอนุมัติ');

            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approver_signature')->nullable()->comment('ลายเซ็นผู้อนุมัติ');
            $table->text('rejection_reason')->nullable()->comment('เหตุผลไม่อนุมัติ');

            $table->text('notes')->nullable()->comment('หมายเหตุเพิ่มเติม');

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('leave_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
