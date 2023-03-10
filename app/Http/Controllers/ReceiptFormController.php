<?php

namespace App\Http\Controllers;

use App\Models\ReceiptForm;
use App\Models\FileUpload;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReceiptFormController extends Controller
{

    public function gmec() {
        return view("receipt_form.gmec");
    }

    public function fileStore(Request $request)
    {
        $image = $request->file('file')[0];
        //print_r($image);
        //exit();
        $imageName = $image->getClientOriginalName();
        $image->move(public_path('form_images'),$imageName);
        
        $imageUpload = new FileUpload();
        $imageUpload->filename = $imageName;
        $imageUpload->save();
        return response()->json(['success'=>$imageName]);
    }

    public function fileDestroy(Request $request)
    {

        $filename =  $request->get('name');
        FileUpload::where('filename',$filename)->delete();
        $path=public_path().'/form_images/'.$filename;
        if (file_exists($path)) {
            unlink($path);
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
