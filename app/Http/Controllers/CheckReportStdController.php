<?php

namespace App\Http\Controllers;

use App\Models\CheckRecieptSchool;
use Illuminate\Http\Request;

class CheckReportStdController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fetchCheckboxStates()
    {
        // Fetch checkbox states from CheckRecieptSchool model
        $checkboxStates = CheckRecieptSchool::pluck('date_check','school_price_id')->toArray();

        return response()->json($checkboxStates);
    }
    public function store(Request $request)
    {
        //
        // dd($request);
        $isChecked = $request->input('isChecked');
        $input = $request->input('input');
        // Extract individual parameters from the params array
        $studentId = $input['student_id'];
        $receiptSchoolId = $input['receipt_school_id'];
        $schoolPriceId = $input['school_price_id'];
        $user = auth()->user()->id;
        $currentDate = date('d/m/Y');
        $labelDate = date('d/m/Y');
        $label = '';
        if ($isChecked === 'true') {
            CheckRecieptSchool::create([
                'student_id' => $studentId,
                'receipt_school_id' => $receiptSchoolId,
                'school_price_id' => $schoolPriceId,
                'date_check' => $currentDate,
                'check_by' => $user,
            ]);
            $label = $labelDate;

            // dd($isChecked, 'created');
        } else {

            CheckRecieptSchool::where('student_id', $studentId)
                ->where('receipt_school_id', $receiptSchoolId)
                ->where('school_price_id', $schoolPriceId)
                ->delete();
            $label = 'Date: ';

            // dd($isChecked, 'deleted');
        }
        return response()->json(['message' => 'Success', 'isChecked' => $isChecked, 'label' => $label]);
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
    }
}
