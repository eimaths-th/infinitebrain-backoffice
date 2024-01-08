<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportDatabase extends Command
{
    protected $signature = 'export:database';
    protected $description = 'Export the entire database';

    public function handle()
    {
        // Use your preferred method to export the database
        // For example, using mysqldump
        $datetime = now()->format('Y-m-d_H-i-s');
        $outputPath = storage_path("app/database_backup_$datetime.sql");

        $command = sprintf(
            //'"C:\xampp\mysql\bin\mysqldump.exe" -u%s -p%s --skip-password -h %s %s > %s',
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.host'),
            config('database.connections.mysql.database'),
            $outputPath
        );

        // dd($command);
        // Execute the command
        exec($command);

        $this->info('Database exported successfully.');
    }
}
