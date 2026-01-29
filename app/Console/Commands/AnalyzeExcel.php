<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class AnalyzeExcel extends Command
{
    protected $signature = 'analyze:excel {file}';
    protected $description = 'Analyze Excel file structure';

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return;
        }

        $this->info("Analyzing $file...");

        try {
            $data = Excel::toCollection(new class implements ToCollection {
                public function collection(Collection $rows)
                {
                    return $rows;
                }
            }, $file);

            $sheet1 = $data->first();

            $this->info("First 5 rows:");
            $sheet1->take(5)->each(function ($row, $index) {
                $this->info("Row $index: " . $row->toJson(JSON_UNESCAPED_UNICODE));
            });
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
