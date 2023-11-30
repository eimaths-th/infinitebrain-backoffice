<?php

namespace App\Http\Controllers;

use App\Models\OpeningClosingTime;
use Illuminate\Http\Request;

class TimeController extends Controller
{
    //
    public function saveTimes(Request $request)
    {
        $request->validate([
            'opening_time'  => 'required',
            'closing_time'  => 'required',
            'route'         => 'required',
            'user_id'       => 'required',
            // 'comments'      => 'required',
        ]);

        $openingTime = $request->input('opening_time');
        $closingTime = $request->input('closing_time');
        $route = $request->input('route');
        $user_id = $request->input('user_id');
        $comments = $request->input('comments');

        OpeningClosingTime::create([
            'user_id' => $user_id,
            'comments' => $comments,
            'route' => $route,
            'opening_time' => $openingTime,
            'closing_time' => $closingTime,
        ]);

        return redirect(route('formsControl'));
    }
    public function formsTimes()
    {
        return view('route_control');
    }
    public function formsControl()
    {
        $controls = OpeningClosingTime::orderBy('id', 'desc')->get();
    
        return view('receipt_form.form_control', [
            'controls' => $controls
        ]);
    }
    public function deleteControl($id)
    {
        $control = OpeningClosingTime::find($id);
        $control->delete();
        return redirect()->back()->with('error', 'Control deleted successfully!');
    }
}
