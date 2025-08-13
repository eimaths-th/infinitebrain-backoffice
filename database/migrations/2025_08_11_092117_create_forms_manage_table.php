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
        Schema::create('forms_manage', function (Blueprint $table) {
            $table->id();
            $table->string('sfield')->nullable();
            $table->string('form_name')->nullable();
            $table->string('form_route')->nullable();

            //edit html
            $table->string('brand')->nullable();
            $table->string('title')->nullable();
            $table->string('sub_title')->nullable();
            $table->string('head_title')->nullable();
            $table->string('registration_date_label')->nullable()->default('รับสมัคร');
            $table->string('contest_date_label')->nullable()->default('สอบวันที่');
            $table->string('registration_fee_label')->nullable()->default('ค่าสมัคร');
            $table->string('admission_criteria_text_label')->nullable()->default('เกณฑ์การรับสมัคร');
            $table->string('admission_criteria_image_label')->nullable()->default('เกณฑ์การเทียบระดับชั้น');

            $table->string('registration_date')->nullable();
            $table->string('contest_date')->nullable();
            $table->string('registration_fee')->nullable();
            $table->text('admission_criteria_text')->nullable();
            $table->string('admission_criteria_image')->nullable();
            $table->string('image_payment')->nullable();
            $table->text('text_footer')->nullable()->default('** กรุณาตรวจสอบข้อมูลให้ถูกต้อง ก่อนทำการชำระเงินค่าสมัครสอบ** หากตรวจสอบข้อมูล และ พบว่าผู้สมัครไม่มีสิทธิ์เข้าสอบตามกฎ ของสนามสอบ ** **ทางบริษัทจะมีค่าดำเนินการทางบัญชี 100 บาท ในกรณีที่ต้องโอนค่าสมัครคืน**');
            $table->text('text_warning')->nullable()->default('** หากสมัครสำเร็จจะได้รับอีเมลตอบกลับทันที กรณีไม่ได้รับอีเมลตอบกลับ รบกวนทำการสมัครใหม่อีกครั้ง หรือทำการติดต่อเจ้าหน้าที่ **');
            $table->text('text_policy')->nullable()->default('ตกลงและยอมรับข้อกำหนดและเงื่อนไข รวมถึงตรวจสอบข้อมูลและเนื้อหาต่างๆเรียบร้อยแล้ว');


            //forms
            $table->string('student_first_name')->nullable()->default('Name (English) / ชื่อ (ภาษาอังกฤษ)');
            $table->string('student_last_name')->nullable()->default('Surname (English) / นามสกุล (ภาษาอังกฤษ)');
            $table->string('student_nick_name')->nullable()->default('Nickname (English) / ชื่อเล่น (ภาษาอังกฤษ)');
            $table->string('date_of_birth')->nullable()->default('Date of Birth / วันเกิด');
            $table->string('student_school_name')->nullable()->default('School Name (English) / ชื่อโรงเรียน (ภาษาอังกฤษ)');
            $table->string('gender')->nullable()->default('Gender / เพศ');
            $table->string('student_grade')->nullable()->default('Grade / ชั้นปี');
            $table->string('package')->nullable()->default('Packages / แพคเกจในการสอบ');
            $table->string('student_address')->nullable()->default('Address / ที่อยู่ภาษาไทยสำหรับจัดส่งของรางวัล');
            $table->string('student_zip')->nullable()->default('Zip Code / รหัสไปรษณีย์ภาษาไทยสำหรับจัดส่งของรางวัล');
            $table->string('parent_name')->nullable()->default('Parent Name / ชื่อผู้ปกครอง');
            $table->string('parent_phone')->nullable()->default('Parent Phone / เบอร์โทรศัพท์ผู้ปกครอง');
            $table->string('contact_email')->nullable()->default('E-mail');
            $table->text('contact_email_detail')->nullable()->default('(กรุณาตรวจสอบที่อยู่อีเมลให้ถูกต้อง หากกรอกผิด จะไม่สามารถส่งข้อมูลในการเข้าสอบได้)');
            $table->string('student_photo')->nullable()->default('อัพโหลดรูปภาพผู้สมัคร');
            $table->string('student_slip_photo')->nullable()->default('อัพโหลดหลักฐานการชำระเงิน');
            $table->text('news')->nullable()->default('ท่านทราบข่าวสารการจัดสอบ ชื่อสนาม จากช่องทางใด');
            $table->integer('create_by')->nullable();
            $table->integer('update_by')->nullable();

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
        Schema::dropIfExists('forms_manage');
    }
};
