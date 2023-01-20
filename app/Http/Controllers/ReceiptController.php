<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
//use Auth;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ReceiptImport;
use App\Exports\UsersExport;

class ReceiptController extends Controller
{

    public function check_permission()
    {
        if (Auth::check()) {
            if (Auth::user()->level == 1 || Auth::user()->level == 3) {
            } else {
                abort(403, 'Unauthorized action.');
            }
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //

        $this->check_permission();

        if ($request->ajax()) {
            //sleep(2);
            $receipt = Receipt::select(
                "receipts.id as receipt_id",
                "receipts.submission_date as receipt_submission_date",
                "receipts.first_name as receipt_firstname",
                "receipts.last_name as receipt_lastname",
                "receipts.name_parent as receipt_parent_name",
                "receipts.mobile as receipt_mobile",
            )
                //->join("students", "students.id", "=", "receipts.student_id")
                //->join("subjects", "subjects.id", "=", "receipts.subject_id")
                //->join("levels", "levels.id", "=", "receipts.level_id")

                //->where('levels.subject', $request->id)
                ->orderBy("receipts.id", "desc")
                ->get();
            //print_r($levels);
            //exit();
            return datatables()->of($receipt)
               /* ->editColumn('student_name', function (Receipt $receipt) {
                    return $receipt->student_name . ' ' . $receipt->student_lastname;
                })*/
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->receipt_id . '" class="flat" name="table_records[]" value="' . $row->receipt_id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="receipt/show/view/en/' . $row->receipt_id . '" class="btn btn-xs btn-success btn-view" id="ViewData" data-id="' . $row->receipt_id . '"><i class="fa fa-eye"></i> View Chart</a> ';
                    $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->receipt_id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->receipt_id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }

        //get subject
        //$subject = Subject::orderBy("id", "asc")->get();

        //get student
        //$student = Student::orderBy("name", "asc")->get();
        return view('receipt.index');
        //->with(['data' => $subject])->with(['data2' => $student]);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function show(Receipt $receipt)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function edit(Receipt $receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Receipt $receipt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function destroy(Receipt $receipt)
    {
        //
    }

    public function importExcel(Request $request)
    {


        if ($request->hasFile('import_file')) {
            $path = $request->file('import_file')->getRealPath();
            
            //$request->file('file')->store('files')
            Excel::import(new ReceiptImport, $path);
            return redirect()->back();
            
        } else {
            return response()->json(['errors' => 'Please insert Import file']);
        }


        return back()->with('error', 'Please Check your file, Something is wrong there.');
    }
}
