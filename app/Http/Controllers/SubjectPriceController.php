<?php

namespace App\Http\Controllers;

use App\Models\SubjectPrice;
use App\Models\Level;
use App\Models\Subject;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class SubjectPriceController extends Controller
{

    public function check_permission()
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->can('subjects.view')) {
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
        //
        $this->check_permission();

        if ($request->id == "") {
            abort(403, 'Please input subject id');
        }
        if ($request->ajax()) {
            //sleep(2);
            $subject_price = SubjectPrice::select(
                "subject_prices.id as id",
                "subjects.title as subject_title",
                "levels.title as level_title",
                "subject_prices.week as week",
                "subject_prices.price as price",
                "subject_prices.type as type",
            )
                ->join("subjects", "subjects.id", "=", "subject_prices.subject_id")
                ->join("levels", "levels.id", "=", "subject_prices.level_id")
                ->where('subject_prices.subject_id', $request->id)
                ->orderBy("subject_prices.id", "asc")
                ->get();
            //print_r($levels);
            //exit();
            if($request->id==3){
                return datatables()->of($subject_price)
                ->editColumn('subject', function (SubjectPrice $subject_price) {
                    return $subject_price->subject_title;
                })
                ->editColumn('type', function (SubjectPrice $subject_price) {
                    return $subject_price->type;
                })

                ->editColumn('level', function (SubjectPrice $subject_price) {
                    return $subject_price->level_title;
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })->toJson();
            }else{
                return datatables()->of($subject_price)
                ->editColumn('subject', function (SubjectPrice $subject_price) {
                    return $subject_price->subject_title;
                })

                ->editColumn('level', function (SubjectPrice $subject_price) {
                    return $subject_price->level_title;
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

        }

        //get subject
        $subject = Subject::orderBy("id", "asc")->get();
        $level = Level::orderBy("id", "asc")->get();
        return view('subjects_price.index')->with(['subject' => $subject])
            ->with(['level' =>  $level]);
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
        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'subject' => 'required|integer',
            'level' => 'required|integer',
            'week' => 'required|integer',
            'old_price' => 'required|integer',
            'price' => 'required|integer',
            'book' => 'required|integer',
            'book_old_price' => 'required|integer',
            'book_price' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        // Getting values from the blade template form
        if($request->get('subject')==3 ){
            $level = new SubjectPrice([
                'subject_id' => $request->get('subject'),
                'level_id' => $request->get('level'),
                'week' => $request->get('week'),
                'old_price' => $request->get('old_price'),
                'price' => $request->get('price'),
                'book' => $request->get('book'),
                'book_old_price' => $request->get('book_old_price'),
                'book_price' => $request->get('book_price'),
                'type' => $request->get('type'),

            ]);
        }else{
            $level = new SubjectPrice([
                'subject_id' => $request->get('subject'),
                'level_id' => $request->get('level'),
                'week' => $request->get('week'),
                'old_price' => $request->get('old_price'),
                'price' => $request->get('price'),
                'book' => $request->get('book'),
                'book_old_price' => $request->get('book_old_price'),
                'book_price' => $request->get('book_price'),
                'type' => 0,

            ]);
        }

        $level->save();
        return response()->json(['success' => 'Cost Add successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SubjectPrice  $subjectPrice
     * @return \Illuminate\Http\Response
     */
    public function show(SubjectPrice $subjectPrice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SubjectPrice  $subjectPrice
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $this->check_permission();
        $data = SubjectPrice::find($id);

        $select_list = "";
        $subject = Subject::orderBy("id", "asc")->get();
        foreach ($subject as $key) {
            if ($data->subject_id == $key->id) {

                $select_list .= '<option value="' . $key->id . '" selected>' . $key->title . '</option>';
            } else {
                $select_list .= '<option value="' . $key->id . '" >' . $key->title . '</option>';
            }
        }

        $select_list2 = "";
        $level = Level::orderBy("id", "asc")->get();
        foreach ($level as $key2) {
            if ($data->level_id == $key2->id) {

                $select_list2 .= '<option value="' . $key2->id . '" selected>' . $key2->title . '</option>';
            } else {
                $select_list2 .= '<option value="' . $key2->id . '" >' . $key2->title . '</option>';
            }
        }

        $select_list3 ="";
        $week = array("1"=>"1 week","2"=>"2 week","3"=>"3 week","4"=>"4 week");
        foreach ($week as $key3 => $value3) {
            if ($data->week == $key3) {

                $select_list3 .= '<option value="' . $key3 . '" selected>' . $value3 . '</option>';
            } else {
                $select_list3 .= '<option value="' . $key3 . '" >' . $value3 . '</option>';
            }
        }
        $select_list4 ="";
        $type = array("1"=>"One Type แบบเดี่ยว","2"=>"Couple แบบคู่");
        foreach ($type as $key4 => $value4) {
            if ($data->type == $key4) {

                $select_list4 .= '<option value="' . $key4 . '" selected>' . $value4 . '</option>';
            } else {
                $select_list4 .= '<option value="' . $key4 . '" >' . $value4 . '</option>';
            }
        }
        if ($data->subject_id == 3) {
            $html = '<div class="form-group">
                    <label for="Subject">Subject:</label>
                    <select class="form-control" id="EditSubject" name="subject" disabled>
                    <option value="" >Select Subject</option>' .
                     $select_list . '
                    </select>
                </div>
                <div class="form-group">
                    <label for="Level">Level:</label>
                    <select class="form-control" id="EditLevel" name="level" disabled>
                    <option value="" >Select Subject</option>' .
                     $select_list2 . '
                    </select>
                </div>
                <div class="form-group">
                            <label for="Type">Type:</label>
                            <select class="form-control" id="EditType" name="type">
                                <option value="" selected>Select Type</option>
                                ' .
                     $select_list4 . '
                            </select>
                        </div>
                <div class="form-group">
                            <label for="Title">Week:</label>
                            <select class="form-control" id="EditWeek" name="week">
                                <option value="" selected>Select Week</option>
                                ' .
                     $select_list3 . '
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Oldprice">Course Old Price:</label>
                            <input type="number" class="form-control" id="EditOprice" name="old_price" value="' . $data->old_price . '"
                                min="1" max="99">
                        </div>
                        <div class="form-group">
                            <label for="Price">Course Price:</label>
                            <input type="number" class="form-control" id="EditPrice" name="price" value="' . $data->price . '"
                                min="1" max="99">
                        </div>
                        <div class="form-group">
                            <label for="Book">Booklets:</label>
                            <input type="number" class="form-control" id="EditBook" name="book" value="' . $data->book . '"
                                min="1" max="99">
                        </div>
                        <div class="form-group">
                            <label for="Price">Book Old Price:</label>
                            <input type="number" class="form-control" id="EditBoprice" name="boprice" value="' . $data->book_old_price . '"
                                min="1" max="99">
                        </div>
                        <div class="form-group">
                            <label for="Price">Book Price:</label>
                            <input type="number" class="form-control" id="EditBprice" name="bprice" value="' . $data->book_price . '"
                                min="1" max="99">
                        </div>';
        }else{
        $html = '<div class="form-group">
                    <label for="Subject">Subject:</label>
                    <select class="form-control" id="EditSubject" name="subject" disabled>
                    <option value="" >Select Subject</option>' .
                     $select_list . '
                    </select>
                </div>
                <div class="form-group">
                    <label for="Level">Level:</label>
                    <select class="form-control" id="EditLevel" name="level" disabled>
                    <option value="" >Select Subject</option>' .
                     $select_list2 . '
                    </select>
                </div>
                <div class="form-group">
                            <label for="Title">Week:</label>
                            <select class="form-control" id="EditWeek" name="week">
                                <option value="" selected>Select Week</option>
                                ' .
                     $select_list3 . '
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Oldprice">Course Old Price:</label>
                            <input type="number" class="form-control" id="EditOprice" name="old_price" value="' . $data->old_price . '"
                                min="1" max="99">
                        </div>
                        <div class="form-group">
                            <label for="Price">Course Price:</label>
                            <input type="number" class="form-control" id="EditPrice" name="price" value="' . $data->price . '"
                                min="1" max="99">
                        </div>
                        <div class="form-group">
                            <label for="Book">Booklets:</label>
                            <input type="number" class="form-control" id="EditBook" name="book" value="' . $data->book . '"
                                min="1" max="99">
                        </div>
                        <div class="form-group">
                            <label for="Price">Book Old Price:</label>
                            <input type="number" class="form-control" id="EditBoprice" name="boprice" value="' . $data->book_old_price . '"
                                min="1" max="99">
                        </div>
                        <div class="form-group">
                            <label for="Price">Book Price:</label>
                            <input type="number" class="form-control" id="EditBprice" name="bprice" value="' . $data->book_price . '"
                                min="1" max="99">
                        </div>';
                    }
        return response()->json(['html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SubjectPrice  $subjectPrice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $this->check_permission();
        $validator =  Validator::make($request->all(), [
            'subject' => 'required|integer',
            'level' => 'required|integer',
            'week' => 'required|integer',
            'old_price' => 'required|integer',
            'price' => 'required|integer',
            'book' => 'required|integer',
            'book_old_price' => 'required|integer',
            'book_price' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        SubjectPrice::find($id)->update($request->all());

        return response()->json(['success' => 'Cost updated successfully']);
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
        SubjectPrice::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted Successfully'];
    }

    public function destroy_all(Request $request)
    {

        $this->check_permission();
        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            SubjectPrice::find($arr_del[$xx])->delete();
        }
        //$ids = $request->get('table_records');
        //User::whereIn('id',explode(",",$ids))->delete();
        return redirect('/subjects_price/?id=' . $request->get('id'))->with('success', 'Deleted Successfully');;
    }
}
