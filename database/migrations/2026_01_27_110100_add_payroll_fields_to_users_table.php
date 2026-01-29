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
        Schema::table('users', function (Blueprint $table) {
            // ข้อมูลพนักงานสำหรับ Payroll
            $table->string('nickname')->nullable()->after('name');              // ชื่อเล่น
            $table->enum('gender', ['male', 'female'])->nullable()->after('nickname'); // เพศ
            $table->string('nationality')->default('ไทย')->after('gender');     // สัญชาติ
            $table->date('birth_date')->nullable()->after('nationality');       // วันเกิด
            $table->date('start_date')->nullable()->after('address');           // วันเริ่มทำงาน
            $table->date('end_date')->nullable()->after('start_date');          // วันออก
            $table->enum('employee_type', ['fulltime', 'parttime', 'contract'])->default('fulltime')->after('end_date');
            $table->string('department')->nullable()->after('position');        // แผนก
            $table->decimal('base_salary', 12, 2)->default(0)->after('department'); // เงินเดือนฐาน
            $table->string('bank_account')->nullable()->after('base_salary');   // เลขบัญชี
            $table->string('bank_name')->nullable()->after('bank_account');     // ชื่อธนาคาร
            $table->string('tax_id', 20)->nullable()->after('bank_name');       // เลขผู้เสียภาษี
            $table->string('social_security_no', 20)->nullable()->after('tax_id'); // เลขประกันสังคม
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nickname',
                'gender',
                'nationality',
                'birth_date',
                'start_date',
                'end_date',
                'employee_type',
                'department',
                'base_salary',
                'bank_account',
                'bank_name',
                'tax_id',
                'social_security_no',
            ]);
        });
    }
};
