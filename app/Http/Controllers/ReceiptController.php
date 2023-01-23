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
                    $html = '<a href="receipt/show/view/' . $row->receipt_id . '" class="btn btn-xs btn-success btn-view" id="ViewData" data-id="' . $row->receipt_id . '"><i class="fa fa-eye"></i> View Receipt</a> ';
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
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'submission_date' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'street_address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'postcode' => 'required|string',
            'name_parent' => 'required|string',
            'mobile' => 'required|string',
            'package' => 'required|string',
            'file_att' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $course = new Receipt($request->all());
        $course->save();
        return response()->json(['success' => 'Course Add successfully']);
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
    public function edit($id)
    {
        //
        $this->check_permission();
        $data = Receipt::find($id);

        $html = '<div class="form-row">
                   <div class="col-md-6">
                        <div class="position-relative form-group">
                            <label for="Submission Date">Submission Date:</label>
                            <input type="text" class="AddDate form-control" name="submission_date" id="EditSubmissiondate" value="' . $data->submission_date . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="position-relative  form-group">
                            <label for="Package">Package:</label>
                            <input type="text" class="form-control" name="package" id="EditPackage" value="' . $data->package . '">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                   <div class="col-md-6">
                    <div class="position-relative form-group">
                        <label for="firstname">First Name:</label>
                        <input type="text" class="form-control" name="firstname" id="EditFirstname" value="' . $data->first_name . '">
                    </div>
                   </div>
                   <div class="col-md-6">
                    <div class="position-relative form-group">
                        <label for="lastname">Last Name:</label>
                        <input type="text" class="form-control" name="lastname" id="EditLastname" value="' . $data->last_name . '">
                    </div>
                  </div>
                </div> 
                
                <div class="form-row">
                <div class="col-md-6">
                        <div class="position-relative form-group">
                        <label for="City">City:</label>
                        <input type="text" class="form-control" name="city" id="EditCity" value="' . $data->city . '">
                    </div>
                </div>
                <div class="col-md-6">
                        <div class="position-relative form-group">
                        <label for="Province">Province:</label>
                        <input type="text" class="form-control" name="province" id="EditProvince" value="' . $data->province . '">
                    </div>
                 </div>
                </div> 
                
                <div class="form-row">
                <div class="col-md-6">
                     <div class="form-group">
                        <label for="Parent">Name Parent:</label>
                        <input type="text" class="form-control" name="name_parent" id="EditParent" value="' . $data->name_parent . '">
                    </div>
                </div>
                <div class="col-md-6">
                        <div class="form-group">
                        <label for="Mobile">Mobile:</label>
                        <input type="text" class="form-control" name="mobile" id="EditMobile" value="' . $data->mobile . '">
                    </div> 
                 </div>
                </div>    
                <div class="form-group">
                <label for="Postcode">Postcode:</label>
                <input type="text" class="form-control" name="postcode" id="EditPostcode" value="' . $data->postcode . '">
            </div>
                <div class="form-group">
                    <label for="Name">Street Address:</label>
                    <textarea class="form-control" name="address" id="EditAddress">' . $data->street_address . '</textarea>
                </div>
                <div class="form-group">
                    <label for="Name">Street Address 2:</label>
                    <textarea class="form-control" name="address2" id="EditAddress2">' . $data->street_address2 . '</textarea>
                </div>

                <div class="form-group">
                    <label for="File">File:</label>
                    <input type="text" class="form-control" id="EditFile" name="file" value="' . $data->file_att . '">
                </div>';

        return response()->json(['html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'submission_date' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'street_address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'postcode' => 'required|string',
            'name_parent' => 'required|string',
            'mobile' => 'required|string',
            'package' => 'required|string',
            'file_att' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        Receipt::find($id)->update($request->all());

        return response()->json(['success' => 'Receipt updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Receipt  $receipt
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $this->check_permission();
        Receipt::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {

        $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Receipt::find($arr_del[$xx])->delete();
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        //return redirect('/receipt/?id=' . $request->get('id'))->with('success', 'Deleted Successfully');
        return redirect('/receipts')->with('success', 'Deleted Successfully');;
    }

    public function importExcel(Request $request)
    {
        if ($request->hasFile('import_file')) {
            $path = $request->file('import_file')->getRealPath();

            //$request->file('file')->store('files')
            Excel::import(new ReceiptImport($request->get('sfield')), $path);
            return redirect()->back()->with('success', 'Import Data Complete');
        } else {
            return response()->json(['errors' => 'Please Check your file, Something is wrong there.']);
        }


        return back()->with('error', 'Please Check your file, Something is wrong there.');
    }
}
