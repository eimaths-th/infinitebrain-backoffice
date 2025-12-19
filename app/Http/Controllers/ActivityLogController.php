<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $users = User::active()->get();
        $logs = ActivityLog::with('user')->latest()->paginate(20);

        $actions = ActivityLog::select('action')
            ->distinct()
            ->pluck('action')
            ->toArray();
        // dd($actions);

        return view('admin.logs.index', compact('logs', 'users', 'actions'));
    }

    public function filter(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        $html = '';
        foreach ($query->latest()->paginate(20) as $log) {
            $html .= '<tr>';
            $html .= '<td>' . $log->user->name . '</td>';
            $html .= '<td>' . $log->action . '</td>';
            $html .= '<td>' . $log->description . '</td>';
            $html .= '<td>';
            if ($log->data_before) {
                # code...
                $html .= '<strong>Before:</strong><br>';
                $html .= '<pre>' . json_encode($log->data_before, JSON_PRETTY_PRINT) . '</pre>';
            }
            if ($log->data_after) {
                $html .= '<strong>After:</strong><br>';
                $html .= '<pre>' . json_encode($log->data_after, JSON_PRETTY_PRINT) . '</pre>';
            }
            $html .= '</td>';
            $html .= '<td>' . $log->ip_address . '</td>';
            $html .= '<td>' . $log->user_agent . '</td>';
            $html .= '<td>' . $log->created_at->format('Y-m-d H:i:s') . '</td>';
            $html .= '</tr>';
        }
        return response()->json([
            'logs' => $query->latest()->paginate(20),
            'actions' => ActivityLog::select('action')->distinct()->pluck('action')->toArray(),
            'html' => $html,
        ]);
    }
    public function fullFilter(Request $request)
    {
        // dd($request->all());
        $users = User::active()->get();

        $actions = ActivityLog::select('action')
            ->distinct()
            ->pluck('action')
            ->toArray();

        $query = ActivityLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->input('month'));
        }

        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->input('year'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        $logs = $query->get();

        return view('admin.logs.index', compact('logs', 'users', 'actions'));
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
