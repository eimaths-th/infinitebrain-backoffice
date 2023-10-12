<?php

namespace App\Http\Controllers;

use App\Models\OpeningClosingTime;
use Illuminate\Http\Request;

class TimeController extends Controller
{
    //
    public function saveTimes(Request $request)
    {
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
        $controls = OpeningClosingTime::all();
        return view('receipt_form.form_control', [
            'controls' => $controls
        ]);
    }
}
