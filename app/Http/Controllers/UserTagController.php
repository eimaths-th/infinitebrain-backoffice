<?php

namespace App\Http\Controllers;

use App\Models\UserTag;
use Illuminate\Http\Request;

class UserTagController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $tags = UserTag::withCount('users')->orderBy('name')->get();
        return view('user_tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:user_tags,name',
            'color' => 'nullable|string|max:20',
        ]);

        UserTag::create([
            'name' => $request->name,
            'color' => $request->color ?? 'secondary',
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'สร้าง Tag เรียบร้อยแล้ว');
    }

    public function update(Request $request, UserTag $userTag)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:user_tags,name,' . $userTag->id,
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean'
        ]);

        $userTag->update([
            'name' => $request->name,
            'color' => $request->color ?? 'secondary',
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->back()->with('success', 'แก้ไข Tag เรียบร้อยแล้ว');
    }

    public function destroy(UserTag $userTag)
    {
        if ($userTag->users()->count() > 0) {
            return redirect()->back()->with('error', 'ไม่สามารถลบ Tag ที่มีผู้ใช้งานอยู่ได้');
        }

        $userTag->delete();
        return redirect()->back()->with('success', 'ลบ Tag เรียบร้อยแล้ว');
    }
}
