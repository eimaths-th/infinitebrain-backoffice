<?php

namespace App\Http\Controllers;

use App\Models\FormGrades;
use App\Models\FormManage;
use App\Models\FormPakages;
use App\Models\OpeningClosingTime;
use Illuminate\Http\Request;

class FormManageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $data = FormManage::all();
        return view('forms_manage.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDatetime($route)
    {
        $set_time = OpeningClosingTime::where('route', $route)->orderBy('id', 'desc')->first();
        if ($set_time->status === 'Online') {
            $start = (new \DateTime($set_time->opening_time))->format('d F Y');
            $closs = (new \DateTime($set_time->closing_time))->format('d F Y');
            $date = $start . ' - ' . $closs;
        } else {
            $start = 'null date';
            $closs = 'null date';
            $date = $start . ' to ' . $closs;
        }
        return $date;
    }

    public function testing_form_manage()
    {
        return view('forms_manage.testing_form_manage');
    }
    public function selectRoute()
    {

        return view('forms_manage.select_route');
    }

    public function create(Request $request)
    {

        $form_route = $request->route;

        $data = FormManage::where([
            'form_route' => $form_route,
            'form_name' => $request->form_name
        ])->first();
        // dd($data);
        if ($data) {
            return view('forms_manage.create', compact('form_route', 'data'));
        } else {
            $dataCreate = FormManage::create([
                'form_name' => $request->form_name,
                'form_route' => $form_route,
            ]);
            $data = FormManage::find($dataCreate->id);
            return view('forms_manage.create', compact('form_route', 'data'));
        }
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
