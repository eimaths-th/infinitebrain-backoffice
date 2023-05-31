<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Subject;
use App\Models\Student;
use App\Models\History;

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

        $results = Subject::orderBy("id", "asc")
        ->get();

        return response()->json($results);
    }

    public function saveHistories(Request $request)
    {

        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $imageName = $image->getClientOriginalName();

            $newname = Str::random(40) . "_" . $imageName;
            $image->move(public_path('form_images'), $newname);

            $course = new History([
                'student_id' => $request->get('student'),
                'subject_id' => $request->get('subject'),
                //'level_id' => $request->get('level'),
                'datetime' => $request->get('date'),
                'stime' => $request->get('stime'),
                //'etime' => $request->get('etime'),
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
    }
}
