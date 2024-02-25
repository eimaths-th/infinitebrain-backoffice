<?php
namespace App\Services;

use App\Models\SchoolPrice;
use App\Models\Statuslog;
use App\Models\Student;
use Carbon\Carbon;

class StatusService
{
    public function updateStudentStatus()
    {
        $students = Student::with('rec_school')->get();

        $updateStatus = $this->updateStatus($students);
        // return redirect()->route('home');
    }
    private function updateStatus($students)
    {
        foreach ($students as $student) {
            $statuses = $this->determineStatus($student);
            $cl = 0;
            foreach ($statuses as $statuse) {
                foreach ($statuse as $subjectGroup => $statu) {

                    $schp_id = $statu['schoolprice_id'];
                    $status = $statu['status'];

                    $existingStatus = Statuslog::where('student_id', $student->id)
                        ->where('group', $subjectGroup)
                        ->latest()
                        ->get();
                    $currentMonthYear = date('Y-m');

                    $status_date = $existingStatus->first()->status_date ?? '';
                    $status_date_year_month = date('Y-m', strtotime($status_date));

                    $select_status_date_year_month = date('Y-m', strtotime($currentMonthYear));

                    $checkDuplicate = $existingStatus = Statuslog::where([
                        'student_id' => $student->id,
                        'schoolprice_id' => $schp_id,
                        'group' => $subjectGroup,
                        'status' => $status,
                    ])->get();
                    if (count($existingStatus) < 1) {
                        Statuslog::create([
                            'student_id' => $student->id,
                            'schoolprice_id' => $schp_id,
                            'nick_name' => $student->nick_name,
                            'group' => $subjectGroup,
                            'status' => $status,
                            'status_date' => now(),
                        ]);
                    } elseif ($existingStatus->first()->status === $status && $existingStatus->first()->group === $subjectGroup && $status_date_year_month == $select_status_date_year_month) {
                    } elseif ($checkDuplicate->count() > 0) {
                        # code...
                    } else {
                        Statuslog::create([
                            'student_id' => $student->id,
                            'schoolprice_id' => $schp_id,
                            'nick_name' => $student->nick_name,
                            'group' => $subjectGroup,
                            'status' => $status,
                            'status_date' => now(),
                        ]);
                    }
                    $lastStatusLog = $student->status_log->last();
                    if ($lastStatusLog) {
                        $student->update(['status' => $lastStatusLog->status]);
                    }
                }
                $cl++;
            }
        }
    }
    public function determineStatus($student)
    {
        $stdId = $student->id;

        $ssss = SchoolPrice::where('student_id', $stdId)
            ->where('course_type', 1)
            ->where('qty', '>', 0)
            ->orderBy('rid')
            ->get();
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
                            $check_resume = Statuslog::where('student_id', $stdId)
                                ->where('group', $group)
                                // ->whereMonth('status_date', $resume_month)
                                // ->whereYear('status_date', $resume_year)
                                ->latest()
                                ->get();


                            if (count($check_resume) > 0) {
                                $firstLog = $check_resume->first();
                                if ($currentMonth->diffInMonths($currentReceiptsubmission_date) == 0 && $currentReceiptsubmission_date->lessThan($currentReceiptStatDate)) {

                                    $status[$group] = [
                                        'status' => 'waitting . . .',
                                        'schoolprice_id' => $school_pice_II->id
                                    ]; // ['waitting . . .', $school_pice_I->id];

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
