<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:branches.view')->only(['index', 'show']);
        $this->middleware('permission:branches.create')->only(['create', 'store']);
        $this->middleware('permission:branches.edit')->only(['edit', 'update']);
        $this->middleware('permission:branches.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        $branches = \App\Models\Branch::with('group')->get();
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        $groups = \App\Models\BranchGroup::all();
        return view('admin.branches.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:branch_groups,id',
            'code' => 'required|unique:branches,code|max:20',
            'name' => 'required|max:100',
        ]);

        \App\Models\Branch::create($request->all());

        return redirect()->route('admin.branches.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function edit($id)
    {
        $branch = \App\Models\Branch::findOrFail($id);
        $groups = \App\Models\BranchGroup::all();
        return view('admin.branches.edit', compact('branch', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $branch = \App\Models\Branch::findOrFail($id);
        
        $request->validate([
            'group_id' => 'required|exists:branch_groups,id',
            'code' => 'required|max:20|unique:branches,code,' . $id,
            'name' => 'required|max:100',
        ]);

        $branch->update($request->all());

        return redirect()->route('admin.branches.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $branch = \App\Models\Branch::findOrFail($id);
        
        // TODO: Check if branch has associated records (vouchers, students, etc.)
        // For now, just soft delete
        $branch->delete();

        return redirect()->route('admin.branches.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
