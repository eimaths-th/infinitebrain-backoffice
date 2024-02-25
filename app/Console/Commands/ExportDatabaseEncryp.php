<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportDatabaseEncryp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:databaseEndcryp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
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

        // Execute the command
        exec($command);

        // Encrypt the backup file
        $encryptedPath = "$outputPath.enc";
        $encryptionKey = 'your_secret_key'; // Replace with your secret key
        $encryptionCommand = sprintf(
            'openssl enc -aes-256-cbc -salt -in %s -out %s -k %s',
            $outputPath,
            $encryptedPath,
            $encryptionKey
        );

        // Execute the encryption command
        exec($encryptionCommand);

        // Remove the unencrypted backup file
        unlink($outputPath);

        $this->info('Database exported and encrypted successfully.');
    }
}
