{{-- ใบลาพักผ่อน --}}

<div class="header">
    <h1>ใบลาพักผ่อน</h1>
</div>

<div class="date-line">
    วันที่ <span class="dotted-line">{{ $leave_request->created_at->day }}</span>
    เดือน <span class="dotted-line">{{ $leave_request->created_at->locale('th')->translatedFormat('F') }}</span>
    พ.ศ. <span class="dotted-line">{{ $leave_request->created_at->year + 543 }}</span>
</div>

<div class="subject-line">
    <strong>เรื่อง</strong> ขออนุญาตลาพักผ่อน
</div>

<div class="to-line">
    <strong>เรียน</strong> ผู้จัดการบริษัท
</div>

<div class="content">
    ข้าพเจ้า <span class="dotted-line" style="min-width: 200px;">{{ $leave_request->user->GetFullName() ?? '' }}</span>
    ตำแหน่ง <span class="dotted-line" style="min-width: 150px;">{{ $leave_request->user->position ?? 'พนักงาน' }}</span>
</div>

<div class="content">
    มีความประสงค์ขอลาพักผ่อน
    ตั้งแต่วันที่ <span class="dotted-line">{{ $leave_request->start_date->format('d') }}</span>
    เดือน <span class="dotted-line">{{ $leave_request->start_date->locale('th')->translatedFormat('F') }}</span>
    พ.ศ. <span class="dotted-line">{{ $leave_request->start_date->year + 543 }}</span>

    ถึงวันที่ <span class="dotted-line">{{ $leave_request->end_date->format('d') }}</span>
    เดือน <span class="dotted-line">{{ $leave_request->end_date->locale('th')->translatedFormat('F') }}</span>
    พ.ศ. <span class="dotted-line">{{ $leave_request->end_date->year + 543 }}</span>

    มีกำหนด <span class="dotted-line">{{ $leave_request->total_days }}</span> วัน
</div>

<div class="content">
    <strong>เหตุผล:</strong> {{ $leave_request->reason }}
</div>

@if($leave_request->contact_phone || $leave_request->contact_address)
<div class="content">
    ในระหว่างลาติดต่อข้าพเจ้าได้ที่
    @if($leave_request->contact_address)
    <br>ที่อยู่: {{ $leave_request->contact_address }}
    @endif
    @if($leave_request->contact_phone)
    <br>โทรศัพท์: {{ $leave_request->contact_phone }}
    @endif
</div>
@endif

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
    การลาพักผ่อน ต้องลาล่วงหน้าอย่างน้อย 2 วัน และไม่สามารถลาติดต่อวันหยุดนักขัตฤกษ์ หรือวันหยุดที่ต้องหยุดอยู่แล้วได้ และไม่สามารถนำไปสะสมปีอื่นได้
</div>
