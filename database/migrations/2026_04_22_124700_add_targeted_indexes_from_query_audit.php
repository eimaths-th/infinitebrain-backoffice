<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('histories')) {
            Schema::table('histories', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('histories');

                if (!array_key_exists('histories_school_price_id_index', $indexes) && Schema::hasColumn('histories', 'school_price_id')) {
                    $table->index('school_price_id');
                }
                if (!array_key_exists('histories_subject_id_index', $indexes) && Schema::hasColumn('histories', 'subject_id')) {
                    $table->index('subject_id');
                }
                if (!array_key_exists('histories_status_index', $indexes) && Schema::hasColumn('histories', 'status')) {
                    $table->index('status');
                }
            });
        }

        if (Schema::hasTable('school_price')) {
            Schema::table('school_price', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('school_price');

                if (!array_key_exists('school_price_approv_id_index', $indexes) && Schema::hasColumn('school_price', 'approv_id')) {
                    $table->index('approv_id');
                }
                if (!array_key_exists('school_price_type_index', $indexes) && Schema::hasColumn('school_price', 'type')) {
                    $table->index('type');
                }
            });
        }

        if (Schema::hasTable('payment_vouchers')) {
            Schema::table('payment_vouchers', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('payment_vouchers');

                if (!array_key_exists('payment_vouchers_created_at_index', $indexes) && Schema::hasColumn('payment_vouchers', 'created_at')) {
                    $table->index('created_at');
                }
            });
        }

        if (Schema::hasTable('receipt_vouchers')) {
            Schema::table('receipt_vouchers', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('receipt_vouchers');

                if (!array_key_exists('receipt_vouchers_branch_code_index', $indexes) && Schema::hasColumn('receipt_vouchers', 'branch_code')) {
                    $table->index('branch_code');
                }
                if (!array_key_exists('receipt_vouchers_payee_id_index', $indexes) && Schema::hasColumn('receipt_vouchers', 'payee_id')) {
                    $table->index('payee_id');
                }
                if (!array_key_exists('receipt_vouchers_status_index', $indexes) && Schema::hasColumn('receipt_vouchers', 'status')) {
                    $table->index('status');
                }
                if (!array_key_exists('receipt_vouchers_branch_code_status_index', $indexes)
                    && Schema::hasColumn('receipt_vouchers', 'branch_code')
                    && Schema::hasColumn('receipt_vouchers', 'status')) {
                    $table->index(['branch_code', 'status']);
                }
            });
        }

        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('payrolls');

                if (!array_key_exists('payrolls_status_index', $indexes) && Schema::hasColumn('payrolls', 'status')) {
                    $table->index('status');
                }
            });
        }

        if (Schema::hasTable('payroll_periods')) {
            Schema::table('payroll_periods', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('payroll_periods');

                if (!array_key_exists('payroll_periods_status_index', $indexes) && Schema::hasColumn('payroll_periods', 'status')) {
                    $table->index('status');
                }
            });
        }

        if (Schema::hasTable('recruitment_applicants')) {
            Schema::table('recruitment_applicants', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('recruitment_applicants');

                if (!array_key_exists('recruitment_applicants_status_index', $indexes) && Schema::hasColumn('recruitment_applicants', 'status')) {
                    $table->index('status');
                }
                if (!array_key_exists('recruitment_applicants_status_created_at_index', $indexes)
                    && Schema::hasColumn('recruitment_applicants', 'status')
                    && Schema::hasColumn('recruitment_applicants', 'created_at')) {
                    $table->index(['status', 'created_at']);
                }
            });
        }

        if (Schema::hasTable('receipt_school')) {
            Schema::table('receipt_school', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('receipt_school');

                if (!array_key_exists('receipt_school_approv_id_index', $indexes) && Schema::hasColumn('receipt_school', 'approv_id')) {
                    $table->index('approv_id');
                }
            });
        }

        if (Schema::hasTable('worktime_logs')) {
            Schema::table('worktime_logs', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('worktime_logs');

                if (!array_key_exists('worktime_logs_approv_id_index', $indexes) && Schema::hasColumn('worktime_logs', 'approv_id')) {
                    $table->index('approv_id');
                }
            });
        }

        if (Schema::hasTable('opening_closing_times')) {
            Schema::table('opening_closing_times', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('opening_closing_times');

                if (!array_key_exists('opening_closing_times_user_id_index', $indexes) && Schema::hasColumn('opening_closing_times', 'user_id')) {
                    $table->index('user_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('histories')) {
            Schema::table('histories', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('histories');

                if (array_key_exists('histories_school_price_id_index', $indexes)) {
                    $table->dropIndex(['school_price_id']);
                }
                if (array_key_exists('histories_subject_id_index', $indexes)) {
                    $table->dropIndex(['subject_id']);
                }
                if (array_key_exists('histories_status_index', $indexes)) {
                    $table->dropIndex(['status']);
                }
            });
        }

        if (Schema::hasTable('school_price')) {
            Schema::table('school_price', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('school_price');

                if (array_key_exists('school_price_approv_id_index', $indexes)) {
                    $table->dropIndex(['approv_id']);
                }
                if (array_key_exists('school_price_type_index', $indexes)) {
                    $table->dropIndex(['type']);
                }
            });
        }

        if (Schema::hasTable('payment_vouchers')) {
            Schema::table('payment_vouchers', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('payment_vouchers');

                if (array_key_exists('payment_vouchers_created_at_index', $indexes)) {
                    $table->dropIndex(['created_at']);
                }
            });
        }

        if (Schema::hasTable('receipt_vouchers')) {
            Schema::table('receipt_vouchers', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('receipt_vouchers');

                if (array_key_exists('receipt_vouchers_branch_code_index', $indexes)) {
                    $table->dropIndex(['branch_code']);
                }
                if (array_key_exists('receipt_vouchers_payee_id_index', $indexes)) {
                    $table->dropIndex(['payee_id']);
                }
                if (array_key_exists('receipt_vouchers_status_index', $indexes)) {
                    $table->dropIndex(['status']);
                }
                if (array_key_exists('receipt_vouchers_branch_code_status_index', $indexes)) {
                    $table->dropIndex(['branch_code', 'status']);
                }
            });
        }

        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('payrolls');

                if (array_key_exists('payrolls_status_index', $indexes)) {
                    $table->dropIndex(['status']);
                }
            });
        }

        if (Schema::hasTable('payroll_periods')) {
            Schema::table('payroll_periods', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('payroll_periods');

                if (array_key_exists('payroll_periods_status_index', $indexes)) {
                    $table->dropIndex(['status']);
                }
            });
        }

        if (Schema::hasTable('recruitment_applicants')) {
            Schema::table('recruitment_applicants', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('recruitment_applicants');

                if (array_key_exists('recruitment_applicants_status_index', $indexes)) {
                    $table->dropIndex(['status']);
                }
                if (array_key_exists('recruitment_applicants_status_created_at_index', $indexes)) {
                    $table->dropIndex(['status', 'created_at']);
                }
            });
        }

        if (Schema::hasTable('receipt_school')) {
            Schema::table('receipt_school', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('receipt_school');

                if (array_key_exists('receipt_school_approv_id_index', $indexes)) {
                    $table->dropIndex(['approv_id']);
                }
            });
        }

        if (Schema::hasTable('worktime_logs')) {
            Schema::table('worktime_logs', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('worktime_logs');

                if (array_key_exists('worktime_logs_approv_id_index', $indexes)) {
                    $table->dropIndex(['approv_id']);
                }
            });
        }

        if (Schema::hasTable('opening_closing_times')) {
            Schema::table('opening_closing_times', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('opening_closing_times');

                if (array_key_exists('opening_closing_times_user_id_index', $indexes)) {
                    $table->dropIndex(['user_id']);
                }
            });
        }
    }
};
