{{-- ใบขอใช้วันหยุดชดเชย / สลับวันหยุด --}}

<div class="header">
    <h1>ใบขอใช้วันหยุดชดเชย / ขอสลับวันหยุด</h1>
</div>

<div class="date-line">
    วันที่ <span class="dotted-line">{{ $leave_request->created_at->day }}</span>
    เดือน <span class="dotted-line">{{ $leave_request->created_at->locale('th')->translatedFormat('F') }}</span>
    พ.ศ. <span class="dotted-line">{{ $leave_request->created_at->year + 543 }}</span>
</div>

<div class="subject-line">
    <strong>เรื่อง</strong> ขออนุญาต
    <span class="checkbox {{ $leave_request->leave_type === 'compensate' ? 'checked' : '' }}"></span> ใช้วันหยุดชดเชย
    <span class="checkbox {{ $leave_request->leave_type === 'swap_day' ? 'checked' : '' }}"></span> สลับวันหยุด
</div>

<div class="to-line">
    <strong>เรียน</strong> ผู้จัดการบริษัท
</div>

<div class="content">
    ข้าพเจ้า <span class="dotted-line" style="min-width: 200px;">{{ $leave_request->user->GetFullName() ?? '' }}</span>
    ตำแหน่ง <span class="dotted-line" style="min-width: 150px;">{{ $leave_request->user->position ?? 'พนักงาน' }}</span>
</div>

<div class="content">
    ขออนุญาต
    @if($leave_request->leave_type === 'compensate')
        ใช้วันหยุดชดเชยของวันที่
        <span class="dotted-line">{{ $leave_request->compensate_for_date ? $leave_request->compensate_for_date->format('d/m/') . ($leave_request->compensate_for_date->year + 543) : '..................' }}</span>
    @else
        สลับวันหยุดจากวันที่
        <span class="dotted-line">{{ $leave_request->swap_from_date ? $leave_request->swap_from_date->format('d/m/') . ($leave_request->swap_from_date->year + 543) : '..................' }}</span>
    @endif
</div>

<div class="content">
    ใช้วันที่ <span class="dotted-line">{{ $leave_request->start_date->format('d') }}</span>
    เดือน <span class="dotted-line">{{ $leave_request->start_date->locale('th')->translatedFormat('F') }}</span>
    พ.ศ. <span class="dotted-line">{{ $leave_request->start_date->year + 543 }}</span>

    @if(!$leave_request->start_date->eq($leave_request->end_date))
    ถึงวันที่ <span class="dotted-line">{{ $leave_request->end_date->format('d') }}</span>
    เดือน <span class="dotted-line">{{ $leave_request->end_date->locale('th')->translatedFormat('F') }}</span>
    พ.ศ. <span class="dotted-line">{{ $leave_request->end_date->year + 543 }}</span>
    @endif

    มีกำหนด <span class="dotted-line">{{ $leave_request->total_days }}</span> วัน
</div>

@if($leave_request->contact_phone)
<div class="content">
    ในระหว่างลาจะติดต่อข้าพเจ้าได้ที่เบอร์โทร <span class="dotted-line">{{ $leave_request->contact_phone }}</span>
</div>
@endif

<div class="content">
    <strong>สาเหตุ:</strong> {{ $leave_request->reason }}
</div>

<div class="content" style="text-align: right; margin-top: 30px;">
    จึงเรียนมาเพื่อโปรดพิจารณา
</div>

<div class="signature-section clearfix">
    <div class="signature-box right">
        <p>ขอแสดงความนับถือ</p>
        @if($leave_request->employee_signature)
            @if($leave_request->signature_is_image)
                <img src="{{ Storage::url($leave_request->employee_signature) }}" class="signature-image" alt="ลายเซ็น">
            @else
                <img src="{{ $leave_request->employee_signature }}" class="signature-image" alt="ลายเซ็น">
            @endif
        @endif
        <div class="signature-line">
            ( {{ $leave_request->user->GetFullName() ?? '' }} )
        </div>
        <p>{{ $leave_request->user->position ?? 'พนักงาน' }}</p>
    </div>
</div>

<div class="approval-section clearfix">
    <div class="approval-box">
        <p>
            <span class="checkbox {{ $leave_request->IsApproved() ? 'checked' : '' }}"></span> อนุญาต
            <span class="checkbox {{ $leave_request->IsRejected() ? 'checked' : '' }}"></span> ไม่อนุญาต
        </p>
        <p style="font-size: 11pt;">
            <span class="checkbox"></span> ไม่ลาล่วงหน้า 2 วัน<br>
            <span class="checkbox"></span> อายุงานยังไม่ครบ 1 ปี<br>
            <span class="checkbox"></span> หยุดติดต่อกับวันหยุดนักขัตฤกษ์ หรือวันหยุดที่ต้องหยุดอยู่แล้ว
        </p>
    </div>

    <div class="approval-box" style="text-align: right;">
        @if($leave_request->IsApproved() && $leave_request->approver_signature)
        <img src="{{ $leave_request->approver_signature }}" class="signature-image" alt="ลายเซ็นผู้อนุมัติ">
        @endif
        <div class="signature-line">
            ( {{ $leave_request->approver->name ?? 'มานะ อินทราภรณ์' }} )
        </div>
        <p>ผู้จัดการบริษัท</p>
    </div>
</div>

<div class="note-section">
    <strong>หมายเหตุ</strong><br>
    กรณีใช้วันหยุดชดเชย หรือสลับวันหยุด ต้องแจ้งล่วงหน้าอย่างน้อย 2 วัน และต้องไม่หยุดต่อเนื่อง
</div>
