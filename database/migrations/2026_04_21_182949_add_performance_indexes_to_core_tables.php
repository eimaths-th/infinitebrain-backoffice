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
        $indexes = [
            'status_log' => [
                'student_id',
                'status_date',
                ['student_id', 'group']
            ],
            'school_price' => [
                'student_id',
                'rid',
                'course_type'
            ],
            'students' => [
                'status',
                'pid'
            ],
            'receipt_school' => [
                'pid',
                'status',
                'receipt_date'
            ]
        ];

        foreach ($indexes as $tableName => $colsArray) {
            if (Schema::hasTable($tableName)) {
                foreach ($colsArray as $cols) {
                    try {
                        Schema::table($tableName, function (Blueprint $t) use ($cols) {
                            $t->index($cols);
                        });
                    } catch (\Exception $e) {
                        // Ignore if index exists or column doesn't exist
                    }
                }
            }
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
