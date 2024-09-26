<?php

namespace App\Http\Controllers;

use App\Exports\ExportForm;
use App\Exports\ExportExcel;
use App\Models\ReceiptForm;
use App\Models\ReceiptDetail;
use App\Models\FileUpload;
use App\Http\Controllers\Controller;
//use App\Http\Controllers\ReceiptController;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
// use File;
use Illuminate\Support\Facades\File;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;
use App\Models\Receipt_RunningNumber;
use App\Models\Receipt_Customer_RunningNumber;
use Mail;
use App\Mail\ContactMail;
use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\ExportUser;

class ReceiptFormController extends Controller
{
    const sfielda = array(

        '0' => 'reward',
        '2' => 'GMEC',
        '4' => 'IMOCSEA',
        '5' => 'INFINITE Spelling Bee',
        '6' => 'GELOSEA',
        '7' => 'LIMOC',
        '8' => 'STEM',
        '9' => 'OLPCS',
        '10' => 'ASIAROPE',
        '11' => 'Cambridge',
        '13' => 'ISOCSEA',
        '14' => 'IMOCSEA+ISOCSEA',
        '15' => 'Huaxiacup',
        '16' => 'Mathleague',
        '17' => 'test_field',
        '18' => 'mathleague-II',
        '19' => 'Huaxiacup-II',
        '20' => 'neos',
        '21' => 'neos-2',
        '22' => 'huaxiacup-final-round',
        '23' => 'ihcmi',
        '24' => 'isc',
        '25' => 'seacso',
        '26' => 'copernicus',
    );

    public function isc()
    {
        return view("receipt_form.isc");
    }
    public function gmec()
    {
        return view("receipt_form.gmec");
    }
    public function ihcmi()
    {
        return view("receipt_form.ihcmi");
    }

    public function olpcs()
    {
        return view("receipt_form.olpcs");
    }

    public function asiarope()
    {
        return view("receipt_form.asiarope");
    }
    public function test_field()
    {
        return view("receipt_form.test_field");
    }
    public function mathleague()
    {
        return view("receipt_form.mathleague");
    }

    public function mathleague_II()
    {
        return view("receipt_form.mathleague_II");
    }

    public function huaxiacup()
    {
        return view("receipt_form.huaxiacup");
    }
    public function huaxiacupFinalRound()
    {
        return view("receipt_form.huaxiacup_III");
    }
    public function neos()
    {
        return view("receipt_form.neos");
    }

    public function neos2()
    {
        return view("receipt_form.neos-II");
    }

    public function huaxiacup_II()
    {
        return view("receipt_form.huaxiacup_II");
    }

    public function img_reward()
    {
        return view("receipt_form.img_reward");
    }

    public function gelosea()
    {
        return view("receipt_form.gelosea");
    }

    public function imocsea()
    {
        return view("receipt_form.imocsea");
    }

    public function isocsea()
    {
        return view("receipt_form.isocsea");
    }

    public function imocsea_isocsea()
    {
        return view("receipt_form.imocsea_isocsea");
    }


    public function cambridge()
    {
        return view("receipt_form.cambridge");
    }
    public function seacso()
    {

        //'25' => 'seacso',
        return view("receipt_form.seacso");
    }
    public function copernicus()
    {

        //'25' => 'seacso',
        return view("receipt_form.copernicus");
    }

