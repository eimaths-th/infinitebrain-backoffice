@extends('layouts.main_template')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">รายการเดินบัญชี (Bank Statements)</h4>
                    <div>
                        <a href="{{ route('bank-statement.create') }}" class="btn btn-primary">
                            <i class="feather icon-plus"></i> เพิ่มรายการ
                        </a>
                        <a href="{{ route('bank-statement.import') }}" class="btn btn-success">
                            <i class="feather icon-upload"></i> นำเข้า Excel
                        </a>
                        <a href="{{ route('bank-statement.sync') }}" class="btn btn-info text-white">
                            <i class="feather icon-refresh-cw"></i> ดึงจากเงินเดือน
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('bank-statement.index') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ request('start_date') }}" placeholder="วันที่เริ่มต้น">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}"
                                    placeholder="วันที่สิ้นสุด">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-secondary btn-block">ค้นหา</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>รายการ</th>
                                    <th>ได้รับจาก</th>
                                    <th>ชำระโดย</th>
                                    <th class="text-right">ยอดเงิน</th>
                                    <th class="text-right">รวมเข้า</th>
                                    <th class="text-right">รวมออก</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($statements as $stmt)
                                    <tr>
                                        <td>{{ $stmt->date ? $stmt->date->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $stmt->description }}</td>
                                        <td>{{ $stmt->received_from }}</td>
                                        <td>{{ $stmt->paid_by }}</td>
                                        <td class="text-right">{{ number_format($stmt->amount, 2) }}</td>
                                        <td class="text-right text-success">{{ number_format($stmt->total_in, 2) }}</td>
                                        <td class="text-right text-danger">{{ number_format($stmt->total_out, 2) }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('bank-statement.destroy', $stmt->id) }}" method="POST"
                                                onsubmit="return confirm('ยืนยันการลบ?');" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"><i
                                                        class="feather icon-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">ไม่พบข้อมูล</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $statements->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
