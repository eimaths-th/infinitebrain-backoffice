<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Subject;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
//use Auth;
use Illuminate\Support\Facades\Auth;


class LevelController extends Controller
{

    public function check_permission()
    {
        if (Auth::check()) {
            if (Auth::user()->level == 1 || Auth::user()->level == 2) {
            } else {
                abort(403, 'Unauthorized action.');
            }
        }
    }

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
        $this->check_permission();

        if ($request->id == "") {
            abort(403, 'Please input level subject');
        }
        if ($request->ajax()) {
            //sleep(2);
            $levels = Level::select(
                "levels.id as id",
                "levels.subject",
                "levels.book_per_level",
                "levels.level_sort",
                "levels.title",
                "levels.title_th",
                "subjects.title as subject_name"
            )
                ->join("subjects", "subjects.id", "=", "levels.subject")
                ->where('levels.subject', $request->id)
                ->orderBy("levels.level_sort", "desc")
                ->get();
            //print_r($levels);
            //exit();
            return datatables()->of($levels)
                ->editColumn('subject', function (Level $level) {
                    return $level->subject_name;
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

        //get subject
        $subject = Subject::orderBy("id", "asc")->get();
        return view('levels.index')->with(['data' => $subject]);
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
        //echo $request->get('sort');
        //$qq = Rule::unique('levels')
        //    ->where('subject', $request->get('subject'))->where('subject', $request->get('subject'));
        //echo $qq;
        //exit();
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'subject' => 'required',
            'book_per_level' => 'required|integer',
            'title' => 'required|string',
            'title_th' => 'required|string',
            'description' => 'required|string',
            'sort' => 'required', Rule::unique('levels')
                ->where('level_sort', $request->get('sort'))
                ->where('subject', $request->get('subject')),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        // Getting values from the blade template form
        $level = new Level([
            'subject' => $request->get('subject'),
            'book_per_level' => $request->get('book_per_level'),
            'title' => $request->get('title'),
            'title_th' => $request->get('title_th'),
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

        $this->check_permission();
        $data = Level::find($id);

        $select_list = "";
        $subject = Subject::orderBy("id", "asc")->get();
        foreach ($subject as $key) {
            if ($data->subject == $key->id) {

                $select_list .= '<option value="' . $key->id . '" selected>' . $key->title . '</option>';
            } else {
                $select_list .= '<option value="' . $key->id . '" >' . $key->title . '</option>';
            }
        }

        $html = '<div class="form-group">
                    <label for="Subject">Subject:</label>
                    <select class="form-control" id="EditSubject" name="subject" disabled>
                    <option value="" >Select Subject</option>' .
            $select_list . '
                    </select>
                </div>
                <div class="form-group">
                    <label for="Title">Title:</label>
                    <input type="text" class="form-control" name="title" id="EditTitle" value="' . $data->title . '">
                </div>
                <div class="form-group">
                    <label for="Title">Title Thai:</label>
                    <input type="text" class="form-control" name="title_th" id="EditTitleTh" value="' . $data->title_th . '">
                </div>
            
                <div class="form-group">
                    <label for="Name">Description</label>
                    <textarea class="form-control" name="description" id="EditDes">' . $data->description . '</textarea>
                </div>
                
                <div class="form-group">
                    <label for="Sort">Sort:</label>
                    <input type="number" class="form-control" id="EditSort" name="sort" value="' . $data->level_sort . '" min="1" max="99">
                </div>
                <div class="form-group">
                    <label for="Book">Book:</label>
                    <input type="number" class="form-control" id="EditBook" name="book" value="' . $data->book_per_level . '" min="1" max="99">
                </div>';

        return response()->json(['html' => $html]);
    }

    public function select_list($id)
    {
        //
        $this->check_permission();

        $data = Level::where('subject', $id)->orderBy("id", "asc")->get();

        $select_list = "";

        foreach ($data as $key) {

            $select_list .= '<option value="' . $key->id . '" > (' . $key->level_sort . ') ' . $key->title . '</option>';
        }

        $data2 = Subject::find($id);

        //print_r($data2);

        $select_list2 = "";

        for ($i = 1; $i <= $data2->book; $i++) {

            $select_list2 .= '<option value="' . $i . '" >' . $i . '</option>';
        }


        return response()->json(['html' => $select_list, 'html2' => $select_list2]);
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
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'subject' => 'required',
            'book_per_level' => 'required|integer',
            'title' => 'required|string',
            'title_th' => 'required|string',
            'description' => 'required|string',
            'level_sort' => 'required|integer',
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
        $this->check_permission();
        Level::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {

        $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Level::find($arr_del[$xx])->delete();
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        return redirect('/levels/?id=' . $request->get('id'))->with('success', 'Deleted Successfully');;
    }
}
