<?php

namespace App\Http\Controllers;

use App\Models\Godeye;
use App\Models\User;
use Illuminate\Http\Request;

class GodeyeContreoller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $god = Godeye::all();
        return view('godeye.index', [
            'godeyes' => $god,

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
        $users = User::all();
        return view('godeye.create', [
            'users' => $users,
        ]);
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

        $request->validate([
            'user_id'       => 'required',
            'explain'       => 'required',
            'precursor_id'  => 'required',
        ]);
        $input = $request->all();

        $god_eye = Godeye::create($input);

        return redirect()->route('Godeye.index');
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
        $godeye = Godeye::find($id);

        // Check if the record exists
        if ($godeye) {
            // Delete the record
            $godeye->delete();

            return redirect()->route('Godeye.index')->with('success', 'Record deleted successfully');
        } else {
            // Handle the case where the record does not exist
            return redirect()->route('Godeyes.index')->with('error', 'Record not found');
        }
    }
}
