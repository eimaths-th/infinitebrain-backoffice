<?php

namespace App\Http\Controllers;

use App\Models\AppDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class AppDownloadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $apps = AppDownload::all();
        return view('app_download.index', [
            'apps' => $apps
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('app_download.modal_create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'file' => 'required',
        ]);
        $date = date('Y-m-d');
        // Store the file in public/app_download folder

        $file = $request->file('file');
        $version = $request->version;
        $fileName = 'v.' . $version . '_' . $date . '_' . $file->getClientOriginalName();
        $file->move(public_path('app_download'), $fileName);

        // Store file information in the database
        AppDownload::create([
            'file_name' => $fileName,
            'name' => $request->name,
            'type' => $request->type,
            'version' => $request->version,
            'upload_by' => auth()->user()->name,
        ]);
        // $uploadedFile = new AppDownload();
        // $uploadedFile->file_name = $fileName;
        // $uploadedFile->save();
        return redirect()->route('app-down-load.index')->with('success', 'File uploaded successfully');
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
        // Find the AppDownload record by ID
        $appDownload = AppDownload::findOrFail($id);

        // Construct the file path
        $filePath = public_path('app_download/' . $appDownload->file_name);
        dd($filePath);
        // Check if the file exists
        if (Storage::exists($filePath)) {
            // Attempt to delete the file
            if (Storage::delete($filePath)) {
                // If deletion successful, delete the record from the database
                $appDownload->delete();
                return redirect()->route('app-down-load.index')->with('success', 'File and record deleted successfully');
            } else {
                // If deletion failed, return error message
                return redirect()->route('app-down-load.index')->with('error', 'File could not be deleted');
            }
        } else {
            // If file not found, return error message
            return redirect()->route('app-down-load.index')->with('error', 'File not found');
        }
    }
}
