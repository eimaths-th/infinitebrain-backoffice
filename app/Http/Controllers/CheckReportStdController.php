<?php

namespace App\Http\Controllers;

use App\Models\CheckRecieptSchool;
use App\Models\SchoolPrice;
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
    public function DataRec($id)
    {
        //
        $schoolPrice = SchoolPrice::find($id);
        $html = '

        <div class="row">
            <div class="col-lg-4 form-group">
                <label for="Code" class="card-title text-dark">Code:</label>
                <label for="Code" class="form-label"> '. ($schoolPrice->std ? $schoolPrice->std->code : 'null').' </label>
            </div>
            <div class="col-lg-4 form-group">
                <label for="Name" class="card-title text-dark">Name:</label>
                <label for="Name" class="form-label">'. ($schoolPrice->std->full_name ? $schoolPrice->std->full_name: 'null') .'</label>
            </div>
            <div class="col-lg-4 form-group">
                <label for="Name" class="card-title text-dark">Name:</label>
                <label for="Name" class="form-label">'. ($schoolPrice->std->nick_name ? $schoolPrice->std->nick_name : 'null') .'</label>
            </div>
            <div class="col-lg-6 form-group text-nowrap">
                <label for="RecieptNumber" class="card-title text-dark">Receipt Number:</label>
                <label for="RecieptNumber" class="form-label"> '. ($schoolPrice->rec_school->receipt_number ? $schoolPrice->rec_school->receipt_number : 'null') .' </label>
            </div>
            <div class="col-lg-6 form-group text-nowrap">
                <label for="SubmissionDate" class="card-title text-dark">Submission Date:</label>
                <label for="SubmissionDate" class="form-label"> '.($schoolPrice->rec_school->submission_date ? $schoolPrice->rec_school->submission_date : 'null') .' </label>
            </div>
            </div>
                    <div class="row">
                        <div class="col-lg-3 form-group text-nowrap">
                            <label for="Subject" class="card-title text-dark">Subject:</label>
                            <label for="Subject" class="form-label"> '. ($schoolPrice->school_sub->title ? $schoolPrice->school_sub->title : 'null') .' </label>
                        </div>
                        <div class="col-lg-3 form-group text-nowrap">
                            <label for="Period" class="card-title text-dark">Period:</label>
                            <label for="Period" class="form-label"> '. ($schoolPrice->period ? $schoolPrice->period : 'null') .' </label>
                        </div>
                        <div class="col-lg-3 form-group text-nowrap">
                            <label for="Group" class="card-title text-dark">Group:</label>
                            <label for="Group" class="form-label"> '. ($schoolPrice->group ? $schoolPrice->group : 'null') . ' </label>
                        </div>
                        <div class="col-lg-3 form-group text-nowrap">
                            <label for="Total" class="card-title text-dark">Total:</label>
                            <label for="Total" class="form-label"> '. ($schoolPrice->total ? $schoolPrice->total : 'null') . ' บาท</label>
                        </div>
                    </div>
                    <label for="note" class="card-title text-dark">Note:</label>
                <textarea name="note2" id="note2" class="form-control" cols="30" rows="5"></textarea>

        ';

        return response()->json(['html' => $html]);


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
        $checkboxStates = CheckRecieptSchool::select('date_check', 'school_price_id', 'note')->get()->toArray();

        // dd($checkboxStates);
        return response()->json($checkboxStates);
    }
    public function store(Request $request)
    {
        //
        // dd($request);
        $note = $request->note;
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
                'note' => $note,
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
            $note = '';

            // dd($isChecked, 'deleted');
        }
        return response()->json(['message' => 'Success', 'isChecked' => $isChecked, 'label' => $label, 'note' => $note]);
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
