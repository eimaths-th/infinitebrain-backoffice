<?php
namespace App\Services;

use App\Models\SchoolPrice;
use App\Models\Statuslog;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatusService
{
    protected $schoolPricesCache = [];
    protected $statusLogsToInsert = [];
    protected $currentMonthYear;
    protected $currentMonth;
    protected $cMonth;
    protected $cYear;
    public function updateStudentStatus()
    {
        // Initialize date variables
        $this->currentMonthYear = date('Y-m');
        $this->currentMonth = Carbon::parse($this->currentMonthYear)->firstOfMonth();
        $this->cMonth = date('m');
        $this->cYear = date('Y');

        // Eager load ALL relationships at once to avoid N+1 queries
        $students = Student::with([
            'rec_school',
            'status_log' => function ($query) {
                $query->latest();
            }
        ])->get();

        $this->updateStatus($students);
    }
    public function updateStatus($students)
    {
        // Pre-load all SchoolPrices for all students at once
        $studentIds = $students->pluck('id')->toArray();
        $schoolPrices = SchoolPrice::whereIn('student_id', $studentIds)
            ->where('course_type', 1)
            ->where('qty', '>', 0)
            ->orderBy('rid')
            ->get()
            ->groupBy('student_id');

        // Pre-load all existing status logs for current month
        $existingStatusLogs = Statuslog::whereIn('student_id', $studentIds)
            ->whereMonth('status_date', $this->cMonth)
            ->whereYear('status_date', $this->cYear)
            ->get()
            ->groupBy(function ($item) {
                return $item->student_id . '_' . $item->group;
            });

        // Pre-load latest status logs per student per group
        $latestStatusLogs = Statuslog::whereIn('student_id', $studentIds)
            ->get()
            ->groupBy(function ($item) {
                return $item->student_id . '_' . $item->group;
            })
            ->map(function ($group) {
                return $group->sortByDesc('id')->first();
            });

        // Cache SchoolPrice by ID
        $allSchoolPriceIds = $schoolPrices->flatten()->pluck('id')->unique()->toArray();
        $this->schoolPricesCache = SchoolPrice::whereIn('id', $allSchoolPriceIds)
            ->get()
            ->keyBy('id');

        $this->statusLogsToInsert = [];
        $studentsToUpdate = [];
        foreach ($students as $student) {
            // Use pre-loaded school prices
            $studentSchoolPrices = $schoolPrices->get($student->id, collect());
            $statuses = $this->determineStatus($student, $studentSchoolPrices);
            
            foreach ($statuses as $statuse) {
                foreach ($statuse as $subjectGroup => $statu) {
                    $schp_id = $statu['schoolprice_id'];
                    $status = $statu['status'];

                    // Use pre-loaded data instead of querying
                    $key = $student->id . '_' . $subjectGroup;
                    $existingStatus = $latestStatusLogs->get($key);
                    $checkDuplicateCollection = $existingStatusLogs->get($key, collect());

                    $status_date = $existingStatus->status_date ?? '';
                    $status_date_year_month = $status_date ? Carbon::parse($status_date)->format('Y-m') : '';

                    // Use cached SchoolPrice
                    $check_schp = $this->schoolPricesCache->get($schp_id);

                    if ($check_schp) {
                        # code...
                        $period = $check_schp->period;


                        $periodd = explode(' - ', $period);
                        if (count($periodd) == 1) {
                            $periods = explode('-', $period);
                            $startMonth = Carbon::parse("$periods[0]-$periods[1]")->format('Y-m');
                            $endMonth = count($periods) > 2 ? Carbon::parse("$periods[2]-$periods[3]")->format('Y-m') : $startMonth;
                        } else {
                            $startMonth = Carbon::parse($periodd[0])->format('Y-m');
                            $endMonth = Carbon::parse($periodd[1])->format('Y-m');
                        }
                        $cMonthYear = $this->currentMonth;
                        $endMonthYear = Carbon::parse($endMonth);
                        $checkDrop = $endMonthYear->diffInMonths($cMonthYear) > 2 && $endMonthYear->lessThan($cMonthYear) || $subjectGroup === 'N';
                    } else {
                        $checkDrop = $subjectGroup === 'N';
                    }

                    $shouldCreate = false;

                    if (!$existingStatus) {
                        $shouldCreate = true;
                    } elseif ($existingStatus->status === $status && 
                              $existingStatus->group === $subjectGroup && 
                              $status_date_year_month == $this->currentMonthYear) {
                        // Skip - same status in same month
                    } elseif ($checkDuplicateCollection->count() > 0 && $checkDuplicateCollection->first()->status === $status) {
                        // Skip - duplicate found
                    } else {
                        if (!$checkDrop) {
                            $shouldCreate = true;
                        }
                    }

                    if ($shouldCreate) {
                        $this->statusLogsToInsert[] = [
                            'student_id' => $student->id,
                            'schoolprice_id' => $schp_id,
                            'nick_name' => $student->nick_name,
                            'group' => $subjectGroup,
                            'status' => $status,
                            'status_date' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // Prepare student status update
                    $lastStatusLog = $student->status_log->last();
                    if ($lastStatusLog && $student->status !== $lastStatusLog->status) {
                        $studentsToUpdate[$student->id] = $lastStatusLog->status;
                    }
                }
            }
        }

        // Bulk insert all status logs at once
        if (!empty($this->statusLogsToInsert)) {
            // Insert in chunks to avoid memory issues
            foreach (array_chunk($this->statusLogsToInsert, 500) as $chunk) {
                Statuslog::insert($chunk);
            }
        }

        // Bulk update student statuses
        if (!empty($studentsToUpdate)) {
            foreach ($studentsToUpdate as $studentId => $newStatus) {
                Student::where('id', $studentId)->update(['status' => $newStatus]);
            }
        }
    }
    public function determineStatus($student, $studentSchoolPrices = null)
    {
        $stdId = $student->id;

        // Use pre-loaded school prices if available
        if ($studentSchoolPrices === null) {
            $ssss = SchoolPrice::where('student_id', $stdId)
                ->where('course_type', 1)
                ->where('qty', '>', 0)
                ->orderBy('rid')
                ->get();
        } else {
            $ssss = $studentSchoolPrices;
        }
        $group_scp = $ssss->groupBy('group');
        $status = [];
        $statusss = [];

        if (!$group_scp->isEmpty()) {

            foreach ($group_scp as $group => $school_pice) {
                // $current_Month = Carbon::createFromFormat('Y-m', '2024-05');
                $current_Month = date('Y-m');
                $currentMonth = Carbon::parse($current_Month)->firstOfMonth();
                $count_qty = count($school_pice);
                $count_rec = 0;
                $monthsDifference = 0;
                $datepr = [];
                $dateDrop = '';
                $dateResume = '';
                $firstEndMonth = '';
                $count_qty = count($school_pice);

                foreach ($school_pice as $school_pice_II) {
                    # code...
                    $submission_date = $school_pice_II->rec_school->submission_date;
                    $period = $school_pice_II->period;

                    $periodd = explode(' - ', $period);
                    if (count($periodd) == 1) {
                        $periods = explode('-', $period);
                        $startMonth = Carbon::parse("$periods[0]-$periods[1]")->format('Y-m');
                        $endMonth = count($periods) > 2 ? Carbon::parse("$periods[2]-$periods[3]")->format('Y-m') : $startMonth;
                    } else {

                        $startMonth = Carbon::parse($periodd[0])->format('Y-m');
                        $endMonth = Carbon::parse($periodd[1])->format('Y-m');
                    }

                    if ($count_rec > 0) {

                        $monthsDifference = Carbon::parse($firstEndMonth)->diffInMonths(Carbon::parse($startMonth));
                    }

                    if ($monthsDifference > 1) {

                        $dateDrop = $firstEndMonth;
                        $dateResume = $startMonth;
                    }
                    $firstEndMonth = $endMonth;
                    $count_rec++;

                    $currentReceiptStatDate = Carbon::parse($startMonth);
                    // $currentReceiptEndDate = Carbon::parse($endMonth);
                    $currentReceiptEndDate = Carbon::parse($firstEndMonth);
                    $currentReceiptsubmission_date = Carbon::parse($submission_date)->firstOfMonth();

                    if ($count_qty == 1 && $currentMonth->diffInMonths($currentReceiptStatDate) <= 0) {
                        $status[$group] = [
                            'status' => 'New Student',
                            'schoolprice_id' => $school_pice_II->id
                        ];
                        // dd('test',$group_scp, $school_pice, $school_pice_II, $status);
                    } elseif ($count_qty == 1 && $currentMonth->diffInMonths($currentReceiptsubmission_date) == 0 && $currentReceiptsubmission_date->lessThan($currentReceiptStatDate)) {
                        # code...
                        $status[$group] = [
                            'status' => 'waitting . . .',
                            'schoolprice_id' => $school_pice_II->id

                        ];
                    }
                    if ($count_qty == 1) {
                        # code...
                        if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
                            # code...
                            $status[$group] = [
                                'status' => 'drop',
                                'schoolprice_id' => $school_pice_II->id
                            ]; // ['drop', $school_pice_I->id];

                        } else {
                            if ($currentMonth->greaterThan($currentReceiptEndDate)) {
                                //กำลังยุติการการเรียน
                                $status[$group] = [
                                    'status' => 'Termination of study',
                                    'schoolprice_id' => $school_pice_II->id
                                ]; // ['Termination of study', $school_pice_I->id];

                            } else {
                                # code...
                                if ($currentMonth->diffInMonths($currentReceiptStatDate) <= 0) {
                                    # code...
                                    $status[$group] = [
                                        'status' => 'New Student',
                                        'schoolprice_id' => $school_pice_II->id
                                    ];
                                } elseif ($currentMonth->diffInMonths($currentReceiptsubmission_date) == 0 && $currentReceiptsubmission_date->lessThan($currentReceiptStatDate)) {
                                    # code...
                                    $status[$group] = [
                                        'status' => 'waitting . . .',
                                        'schoolprice_id' => $school_pice_II->id

                                    ];
                                } else {
                                    # code...
                                    $status[$group] = [
                                        'status' => 'studying . . .',
                                        'schoolprice_id' => $school_pice_II->id
                                    ];
                                }
                            }
                        }
                    } elseif ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
                        //พ้นสภาพนักเรียน
                        $status[$group] = [
                            'status' => 'drop',
                            'schoolprice_id' => $school_pice_II->id
                        ]; // ['drop', $school_pice_I->id];

                    } else {
                        if ($currentMonth->greaterThan($currentReceiptEndDate)) {
                            //กำลังยุติการการเรียน
                            $status[$group] = [
                                'status' => 'Termination of study',
                                'schoolprice_id' => $school_pice_II->id
                            ]; // ['Termination of study', $school_pice_I->id];

                        } else {
                            //กำลังเรียนอยู่
                            // Use pre-loaded status logs from student relationship
                            $check_resume = $student->status_log
                                ->where('group', $group)
                                ->sortByDesc('id')
                                ->values();

                            if (count($check_resume) > 0) {
                                $firstLog = $check_resume->first();
                                if ($currentMonth->diffInMonths($currentReceiptsubmission_date) == 0 && $currentReceiptsubmission_date->lessThan($currentReceiptStatDate)) {
                                    //จ่ายในเดือนปัจจุบัน                                                      //วันที่จ่ายน้อยกว่าวันที่เริ่มเรียน
                                    if ($monthsDifference == 1) {
                                        $status[$group] = [
                                            'status' => 'studying . . .',
                                            'schoolprice_id' => $school_pice_II->id
                                        ]; // ['studying . . .', $school_pice_I->id];

                                    } else {
                                        $status[$group] = [
                                            'status' => 'waitting . . .',
                                            'schoolprice_id' => $school_pice_II->id

                                        ];
                                    }

                                    // dd($check_resume, $firstLog, $firstLog->status, $status, $currentReceiptEndDate);

                                    // $status[$group] = [
                                    //     'schoolprice_id' => $school_pice_II->id

                                    // ];
                                } else {
                                    if ($dateResume) {
                                        $submissionDateResume = Carbon::parse($dateResume);

                                        if ($currentMonth->diffInMonths($submissionDateResume) > 0) {
                                            $status[$group] = [
                                                'status' => 'studying . . .',
                                                'schoolprice_id' => $school_pice_II->id
                                            ]; // ['studying . . .', $school_pice_I->id];

                                        } else {
                                            $status[$group] = [
                                                'status' => 'resume',
                                                'schoolprice_id' => $school_pice_II->id
                                            ]; // ['resume', $school_pice_I->id];

                                        }
                                    } else {
                                        $status[$group] = [
                                            'status' => 'studying . . .',
                                            'schoolprice_id' => $school_pice_II->id
                                        ];
                                    }
                                }
                            }
                        }
                    }
                    $statusss[] = $status;
                }
            }
        } else {
            $status['N'] = [
                'status' => 'None Receipt',
                'schoolprice_id' => null
            ];
            $statusss[] = $status;
        }

        $status = $statusss;

        return $status;
    }
}
