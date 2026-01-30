<?php

namespace App\Http\Controllers;

use App\Models\BankStatement;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function showSync()
    {
        $periods = PayrollPeriod::whereIn('status', ['approved', 'paid'])->orderBy('id', 'desc')->get();
        return view('bank_statement.sync', compact('periods'));
    }

    public function syncPayroll(Request $request)
    {
        $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'sync_type' => 'required|in:net_pay,items', // Future support for items
        ]);

        $period = PayrollPeriod::with('payrolls')->findOrFail($request->payroll_period_id);

        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($period->payrolls as $payroll) {
                // Check if already synced
                if (BankStatement::where('payroll_id', $payroll->id)->exists()) {
                    continue;
                }

                if ($payroll->net_pay <= 0) continue;

                BankStatement::create([
                    'date' => $period->payment_date ?? now(),
                    'amount' => $payroll->net_pay,
                    'account_out' => 'Company Account', // Default or Config
                    'description' => "เงินเดือน {$period->period_name} - {$payroll->employee_name}",
                    'paid_by' => 'Infinite Brain',
                    'received_from' => $payroll->employee_name,
                    'total_out' => $payroll->net_pay,
                    'created_by' => auth()->id(),
                    'payroll_id' => $payroll->id,
                ]);
                $count++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        return redirect()->route('bank-statement.index')->with('success', "นำเข้าข้อมูลเงินเดือนเรียบร้อยแล้ว {$count} รายการ");
    }
}
