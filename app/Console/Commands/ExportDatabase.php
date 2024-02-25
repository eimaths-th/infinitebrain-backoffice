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
        $date = now()->format('Y-m');

        // Create a new directory based on the current date
        $backupDirectory = storage_path("app/$date");
        if (!file_exists($backupDirectory)) {
            mkdir($backupDirectory, 0777, true);
        }

        $outputPath = "$backupDirectory/database_backup_$datetime.sql";

        $command = sprintf(
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
