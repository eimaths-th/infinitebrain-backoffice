<?php

namespace App\Http\Controllers;

use App\Models\SchoolPrice;
use App\Models\Student;
use App\Models\StudentBonus;
use Illuminate\Http\Request;

class BonusStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $bonus = StudentBonus::all();
        return view('bonuses.index', [
            'bonus' => $bonus,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $students = Student::all();
        return view('bonuses.craete', [
            'students' => $students
        ]);
    }
    public function schpid($id)
    {
        //
        $studentId = $id;

        if ($studentId == null) {
            # code...
        } else {

            $std = Student::find($studentId);
            // $rc = Receipt_School::where('pid', $std->id)->get();
            // $rcIds = $rc->pluck('id'); // Retrieve all 'id' values from the collection
            $sch_p = SchoolPrice::where([
                'student_id' => $std->id,
                'course_type' => '1'
            ])->get();

            $html = '<option value="">Select Reciept Details</option>';
            foreach ($sch_p as $sch_p1) {
                # code...
                $html .= '<option value="' . $sch_p1->id . '"> ' . $sch_p1->period . ' / ' . $sch_p1->school_sub->title . ' </option>';
            }
            return response()->json([
                'studentsub' => $html
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //

        $request->validate([
            'student_id'  => 'required',
            'schoolp_id'  => 'required',
            'bonus'       => 'required',
            // 'user_id'       => 'required',
            // 'comments'      => 'required',
        ]);
        $input = $request->all();

        $studentBonus = StudentBonus::create($input);
        $studentBonus->update(['approv_id' => auth()->user()->id]);
        // dd($request);
        return redirect()->route('studentBonus.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        StudentBonus::find($id)->delete();
        return redirect()->route('studentBonus.index');

    }
}
