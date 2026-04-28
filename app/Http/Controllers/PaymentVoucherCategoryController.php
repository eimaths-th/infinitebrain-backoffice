<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentVoucherCategory;

class PaymentVoucherCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('payment-voucher-category.view');
        $categories = PaymentVoucherCategory::orderBy('sort_order')->get();
        return view('payment_voucher_category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('payment-voucher-category.create');
        return view('payment_voucher_category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('payment-voucher-category.create');
        
        $request->validate([
            'key' => 'required|string|max:50|unique:payment_voucher_categories',
            'label' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        PaymentVoucherCategory::create([
            'key' => $request->key,
            'label' => $request->label,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return redirect()->route('payment-voucher-category.index')
            ->with('success', 'เพิ่มหมวดหมู่ใบสำคัญจ่ายเรียบร้อยแล้ว');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('payment-voucher-category.edit');
        $category = PaymentVoucherCategory::findOrFail($id);
        return view('payment_voucher_category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('payment-voucher-category.edit');
        
        $category = PaymentVoucherCategory::findOrFail($id);

        $request->validate([
            'key' => 'required|string|max:50|unique:payment_voucher_categories,key,' . $category->id,
            'label' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update([
            'key' => $request->key,
            'label' => $request->label,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active') ? $request->is_active : false,
        ]);

        return redirect()->route('payment-voucher-category.index')
            ->with('success', 'อัปเดตหมวดหมู่ใบสำคัญจ่ายเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('payment-voucher-category.delete');
        
        $category = PaymentVoucherCategory::findOrFail($id);
        
        // We use soft delete
        $category->delete();

        return redirect()->route('payment-voucher-category.index')
            ->with('success', 'ลบหมวดหมู่ใบสำคัญจ่ายเรียบร้อยแล้ว');
    }
}
