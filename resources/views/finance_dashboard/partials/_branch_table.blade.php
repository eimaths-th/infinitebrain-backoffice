<div class="table-responsive">
    <table class="table table-dashboard mb-0">
        <thead>
            <tr>
                <th>สาขา</th>
                <th class="text-center">จำนวน</th>
                <th class="text-end">ยอดรวม</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $branch)
                <tr>
                    <td>
                        <span class="badge bg-light text-dark me-1">{{ $branch->branch_code }}</span>
                        {{ $branch->branch_name }}
                    </td>
                    <td class="text-center">{{ number_format($branch->count) }}</td>
                    <td class="text-end fw-bold">฿{{ number_format($branch->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูล</td>
                </tr>
            @endforelse
            @if(count($data) > 0)
            <tr>
                <td colspan="1" class="text-end fw-bold">รวมทั้งหมด</td>
                <td class="text-center fw-bold">{{ number_format($data->sum('count')) }}</td>
                <td class="text-end fw-bold">฿{{ number_format($data->sum('total'), 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
