<div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light sticky-top">
            <tr>
                <th width="5%">#</th>
                <th width="20%">พนักงาน</th>
                <th width="15%">วันหยุดประจำ</th>
                <th width="10%">สิทธิ์ชดเชย</th>
                <th width="50%">รายละเอียดวันชดเชย</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $user)
            @php
                $days_off_names = [];
                $day_names = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
                if($user->days_off) {
                    foreach(explode(',', $user->days_off) as $d) {
                        $days_off_names[] = $day_names[(int)$d] ?? $d;
                    }
                }
                $total_compensate = $user->compensateDays->count();
                $used_compensate = $user->compensateDays->where('is_used', true)->count();
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $user->name }}</strong>
                    <br><small class="text-muted">{{ $user->code }}</small>
                </td>
                <td>
                    @foreach($days_off_names as $dn)
                    <span class="badge bg-secondary">{{ $dn }}</span>
                    @endforeach
                </td>
                <td>
                    <span class="badge bg-success fs-6">{{ $total_compensate }}</span>
                    @if($used_compensate > 0)
                    <span class="badge bg-warning">ใช้แล้ว {{ $used_compensate }}</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($user->compensateDays as $cd)
                        <span class="badge {{ $cd->is_used ? 'bg-secondary text-decoration-line-through' : 'bg-info' }}"
                              title="{{ $cd->holiday->name ?? '' }}">
                            {{ \Carbon\Carbon::parse($cd->holiday_date)->format('d/m') }}
                            ({{ $cd->holiday->name ?? '-' }})
                        </span>
                        @endforeach
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="fas fa-info-circle me-2"></i>
                    ไม่มีพนักงานที่ได้รับสิทธิ์วันชดเชยในปี {{ $year }}
                    <br><small>วันหยุดบริษัทต้องตรงกับวันหยุดประจำสัปดาห์ของพนักงาน</small>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($users->count() > 0)
<div class="p-2 border-top">
    <small class="text-muted">
        <i class="fas fa-info-circle me-1"></i>
        พนักงานจะได้รับวันลาชดเชยเมื่อวันหยุดบริษัทตรงกับวันหยุดประจำสัปดาห์ของตนเอง
    </small>
</div>
@endif
