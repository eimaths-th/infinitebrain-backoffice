<?php

namespace App\Http\Controllers;

use App\Models\ReceiptForm;
use App\Models\FileUpload;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ReceiptController;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use File;
use Illuminate\Support\Facades\Auth;

class ReceiptFormController extends Controller
{
    const sfielda = array('2' => 'GMEC', '4' => 'IMOCSEA', '5' => 'INFINITE Spelling Bee', '6' => 'GELOSEA', '7' => 'LIMOC', '8' => 'STEM');

    public function gmec()
    {
        return view("receipt_form.gmec");
    }

    public function fileStore(Request $request)
    {
        $filecount = count($request->file('file'));
        //return response()->json(['success'=>$x]);
        //print_r($request->file('file'));
        //exit();
        for ($i = 0; $i <= ($filecount - 1); $i++) {
            $image = $request->file('file')[$i];
            //print_r($image);
            //exit();
            $imageName = $image->getClientOriginalName();
            $newname = Str::random(40) . "_" . $imageName;
            $image->move(public_path('form_images'), $newname);

            $imageUpload = new FileUpload();
            $imageUpload->filename = $newname;
            $imageUpload->oldname = $imageName;
            $imageUpload->save();
            //return response()->json(['success' => $imageName]);
        }
        return response()->json(['success' => $newname]);
    }

    public function fileDestroy(Request $request)
    {

        $filename =  $request->get('name');
        $oldfile = FileUpload::where('filename', $filename)->get();
        $path = public_path() . '/form_images/' . $oldfile[0]->filename;
        if (file_exists($path)) {
            unlink($path);
            FileUpload::where('filename', $filename)->delete();
        }
        return $filename;
    }




