<?php

namespace App\Http\Controllers;

use App\Models\ReceiptForm;
use App\Models\FileUpload;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        return response()->json(['success' => $imageName]);
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