    public function fileStore(Request $request)
    {
        // dd($request);

        $validator = Validator::make($request->all(), [
            'file.*' => 'required', // max:10240 is 10 MB
            // 'file.*' => 'required|mimes:jpeg,png,pdf,doc,docx|max:10240', // max:10240 is 10 MB
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        $filecount = count($request->file('file'));

        $newname = "";
        for ($i = 0; $i <= ($filecount - 1); $i++) {
            $image = $request->file('file')[$i];
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
            if (Auth::user()->level == 1 || Auth::user()->level == 3 || Auth::user()->level == 0) {
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
                "receipt_forms.sfield as receipt_sfield",
                "receipt_forms.submission_date as receipt_submission_date",
                "receipt_forms.first_name as receipt_firstname",
                "receipt_forms.last_name as receipt_lastname",
                "receipt_forms.name_parent as receipt_parent_name",
                "receipt_forms.mobile as receipt_mobile",
                "receipt_forms.generate as receipt_generate",
                "receipt_forms.photo_att as file",
            )
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
                    $html = '';

                    if ($row->receipt_sfield == '23' && $row->file) {
                        $files = explode('|', $row->file);

                        foreach ($files as $file) {
                            if (!empty($file)) {
                                $filePath = public_path('receipt_form_images/' . $file);

                                if (file_exists($filePath)) {
                                    $html .= '<a href="' . asset('receipt_form_images/' . $file) . '" class="btn btn-xs btn-info btn-edit"><i class="fa fa-download"></i> Download file</a> ';
                                } else {
                                    $html .= '<p class="text-danger">File ' . htmlspecialchars($file) . ' does not exist.</p>';
                                }
                            }
                        }
                    }

                    if ($row->receipt_generate == 0) {

                        if ($row->receipt_sfield == '23') {
                            # code...
                        } else {
                            # code...
                            $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->receipt_id . '"><i class="fa fa-edit"></i> Generate Receipt</a> ';
                        }

                        $html .= '<a href="#" data-rowid="' . $row->receipt_id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    } else {

                        if ($row->receipt_sfield == '23') {
                            # code...
                        } else {
                            # code...
                            $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->receipt_id . '"><i class="fa fa-edit"></i> Edit</a> ';
                        }
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
        // if ($request->get('sfield') === "18" || $request->get('sfield') === "19") {
        // if ($request->get('sfield') === "19") {
        //     dd('error');
        // }

        // $test = Receipt::find(1893);
        // dd($test->recform->id);
        $this->check_permission();
        if ($request->get('sfield') === "17") {
            $validator =  Validator::make($request->all(), [
                'submission_date' => 'required|string',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                // 'street_address' => 'required|string',
                //'city' => 'required|string',
                //'province' => 'required|string',
                // 'postcode' => 'required|string',
                // 'name_parent' => 'required|string',
                'mobile' => 'required|string',
                'package' => 'required|string',
                //'file_att' => 'required|string',
                'description' => 'required|string',
                'grade' => 'required|string',
            ]);
        } else {
            # code...
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
                //'file_att' => 'required|string',
                'description' => 'required|string',
                'grade' => 'required|string',
            ]);
        }


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $id = $request->get('fid');
        $form = ReceiptForm::find($id);

        if ($request->get('sfield') === "17") {

            $adminEmail = $request->get('email');
            Mail::to($adminEmail)->send(new ContactMail($form));
        }



        if ($form->generate == 1) {
            return response()->json(['error' => 'Receive is already generate']);
        }

        $rnumber = Receipt_RunningNumber::generate($request->get('sfield'), $request->get('submission_date'));

        $rcnumber = Receipt_Customer_RunningNumber::generate($request->get('sfield'));
        // dd($request);

        $request->request->add(['receipt_number' => $rnumber, 'receipt_customer_number' => $rcnumber]);

        $receipt = new Receipt($request->all());
        $receipt->recform_id =  $form->id;
        $receipt->save();
        // dd(
        //     $request,
        //     $adminEmail
        // );

        //$form = ReceiptForm::find($id);


        $form->generate =  '1';
        $form->rec_id =  $receipt->id;
        $form->save();

        //add more inages
        $img = $request->get('img');
        $stu = "";
        $urlbr = "";
        if ($img != Null || $img != "") {


            foreach ($img as $fileb) {
                //echo $filea;
                // $stu .= $sss . "|";
                $oldfile = FileUpload::where('oldname', $fileb)->get();
                $stu .= $oldfile[0]->filename . "|";
                File::move(public_path() . '/form_images/' . $oldfile[0]->filename, public_path() . '/receipt_form_images/' . $oldfile[0]->filename);

                //watermark
                $urlb = url('/') . '/receipt_form_images/' . $oldfile[0]->filename;

                //watermark
                $fileex = explode("/", $urlb);
                $this->textWatermark(end($fileex), 'Date Register : ' . $request->get('submission_date'));
                $urlbr .= str_replace('receipt_form_images', 'receipt_form_watermark_images', $urlb) . "|";
                //watermark

                FileUpload::where('filename', $oldfile[0]->filename)->delete();
            }
        }

        $dataimg = ReceiptForm::find($request->get('fid'));
        if ($dataimg->file_att != Null && $dataimg->file_att != "") {
            $stu = $dataimg->file_att . "|" . $stu;
        }

        $datare = Receipt::find($receipt->id);
        if ($datare->file_att != Null && $datare->file_att != "") {
            $urlbr = $datare->file_att . "|" . $urlbr;
        }

        $stu = rtrim($stu, "|");
        $urlbr = rtrim($urlbr, "|");

        ReceiptForm::find($request->get('fid'))->update(['file_att' => $stu]);
        Receipt::find($receipt->id)->update(['file_att' => $urlbr]);
        //add more images
        $rid = $receipt->id;



        if ($request->get('sfield') === "17") {
            # code...
            $price = 500;
        } else {
            $out_price = $this->delete_all_between('(', ')', $request->get('package'));
            $price = preg_replace("/[^0-9.]/", "", $out_price);
            # code...
        }

        // $package = $request->get('package');
        // $parts1 = explode(' : ', $package); //500 baht
        // $parts2 = explode(' baht', $parts1[1]);
        // $price1 = !empty($parts2[0]) ? intval($parts2[0]) : null;


        // $package = $request->get('package');

        // // Define a regular expression pattern to match the desired format
        // $pattern = '/^([^\s]+) : (\d+) baht$/';

        // // Use preg_match to extract the message and price from the package string
        // if (preg_match($pattern, $package, $matches)) {
        //     $price2 = intval($matches[2]);
        //     $message = $matches[1];

        // } else {
        // }


        // dd($price);

        if (strtolower($request->get('dcode')) == "meta200") {
            $discount = 200;
        } elseif (strtolower($request->get('dcode')) == "cem100") {
            $discount = 100;
        } elseif (strtolower($request->get('dcode')) == "cbp100") {
            $discount = 100;
        } elseif (strtolower($request->get('dcode')) == "cbl150") {
            $discount = 150;
        } else {
            $discount = 0;
        }

        $rdata = new ReceiptDetail([
            'receipt_id' => $rid,
            'description' => $request->get('description'),
            'quantity' => 1,
            //'unit' => '',
            'unitprice' => (int)$price,
            'discount' => $discount,
            //'amount' => $request->get('book_old_price'),
        ]);
        $rdata->save();

        //secound description
        if ($request->get('sfield') == 11) {
            if (str_contains($request->get('description'), 'Primary')) {
                $sdes = 'ค่าลงทะเบียนระดับประถม';
            } else {
                $sdes = 'ค่าลงทะเบียนระดับมัธยม';
            }
        } else {
            if ($request->get('sfield') == 16 || $request->get('sfield') == 17 || $request->get('sfield') == 24 || $request->get('sfield') == 25) {
                $sdes = '';
            } elseif ($request->get('sfield') == 18 || $request->get('sfield') == 19) {

                if (str_contains($request->get('description'), 'Private')) {
                    $sdes = 'สมัครติวแบบส่วนตัว';
                } else {
                    $sdes = 'สมัครติว';
                }
            } elseif ($request->get('sfield') == 20 || $request->get('sfield') == 21) {
                # code...
                $sdes = 'สมัครสอบปกติ';
            } else {
                if (str_contains($request->get('description'), 'tutoring')) {
                    $sdes = 'สมัครสอบรวมติว';
                } elseif ($request->get('sfield') == 10) {
                    # code...
                    $sdes = '';
                } else {
                    $sdes = 'สมัครสอบไม่รวมติว';
                }
            }
        }

        $rtdata = new ReceiptDetail([
            'receipt_id' => $rid,
            'description' => $sdes,
            //'quantity' => 1,
            //'unit' => '',
            //'unitprice' => (int)$price,
            //'discount' => '',
            //'amount' => $request->get('book_old_price'),
        ]);
        $rtdata->save();

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
        if ($request->get('sfield') == '17') {
            # code...
            $validator =  Validator::make($request->all(), [
                // 'firstname' => 'required|string',
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                // 'nickname' => 'required|string',
                // 'birthdate' => 'required|string',
                // 'schoolname' => 'required|string',
                'grade' => 'required|string',
                'package' => 'required|string',
                // 'parent' => 'required|string',
                // 'address' => 'required|string',
                // 'postcode' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                // 'newsf' => 'required|string',
            ]);
        } elseif ($request->get('sfield') == '23') {
            # code...
            $validator =  Validator::make($request->all(), [
                // 'firstname' => 'required|string',
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'nickname' => 'required|string',
                'birthdate' => 'required|string',
                'schoolname' => 'required|string',
                'grade' => 'required|string',
                'address' => 'required|string',
                'postcode' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'package' => 'required|string',
                'newsf' => 'required|string',

            ]);
        } else {
            # code...
            $validator =  Validator::make($request->all(), [
                // 'firstname' => 'required|string',
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
        }

        if ($validator->fails()) {
            //return response()->json(['errors' => $validator->errors()->all()]);
            return back()->withErrors(['errors' => $validator->errors()->all()]);
        }
        // dd($request);

        if ($request->get('sfield') == '17') {
            $filesa = $request->get('imgFiles');
            $filesb = $request->get('imgFiles2');

            $fileas = "";
            $filebs = "";
            foreach ($filesb as $fileb) {
                //echo $fileb;

                $oldfile = FileUpload::where('oldname', $fileb)->get();
                $filebs .= $oldfile[0]->filename . "|";
                File::move(public_path() . '/form_images/' . $oldfile[0]->filename, public_path() . '/receipt_form_images/' . $oldfile[0]->filename);
                FileUpload::where('filename', $oldfile[0]->filename)->delete();
            }
            $filebs = rtrim($filebs, "|");
        } else {
            # code...
            $filesa = $request->get('imgFiles');
            $fileas = "";
            foreach ($filesa as $filea) {
                //echo $filea;
                $oldfile = FileUpload::where('oldname', $filea)->get();
                $fileas .= $oldfile[0]->filename . "|";
                // }
                File::move(public_path() . '/form_images/' . $oldfile[0]->filename, public_path() . '/receipt_form_images/' . $oldfile[0]->filename);

                FileUpload::where('filename', $oldfile[0]->filename)->delete();
            }
            if ($request->get('sfield') == '23') {
                # code...
                $filebs = '';
            } else {
                $fileas = rtrim($fileas, "|");
                $filesb = $request->get('imgFiles2');
                $filebs = "";
                foreach ($filesb as $fileb) {
                    //echo $fileb;
                    $oldfile = FileUpload::where('oldname', $fileb)->get();
                    $filebs .= $oldfile[0]->filename . "|";
                    File::move(public_path() . '/form_images/' . $oldfile[0]->filename, public_path() . '/receipt_form_images/' . $oldfile[0]->filename);
                    FileUpload::where('filename', $oldfile[0]->filename)->delete();
                }
                $filebs = rtrim($filebs, "|");
            }
        }

        if ($request->get('sfield') == 22) {
            # code...
            $award = $request->get('award');
        } else {
            # code...
            $award = '';
        }
        // dd($request);
        // $receiptForm = ReceiptForm::create([

        $receiptForm = new ReceiptForm([
            'sfield' => $request->get('sfield'),
            'submission_date' => date("Y-m-d"),
            'subject' => $request->get('subject'),
            'first_name' => $request->get('firstname'),
            'last_name' => $request->get('lastname'),
            'nick_name' => $request->get('nickname'),
            'gender' => $request->get('gender'),
            'birth_date' => $request->get('birthdate'),
            'school_name' => $request->get('schoolname'),
            'award' => $award,
            'grade' => $request->get('grade'),
            'package' => $request->get('package'),
            'contest' => $request->get('contest'),
            'discount_code' => $request->get('dcode'),
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

        if ($request->get('sfield') == 0 || $request->get('sfield') == 17) {
            // dd($request);
        } else {
            Mail::to($adminEmail)->send(new ContactMail($receiptForm));
        }
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
                $photo_att .= "<div><img src=\"" . $urla . "\" height=\"80\"></div>&nbsp;&nbsp;
                <a class='btn btn-xs btn-info btn-view' href=\"" . urldecode($urla) . "\" target=\"blank\"><i class='fa fa-search'></i></a>";
                //}
            }
        }
        // dd($urla);


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
                $file_att .= "<div><img src=\"" . $urlb . "\" height=\"80\"></div>&nbsp;&nbsp;
                <a class='btn btn-xs btn-info btn-view' href=\"" . urldecode($urlb) . "\" target=\"blank\"><i class='fa fa-search'></i></a><a href='#' class='btn btn-xs btn-danger btn-edit' id='getDeleteData' data-id2='" . $data->id . "' data-id='" . $arrf[$j] . "'><i class='fa fa-trash'></i></a><br><br>";
                //}
                $furl .= $urlb;
            }
        }

        $data->photo = $photo_att;
        $data->file = $file_att;

        $data->file_re = $furl;

        if ($data->generate == 0) {
            $data->rnumber = Receipt_RunningNumber::pre_generate($data->sfield, $data->submission_date);
        } else {
            $data->rnumber = '';
        }

        // dd($data->rnumber);


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

        $img = $request->get('img');
        $stu = "";
        if ($img != Null || $img != "") {


            foreach ($img as $fileb) {
                //echo $filea;
                // $stu .= $sss . "|";
                $oldfile = FileUpload::where('oldname', $fileb)->get();
                $stu .= $oldfile[0]->filename . "|";
                File::move(public_path() . '/form_images/' . $oldfile[0]->filename, public_path() . '/receipt_form_images/' . $oldfile[0]->filename);
                FileUpload::where('filename', $oldfile[0]->filename)->delete();
            }
        }

        $dataimg = ReceiptForm::find($request->get('fid'));
        if ($dataimg->file_att != Null && $dataimg->file_att != "") {
            $stu = $dataimg->file_att . "|" . $stu;
        }
        $stu = rtrim($stu, "|");
        ReceiptForm::find($request->get('fid'))->update(['file_att' => $stu]);

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

        // dd($request);

        if ($request->excel === "1") {
            # code...
            return Excel::download(new ExportExcel($request->get('sfield'), $request->get('table_records')), 'ReceiptForm.xlsx');
        } else {
            # code...
            return Excel::download(new ExportForm($request->get('sfield'), $request->get('table_records')), 'Registerform.xlsx');
        }
    }


    public function deleteImg($id, $id2)
    {
        $dataimg = ReceiptForm::find($id2);
        $img = $dataimg->file_att;
        $img = explode("|", $img);

        if (($key = array_search($id, $img)) !== false) {
            unset($img[$key]);
        }
        $dataNew = "";
        foreach ($img as $fileb) {
            $dataNew .= $fileb . "|";
        }
        $dataNew = rtrim($dataNew, "|");


        ReceiptForm::find($id2)->update(['file_att' => $dataNew]);


        $link = (url('/') . '/receipts_form/' . $id);


        $html = "";
        $pic = explode('|', $dataNew);
        foreach ($pic as $pics) {
            if ($pics != NULL && $pics != "") {
                /*  $html .= '
                <div class="col-md-4 text-center mb-3 ">
                <img src="' . url('/') . '/receipts_form/' . $pics . '" class=" mt-5 mb-3" height="400" width="300">
                <a href="#" class="btn btn-xs btn-danger btn-edit" id="getDeleteData" data-id2="' . $id2 . '" data-id="' . $pics . '">delete </a>
                </div>
                '; */
                $this->textWatermark($pics, 'Date Register : ' . $dataimg->submission_date);
                //$urlb = str_replace('receipt_form_images', 'receipt_form_watermark_images', $urlb);


                $html .= "<div><img src=\"" . url('/') . '/receipt_form_watermark_images/' . $pics . "\" height=\"80\"></div>&nbsp;&nbsp;
                <a class='btn btn-xs btn-info btn-view' href=\"" . urldecode(url('/') . '/receipt_form_watermark_images/' . $pics) . "\" target=\"blank\"><i class='fa fa-search'></i></a><a href='#' class='btn btn-xs btn-danger btn-edit' id='getDeleteData' data-id2='" . $id2 . "' data-id='" . $pics . "'><i class='fa fa-trash'></i></a><br><br>";
            } else {
                $html = "";
            }
        }
        return response()->json(['imgs' => $html]);
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
        // dd($id);

        $oldfile = ReceiptForm::find($id);
        // dd($oldfile);

        if ($oldfile->generate == 0) {
            $arrl = explode('|', $oldfile->photo_att);
            for ($i = 0; $i < count($arrl); $i++) {
                $path = public_path() . '/receipt_form_images/' . $arrl[$i];
                // echo "Attempting to delete: $path\n"; // Debug output

                if (file_exists($path)) {
                    if (!is_dir($path)) {
                        unlink($path);
                        // echo "Deleted: $path\n"; // Debug output
                    } else {
                        // echo "Skipping directory: $path\n"; // Debug output
                    }
                } else {
                    // echo "File does not exist: $path\n"; // Debug output
                }
            }
            if ($oldfile->sfield === '23') {
                # code...
            } else {
                # code...
                $arrb = explode('|', $oldfile->file_att);
                for ($j = 0; $j <= count($arrb) - 1; $j++) {
                    $path = public_path() . '/receipt_form_images/' . $arrb[$j];
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }

            //print_r($oldfile);

            ReceiptForm::where('id', $id)->delete();
        }
    }

    public function destroy($id)
    {
        //
        // dd($id);
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
