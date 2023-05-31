<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Student;

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
}
