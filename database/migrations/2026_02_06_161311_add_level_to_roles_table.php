<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->integer('level')->default(1)->after('guard_name');
        });

        // Seed initial levels
        $roles = [
            'super-admin' => 99,
            'admin' => 50,
            'finance' => 30,
            'staff' => 10,
            'parent' => 1
        ];

        foreach ($roles as $name => $level) {
            try {
                // Use DB directly to avoid model caching issues during migration
                DB::table('roles')->where('name', $name)->update(['level' => $level]);
            } catch (\Exception $e) {
                // Ignore if role doesn't exist
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
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
