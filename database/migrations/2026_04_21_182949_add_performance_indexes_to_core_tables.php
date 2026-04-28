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
        // 1. ตาราง Statuslog
        if (Schema::hasTable('status_log')) {
            Schema::table('status_log', function (Blueprint $table) {
                $table->index('student_id');
                $table->index('status_date');
                $table->index(['student_id', 'group']);
            });
        }

        // 2. ตาราง SchoolPrice
        if (Schema::hasTable('school_price')) {
            Schema::table('school_price', function (Blueprint $table) {
                $table->index('student_id');
                $table->index('rid');
                $table->index('course_type');
            });
        }

        // 3. ตาราง Students
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (Schema::hasColumn('students', 'status')) {
                    $table->index('status');
                }
                if (Schema::hasColumn('students', 'pid')) {
                    $table->index('pid');
                }
            });
        }

        // 4. ตาราง Receipt_School
        if (Schema::hasTable('receipt_school')) {
            Schema::table('receipt_school', function (Blueprint $table) {
                if (Schema::hasColumn('receipt_school', 'pid')) {
                    $table->index('pid');
                }
                if (Schema::hasColumn('receipt_school', 'status')) {
                    $table->index('status');
                }
                if (Schema::hasColumn('receipt_school', 'receipt_date')) {
                    $table->index('receipt_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('status_log')) {
            Schema::table('status_log', function (Blueprint $table) {
                $table->dropIndex(['student_id']);
                $table->dropIndex(['status_date']);
                $table->dropIndex(['student_id', 'group']);
            });
        }

        if (Schema::hasTable('school_price')) {
            Schema::table('school_price', function (Blueprint $table) {
                $table->dropIndex(['student_id']);
                $table->dropIndex(['rid']);
                $table->dropIndex(['course_type']);
            });
        }

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropIndex(['pid']);
            });
        }

        if (Schema::hasTable('receipt_school')) {
            Schema::table('receipt_school', function (Blueprint $table) {
                $table->dropIndex(['pid']);
                $table->dropIndex(['status']);
                $table->dropIndex(['receipt_date']);
            });
        }
    }
};
