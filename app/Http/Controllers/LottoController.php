<?php

namespace App\Http\Controllers;

use App\Models\LottoMain;
use App\Models\LottoResult;
use Illuminate\Http\Request;

class LottoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $types = LottoMain::orderBy("id", "desc")->get();
            return datatables()->of($types)
                ->editColumn('type', function (LottoMain $type) {
                    return $type->type == 0 ? "โต๊ด" : "Normal";
                })
                ->editColumn('max', function (LottoMain $max) {
                    return number_format($max->max) . ' บาท';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })
                ->toJson();
        }

        return view('lottoes.main');
    }
    public function result(Request $request)
    {
        if ($request->ajax()) {
            $types = LottoResult::orderBy("id", "desc")->get();
            return datatables()->of($types)

                ->addColumn('action', function ($row) {
                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" data-rowid="' . $row->id . '" class="btn btn-xs btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</a>';
                    return $html;
                })
                ->toJson();
        }

        return view('lottoes.results');
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
        // dd($request->all());
        $length = strlen($request->numbers);
        $lotto = LottoMain::create([
            'numbers' => $request->numbers,
            'length' => $length,
            'max' => $request->max,
            'date' => $request->date,
            'type' => $request->type,
        ]);
        return response()->json(['success' => 'success !']);
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
        //
    }
}
