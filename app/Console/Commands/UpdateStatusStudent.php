<?php

namespace App\Console\Commands;

use App\Models\SchoolPrice;
use App\Models\Statuslog;
use App\Models\Student;
use Illuminate\Console\Command;
use App\Services\StatusService;


class UpdateStatusStudent extends Command
{
    protected $signature = 'updateStatus:student';
    protected $description = 'Update student status';

    protected $statusService;
    public function __construct(StatusService $statusService)
    {
        parent::__construct();
        $this->statusService = $statusService;
    }

    public function handle()
    {
        $this->info('Starting student status update...');

        // 1. Sync group from school_sub (subjects) efficiently - only for those missing group
        $this->info('Syncing missing groups for school prices...');
        SchoolPrice::where(function($query) {
            $query->whereNull('group')->orWhere('group', '');
        })->chunk(500, function ($prices) {
            foreach ($prices as $upg) {
                if ($upg->school_sub && $upg->school_sub->group) {
                    $upg->update(['group' => $upg->school_sub->group]);
                }
            }
        });

        // 2. Update student statuses in chunks via StatusService
        $this->info('Processing student status calculation...');
        $this->statusService->updateStudentStatus();

        $this->info('Student status updated successfully.');
    }

}
