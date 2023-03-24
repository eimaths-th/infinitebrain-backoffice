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
        //
        $this->check_permission();

        if ($request->id == "") {
            abort(403, 'Please input subject id');
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
    public function edit(SubjectPrice $subjectPrice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SubjectPrice  $subjectPrice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SubjectPrice $subjectPrice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SubjectPrice  $subjectPrice
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubjectPrice $subjectPrice)
    {
        //
    }
}
