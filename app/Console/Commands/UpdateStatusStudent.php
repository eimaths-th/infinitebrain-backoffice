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
        // Your logic to update student status
        $school_picei_update_group = SchoolPrice::all();
        // $subject_ids = $school_picei_update_group->pluck('subject')->toArray();
        foreach ($school_picei_update_group as $upg) {
            # code...
            $upg->update(['group' => $upg->school_sub->group]);
            // dd($upg->id,$group);
        }
        $students = Student::with('rec_school')->get();
        $this->statusService->updateStatus($students);
        // $this->updateStatus($students);

        $this->info('Student status updated successfully.');
    }

}
