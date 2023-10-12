<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RouteToggleController extends Controller
{
    //
    public function showControlPage()
    {
        // Your code to display the control page
        return view('route_control'); // Create a blade view for the control page
    }

    public function toggle(Request $request, $route)
    {
        // Your code to handle the toggle action
        $status = $request->input('status'); // Assuming you have an input named 'status' in your form
        // Toggle the status of the specific route

        return redirect()->back()->with('success', 'Route status updated successfully');
    }
}
