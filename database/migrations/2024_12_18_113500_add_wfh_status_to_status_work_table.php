<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $statuses = [
            ['id' => 14, 'name' => 'วันหยุด'],
            ['id' => 15, 'name' => 'WFH'],
        ];

        foreach ($statuses as $status) {
            $exists = DB::table('status_work')->where('id', $status['id'])->exists();
            if (!$exists) {
                DB::table('status_work')->insert($status);
            }
        }
    }

    public function down(): void
    {
        DB::table('status_work')->whereIn('id', [14, 15])->delete();
    }
};
