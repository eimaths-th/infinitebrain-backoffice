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
        $controls = OpeningClosingTime::orderBy('id', 'desc')
            ->get()
            ->groupBy('route');
        // dd($controls);
        $now = now();

        foreach ($controls as $route => $time) {
            # code...

            $times = OpeningClosingTime::where('route', $route)->latest()->first();
            //

            $openingDateTime = $now->copy()->setDateTimeFrom($times->opening_time);
            $closingDateTime = $now->copy()->setDateTimeFrom($times->closing_time);

            if ($now < $openingDateTime || $now > $closingDateTime) {
                $status = 'Offline';
                $times->update(['status' => $status]);
                // return abort(404, 'Route is currently closed.');
            } else {
                # code...
                $status = 'Online';
                $times->update(['status' => $status]);
            }
        }
        // dd($controls);
        $control = OpeningClosingTime::orderBy('id', 'desc')
            ->get();
        return view('receipt_form.form_control', [
            'controls' => $control
        ]);
    }
    public function deleteControl($id)
    {
        $control = OpeningClosingTime::find($id);
        $control->delete();
        return redirect()->back()->with('error', 'Control deleted successfully!');
    }
}
