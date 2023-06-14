<?php

namespace App\Http\Controllers;

use App\Exports\ExportForm;
use App\Models\ReceiptForm;
use App\Models\ReceiptDetail;
use App\Models\FileUpload;
use App\Http\Controllers\Controller;
//use App\Http\Controllers\ReceiptController;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;
use App\Models\Receipt_RunningNumber;
use App\Models\Receipt_Customer_RunningNumber;
use Mail;
use App\Mail\ContactMail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportUser;

class ReceiptFormController extends Controller
{
    const sfielda = array('2' => 'GMEC', '4' => 'IMOCSEA', '5' => 'INFINITE Spelling Bee', '6' => 'GELOSEA', '7' => 'LIMOC', '8' => 'STEM', '9' => 'OLPCS', '10' => 'ASIAROPE'
    , '11' => 'Cambridge');

    public function gmec()
    {
        return view("receipt_form.gmec");
    }

    public function olpcs()
    {
        return view("receipt_form.olpcs");
    }

    public function asiarope()
    {
        return view("receipt_form.asiarope");
    }

    public function gelosea()
    {
        return view("receipt_form.gelosea");
    }

    public function cambridge()
    {
        return view("receipt_form.cambridge");
    }

    public function fileStore(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'file.*' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $filecount = count($request->file('file'));
        //return response()->json(['success'=>$x]);
        //print_r($request->file('file'));
        //exit();
        $newname = "";
        for ($i = 0; $i <= ($filecount - 1); $i++) {
            $image = $request->file('file')[$i];
            //print_r($image);
            //exit();
            $imageName = $image->getClientOriginalName();
            $oldfile = FileUpload::where('oldname', $imageName)->get();


            if (!is_array($oldfile)) {

                $newname = Str::random(40) . "_" . $imageName;
                $image->move(public_path('form_images'), $newname);

                $imageUpload = new FileUpload();
                $imageUpload->filename = $newname;
                $imageUpload->oldname = $imageName;
                $imageUpload->save();
            }

            //return response()->json(['success' => $newname]);

            //$newnamea[$i] = $newname;
        }
        return response()->json(['success' => $newname]);
    }

    public function fileStoreAPI(Request $request)
    {

        //$image = $request->file('file');
        //$imageName = $image->getClientOriginalName();

        //$newname = Str::random(40) . "_" . $imageName;
        //$image->move(public_path('form_images'), $newname);

        //return response()->json(['success' => $newname]);
    }


    public function fileDestroy(Request $request)
    {

        $filename =  $request->get('name');
        $oldfile = FileUpload::where('oldname', $filename)->get();
        $path = public_path() . '/form_images/' . $oldfile[0]->filename;
        if (file_exists($path)) {
            unlink($path);
            FileUpload::where('oldname', $filename)->delete();
        }
        return $filename;
    }


    public function delete_all_between($beginning, $end, $string)
    {
        $beginningPos = strpos($string, $beginning);
        $endPos = strpos($string, $end);
        if ($beginningPos === false || $endPos === false) {
            return $string;
        }

        $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

        return $this->delete_all_between($beginning, $end, str_replace($textToDelete, '', $string));
    }

