<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('users');
                if (!array_key_exists('users_level_index', $indexes)) $table->index('level');
                if (!array_key_exists('users_status_index', $indexes)) $table->index('status');
            });
        }

        if (Schema::hasTable('parent_registers')) {
            Schema::table('parent_registers', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('parent_registers');
                if (!array_key_exists('parent_registers_pid_index', $indexes)) $table->index('pid');
            });
        }

        if (Schema::hasTable('receipts')) {
            Schema::table('receipts', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('receipts');
                if (!array_key_exists('receipts_receipt_number_index', $indexes)) $table->index('receipt_number');
                if (!array_key_exists('receipts_type_index', $indexes)) $table->index('type');
                if (!array_key_exists('receipts_ref_date_index', $indexes)) $table->index('ref_date');
            });
        }

        if (Schema::hasTable('receipt_details')) {
            Schema::table('receipt_details', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('receipt_details');
                if (!array_key_exists('receipt_details_receipt_id_index', $indexes)) $table->index('receipt_id');
            });
        }

        if (Schema::hasTable('payment_vouchers')) {
            Schema::table('payment_vouchers', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('payment_vouchers');
                if (!array_key_exists('payment_vouchers_status_index', $indexes)) $table->index('status');
                if (!array_key_exists('payment_vouchers_created_by_index', $indexes)) $table->index('created_by');
                if (!array_key_exists('payment_vouchers_branch_code_index', $indexes)) $table->index('branch_code');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['level']);
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('parent_registers')) {
            Schema::table('parent_registers', function (Blueprint $table) {
                $table->dropIndex(['pid']);
            });
        }

        if (Schema::hasTable('receipts')) {
            Schema::table('receipts', function (Blueprint $table) {
                $table->dropIndex(['receipt_number']);
                $table->dropIndex(['type']);
                $table->dropIndex(['ref_date']);
            });
        }

        if (Schema::hasTable('receipt_details')) {
            Schema::table('receipt_details', function (Blueprint $table) {
                $table->dropIndex(['receipt_id']);
            });
        }

        if (Schema::hasTable('payment_vouchers')) {
            Schema::table('payment_vouchers', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropIndex(['created_by']);
                $table->dropIndex(['branch_code']);
            });
        }
    }
};
