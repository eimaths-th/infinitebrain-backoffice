<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Subject;
use App\Models\Student;
use App\Models\History;
use App\Models\SchoolPrice;
use App\Models\Receipt_School;
use Carbon\Carbon;

class APIController extends Controller
{

    public function searchStudent(Request $request)
    {
        //$query = $request->query('query');

        $results = Student::orderBy("code", "asc")
            ->get();

        return response()->json($results);
    }
    //
    public function searchSubject(Request $request)
    {
        //$query = $request->query('query');

        /* $results = Subject::orderBy("id", "asc")
            ->get(); */

        $studentId = $request->get('std');

        if ($studentId == null) {
            # code...
            $results = [];
        } else {

            $std = Student::find($studentId);
            // $rc = Receipt_School::where('pid', $std->id)->get();
            // $rcIds = $rc->pluck('id'); // Retrieve all 'id' values from the collection
            $sch_p = SchoolPrice::where('student_id', $std->id)->get();
            $sch_p_sub = [];

            foreach ($sch_p as $sch_p1) {
                # code...
                if ($sch_p1->qty !== null) {
                    $sch_p_sub[] = $sch_p1->subject;
                }
            }

            $results = Subject::orderBy("id", "asc")
                ->whereIn('id', $sch_p_sub)
                ->get();
        }

        return response()->json($results);
    }

    public function stdsub($id)
    {
        $studentId = $id;

        if ($studentId == null) {
            # code...
        } else {

            $std = Student::find($studentId);
            $rc = Receipt_School::where('pid', $std->id)->get();
            $rcIds = $rc->pluck('id'); // Retrieve all 'id' values from the collection
            $sch_p = SchoolPrice::whereIn('rid', $rcIds)->get();
            $sch_p_sub = [];

            foreach ($sch_p as $sch_p1) {
                # code...
                if ($sch_p1->qty !== null) {
                    $sch_p_sub[] = $sch_p1->subject;
                }
            }

            $subject = Subject::orderBy("id", "asc")
                ->whereIn('id', $sch_p_sub)
                ->get();


            $html = '<option value="">Select Subject</option>';
            foreach ($subject as $subject1) {
                # code...
                $html .= '<option value="' . $subject1->id . '"> ' . $subject1->title . ' </option>';
            }
            return response()->json([
                'studentsub' => $html
            ]);
        }
    }

    /* public function saveHistories(Request $request)
    {

        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $imageName = $image->getClientOriginalName();

            $newname = Str::random(40) . "_" . $imageName;
            $image->move(public_path('form_images'), $newname);

            //endtime
            $time =  $request->get('stime'); // Time string
            $durationHours = $request->get('hour'); // Duration in hours to add

            $updatedTime = Carbon::createFromFormat('H:i:s', $time)
                ->addHours($durationHours)
                ->format('H:i:s');

            $course = new History([
                'student_id' => $request->get('student'),
                'subject_id' => $request->get('subject'),
                //'level_id' => $request->get('level'),
                'datetime' => $request->get('date'),
                'stime' => $request->get('stime'),
                'etime' => $updatedTime,
                //'description' => $request->get('des'),
                'signature' => $newname,
            ]);
            $course->save();

            return response()->json([
                'message' => 'Data Save Successfully',
                'file' => $newname
            ]);
        }

        return response()->json(['message' => 'No file found'], 400);
    } */

    public function saveHistories(Request $request)
    {
        $today = $request->get('date'); // Y-m-d format

        // Your date range in the format "2024-01 - 2024-01"
        $range = SchoolPrice::where('student_id', $request->get('student'))
            ->orderBy("id", "desc")
            ->first();

        //dd($range);

        // Check if $range is not null and $range->period is not an empty string
        if ($range && $range->period !== '') {
            $dateRange = $range->period;

            $rem = str_replace(" ", "", $dateRange);

            // Split the date range into start and end months
            list($startMonth, $endMonth) = explode('-', str_replace(" ", "", $dateRange));

            // Convert start and end months to Carbon instances
            $startCarbon = Carbon::parse(substr($rem, 0, 6))->startOfMonth();
            $endCarbon = Carbon::parse(substr($rem, 7, 6))->endOfMonth();

            // Convert the current date to a Carbon instance
            $todayCarbon = Carbon::parse($today);

            // Check if the present date is within the date range
            $isWithinRange = $todayCarbon->between($startCarbon, $endCarbon);

            if ($isWithinRange) {
                if ($request->hasFile('file')) {
                    // Your logic for handling file upload
                    $image = $request->file('file');
                    $imageName = $image->getClientOriginalName();

                    $newname = Str::random(40) . "_" . $imageName;
                    $image->move(public_path('form_images'), $newname);

                    //endtime
                    $time =  $request->get('stime'); // Time string
                    $durationHours = $request->get('hour'); // Duration in hours to add

                    $updatedTime = Carbon::createFromFormat('H:i:s', $time)
                        ->addHours($durationHours)
                        ->format('H:i:s');

                    $course = new History([
                        'student_id' => $request->get('student'),
                        'subject_id' => $request->get('subject'),
                        'school_price_id' => $range->id,
                        'datetime' => $request->get('date'),
                        'stime' => $request->get('stime'),
                        'etime' => $updatedTime,
                        'signature' => $newname,
                    ]);

                    $course->save();

                    return response()->json([
                        'message' => 'Data Save Successfully',
                        'file' => $newname
                    ]);
                }

                return response()->json(['message' => 'No file found'], 400);
            } else {
                // Present date is outside the specified range
                return response()->json(['message' => 'Date is outside of school price period.'], 400);
            }
        } else {
            /// $range is null or $range->period is an empty string
            return response()->json(['message' => 'School price range not found.'], 400);
        }
    }
}
