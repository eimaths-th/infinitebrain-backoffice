<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Auth;



class SubjectController extends Controller
{
    public function check_permission()
    {
        if (Auth::check()) {
            if (Auth::user()->level == 1 || Auth::user()->level == 2) {
            } else {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
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

        $this->check_permission();


        if ($request->ajax()) {
            //sleep(2);
            $subjects = Subject::select(
                "subjects.id as id",
                "subjects.book",
                "subjects.book_per_month",
                "subjects.title as subject_name",
                "subjects.title_th as subject_name_th"
            )
                //->join("subjects", "subjects.id", "=", "subjects.subject")
                //->where('subjects.subject', $request->id)
                ->orderBy("subjects.id", "asc")
                ->get();
            //print_r($subjects);
            //exit();
            return datatables()->of($subjects)
                ->editColumn('subject_name', function (Subject $subjects) {
                    return $subjects->subject_name;
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-primary btn-view" id="getViewData" data-id="' . $row->id . '"><i class="fa fa-eye"></i> View</a> ';
                    $html .= '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
        }


        return view('subjects.index');
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
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'title' => 'required|string',
            'title_th' => 'required|string',
            'book' => 'required|integer',
            'book_per_month' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        // Getting values from the blade template form
        $level = new Subject([
            'title' => $request->get('title'),
            'title_th' => $request->get('title_th'),
            'book' => $request->get('book'),
            'book_per_month' => $request->get('book_per_month'),
        ]);
        $level->save();
        return response()->json(['success' => 'Subject Add successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\Response
     */
    public function show(Subject $subject)
    {
        //
    }

    function overview(Request $request)
    {

        $this->check_permission();

        $sub = Subject::all();

        return view('subjects.view_std', [
            'subj' => $sub
        ]);
    }

    public function searchsubSTD(Request $request)
    {

        $this->check_permission();
        $subj = Subject::all();
        $id = $request->subject;
        $month = $request->month;
        $year = $request->year;
        $std = History::where('subject_id', $id)
            ->whereMonth('datetime', $month)
            ->whereYear('datetime', $year)
            ->get();
        // dd($request, $std);

        return view('subjects.view_std', [
            'subj' => $subj,
            'std'  => $std
        ]);
    }

    function view($id)
    {



        $this->check_permission();
        $month = date('m');
        $year = date('Y');
        $data = Subject::find($id);

        $subj = History::where('subject_id', $id)
            ->whereMonth('datetime', $month)
            ->whereYear('datetime', $year)
            ->get();

        $count_subj = count($subj);
        // dd($count_subj);
        // $date = thaiDate('D F Y');
        $html = '
                <div class="form-group">
                    <label for="Title">Title:</label>
                    <input type="text" class="form-control" name="title" id="EditTitle" value="' . $data->title . '">
                </div>
                <div class="form-group">
                    <label for="Title">Title Thai:</label>
                    <input type="text" class="form-control" name="title_th" id="EditTitleTh" value="' . $data->title_th . '">
                </div>
                <div class="form-group">
                   <label for="Book">Book:</label>
                <input type="number" class="form-control" id="EditBook" name="book" value="' . $data->book . '" min="1" max="99">
                </div>
                <div class="form-group">
                    <label for="BookP">Book Per Month:</label>
                    <input type="number" class="form-control" id="EditBookP" name="bookp" value="' . $data->book_per_month . '" min="1" max="99">
                </div>
                <div class="form-group mt-3">
                    <label for="BookP">จำนวนนักเรียนในคลาส ' .  $month . '/' . $year . ' :</label>
                    <label for="BookP"> ' . $count_subj . ' คน </label>
                </div>



                ';
        return response()->json(['html' => $html]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $this->check_permission();
        $data = Subject::find($id);

        $html = '
                <div class="form-group">
                    <label for="Title">Title:</label>
                    <input type="text" class="form-control" name="title" id="EditTitle" value="' . $data->title . '">
                </div>
                <div class="form-group">
                    <label for="Title">Title Thai:</label>
                    <input type="text" class="form-control" name="title_th" id="EditTitleTh" value="' . $data->title_th . '">
                </div>
                <div class="form-group">
                   <label for="Book">Book:</label>
                <input type="number" class="form-control" id="EditBook" name="book" value="' . $data->book . '" min="1" max="99">
                </div>
                <div class="form-group">
                    <label for="BookP">Book Per Month:</label>
                    <input type="number" class="form-control" id="EditBookP" name="bookp" value="' . $data->book_per_month . '" min="1" max="99">
                </div>';

        return response()->json(['html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'title' => 'required|string',
            'title_th' => 'required|string',
            'book' => 'required|integer',
            'book_per_month' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        Subject::find($id)->update($request->all());

        return response()->json(['success' => 'Subject updated successfully']);
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
        Subject::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {

        $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Subject::find($arr_del[$xx])->delete();
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        return redirect('/subjects')->with('success', 'Deleted Successfully');;
    }
}
