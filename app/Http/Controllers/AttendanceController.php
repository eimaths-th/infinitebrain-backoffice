<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\AttendanceImport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx',
        ]);

        $path = $request->file('file')->getRealPath();
        $data = Excel::import(new AttendanceImport, $path);

        return response()->json(['message' => 'Attendance data imported successfully']);
    }
}
