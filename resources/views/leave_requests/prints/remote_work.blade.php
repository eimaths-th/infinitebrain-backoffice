{{-- แบบขออนุญาตออกปฏิบัติงานนอกสถานที่ --}}

<div class="header">
    <h1>แบบขออนุญาตออกปฏิบัติงานนอกสถานที่</h1>
</div>

<div class="date-line">
    วันที่ <span class="dotted-line">{{ $leave_request->created_at->day }}</span>
    เดือน <span class="dotted-line">{{ $leave_request->created_at->locale('th')->translatedFormat('F') }}</span>
    พ.ศ. <span class="dotted-line">{{ $leave_request->created_at->year + 543 }}</span>
</div>

<div class="subject-line">
    <strong>เรื่อง</strong> ขออนุญาตออกปฏิบัติงานนอกสถานที่
</div>

<div class="to-line">
    <strong>เรียน</strong> ผู้จัดการบริษัท
</div>

<div class="content">
    ข้าพเจ้า <span class="dotted-line" style="min-width: 200px;">{{ $leave_request->user->GetFullName() ?? '' }}</span>
    ตำแหน่ง <span class="dotted-line" style="min-width: 150px;">{{ $leave_request->user->position ?? 'พนักงาน' }}</span>
</div>

<div class="content">
    ขออนุญาตออกปฏิบัติงานนอกสถานที่
    ในวันที่ <span class="dotted-line">{{ $leave_request->start_date->format('d') }}</span>
    เดือน <span class="dotted-line">{{ $leave_request->start_date->locale('th')->translatedFormat('F') }}</span>
    พ.ศ. <span class="dotted-line">{{ $leave_request->start_date->year + 543 }}</span>
</div>

<div class="content">
    ตั้งแต่เวลา <span class="dotted-line">{{ $leave_request->start_time ?? '...........' }}</span>
    ถึง <span class="dotted-line">{{ $leave_request->end_time ?? '...........' }}</span>
</div>

<div class="content">
    <strong>สถานที่:</strong> <span class="dotted-line" style="min-width: 300px;">{{ $leave_request->location ?? '' }}</span>
</div>

<div class="content">
    <strong>เหตุผล/รายละเอียดงาน:</strong> {{ $leave_request->reason }}
</div>

<table class="info-table" style="margin-top: 20px;">
    <tr>
        <td width="50%">
            <span class="checkbox"></span> ไม่ได้สแกนนิ้วมือเข้า - ออกทำงาน
        </td>
        <td>
            <span class="checkbox"></span> สแกนนิ้วมือเข้า - ออกทำงาน
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="checkbox"></span> สแกนนิ้วมือครึ่งวัน
            <span class="checkbox"></span> เข้า
            <span class="checkbox"></span> ออก
        </td>
    </tr>
</table>

<div class="content" style="text-align: right; margin-top: 30px;">
    ขอแสดงความนับถือ
</div>

<div class="signature-section clearfix">
    <div class="signature-box right">
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
    <div class="approval-box" style="text-align: center; width: 100%;">
        <p><strong>ผู้อนุมัติ</strong></p>
        @if($leave_request->IsApproved() && $leave_request->approver_signature)
        <img src="{{ $leave_request->approver_signature }}" class="signature-image" alt="ลายเซ็นผู้อนุมัติ">
        @endif
        <div class="signature-line" style="margin: 0 auto;">
            ( {{ $leave_request->approver->name ?? 'มานะ อินทราภรณ์' }} )
        </div>
        <p>ผู้จัดการ</p>
    </div>
</div>