    public function textWatermark($image, $text)
    {
        $img = Image::make(public_path('receipt_form_images/' . $image));

        $img->text($text, 610, 670, function ($font) {
            $font->file(public_path('font/amandasignature.ttf'));
            $font->size(60);
            $font->color('#ff0000');
            $font->align('center');
            $font->valign('bottom');
            $font->angle(80);
        });

        $img->save(public_path('receipt_form_watermark_images/' . $image));

        $img->encode('png');
        $type = 'png';
        $new_image = 'data:image/' . $type . ';base64,' . base64_encode($img);

        //return view('show_watermark', compact('new_image'));
        return  $new_image;
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
                ->addColumn('receipt_status', function ($row) {
                    if ($row->receipt_generate == 0) {
                        $html = '<i class="text-danger fa fa-edit"></i> ยังไม่ได้ออกใบเสร็จ ';
                    } else {
                        $html = '<i class="text-success fa fa-edit"></i> ออกใบเสร็จแล้ว ';
                    }
                    return $html;
                })
                ->addColumn('action', function ($row) {

                    if ($row->receipt_generate == 0) {
                        $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->receipt_id . '"><i class="fa fa-edit"></i> Generate Receipt</a> ';
                        $html .= '<a href="#" data-rowid="' . $row->receipt_id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    } else {
                        $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->receipt_id . '"><i class="fa fa-edit"></i> Edit</a> ';
                        $html .= '<a href="#" onclick="toastr.success(\'ออก Receive แล้วไม่สามารถลบได้\', {
                            timeOut: 5000
                        });" data-rowid="' . $row->receipt_id . '" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Delete</a>';
                    }
                    return $html;
                })->rawColumns(['checkbox', 'receipt_status', 'action'])->toJson();
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
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'submission_date' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'street_address' => 'required|string',
            //'city' => 'required|string',
            //'province' => 'required|string',
            'postcode' => 'required|string',
            'name_parent' => 'required|string',
            'mobile' => 'required|string',
            'package' => 'required|string',
            'file_att' => 'required|string',
            'description' => 'required|string',
            'grade' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $id = $request->get('fid');


        $rnumber = Receipt_RunningNumber::generate($request->get('sfield'));
        $rcnumber = Receipt_Customer_RunningNumber::generate($request->get('sfield'));
        $request->request->add(['receipt_number' => $rnumber, 'receipt_customer_number' => $rcnumber]);

        $receipt = new Receipt($request->all());
        $receipt->save();

        $form = ReceiptForm::find($id);
        $form->generate =  '1';
        $form->save();


        $rid = $receipt->id;

        $out_price = $this->delete_all_between('(', ')', $request->get('package'));

        $price = preg_replace("/[^0-9.]/", "", $out_price);

        $rdata = new ReceiptDetail([
            'receipt_id' => $rid,
            'description' => $request->get('description'),
            'quantity' => 1,
            //'unit' => '',
            'unitprice' => (int)$price,
            //'discount' => '',
            //'amount' => $request->get('book_old_price'),
        ]);
        $rdata->save();


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

        //print_r($filesa);
        //print_r($filesb);
        //exit();
        $fileas = "";
        foreach ($filesa as $filea) {
            //echo $filea;
            $oldfile = FileUpload::where('oldname', $filea)->get();
            $fileas .= $oldfile[0]->filename . "|";

            File::move(public_path() . '/form_images/' . $oldfile[0]->filename, public_path() . '/receipt_form_images/' . $oldfile[0]->filename);
            FileUpload::where('filename', $oldfile[0]->filename)->delete();
        }
        $filebs = "";
        foreach ($filesb as $fileb) {
            //echo $fileb;

            $oldfile = FileUpload::where('oldname', $fileb)->get();
            $filebs .= $oldfile[0]->filename . "|";
            File::move(public_path() . '/form_images/' . $oldfile[0]->filename, public_path() . '/receipt_form_images/' . $oldfile[0]->filename);
            FileUpload::where('filename', $oldfile[0]->filename)->delete();
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
            'contest' => $request->get('contest'),
            'name_parent' => $request->get('parent'),
            'street_address' => $request->get('address'),
            'postcode' => $request->get('postcode'),
            'mobile' => $request->get('phone'),
            'email' => $request->get('email'),
            'news_form' => $request->get('newsf'),
            'photo_att' => $fileas,
            'file_att' => $filebs,
        ]);

        // print_r (['sfielda' =>  self::sfielda]);
        // print_r (['sfield' => $request->sfield]);
        // exit();

        $adminEmail = $request->get('email');
        Mail::to($adminEmail)->send(new ContactMail($receiptForm));
        $receiptForm->save();

        return redirect('/form/complete/' . strtolower(self::sfielda[$request->get('sfield')]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReceiptForm  $receiptForm
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //

        return view("receipt_form.complete")->with(['sfield' => $request->sfield]);
    }
    public function show1(ReceiptForm $receiptForm)
    {
        //
        return view("receipt_form.complete1");
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

                //watermark
                $fileex = explode("/", $urlb);
                $this->textWatermark(end($fileex), 'Date Register : ' . $data->submission_date);
                $urlb = str_replace('receipt_form_images', 'receipt_form_watermark_images', $urlb);

                //$retu = $this->get_img_url($urla);
                //if ($this->URL_exists($retu)) {
                $file_att .= "<div><a href=\"" . urldecode($urlb) . "\" target=\"blank\"><img src=\"" . $urlb . "\" height=\"80\">click to view</a></div>&nbsp;&nbsp;";
                //}
                $furl .= $urlb;
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
    public function update(Request $request)
    {
        //


        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'nick_name' => 'required|string',
            'birth_date' => 'required|string',
            'school_name' => 'required|string',
            'grade' => 'required|string',
            'package' => 'required|string',
            'name_parent' => 'required|string',
            'street_address' => 'required|string',
            'postcode' => 'required|string',
            'mobile' => 'required|string',
            'email' => 'required|email',
            'news_form' => 'required|string',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }



        ReceiptForm::find($request->get('fid'))->update($request->all());
        return response()->json(['success' => 'Receipt Form updated successfully']);
    }


    public function exportForm(Request $request)
    {
        return Excel::download(new ExportForm($request->get('sfield'), $request->get('table_records')), 'form.xlsx');
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
        if ($oldfile[0]->generate == 0) {
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
