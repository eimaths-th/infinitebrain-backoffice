<?php

namespace App\Http\Controllers;

use App\Models\ReceiptForm;
use App\Models\FileUpload;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use File;

class ReceiptFormController extends Controller
{

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
        foreach($filesa as $filea) {
            //echo $filea;
            $fileas.=$filea;
            File::move(public_path().'/form_images/'.$filea, public_path().'/receipt_form_images/'.$filea);
        }
        $filebs = "";
        foreach($filesb as $fileb) {
            //echo $fileb;
            $filebs.=$fileb;
            File::move(public_path().'/form_images/'.$fileb, public_path().'/receipt_form_images/'.$fileb);
        }

       $receiptForm = new ReceiptForm([
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
    public function edit(ReceiptForm $receiptForm)
    {
        //
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
    public function destroy(ReceiptForm $receiptForm)
    {
        //
    }
}
