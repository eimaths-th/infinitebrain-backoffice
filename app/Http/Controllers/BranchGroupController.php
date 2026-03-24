<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BranchGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:branch-groups.view')->only(['index', 'show']);
        $this->middleware('permission:branch-groups.create')->only(['create', 'store']);
        $this->middleware('permission:branch-groups.edit')->only(['edit', 'update']);
        $this->middleware('permission:branch-groups.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        $groups = \App\Models\BranchGroup::withCount('branches')->get();
        return view('admin.branch_groups.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.branch_groups.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:branch_groups,code|max:20',
            'name' => 'required|max:100',
        ]);

        \App\Models\BranchGroup::create($request->all());

        return redirect()->route('admin.branch-groups.index')->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function edit($id)
    {
        $group = \App\Models\BranchGroup::findOrFail($id);
        return view('admin.branch_groups.edit', compact('group'));
    }

    public function update(Request $request, $id)
    {
        $group = \App\Models\BranchGroup::findOrFail($id);
        
        $request->validate([
            'code' => 'required|max:20|unique:branch_groups,code,' . $id,
            'name' => 'required|max:100',
        ]);

        $group->update($request->all());

        return redirect()->route('admin.branch-groups.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $group = \App\Models\BranchGroup::findOrFail($id);
        
        if ($group->branches()->count() > 0) {
            return redirect()->back()->with('error', 'ไม่สามารถลบได้ เนื่องจากมีสาขาภายใต้กลุ่มนี้');
        }

        $group->delete();

        return redirect()->route('admin.branch-groups.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }
}
