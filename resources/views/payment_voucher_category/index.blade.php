@extends('layouts.main_template')

@section('title', 'จัดการหมวดหมู่ใบสำคัญจ่าย')

@section('content')
<div class="app-main__inner">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-menu icon-gradient bg-mean-fruit"></i>
                </div>
                <div>จัดการหมวดหมู่ใบสำคัญจ่าย (Payment Voucher Categories)
                    <div class="page-title-subheading">เพิ่ม ลบ แก้ไข หมวดหมู่ใบสำคัญจ่าย</div>
                </div>
            </div>
            <div class="page-title-actions">
                @can('payment-voucher-category.create')
                <a href="{{ route('payment-voucher-category.create') }}" class="btn-shadow btn btn-success">
                    <i class="fa fa-plus"></i> เพิ่มหมวดหมู่
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <p class="mb-0">{{ $message }}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <p class="mb-0">{{ $message }}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
    </div>

    <div class="main-card mb-3 card">
        <div class="card-body">
            <table class="mb-0 table table-hover">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>รหัสหมวดหมู่ (Key)</th>
                        <th>ชื่อหมวดหมู่ (Label)</th>
                        <th>สถานะ</th>
                        <th>ลำดับการแสดงผล</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $category)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><code>{{ $category->key }}</code></td>
                        <td>{{ $category->label }}</td>
                        <td>
                            @if($category->is_active)
                            <span class="badge bg-success">เปิดใช้งาน</span>
                            @else
                            <span class="badge bg-secondary">ปิดใช้งาน</span>
                            @endif
                        </td>
                        <td>{{ $category->sort_order }}</td>
                        <td>
                            @can('payment-voucher-category.edit')
                            <a href="{{ route('payment-voucher-category.edit', $category->id) }}" class="btn btn-warning btn-sm">
                                <i class="fa fa-edit"></i> แก้ไข
                            </a>
                            @endcan

                            @can('payment-voucher-category.delete')
                            <form action="{{ route('payment-voucher-category.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบข้อมูล?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i> ลบ
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">ไม่พบข้อมูลหมวดหมู่</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
