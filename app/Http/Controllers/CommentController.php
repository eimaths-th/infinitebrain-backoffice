<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;
use File;
use Illuminate\Support\Str;
use App\Models\FileUpload;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    //
   public function index(Request $request)
    {
        //
        // $this->check_permission();
        if ($request->ajax()) {
            //sleep(2);
            $comment = Comment::orderBy("id", "desc")
                ->get();
            return datatables()->of($comment)
                /*->editColumn('subject', function (Student $Student) {
                    if ($Student->subject == 1) {
                        $ltext = "Math";
                    } else {
                        $ltext = "English";
                    }

                    return $ltext;
                })*/
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('photo', function ($row) {
                    $html = "";
                    $pic = explode('|',$row->photo);
                    foreach($pic as $pics){
                        $html .= '<img src="'.url('/').'/comments_images/'.$pics.'" class="me-2" height="50" width="50">';
                    }
                    return $html;
                })
                ->addColumn('action', function ($row) {
                    
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->rawColumns(['checkbox', 'photo', 'action'])->toJson();
        }

        return view('comment.comment');
    }
    public function edit($id)
    {
        $html ="";
        //
        // $this->check_permission();
        
        $data = Comment::find($id);
        $html = '
        <div class="form-group">
            <label for="Name">User:</label>
            <input type="text" class="form-control" name="user" id="user" value="' . $data->user . '" disabled>
        </div>
        <div class="form-group">
            <label for="Name">Comment:</label>
            <input type="text" class="form-control" name="comment" id="comment" value="' . $data->comment . '">
        </div>
        <div class="form-group">
        <label for="Name">Photo:</label>
        </div>

  
    ';
        $pic = explode('|',$data->photo);
        foreach($pic as $pics){
         
           $html .= '
         
           <img src="'.url('/').'/comments_images/'. $pics .'" class="me-2"  name="photo" id="user" height="100" width="100">
           ';
        }
        

        return response()->json(['html' => $html]);
    }
    public function store1(Request $request)
    {
        

        //     $validator =  Validator::make($request->all(), [
        //         'user' => 'required|string',
        //         'comment' => 'required|string',
        //         // 'photo' => 'required|string',
        //     ]);
  
        
       
        // if ($validator->fails()) {
        //     //return response()->json(['errors' => $validator->errors()->all()]);
        //     return back()->withErrors(['errors' => $validator->errors()->all()]);
        // }
      
        $filesa = $request->get('imgFiles');
        $fileas = "";
        foreach ($filesa as $filea) {
            //echo $filea;
            $oldfile = FileUpload::where('oldname', $filea)->get();
            $fileas .= $oldfile[0]->filename . "|";

            File::move(public_path() . '/comment_images/' . $oldfile[0]->filename, public_path() . '/comments_images/' . $oldfile[0]->filename);
            FileUpload::where('filename', $oldfile[0]->filename)->delete();
        }
       
        $fileas = rtrim($fileas, "|");
        $comment = new Comment([
            'user' => Auth::user()->name,
            'comment' => $request->get('comment'),
            'photo' => $fileas,
            
        ]);
       
        $comment->save();
        
        return redirect('/comment');
    }
    public function update(Request $request, $id)
    {
        //
        $validator =  Validator::make($request->all(), [
            // 'code' => 'required|string|max:4|unique:parent,code,' . $id,
            'user' => 'required|string|max:255',
            'comment' => 'required|string|max:255',
            // 'mobile' => 'required|string|max:10',
            // 'photo' => 'max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        Comment::find($id)->update($request->all());

        return response()->json(['success' => 'Comment updated successfully']);
    }
    public function fileStore(Request $request)
    {
        $filecount = count($request->file('file'));
        //return response()->json(['success'=>$x]);
        // print_r($request->file('file'));
        // exit();
        $newname = "";
        for ($i = 0; $i <= ($filecount - 1); $i++) {
            $image = $request->file('file')[$i];
            //print_r($image);
            //exit();
            $imageName = $image->getClientOriginalName();
            $oldfile = FileUpload::where('oldname', $imageName)->get();


            if (!is_array($oldfile)) {

                $newname = Str::random(40) . "_" . $imageName;
                $image->move(public_path('comment_images'), $newname);

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

    public function fileDestroy(Request $request)
    {

        $filename =  $request->get('name');
        $oldfile = FileUpload::where('oldname', $filename)->get();
        $path = public_path() . '/comment_images/' . $oldfile[0]->filename;
        if (file_exists($path)) {
            unlink($path);
            FileUpload::where('oldname', $filename)->delete();
        }
        return $filename;
    }
    public function destroy_func($id)
    {
        //
        $oldfile = Comment::where('id', $id)->get();
        if ($oldfile[0]->generate == 0) {
            $arrl = explode('|', $oldfile[0]->photo);
            for ($i = 0; $i <= count($arrl) - 1; $i++) {
                $path = public_path() . '/comments/' . $arrl[$i];
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            //print_r($oldfile);

            Comment::where('id', $id)->delete();
        }
    }

    public function destroy($id)
    {
        //
        // $this->check_permission();
        $this->destroy_func($id);
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {

        // $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            $this->destroy_func($arr_del[$xx]);
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        //return redirect('/receipt/?id=' . $request->get('id'))->with('success', 'Deleted Successfully');
        return redirect('/comment')->with('success', 'Deleted Successfully');
    }
}
