<?php

namespace App\Http\Controllers;

use App\Models\BankStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BankStatementImport;

class BankStatementController extends Controller
{
    public function index(Request $request)
    {
        $query = BankStatement::query();

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $statements = $query->orderBy('date', 'desc')->paginate(50);

        return view('bank_statement.index', compact('statements'));
    }

    public function create()
    {
        return view('bank_statement.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        // Calculate totals if not provided (basic logic, can be refined)
        // Auto-calculate total_in/out based on simplistic logic if needed,
        // but primarily relying on input for now as per schema flexibility.

        BankStatement::create($data);

        return redirect()->route('bank-statement.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function showImport()
    {
        return view('bank_statement.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new BankStatementImport, $request->file('file'));
            return redirect()->route('bank-statement.index')->with('success', 'นำเข้าข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'เกิดข้อผิดพลาดในการนำเข้า: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $statement = BankStatement::findOrFail($id);
        $statement->delete();

        return redirect()->route('bank-statement.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
