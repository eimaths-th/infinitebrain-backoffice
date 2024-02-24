<?php

namespace App\Http\Controllers;

use App\Models\Receipt_School;
use App\Models\SchoolPrice;
use App\Models\Statuslog;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RetrospectiveCheckController extends Controller
{

    public function performRetrospectiveCheck()
    {
        $students = Student::with('rec_school')->get();
        // $students = Student::where('id', 1)->get();
        $school_picei_update_group = SchoolPrice::all();
        // $subject_ids = $school_picei_update_group->pluck('subject')->toArray();
        foreach ($school_picei_update_group as $upg) {
            # code...
            $group = $upg->school_sub->group;
            $upg->update(['group' => $group]);
            // dd($upg->id,$group);

        }
        foreach ($students as $student) {
            $statuses = $this->determineStatus3($student);
            $cl = 0;
            foreach ($statuses as $statuse) {
                foreach ($statuse as $subjectGroup => $statu) {

                    $schp_id = $statu['schoolprice_id'];
                    $status = $statu['status'];

                    $existingStatus = Statuslog::where('student_id', $student->id)
                        ->where('group', $subjectGroup)
                        ->latest()
                        ->get();
                    // dd($existingStatus,$status,$subjectGroup,$student->id);
                    $currentMonthYear = date('Y-m');

                    $status_date = $existingStatus->first()->status_date ?? '';
                    $status_date_year_month = date('Y-m', strtotime($status_date));

                    $select_status_date_year_month = date('Y-m', strtotime($currentMonthYear));
                    // dd($status_date_year_month, $select_status_date_year_month);

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

        $month = date('m');

        $year = date('Y');

        $latestStatusCounts = Statuslog::select('group', 'student_id', 'status')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('status_log')
                    ->groupBy('group', 'student_id');
            })
            ->whereYear('status_date', $year)
            ->whereMonth('status_date', $month)
            ->get();

        $groupStatusCounts = [];

        foreach ($latestStatusCounts as $record) {

            $group = $record->group;
            $status = $record->status;

            if (!isset($groupStatusCounts[$group])) {
                $groupStatusCounts[$group] = [];
            }

            if (!isset($groupStatusCounts[$group][$status])) {
                $groupStatusCounts[$group][$status] = 0;
            }
            $groupStatusCounts[$group][$status]++;
        }
        $cstdlog = Statuslog::all()->count();
        dd($latestStatusCounts, $groupStatusCounts, $cstdlog);
    }

    private function determineStatus($student)
    {
        $stdId = $student->id;
        // $stdId = 16;

        $receipt = Receipt_School::whereRaw("FIND_IN_SET($stdId, REPLACE(pid, '|', ','))")->get();

        $ssss = SchoolPrice::where('student_id', $stdId)
            ->where('course_type', 1)
            ->where('qty', '>', 0)
            ->orderBy('rid')
            ->get();
        $group_scp = $ssss->groupBy('group');
        $status = [];
        if (!$group_scp->isEmpty()) {

            foreach ($group_scp as $group => $school_pice) {
                $startdate = null;
                // $submission_date = $school_pice->rec_school->submission_date;
                if (!$school_pice->isEmpty()) {
                    $startdate = $school_pice->first()->period;
                }

                $parts = explode('-', $startdate);
                $lastTwoParts = array_slice($parts, 0, 2);
                $start_date = implode('-', $lastTwoParts);
                $currentReceiptStartDate = Carbon::parse($start_date);

                // $current_Month = Carbon::createFromFormat('Y-m', '2024-05');

                $current_Month = date('Y-m');
                $currentMonth = Carbon::parse($current_Month)->firstOfMonth();

                $count_qty = count($school_pice);

                if ($count_qty == 1) {
                    if ($currentMonth->diffInMonths($currentReceiptStartDate) <= 0) {
                        $status[$group] = 'New Student';
                    } else {
                        $submission_date = $school_pice->last()->rec_school->submission_date;
                        if (count($school_pice) > 1) {
                            $period = $school_pice->last()->period;
                        } else {
                            $period = $school_pice->first()->period;
                            // dd($count_qty, $school_pice);
                        }

                        $parts = explode('-', $period);
                        $parts_submission_date = explode('-', $submission_date);

                        $lastTwoParts = array_slice($parts, -2);
                        $firstTwoParts = array_slice($parts, 0, 2);
                        $first_submission_date = array_slice($parts_submission_date, 0, 2);

                        $start_date = implode('-', $firstTwoParts);
                        $end_date = implode('-', $lastTwoParts);
                        $submissionDate = implode('-', $first_submission_date);

                        $currentReceiptStatDate = Carbon::parse($start_date);
                        $currentReceiptEndDate = Carbon::parse($end_date);
                        $currentReceiptsubmission_date = Carbon::parse($submissionDate);

                        if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
                            //พ้นสภาพนักเรียน
                            // $status = 'พ้นสภาพนักเรียน';
                            $status[$group] = 'drop';
                        } else {
                            if ($currentMonth->greaterThan($currentReceiptEndDate)) {
                                //กำลังยุติการการเรียน
                                $status[$group] = 'Termination of study';
                            } else {

                                if ($currentMonth->diffInMonths($currentReceiptsubmission_date) == 0 && $currentReceiptsubmission_date->lessThan($currentReceiptStatDate)) {
                                    # code...
                                    //แก้บัค 22/09/2023
                                    $status[$group] = 'waitting . . .';
                                } else {
                                    $status[$group] = 'studying . . .';
                                }
                            }
                        }
                        // $statuses[$group] = 'old Student';
                    }
                } elseif ($count_qty > 1) {
                    $count_rec = 0;
                    $monthsDifference = 0;
                    $datepr = [];
                    $dateDrop = '';
                    $dateResume = '';
                    $firstEndMonth = '';

                    foreach ($school_pice as $school_pice_I) {
                        # code...
                        $submission_date = $school_pice_I->rec_school->submission_date;
                        $period = $school_pice_I->period;
                        $datepr[] = $period;

                        $parts = explode('-', $period);
                        $parts_submission_date = explode('-', $submission_date);

                        $lastTwoParts = array_slice($parts, -2);
                        $firstTwoParts = array_slice($parts, 0, 2);
                        $first_submission_date = array_slice($parts_submission_date, 0, 2);

                        $start_date = implode('-', $firstTwoParts);
                        $end_date = implode('-', $lastTwoParts);
                        $submissionDate = implode('-', $first_submission_date);

                        $currentReceiptStatDate = Carbon::parse($start_date);
                        $currentReceiptEndDate = Carbon::parse($end_date)->endOfMonth();
                        $currentReceiptsubmission_date = Carbon::parse($submissionDate);

                        $test_sub = Carbon::parse($school_pice_I->rec_school->submission_date)->firstOfMonth();
                        $test_rec = Receipt_School::find($school_pice_I->rec_school->id);

                        $periodd = explode(' - ', $school_pice_I->period);
                        if (count($periodd) == 1) {
                            $periods = explode('-', $school_pice_I->period);
                            $startMonth = Carbon::parse("$periods[0]-$periods[1]")->format('Y-m');
                            $endMonth = count($periods) > 2 ? Carbon::parse("$periods[2]-$periods[3]")->format('Y-m') : $startMonth;

                            if ($count_rec > 0) {
                                $monthsDifference = Carbon::parse($firstEndMonth)->diffInMonths(Carbon::parse($startMonth));
                                // $endMonth = count($periods) > 2 ? Carbon::parse("$periods[2]-$periods[3]")->format('Y-m') : $startMonth;
                            }
                        } else {

                            $startMonth = Carbon::parse($periodd[0])->format('Y-m');
                            $endMonth = Carbon::parse($periodd[1])->format('Y-m');

                            if ($count_rec > 0) {
                                $monthsDifference = Carbon::parse($firstEndMonth)->diffInMonths(Carbon::parse($startMonth));
                                // $endMonth = Carbon::parse($periodd[1])->format('Y-m');
                            }
                        }

                        if ($monthsDifference > 1) {

                            $dateDrop = $firstEndMonth;
                            $dateResume = $startMonth;
                        }
                        $firstEndMonth = $endMonth;
                        $count_rec++;
                        $currentReceiptStatDate = Carbon::parse($startMonth);
                        $currentReceiptEndDate = Carbon::parse($firstEndMonth);
                        $currentReceiptsubmission_date = Carbon::parse($submissionDate);

                        if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
                            //พ้นสภาพนักเรียน
                            $status[$group] = 'drop';
                        } else {
                            if ($currentMonth->greaterThan($currentReceiptEndDate)) {
                                //กำลังยุติการการเรียน
                                $status[$group] = 'Termination of study';
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
                                    $newEnrollmentReceipt = SchoolPrice::where('student_id', $stdId)
                                        ->where('qty', '>', 0)
                                        ->where('course_type', 1)
                                        ->where('group', $group)
                                        ->orderBy('id', 'desc')
                                        ->first();
                                    $startdate = $newEnrollmentReceipt->period;
                                    $parts = explode('-', $startdate);
                                    $lastTwoParts = array_slice($parts, 0, 2);
                                    $start_date = implode('-', $lastTwoParts);

                                    $submissionDateResume = Carbon::parse($dateResume);

                                    if ($currentMonth->diffInMonths($currentReceiptsubmission_date) == 0 && $currentReceiptsubmission_date->lessThan($currentReceiptStatDate)) {

                                        $status[$group] = 'waitting . . .';
                                    } else {
                                        if ($dateResume) {
                                            if ($currentMonth->diffInMonths($submissionDateResume) > 0) {
                                                $status[$group] = 'studying . . .';
                                            } else {
                                                $status[$group] = 'resume';
                                            }
                                        } else {
                                            $status[$group] = 'studying . . .';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $status['N'] = 'None Receipt';
        }


        return $status;
    }
    private function determineStatus2($student)
    {
        $stdId = $student->id;
        // $stdId = 16;


        $ssss = SchoolPrice::where('student_id', $stdId)
            ->where('course_type', 1)
            ->where('qty', '>', 0)
            ->orderBy('rid')
            ->get();
        $group_scp = $ssss->groupBy('group');
        $status = [];
        $statusss = [];
        // dd('bug1', $stdId, $status,$group_scp);

        if (!$group_scp->isEmpty()) {

            foreach ($group_scp as $group => $school_pice) {
                $startdate = null;
                // $submission_date = $school_pice->rec_school->submission_date;
                if (!$school_pice->isEmpty()) {
                    $startdate = $school_pice->first()->period;
                }

                $parts = explode('-', $startdate);
                $lastTwoParts = array_slice($parts, 0, 2);
                $start_date = implode('-', $lastTwoParts);
                $currentReceiptStartDate = Carbon::parse($start_date);

                // $current_Month = Carbon::createFromFormat('Y-m', '2024-05');

                $current_Month = date('Y-m');
                $currentMonth = Carbon::parse($current_Month)->firstOfMonth();

                $count_qty = count($school_pice);

                if ($count_qty == 1) {

                    if ($currentMonth->diffInMonths($currentReceiptStartDate) <= 0) {
                        $status[$group] =
                            // 'New Student';
                            [
                                'status' => 'New Student',
                                'schoolprice_id' => $school_pice->first()->id
                            ];
                    } else {
                        $submission_date = $school_pice->first()->rec_school->submission_date;
                        $period = $school_pice->first()->period;


                        $parts = explode('-', $period);
                        $parts_submission_date = explode('-', $submission_date);

                        $lastTwoParts = array_slice($parts, -2);
                        $firstTwoParts = array_slice($parts, 0, 2);
                        $first_submission_date = array_slice($parts_submission_date, 0, 2);

                        $start_date = implode('-', $firstTwoParts);
                        $end_date = implode('-', $lastTwoParts);
                        $submissionDate = implode('-', $first_submission_date);

                        $currentReceiptStatDate = Carbon::parse($start_date);
                        $currentReceiptEndDate = Carbon::parse($end_date);
                        $currentReceiptsubmission_date = Carbon::parse($submissionDate);

                        if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
                            //พ้นสภาพนักเรียน
                            // $status = 'พ้นสภาพนักเรียน';
                            $status[$group] =
                                // 'drop';
                                [
                                    'status' => 'drop',
                                    'schoolprice_id' => $school_pice->first()->id
                                ];
                        } else {
                            if ($currentMonth->greaterThan($currentReceiptEndDate)) {
                                //กำลังยุติการการเรียน
                                $status[$group] =
                                    // 'Termination of study';
                                    [
                                        'status' => 'Termination of study',
                                        'schoolprice_id' => $school_pice->first()->id
                                    ];
                            } else {

                                if ($currentMonth->diffInMonths($currentReceiptsubmission_date) == 0 && $currentReceiptsubmission_date->lessThan($currentReceiptStatDate)) {
                                    # code...
                                    //แก้บัค 22/09/2023
                                    $status[$group] =
                                        // 'waitting . . .';
                                        [
                                            'status' => 'waitting . . .',
                                            'schoolprice_id' => $school_pice->first()->id
                                        ];
                                } else {
                                    $status[$group] =
                                        // 'studying . . .';
                                        [
                                            'status' => 'studying . . .',
                                            'schoolprice_id' => $school_pice->first()->id
                                        ];
                                }
                            }
                        }
                        // $statuses[$group] = 'old Student';
                    }
                    $statusss[] = $status;
                } elseif ($count_qty > 1) {
                    $count_rec = 0;
                    $monthsDifference = 0;
                    $datepr = [];
                    $dateDrop = '';
                    $dateResume = '';
                    $firstEndMonth = '';

                    foreach ($school_pice as $school_pice_I) {

                        // dd($school_pice);
                        # code...
                        $submission_date = $school_pice_I->rec_school->submission_date;
                        $period = $school_pice_I->period;
                        $datepr[] = $period;

                        $parts = explode('-', $period);
                        $parts_submission_date = explode('-', $submission_date);

                        $lastTwoParts = array_slice($parts, -2);
                        $firstTwoParts = array_slice($parts, 0, 2);
                        $first_submission_date = array_slice($parts_submission_date, 0, 2);

                        $start_date = implode('-', $firstTwoParts);
                        $end_date = implode('-', $lastTwoParts);
                        $submissionDate = implode('-', $first_submission_date);

                        $currentReceiptStatDate = Carbon::parse($start_date);
                        $currentReceiptEndDate = Carbon::parse($end_date)->endOfMonth();
                        $currentReceiptsubmission_date = Carbon::parse($submissionDate);

                        $test_sub = Carbon::parse($school_pice_I->rec_school->submission_date)->firstOfMonth();
                        $test_rec = Receipt_School::find($school_pice_I->rec_school->id);

                        $periodd = explode(' - ', $school_pice_I->period);
                        if (count($periodd) == 1) {
                            $periods = explode('-', $school_pice_I->period);
                            $startMonth = Carbon::parse("$periods[0]-$periods[1]")->format('Y-m');
                            $endMonth = count($periods) > 2 ? Carbon::parse("$periods[2]-$periods[3]")->format('Y-m') : $startMonth;

                            if ($count_rec > 0) {
                                $monthsDifference = Carbon::parse($firstEndMonth)->diffInMonths(Carbon::parse($startMonth));
                                // $endMonth = count($periods) > 2 ? Carbon::parse("$periods[2]-$periods[3]")->format('Y-m') : $startMonth;
                            }
                        } else {

                            $startMonth = Carbon::parse($periodd[0])->format('Y-m');
                            $endMonth = Carbon::parse($periodd[1])->format('Y-m');

                            if ($count_rec > 0) {
                                $monthsDifference = Carbon::parse($firstEndMonth)->diffInMonths(Carbon::parse($startMonth));
                                // $endMonth = Carbon::parse($periodd[1])->format('Y-m');
                            }
                        }

                        if ($monthsDifference > 1) {

                            $dateDrop = $firstEndMonth;
                            $dateResume = $startMonth;
                        }
                        $firstEndMonth = $endMonth;
                        $count_rec++;
                        $currentReceiptStatDate = Carbon::parse($startMonth);
                        $currentReceiptEndDate = Carbon::parse($firstEndMonth);
                        $currentReceiptsubmission_date = Carbon::parse($submissionDate);

                        if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
                            //พ้นสภาพนักเรียน
                            $status[$group] =
                                // ['drop', $school_pice_I->id];
                                [
                                    'status' => 'drop',
                                    'schoolprice_id' => $school_pice_I->id
                                ];
                        } else {
                            if ($currentMonth->greaterThan($currentReceiptEndDate)) {
                                //กำลังยุติการการเรียน
                                $status[$group] =
                                    // ['Termination of study', $school_pice_I->id];
                                    [
                                        'status' => 'Termination of study',
                                        'schoolprice_id' => $school_pice_I->id
                                    ];
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

                                        $status[$group] =
                                            // ['waitting . . .', $school_pice_I->id];
                                            [
                                                'status' => 'waitting . . .',
                                                'schoolprice_id' => $school_pice_I->id
                                            ];
                                    } else {
                                        if ($dateResume) {
                                            $submissionDateResume = Carbon::parse($dateResume);

                                            if ($currentMonth->diffInMonths($submissionDateResume) > 0) {
                                                $status[$group] =
                                                    [
                                                        'status' => 'studying . . .',
                                                        'schoolprice_id' => $school_pice_I->id
                                                    ];
                                                // ['studying . . .', $school_pice_I->id];

                                            } else {
                                                $status[$group] =
                                                    // ['resume', $school_pice_I->id];
                                                    [
                                                        'status' => 'resume',
                                                        'schoolprice_id' => $school_pice_I->id
                                                    ];
                                            }
                                        } else {
                                            $status[$group] = [
                                                'status' => 'studying . . .',
                                                'schoolprice_id' => $school_pice_I->id
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                        $statusss[] = $status;
                    }
                }
            }
        } else {
            $status['N'] = [
                'status' => 'None Receipt',
                'schoolprice_id' => null
            ];
            $statusss[] = $status;
        }
        // if ($count_rec = 2) {
        //     # code...
        //     dd('bug', $status, $count_rec, $datepr);
        // }
        $status = $statusss;

        // dd('bug', $stdId, $status);

        return $status;
    }
    private function determineStatus3($student)
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
    // private function determineStatus($student)
    // {
    //     $stdId = $student->id;
    //     // $stdId = 15;
    //     $receipt = Receipt_School::whereRaw("FIND_IN_SET($stdId, REPLACE(pid, '|', ','))")->get();

    //     $ssss = SchoolPrice::where('student_id', $stdId)
    //         ->where('qty', '>', 0)
    //         ->orderBy('rid')
    //         ->get();
    //     $group_scp = $ssss->groupBy('group');
    //     $status = [];


    //     if (!$group_scp->isEmpty()) {
    //         foreach ($group_scp as $group => $school_pice) {
    //             $startdate = null;

    //             if (!$school_pice->isEmpty()) {
    //                 $startdate = $school_pice->first()->period;
    //             }

    //             $parts = explode('-', $startdate);
    //             $lastTwoParts = array_slice($parts, 0, 2);
    //             $start_date = implode('-', $lastTwoParts);
    //             $currentReceiptStartDate = Carbon::parse($start_date);

    //             $current_Month = date('Y-m');
    //             // $current_Month = Carbon::createFromFormat('Y-m', '2023-09');
    //             $currentMonth = Carbon::parse($current_Month);

    //             $count_receipt = count($receipt);
    //             $count_qty = count($school_pice);

    //             if ($count_qty < 1) {
    //                 $status[$group] = 'None Receipt';
    //             } elseif ($count_qty == 1) {
    //                 if ($currentMonth->diffInMonths($currentReceiptStartDate) <= 0) {
    //                     $status[$group] = 'New Student';
    //                 } else {

    //                     if (count($school_pice) > 1) {
    //                         $startcourse = $school_pice->first()->period;
    //                         $period = $school_pice->last()->period;
    //                     } else {

    //                         $startcourse = $school_pice->first()->period;
    //                         $period = $school_pice->first()->period;
    //                     }
    //                     $parts = explode('-', $period);
    //                     $lastTwoParts = array_slice($parts, -2);
    //                     $end_date = implode('-', $lastTwoParts);
    //                     $currentReceiptEndDate = Carbon::parse($end_date);
    //                     if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
    //                         //พ้นสภาพนักเรียน
    //                         // $status = 'พ้นสภาพนักเรียน';
    //                         $status[$group] = 'drop';
    //                     } else {
    //                         if ($currentMonth->greaterThan($currentReceiptEndDate)) {
    //                             //กำลังยุติการการเรียน
    //                             $status[$group] = 'Termination of study';
    //                         } else {
    //                             // if ($student->status == 'resume') {
    //                             //     # code...
    //                             //     //กลับมาเรียน
    //                             // dd($stdId);
    //                             //     $status[$group] = 'resume';
    //                             // } else {
    //                             //     # code...
    //                             //     //กำลังเรียนอยู่
    //                             $status[$group] = 'studying . . .';
    //                             // }
    //                         }
    //                     }
    //                     // $statuses[$group] = 'old Student';
    //                 }
    //             } elseif ($count_qty > 1) {

    //                 if (count($school_pice) > 1) {
    //                     $startcourse = $school_pice->first()->period;
    //                     $period = $school_pice->last()->period;
    //                 } else {

    //                     $startcourse = $school_pice->first()->period;
    //                     $period = $school_pice->last()->period;
    //                 }

    //                 $parts = explode('-', $period);
    //                 $lastTwoParts = array_slice($parts, -2);
    //                 $end_date = implode('-', $lastTwoParts);

    //                 $currentReceiptEndDate = Carbon::parse($end_date);

    //                 if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
    //                     //พ้นสภาพนักเรียน
    //                     // $status = 'พ้นสภาพนักเรียน';
    //                     $status[$group] = 'drop';
    //                 } else {
    //                     if ($currentMonth->greaterThan($currentReceiptEndDate)) {
    //                         //กำลังยุติการการเรียน
    //                         // dd($end_date);
    //                         $status[$group] = 'Termination of study';
    //                     } else {
    //                         //กำลังเรียนอยู่
    //                         $resume_month = date('m');
    //                         // $resume_month = '10';
    //                         // $resume_month = Carbon::createFromFormat('Y-m', '2023-10');
    //                         $resume_year = date('Y');
    //                         $check_resume = Statuslog::where('student_id', $stdId)
    //                             ->where('group', $group)
    //                             ->whereMonth('status_date', $resume_month)
    //                             ->whereYear('status_date', $resume_year)
    //                             ->latest()
    //                             ->get();
    //                         // dd($check_resume);
    //                         if (count($check_resume) > 0 && $check_resume->first()->status == 'resume'  ) {

    //                             $status[$group] = 'resume';
    //                         } else {
    //                             $status[$group] = 'studying . . .';
    //                         }
    //                     }
    //                 }
    //                 // dd( $end_date,$school_pice,$student);
    //             }
    //         }
    //     } else {
    //         $status['N'] = 'None Receipt';
    //     }

    //     return $status;
    // }

    //################## 31/08/2023 ################################

    //#################### 30/08/2023 ###########################
    // public function performRetrospectiveCheck()
    // {
    //     $students = Student::with('rec_school')->get();
    //     // $students = Student::where('id',300)->get();

    //     // $month = date('m');
    //     // dd($month);

    //     $month = '08';
    //     $year = date('Y');
    //     $monthyear = date('Y-m', strtotime('2023-09'));
    //     // dd($month);
    //     // $currentMonthYear = Carbon::now()->format('Y-m');
    //     $currentMonthYear = Carbon::createFromFormat('Y-m', '2023-08');

    //     $latestStatusCounts = DB::table('status_log')
    //         ->select('student_id', 'status')
    //         ->whereIn('id', function ($query) {
    //             $query->select(DB::raw('MAX(id)'))
    //                 ->from('status_log')
    //                 ->groupBy('student_id');
    //         })
    //         // ->whereRaw(DB::raw('DATE_FORMAT(status_date, "%Y-%m") = "' . $currentMonthYear . '"'))
    //         ->whereMonth('status_date', $month)
    //         ->whereYear('status_date', $year)
    //         ->get()
    //         ->groupBy('status')
    //         ->map->count();


    //     $e = 0;
    //     $m = 0;

    //     foreach ($students as $student) {
    //         $status = $this->determineStatus($student);
    //         // $cm = $this->countGroupSubject($m, $e, $student);
    //         // Check if a status with the same state exists for the student
    //         $existingStatus = Statuslog::where('student_id', $student->id)
    //             // ->where('status', $status)
    //             ->latest()
    //             ->get()
    //             // ->exists()
    //         ;
    //         // dd($existingStatus->first()->status );
    //         if ($existingStatus->first()->status === 'drop') {
    //             // Query to check if there's a newer enrollment receipt for the student
    //             $newEnrollmentReceipt = Receipt_School::whereRaw("FIND_IN_SET($student->id, REPLACE(pid, '|', ','))")
    //                 // ->where('type', 'enrollment') // or other identifier for enrollment receipts
    //                 ->orderBy('submission_date', 'desc')
    //                 ->first();
    //             $submission_date = $newEnrollmentReceipt->submission_date;
    //             $submissionDate = Carbon::parse($submission_date);

    //             // If a newer enrollment receipt is found, update status to "Resume"
    //             $current_Month = date('Y-m');
    //             // $current_Month = Carbon::createFromFormat('Y-m', '2023-11');
    //             $currentMonth = Carbon::parse($current_Month);
    //             // dd( $currentMonth,$submissionDate,$currentMonth->diffInMonths($submissionDate));
    //             if ($currentMonth->diffInMonths($submissionDate) < 1) {
    //                 $status = 'resume';
    //             }
    //         }
    //         if (!empty($existingStatus->first()->status)) {
    //             // Store status in the status log table
    //             if ($existingStatus->first()->status !== $status) {
    //                 # code...
    //                 Statuslog::create([
    //                     'student_id' => $student->id,
    //                     'nick_name' => $student->nick_name,
    //                     'status' => $status,
    //                     'status_date' => now(),
    //                 ]);
    //             }
    //         } else {
    //             Statuslog::create([
    //                 'student_id' => $student->id,
    //                 'nick_name' => $student->nick_name,
    //                 'status' => $status,
    //                 'status_date' => now(),
    //             ]);
    //         }

    //         $lastStatusLog = $student->status_log->last();
    //         if ($lastStatusLog) {
    //             $student->update(['status' => $lastStatusLog->status]);
    //         }
    //     }
    //     $test = Receipt_School::whereRaw("FIND_IN_SET(28, REPLACE(pid, '|', ','))")->get();
    //     // dd($test);
    //     $msg = "อัพเดทสเตตัสแล้วไอสัส";
    //     foreach ($students as $cn) {
    //         # code...

    //         $stdId = $cn->id;
    //         $receipt = Receipt_School::whereRaw("FIND_IN_SET($stdId, REPLACE(pid, '|', ','))")->get();

    //         $rid = $receipt->pluck('id')->toArray();
    //         // $SchoolPrices = SchoolPrice::where('rid', $rid)->get();
    //         // $arr_test_rec = $receipt->pluck('id')->toArray();
    //         $scp = SchoolPrice::where('student_id', $stdId)->where('qty', '>', 0)->get();


    //         foreach ($scp as $scps) {

    //             ($scps->school_sub->group === 'M') ? $m++ : $e++;
    //         }
    //     }

    //     return response()->json(['message' => $msg, 'status' => $status, 'math ' => $m, 'eng ' => $e]);

    //     // $response = json_decode('{"message":"\u0e2d\u0e31\u0e1e\u0e40\u0e14\u0e17\u0e2a\u0e40\u0e15\u0e15\u0e31\u0e2a\u0e41\u0e25\u0e49\u0e27\u0e44\u0e2d\u0e2a\u0e31\u0e2a","status":" '.$status.' "}');
    //     // echo $response->message;  // Output: ดึงข้อมูลเสร็จสิ้นไอสัส
    //     // echo $response->status;   // Output: studying . . .
    // }

    // private function determineStatus($student)
    // {
    //     // Get all items associated with the receipt
    //     // $items = $student->rec_school->where('qty');
    //     // dd($student);
    //     $stdId = $student->id;
    //     $receipt = Receipt_School::whereRaw("FIND_IN_SET($stdId, REPLACE(pid, '|', ','))")->get();
    //     $rid = $receipt->pluck('id')->toArray();
    //     // $SchoolPrices = SchoolPrice::where('rid', $rid)->get();
    //     // $arr_test_rec = $receipt->pluck('id')->toArray();
    //     $scp = SchoolPrice::where('student_id', $stdId)->where('qty', '>', 0)->get();
    //     // $m = 0;
    //     // $e = 0;
    //     // echo 'M : ' . $m . ' | E : ' . $e . ' ';
    //     // dd($scp);
    //     $scp_ids = $scp->pluck('rid')->toArray();
    //     $qty = Receipt_School::whereIn('id', $scp_ids)->get();
    //     $startdate = null; // Initialize the variable with a default value
    //     // dd($scp);
    //     if (!$scp->isEmpty()) {
    //         $startdate = $scp->first()->period;
    //     }
    //     // set วันที่ เริ่มเรียนกับปัจจุบัน ยังไม่ตรงกัน
    //     $parts = explode('-', $startdate);
    //     $lastTwoParts = array_slice($parts, 0, 2);
    //     $start_date = implode('-', $lastTwoParts);
    //     $currentReceiptStartDate = Carbon::parse($start_date);
    //     $current_Month = date('Y-m');
    //     // $current_Month = date('Y-m', '2023-09');
    //     // $current_Month = Carbon::createFromFormat('Y-m', '2023-11');
    //     $currentMonth = Carbon::parse($current_Month);
    //     // dd( $student,
    //     //     $startdate,
    //     //     $parts,
    //     //     $lastTwoParts,
    //     //     $start_date,
    //     //     $currentReceiptStartDate,
    //     //     $currentMonth,
    //     //     $currentMonth->diffInMonths($currentReceiptStartDate),
    //     // );
    //     //logic
    //     $count_receipt = count($receipt);
    //     $count_qty = count($qty);
    //     // dd(
    //     //     // $stdId,
    //     //     // $count_receipt,
    //     //     // $receipt,
    //     //     $qty,
    //     //     $rid,
    //     //     // // $SchoolPrices,
    //     //     $receipt,
    //     //     $scp_ids
    //     // );
    //     if ($count_qty < 1) {

    //         return 'None Receipt';
    //     } elseif ($count_qty == 1) {

    //         if ($currentMonth->diffInMonths($currentReceiptStartDate) <= 0) {
    //             # code...
    //             return 'New Student';
    //         } else {
    //             # code...
    //             $scp = SchoolPrice::whereIn('rid', $rid)->where('qty', '>', 0)->get();
    //             $scp_ids = $scp->pluck('rid')->toArray();
    //             $school_pice = SchoolPrice::whereIn('rid', $scp_ids)->get();
    //             // dd($rec_ids, $school_pice);

    //             if (count($school_pice) > 1) {
    //                 $startcourse = $school_pice->first()->period;
    //                 $period = $school_pice->last()->period;
    //             } else {

    //                 $startcourse = $school_pice->first()->period;
    //                 $period = $school_pice->first()->period;
    //                 // dd($startcourse,$school_pice,$period);
    //                 // dd($id_parent, $std->pid, $stdId, $std, $rec, $rec_ids, $school_pice);
    //                 // dd($startcourse,$period);
    //             }
    //             $parts = explode('-', $period);
    //             $lastTwoParts = array_slice($parts, -2);
    //             $end_date = implode('-', $lastTwoParts);

    //             $currentReceiptEndDate = Carbon::parse($end_date);

    //             if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
    //                 //พ้นสภาพนักเรียน
    //                 // $status = 'พ้นสภาพนักเรียน';
    //                 return 'drop';
    //             } else {
    //                 if ($currentMonth->greaterThan($currentReceiptEndDate)) {
    //                     //กำลังยุติการการเรียน
    //                     return 'Termination of study';
    //                 } else {
    //                     //กำลังเรียนอยู่
    //                     return 'studying . . .';
    //                 }
    //             }

    //             return 'old Student';
    //         }
    //     } elseif ($count_qty > 1) {
    //         # code...
    //         $scp = SchoolPrice::whereIn('rid', $rid)->where('qty', '>', 0)->get();
    //         $scp_ids = $scp->pluck('rid')->toArray();
    //         $school_pice = SchoolPrice::whereIn('rid', $scp_ids)->get();
    //         // dd($rec_ids, $school_pice);

    //         if (count($school_pice) > 1) {
    //             $startcourse = $school_pice->first()->period;
    //             $period = $school_pice->last()->period;
    //         } else {

    //             $startcourse = $school_pice->first()->period;
    //             $period = $school_pice->first()->period;
    //             // dd($startcourse,$school_pice,$period);
    //             // dd($id_parent, $std->pid, $stdId, $std, $rec, $rec_ids, $school_pice);
    //             // dd($startcourse,$period);
    //         }
    //         $parts = explode('-', $period);
    //         $lastTwoParts = array_slice($parts, -2);
    //         $end_date = implode('-', $lastTwoParts);

    //         $currentReceiptEndDate = Carbon::parse($end_date);

    //         if ($currentMonth->diffInMonths($currentReceiptEndDate) > 1 && $currentMonth->greaterThan($currentReceiptEndDate)) {
    //             //พ้นสภาพนักเรียน
    //             // $status = 'พ้นสภาพนักเรียน';
    //             return 'drop';
    //         } else {
    //             if ($currentMonth->greaterThan($currentReceiptEndDate)) {
    //                 //กำลังยุติการการเรียน
    //                 return 'Termination of study';
    //             } else {
    //                 //กำลังเรียนอยู่
    //                 return 'studying . . .';
    //             }
    //         }
    //     }
    // }

    //#################### 30/08/2023 ###########################

    // private function determineStatus($receipt)
    // {
    //     // Your logic to determine the student's status based on receipt and associated data
    //     // Example logic:
    //     if ($receipt->school_pice->qty >= 1) {
    //         return 'Approved';
    //     } else {
    //         return 'Pending';
    //     }
    // }

    // private function countGroupSubject($m, $e, $student)
    // {

    // $stdId = $student->id;
    // $receipt = Receipt_School::whereRaw("FIND_IN_SET($stdId, REPLACE(pid, '|', ','))")->get();

    // $rid = $receipt->pluck('id')->toArray();
    // // $SchoolPrices = SchoolPrice::where('rid', $rid)->get();
    // // $arr_test_rec = $receipt->pluck('id')->toArray();
    // $scp = SchoolPrice::where('student_id', $stdId)->where('qty', '>', 0)->get();


    // foreach ($scp as $scps) {

    //     ($scps->school_sub->group === 'M') ? $m++ : $e++;
    // }
    //     return $m;
    // }

    //############################################################################################

    // private function determineStatus($receipt)
    // {
    //     // Get all items associated with the receipt
    //     $items = $receipt->items;

    //     // Check if there are any product items with qty = 1
    //     $productItems = $items->where('qty', 1)->where('item_type', 'product');

    //     // Determine the status based on the presence of product items
    //     if ($productItems->isEmpty()) {
    //         return 'Pending';
    //     } else {
    //         // Check if all product items are accounted for (i.e., have qty > 0)
    //         $allProductItemsAccountedFor = $productItems->every(function ($item) {
    //             return $item->qty > 0;
    //         });

    //         return $allProductItemsAccountedFor ? 'Approved' : 'Pending';
    //     }
    // }



    //############################################################################################

    //save code
    // public function performRetrospectiveCheck()
    // {
    //     $receipts = Receipt_School::with('school_pice')->get();

    //     foreach ($receipts as $receipt) {
    //         $status = $this->determineStatus($receipt);
    //         $receipt->update(['status' => $status]);
    //     }

    //     return response()->json(['message' => 'Retrospective check completed']);
    // }

    // private function determineStatus($receipt)
    // {
    //     // Your logic to determine the student's status based on receipt and associated data
    //     // Example logic:
    //     if ($receipt->school_pice->qty >= 1) {
    //         return 'Approved';
    //     } else {
    //         return 'Pending';
    //     }
    // }

    //##########################################################################################


    // public function performRetrospectiveCheck()
    // {
    //     $receipts = Receipt_School::with('school_pice')->get();

    //     foreach ($receipts as $receipt) {
    //         $status = $this->determineStatus($receipt);
    //         $receipt->update(['status' => $status]);
    //     }

    //     return response()->json(['message' => 'Retrospective check completed']);
    // }

    // private function determineStatus($receipt)
    // {
    //     // Count the number of product items with qty > 0 for the receipt
    //     $productItemsCount = $receipt->school_pice()
    //         // ->where('item_type', 'product')
    //         ->where('qty', '>', 0)
    //         ->count();

    //     // Determine the status based on the count of product items
    //     if ($productItemsCount == 1) {
    //         return 'newbie';
    //     } elseif ($productItemsCount == null) {
    //         # code...
    //         return 'None Reciept';
    //     }elseif ($productItemsCount > 1) {
    //         # code...
    //         return 'old std';

    //     }
    //     else {
    //         return 'error';
    //     }
    // }
}
// save code 5/9/2023
