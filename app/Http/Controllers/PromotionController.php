<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function CheckPermission()
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->can('subjects.view')) {
                return;
            }
        }

        abort(403, 'Unauthorized action.');
    }

    public function Index(Request $request)
    {
        $this->CheckPermission();

        if ($request->ajax()) {
            $promotions = Promotion::select(
                'promotions.id as id',
                'promotions.name as name',
                'promotions.is_active as is_active'
            )
                ->orderBy('promotions.id', 'desc')
                ->get();

            return datatables()->of($promotions)
                ->editColumn('is_active', function (Promotion $promotion) {
                    if ((int) $promotion->is_active === 1) {
                        return '<span class="badge bg-success">Active</span>';
                    }

                    return '<span class="badge bg-secondary">Inactive</span>';
                })
                ->addColumn('action', function (Promotion $promotion) {
                    $toggle_label = ((int) $promotion->is_active === 1) ? 'Disable' : 'Enable';
                    $toggle_class = ((int) $promotion->is_active === 1) ? 'btn-secondary' : 'btn-success';

                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $promotion->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" class="btn btn-xs ' . $toggle_class . ' btn-toggle-status" data-id="' . $promotion->id . '"><i class="fa fa-power-off"></i> ' . $toggle_label . '</a>';

                    return $html;
                })
                ->rawColumns(['is_active', 'action'])
                ->toJson();
        }

        return view('promotions.index');
    }

    public function Store(Request $request)
    {
        $this->CheckPermission();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:promotions,name',
            'is_active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        Promotion::create([
            'name' => trim($request->get('name')),
            'is_active' => $request->get('is_active'),
        ]);

        return response()->json(['success' => 'Promotion Add successfully']);
    }

    public function Edit($id)
    {
        $this->CheckPermission();

        $promotion = Promotion::findOrFail($id);

        return response()->json(['data' => $promotion]);
    }

    public function Update(Request $request, $id)
    {
        $this->CheckPermission();

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('promotions', 'name')->ignore($id),
            ],
            'is_active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $promotion = Promotion::findOrFail($id);
        $promotion->update([
            'name' => trim($request->get('name')),
            'is_active' => $request->get('is_active'),
        ]);

        return response()->json(['success' => 'Promotion updated successfully']);
    }

    public function ToggleStatus($id)
    {
        $this->CheckPermission();

        $promotion = Promotion::findOrFail($id);
        $promotion->update([
            'is_active' => ((int) $promotion->is_active === 1) ? 0 : 1,
        ]);

        return response()->json(['success' => 'Promotion status updated successfully']);
    }
}