    public function check_permission()
    {
        if (Auth::check()) {
            if (Auth::user()->level == 1 || Auth::user()->level == 3) {
                return true;
            } else {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
            //return Redirect("login")->withSuccess('Please Login');
            //exit();
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
        if ($request->sfield == "") {
            abort(403, 'Please input Field');
        }
        if ($request->ajax()) {
            //sleep(2);
            $receipt = ReceiptForm::select(
                "receipt_forms.id as receipt_id",
                "receipt_forms.submission_date as receipt_submission_date",
                "receipt_forms.first_name as receipt_firstname",
                "receipt_forms.last_name as receipt_lastname",
                "receipt_forms.name_parent as receipt_parent_name",
                "receipt_forms.mobile as receipt_mobile",
                "receipt_forms.generate as receipt_generate",
            )
                //->join("students", "students.id", "=", "receipts.student_id")
                //->join("subjects", "subjects.id", "=", "receipts.subject_id")
                //->join("levels", "levels.id", "=", "receipts.level_id")

                ->where('receipt_forms.sfield', $request->sfield)
                ->orderBy("receipt_forms.id", "desc")
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
                ->editColumn('receipt_generate', function ($row) {
                    if ($row->receipt_generate == 0) {
                        $html = '<font class="text-danger"> ยังไม่ได้ออกใบเสร็จ </font>';
                    } else {
                        $html = '<font class="text-success"> ออกใบเสร็จแล้ว </font>';
                    }
                    return $html;
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->receipt_id . '"><i class="fa fa-edit"></i> Generate Receipt</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->receipt_id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }

        //get subject
        //$subject = Subject::orderBy("id", "asc")->get();

        //get student
        //$student = Student::orderBy("name", "asc")->get();

        return view('receipt_form.index')
            ->with(['sfielda' =>  self::sfielda])
            ->with(['sfield' => $request->sfield]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $receipt = new Receipt();
        $receipt->store($request);
        ReceiptForm::find($request->get('id'))->update($request->all());
        return response()->json(['success' => 'Receive Add successfully']);
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
        $validator =  Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'nickname' => 'required|string',
            'birthdate' => 'required|string',
            'schoolname' => 'required|string',
            'grade' => 'required|string',
            'package' => 'required|string',
            'parent' => 'required|string',
            'address' => 'required|string',
            'postcode' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'newsf' => 'required|string',
        ]);

        if ($validator->fails()) {
            //return response()->json(['errors' => $validator->errors()->all()]);
            return back()->withErrors(['errors' => $validator->errors()->all()]);
        }
        $filesa = $request->get('imgFiles');
        $filesb = $request->get('imgFiles2');
        $fileas = "";
        foreach ($filesa as $filea) {
            //echo $filea;
            $fileas .= $filea . "|";
            File::move(public_path() . '/form_images/' . $filea, public_path() . '/receipt_form_images/' . $filea);
            FileUpload::where('filename', $filea)->delete();
        }
        $filebs = "";
        foreach ($filesb as $fileb) {
            //echo $fileb;
            $filebs .= $fileb . "|";
            File::move(public_path() . '/form_images/' . $fileb, public_path() . '/receipt_form_images/' . $fileb);
            FileUpload::where('filename', $fileb)->delete();
        }

        $filebs = rtrim($filebs, "|");
        $fileas = rtrim($fileas, "|");

        $receiptForm = new ReceiptForm([
            'sfield' => $request->get('sfield'),
            'submission_date' => date("Y-m-d"),
            'first_name' => $request->get('firstname'),
            'last_name' => $request->get('lastname'),
            'nick_name' => $request->get('nickname'),
            'birth_date' => $request->get('birthdate'),
            'school_name' => $request->get('schoolname'),
            'grade' => $request->get('grade'),
            'package' => $request->get('package'),
            'name_parent' => $request->get('parent'),
            'street_address' => $request->get('address'),
            'postcode' => $request->get('postcode'),
            'mobile' => $request->get('phone'),
            'email' => $request->get('email'),
            'news_form' => $request->get('newsf'),
            'photo_att' => $fileas,
            'file_att' => $filebs,
        ]);
        $receiptForm->save();
        return redirect('/form/complete');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReceiptForm  $receiptForm
     * @return \Illuminate\Http\Response
     */
    public function show(ReceiptForm $receiptForm)
    {
        //
        return view("receipt_form.complete");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReceiptForm  $receiptForm
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        //
        $this->check_permission();
        $data = ReceiptForm::find($id);

        $photo_att = '';
        if ($data->photo_att) {

            $arrl = explode('|', $data->photo_att);
            for ($i = 0; $i <= count($arrl) - 1; $i++) {
                //$urla = "https://" . $arrl[$i];
                $urla = url('/') . '/receipt_form_images/' . $arrl[$i];
                //$retu = $this->get_img_url($urla);
                //if ($this->URL_exists($retu)) {
                $photo_att .= "<div><a href=\"" . urldecode($urla) . "\" target=\"blank\"><img src=\"" . $urla . "\" height=\"80\">click to view</a></div>&nbsp;&nbsp;";
                //}
            }
        }

        $file_att = '';
        $furl = "";
        if ($data->file_att) {

            $arrf = explode('|', $data->file_att);
            for ($j = 0; $j <= count($arrf) - 1; $j++) {
                //$urla = "https://" . $arrl[$i];
                $urlb = url('/') . '/receipt_form_images/' . $arrf[$j];
                //$retu = $this->get_img_url($urla);
                //if ($this->URL_exists($retu)) {
                $file_att .= "<div><a href=\"" . urldecode($urlb) . "\" target=\"blank\"><img src=\"" . $urlb . "\" height=\"80\">click to view</a></div>&nbsp;&nbsp;";
                //}
                $furl.=$urlb;
            }
        }

        $data->photo = $photo_att;
        $data->file = $file_att;

        $data->file_re = $furl;

        return response()->json([
            'data' =>  $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReceiptForm  $receiptForm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReceiptForm $receiptForm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReceiptForm  $receiptForm
     * @return \Illuminate\Http\Response
     */
    public function destroy_func($id)
    {
        //
        $oldfile = ReceiptForm::where('id', $id)->get();
        $arrl = explode('|', $oldfile[0]->photo_att);
        for ($i = 0; $i <= count($arrl) - 1; $i++) {
            $path = public_path() . '/receipt_form_images/' . $arrl[$i];
            if (file_exists($path)) {
                unlink($path);  
            }
        }

        $arrb = explode('|', $oldfile[0]->file_att);
        for ($j = 0; $j <= count($arrb) - 1; $j++) {
            $path = public_path() . '/receipt_form_images/' . $arrb[$j];
            if (file_exists($path)) {
                unlink($path);  
            }
        }
        //print_r($oldfile);

        ReceiptForm::where('id', $id)->delete();
    }

    public function destroy($id)
    {
        //
        $this->check_permission();
        $this->destroy_func($id);
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {

        $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            $this->destroy_func($arr_del[$xx]);
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        //return redirect('/receipt/?id=' . $request->get('id'))->with('success', 'Deleted Successfully');
        return redirect('/receipts_form/?sfield=' . $request->get('sfield'))->with('success', 'Deleted Successfully');
    }
}
