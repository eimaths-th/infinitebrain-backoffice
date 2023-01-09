<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;



class LevelController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        //echo $request->id;
        //exit();

        if ($request->id == "") {
            abort(403, 'Please input level subject');
        }
        if ($request->ajax()) {
            sleep(2);
            $levels = Level::where('subject', $request->id)->orderBy("level_sort", "desc")
                ->get();
            return datatables()->of($levels)
                ->editColumn('subject', function (Level $level) {
                    if ($level->subject == 1) {
                        $ltext = "Math";
                    } else {
                        $ltext = "English";
                    }

                    return $ltext;
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }

        return view('levels.index');
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
            'subject' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'sort' => 'required', Rule::unique('levels')->where(function ($query) use ($request) {
                return $query->where('subject', $request->get('subject'))
                    ->where('level_sort', $request->get('sort'));
            }),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        // Getting values from the blade template form
        $level = new Level([
            'subject' => $request->get('subject'),
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'level_sort' => $request->get('sort'),
        ]);
        $level->save();
        return response()->json(['success' => 'Level Add successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function show(Level $level)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $data = Level::find($id);
        if ($data->subject == 1) {
            $aselected = 'selected';
        } else {
            $aselected = '';
        }

        if ($data->subject == 0) {
            $bselected = 'selected';
        } else {
            $bselected = '';
        }

        $html = '<div class="form-group">
                    <label for="Subject">Subject:</label>
                    <select class="form-control" id="EditSubject" name="subject" disabled>
                        <option value="" selected>Select Subject</option>
                        <option value="1" ' . $aselected . '>Math</option>
                        <option value="0" ' . $bselected . '>English</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="Title">Title:</label>
                    <input type="text" class="form-control" name="title" id="EditTitle" value="' . $data->title . '">
                </div>
            
                <div class="form-group">
                    <label for="Name">Description</label>
                    <textarea class="form-control" name="description" id="EditDes">' . $data->description . '</textarea>
                </div>
                
                <div class="form-group">
                    <label for="Sort">Sort:</label>
                    <input type="number" class="form-control" id="EditSort" name="sort" value="' . $data->level_sort . '" min="1" max="99">
                </div>';

        return response()->json(['html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        //print_r($request);
        //return response()->json(['success'=>$request->all()]);
        //exit();
        $validator =  Validator::make($request->all(), [
            'subject' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'level_sort' => 'required|integer|unique:levels,' . $request->get('subject') . ',level_sort,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        Level::find($id)->update($request->all());

        return response()->json(['success' => 'Level updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        Level::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Level::find($arr_del[$xx])->delete();
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        return redirect('/levels/?id=' . $request->get('id'))->with('success', 'Deleted Successfully');;
    }
}
