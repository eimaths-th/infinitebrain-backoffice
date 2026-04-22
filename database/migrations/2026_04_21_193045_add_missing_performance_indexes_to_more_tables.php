<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('attendance')) {
            Schema::table('attendance', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('attendance');
                if (!array_key_exists('attendance_employee_id_index', $indexes) && Schema::hasColumn('attendance', 'employee_id')) $table->index('employee_id');
                if (!array_key_exists('attendance_date_index', $indexes) && Schema::hasColumn('attendance', 'date')) $table->index('date');
            });
        }
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('courses');
                if (!array_key_exists('courses_student_id_index', $indexes) && Schema::hasColumn('courses', 'student_id')) $table->index('student_id');
                if (!array_key_exists('courses_subject_id_index', $indexes) && Schema::hasColumn('courses', 'subject_id')) $table->index('subject_id');
                if (!array_key_exists('courses_level_id_index', $indexes) && Schema::hasColumn('courses', 'level_id')) $table->index('level_id');
            });
        }
        if (Schema::hasTable('paper_reports')) {
            Schema::table('paper_reports', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('paper_reports');
                if (!array_key_exists('paper_reports_std_id_index', $indexes) && Schema::hasColumn('paper_reports', 'std_id')) $table->index('std_id');
                if (!array_key_exists('paper_reports_rec_id_index', $indexes) && Schema::hasColumn('paper_reports', 'rec_id')) $table->index('rec_id');
                if (!array_key_exists('paper_reports_schp_id_index', $indexes) && Schema::hasColumn('paper_reports', 'schp_id')) $table->index('schp_id');
            });
        }
        if (Schema::hasTable('status_log')) {
            Schema::table('status_log', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('status_log');
                if (!array_key_exists('status_log_schoolprice_id_index', $indexes) && Schema::hasColumn('status_log', 'schoolprice_id')) $table->index('schoolprice_id');
                if (!array_key_exists('status_log_status_index', $indexes) && Schema::hasColumn('status_log', 'status')) $table->index('status');
            });
        }
        if (Schema::hasTable('student_bonus')) {
            Schema::table('student_bonus', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('student_bonus');
                if (!array_key_exists('student_bonus_student_id_index', $indexes) && Schema::hasColumn('student_bonus', 'student_id')) $table->index('student_id');
                if (!array_key_exists('student_bonus_schoolp_id_index', $indexes) && Schema::hasColumn('student_bonus', 'schoolp_id')) $table->index('schoolp_id');
            });
        }
        if (Schema::hasTable('worktime_logs')) {
            Schema::table('worktime_logs', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('worktime_logs');
                if (!array_key_exists('worktime_logs_user_id_index', $indexes) && Schema::hasColumn('worktime_logs', 'user_id')) $table->index('user_id');
                if (!array_key_exists('worktime_logs_rectime_id_index', $indexes) && Schema::hasColumn('worktime_logs', 'rectime_id')) $table->index('rectime_id');
            });
        }
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('students');
                if (!array_key_exists('students_code_index', $indexes) && Schema::hasColumn('students', 'code')) $table->index('code');
            });
        }
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('users');
                if (!array_key_exists('users_code_index', $indexes) && Schema::hasColumn('users', 'code')) $table->index('code');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('attendance')) Schema::table('attendance', function (Blueprint $table) { $table->dropIndex(['employee_id']); $table->dropIndex(['date']); });
        if (Schema::hasTable('courses')) Schema::table('courses', function (Blueprint $table) { $table->dropIndex(['student_id']); $table->dropIndex(['subject_id']); $table->dropIndex(['level_id']); });
        if (Schema::hasTable('paper_reports')) Schema::table('paper_reports', function (Blueprint $table) { $table->dropIndex(['std_id']); $table->dropIndex(['rec_id']); $table->dropIndex(['schp_id']); });
        if (Schema::hasTable('status_log')) Schema::table('status_log', function (Blueprint $table) { $table->dropIndex(['schoolprice_id']); $table->dropIndex(['status']); });
        if (Schema::hasTable('student_bonus')) Schema::table('student_bonus', function (Blueprint $table) { $table->dropIndex(['student_id']); $table->dropIndex(['schoolp_id']); });
        if (Schema::hasTable('worktime_logs')) Schema::table('worktime_logs', function (Blueprint $table) { $table->dropIndex(['user_id']); $table->dropIndex(['rectime_id']); });
        if (Schema::hasTable('students')) Schema::table('students', function (Blueprint $table) { $table->dropIndex(['code']); });
        if (Schema::hasTable('users')) Schema::table('users', function (Blueprint $table) { $table->dropIndex(['code']); });
    }
};
