<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestAttachment;
use App\Models\Recordtime;
use App\Models\User;
use App\Models\UserCompensateDay;
use App\Services\LeaveRequestExportService;
use App\Services\LineMessagingService;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function Index(Request $request)
    {
        $user = Auth::user();
        $query = LeaveRequest::with(['user', 'approver']);

        if ($user->level == 0) {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        $leave_requests = $query->orderBy('created_at', 'desc')->paginate(20);
        $users = User::active()->orderBy('name')->get();

        return view('leave_requests.index', [
            'leave_requests' => $leave_requests,
            'users' => $users,
            'leave_types' => LeaveRequest::LEAVE_TYPES,
            'statuses' => LeaveRequest::STATUSES,
        ]);
    }

    public function Create()
    {
        $user = Auth::user();
        $compensate_days = UserCompensateDay::where('user_id', $user->id)
            ->where('is_used', false)
            ->with('holiday')
            ->get();

        return view('leave_requests.create', [
            'leave_types' => LeaveRequest::LEAVE_TYPES,
            'half_day_options' => LeaveRequest::HALF_DAY_OPTIONS,
            'title_prefixes' => User::TITLE_PREFIXES,
            'user' => $user,
            'compensate_days' => $compensate_days,
        ]);
    }

    public function Store(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:' . implode(',', array_keys(LeaveRequest::LEAVE_TYPES)),
            'submitted_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'employee_signature' => 'required',
        ]);

        $user = Auth::user();

        $title_prefix = $request->title_prefix;
        if ($title_prefix === 'other' && $request->custom_title_prefix) {
            $title_prefix = $request->custom_title_prefix;
        }

        $user->update([
            'title_prefix' => $title_prefix,
            'position' => $request->position,
        ]);

        $start_date = Carbon::parse($request->start_date);
        $end_date = Carbon::parse($request->end_date);

        $total_days = $start_date->diffInDays($end_date) + 1;
        if ($request->half_day !== 'none') {
            $total_days = 0.5;
        }

        $signature_data = $request->employee_signature;
        $signature_is_image = false;
        $save_new_signature = $request->save_new_signature || $request->save_uploaded_signature;

        if (strpos($signature_data, 'saved:') === 0) {
            $signature_data = str_replace('saved:', '', $signature_data);
            $signature_is_image = true;
        } elseif ($request->hasFile('signature_image')) {
            $file = $request->file('signature_image');
            $image = Image::make($file)->encode('webp', 90);
            $filename = 'signatures/' . $user->id . '_' . time() . '.webp';
            Storage::disk('public')->put($filename, $image);
            $signature_data = $filename;
            $signature_is_image = true;

            if ($save_new_signature) {
                $user->update([
                    'signature' => $filename,
                    'signature_is_image' => true,
                ]);
            }
        } elseif ($signature_data && preg_match('/^data:image\/(\w+);base64,/', $signature_data)) {
            $image = Image::make($signature_data)->encode('webp', 90);
            $filename = 'signatures/' . $user->id . '_' . time() . '.webp';
            Storage::disk('public')->put($filename, $image);
            $signature_data = $filename;
            $signature_is_image = true;

            if ($save_new_signature) {
                $user->update([
                    'signature' => $filename,
                    'signature_is_image' => true,
                ]);
            }
        }

        $submitted_date = Carbon::parse($request->submitted_date);

        $leave_request = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => $request->leave_type,
            'submitted_date' => $submitted_date,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_days' => $total_days,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'location' => $request->location,
            'contact_phone' => $request->contact_phone,
            'contact_address' => $request->contact_address,
            'compensate_for_date' => $request->compensate_for_date,
            'swap_from_date' => $request->swap_from_date,
            'half_day' => $request->half_day ?? 'none',
            'employee_signature' => $signature_data,
            'signature_is_image' => $signature_is_image,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->getSize() > 10 * 1024 * 1024) {
                    continue;
                }

                $original_name = $file->getClientOriginalName();
                $file_name = time() . '_' . $user->id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file_path = $file->storeAs('leave_attachments', $file_name, 'public');

                LeaveRequestAttachment::create([
                    'leave_request_id' => $leave_request->id,
                    'file_name' => $file_name,
                    'original_name' => $original_name,
                    'file_path' => $file_path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        $line_messaging = new LineMessagingService();
        $line_messaging->SendLeaveRequestNotification($leave_request);

        return redirect()->route('leave-requests.index')
            ->with('success', 'ส่งใบลาเรียบร้อยแล้ว รอการอนุมัติ');
    }

    public function Show($id)
    {
        $leave_request = LeaveRequest::with(['user', 'approver', 'attachments'])->findOrFail($id);

        $user = Auth::user();
        if ($user->level > 1 && $leave_request->user_id !== $user->id) {
            abort(403, 'ไม่มีสิทธิ์ดูใบลานี้');
        }

        $leave_stats = LeaveRequest::GetUserLeaveStats($leave_request->user_id);

        return view('leave_requests.show', [
            'leave_request' => $leave_request,
            'leave_types' => LeaveRequest::LEAVE_TYPES,
            'leave_stats' => $leave_stats,
            'current_year' => now()->year,
        ]);
    }

    public function Edit($id)
    {
        $leave_request = LeaveRequest::with(['user', 'attachments'])->findOrFail($id);

        $user = Auth::user();
        if ($leave_request->user_id !== $user->id) {
            abort(403, 'ไม่มีสิทธิ์แก้ไขใบลานี้');
        }

        if (!$leave_request->IsPending()) {
            return redirect()->route('leave-requests.show', $id)
                ->with('error', 'ไม่สามารถแก้ไขใบลาที่พิจารณาแล้ว');
        }

        $compensate_days = UserCompensateDay::where('user_id', $user->id)
            ->where('is_used', false)
            ->with('holiday')
            ->get();

        return view('leave_requests.edit', [
            'leave_request' => $leave_request,
            'leave_types' => LeaveRequest::LEAVE_TYPES,
            'half_day_options' => LeaveRequest::HALF_DAY_OPTIONS,
            'title_prefixes' => User::TITLE_PREFIXES,
            'user' => $user,
            'compensate_days' => $compensate_days,
        ]);
    }

    public function Update(Request $request, $id)
    {
        $leave_request = LeaveRequest::findOrFail($id);

        $user = Auth::user();
        if ($leave_request->user_id !== $user->id) {
            return back()->with('error', 'ไม่มีสิทธิ์แก้ไขใบลานี้');
        }

        if (!$leave_request->IsPending()) {
            return back()->with('error', 'ไม่สามารถแก้ไขใบลาที่พิจารณาแล้ว');
        }

        $request->validate([
            'leave_type' => 'required|in:' . implode(',', array_keys(LeaveRequest::LEAVE_TYPES)),
            'submitted_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $start_date = Carbon::parse($request->start_date);
        $end_date = Carbon::parse($request->end_date);
        $submitted_date = Carbon::parse($request->submitted_date);

        $total_days = $start_date->diffInDays($end_date) + 1;
        if ($request->half_day !== 'none') {
            $total_days -= 0.5;
        }

        $update_data = [
            'leave_type' => $request->leave_type,
            'submitted_date' => $submitted_date,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_days' => $total_days,
            'half_day' => $request->half_day ?? 'none',
            'reason' => $request->reason,
        ];

        if ($request->leave_type === 'remote_work') {
            $update_data['location'] = $request->location;
        }

        if (in_array($request->leave_type, ['sick', 'personal', 'maternity', 'ordination', 'military', 'vacation'])) {
            $update_data['contact_phone'] = $request->contact_phone;
            $update_data['contact_address'] = $request->contact_address;
        }

        if ($request->leave_type === 'compensate') {
            $update_data['compensate_for_date'] = $request->compensate_for_date;
        }

        if ($request->leave_type === 'swap_day') {
            $update_data['swap_from_date'] = $request->swap_from_date;
            $update_data['swap_to_date'] = $request->swap_to_date;
        }

        if ($request->start_time) {
            $update_data['start_time'] = $request->start_time;
            $update_data['end_time'] = $request->end_time;
        }

        $leave_request->update($update_data);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('leave_attachments', 'public');
                LeaveRequestAttachment::create([
                    'leave_request_id' => $leave_request->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()->route('leave-requests.show', $leave_request->id)
            ->with('success', 'แก้ไขใบลาเรียบร้อยแล้ว');
    }

    public function PendingApprovals()
    {
        $user = Auth::user();
        if ($user->level > 1) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $pending_requests = LeaveRequest::with('user')
            ->pending()
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('leave_requests.pending', [
            'pending_requests' => $pending_requests,
            'leave_types' => LeaveRequest::LEAVE_TYPES,
        ]);
    }

    public function Approve(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->level != 0) {
            return response()->json(['success' => false, 'message' => 'เฉพาะ Owner เท่านั้นที่สามารถอนุมัติใบลาได้'], 403);
        }

        $leave_request = LeaveRequest::findOrFail($id);

        if (!$leave_request->IsPending()) {
            return response()->json(['success' => false, 'message' => 'ใบลานี้ได้รับการพิจารณาแล้ว'], 400);
        }

        $request->validate([
            'approver_signature' => 'required',
        ]);

        $approver_signature = $request->approver_signature;

        if (strpos($approver_signature, 'saved:') === 0) {
            $signature_path = str_replace('saved:', '', $approver_signature);
            $full_path = storage_path('app/public/' . $signature_path);

            if (file_exists($full_path)) {
                $approver_signature = Storage::url($signature_path);
            }
        }

        $leave_request->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approver_signature' => $approver_signature,
        ]);

        $this->UpdateRecordtime($leave_request);

        if ($leave_request->leave_type === 'compensate' && $leave_request->compensate_for_date) {
            UserCompensateDay::where('user_id', $leave_request->user_id)
                ->where('holiday_date', $leave_request->compensate_for_date)
                ->update([
                    'is_used' => true,
                    'used_date' => $leave_request->start_date,
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'อนุมัติใบลาเรียบร้อยแล้ว',
        ]);
    }

    public function Reject(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->level != 0) {
            return response()->json(['success' => false, 'message' => 'เฉพาะ Owner เท่านั้นที่สามารถปฏิเสธใบลาได้'], 403);
        }

        $leave_request = LeaveRequest::findOrFail($id);

        if (!$leave_request->IsPending()) {
            return response()->json(['success' => false, 'message' => 'ใบลานี้ได้รับการพิจารณาแล้ว'], 400);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leave_request->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ปฏิเสธใบลาเรียบร้อยแล้ว',
        ]);
    }

    protected function UpdateRecordtime(LeaveRequest $leave_request)
    {
        $status_map = [
            'sick' => 3,        // ลาป่วย
            'personal' => 4,    // ลากิจ
            'maternity' => 10,  // ลาคลอด
            'ordination' => 11, // ลาบวช
            'military' => 12,   // ลารับราชการทหาร
            'vacation' => 2,    // ลาพักผ่อน
            'compensate' => 14, // วันหยุดชดเชย
            'swap_day' => 14,   // สลับวันหยุด
            'remote_work' => 15, // ทำงานนอกสถานที่ = WFH
        ];

        $status_id = $status_map[$leave_request->leave_type] ?? 4;

        $current_date = $leave_request->start_date->copy();
        while ($current_date->lte($leave_request->end_date)) {
            $record = Recordtime::where('user_id', $leave_request->user_id)
                ->where('date', $current_date->format('Y-m-d'))
                ->first();

            $status_name = LeaveRequest::LEAVE_TYPES[$leave_request->leave_type] ?? 'ลา';
            $explain = $status_name . ' (ใบลา #' . $leave_request->id . ')';

            if ($record) {
                $record->update([
                    'status' => $status_id,
                    'status_name' => $status_name,
                    'explain' => $explain,
                ]);
            } else {
                Recordtime::create([
                    'user_id' => $leave_request->user_id,
                    'type_user' => $leave_request->user->type_user ?? 0,
                    'date' => $current_date->format('Y-m-d'),
                    'stime' => null,
                    'etime' => null,
                    'status' => $status_id,
                    'status_name' => $status_name,
                    'late_time' => 0,
                    'explain' => $explain,
                ]);
            }

            $current_date->addDay();
        }
    }

    public function SaveSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required',
        ]);

        $user = Auth::user();
        $signature_data = $request->signature;
        $signature_is_image = false;

        if ($request->hasFile('signature_image')) {
            $file = $request->file('signature_image');
            $image = Image::make($file)->encode('webp', 90);
            $filename = 'signatures/user_' . $user->id . '.webp';
            Storage::disk('public')->put($filename, $image);
            $signature_data = $filename;
            $signature_is_image = true;
        }

        $user->update([
            'signature' => $signature_data,
            'signature_is_image' => $signature_is_image,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'บันทึกลายเซ็นเรียบร้อยแล้ว',
        ]);
    }

    public function PrintLeave($id)
    {
        $leave_request = LeaveRequest::with(['user', 'approver'])->findOrFail($id);

        $user = Auth::user();
        if ($user->level > 1 && $leave_request->user_id !== $user->id) {
            abort(403, 'ไม่มีสิทธิ์ดูใบลานี้');
        }

        return view('leave_requests.print', [
            'leave_request' => $leave_request,
        ]);
    }

    public function ExportDocx($id)
    {
        $leave_request = LeaveRequest::with(['user', 'approver'])->findOrFail($id);

        $user = Auth::user();
        if ($user->level > 1 && $leave_request->user_id !== $user->id) {
            abort(403, 'ไม่มีสิทธิ์ดูใบลานี้');
        }

        try {
            $export_service = new LeaveRequestExportService();
            $file_path = $export_service->Export($leave_request);

            $file_name = 'ใบลา_' . $leave_request->GetLeaveTypeName() . '_' . $leave_request->user->name . '_' . $leave_request->id . '.docx';

            return response()->download($file_path, $file_name)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function ExportPdf($id)
    {
        $leave_request = LeaveRequest::with(['user', 'approver'])->findOrFail($id);

        $user = Auth::user();
        if ($user->level > 1 && $leave_request->user_id !== $user->id) {
            abort(403, 'ไม่มีสิทธิ์ดูใบลานี้');
        }

        try {
            $export_service = new LeaveRequestExportService();
            $docx_path = $export_service->Export($leave_request);

            $pdf_path = $this->ConvertDocxToPdf($docx_path);

            if (!$pdf_path || !file_exists($pdf_path)) {
                @unlink($docx_path);
                return response()->view('errors.pdf_error', [
                    'message' => 'ไม่สามารถแปลงไฟล์เป็น PDF ได้ กรุณาตรวจสอบว่าติดตั้ง LibreOffice แล้ว',
                    'leave_request_id' => $id,
                ], 500);
            }

            @unlink($docx_path);

            $file_name = 'ใบลา_' . $leave_request->GetLeaveTypeName() . '_' . $leave_request->user->name . '_' . $leave_request->id . '.pdf';

            return response()->file($pdf_path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $file_name . '"',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->view('errors.pdf_error', [
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'leave_request_id' => $id,
            ], 500);
        }
    }

    protected function ConvertDocxToPdf($docx_path)
    {
        $output_dir = dirname($docx_path);
        $libreoffice_path = config('services.libreoffice.path', '/usr/bin/soffice');

        $home_dir = storage_path('app/temp/libreoffice_home');
        if (!file_exists($home_dir)) {
            mkdir($home_dir, 0755, true);
        }
        putenv('HOME=' . $home_dir);

        $command = sprintf(
            '%s --headless --nofirststartwizard --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($libreoffice_path),
            escapeshellarg($output_dir),
            escapeshellarg($docx_path)
        );

        \Illuminate\Support\Facades\Log::info('LibreOffice command', [
            'command' => $command,
            'docx_path' => $docx_path,
            'docx_exists' => file_exists($docx_path),
        ]);

        exec($command, $output, $return_code);

        \Illuminate\Support\Facades\Log::info('LibreOffice result', [
            'output' => $output,
            'return_code' => $return_code,
        ]);

        $pdf_path = preg_replace('/\.docx$/i', '.pdf', $docx_path);

        if (file_exists($pdf_path)) {
            return $pdf_path;
        }

        \Illuminate\Support\Facades\Log::error('LibreOffice conversion failed', [
            'command' => $command,
            'output' => $output,
            'return_code' => $return_code,
            'pdf_path' => $pdf_path,
            'pdf_exists' => file_exists($pdf_path),
        ]);

        return null;
    }

    public function Destroy($id)
    {
        $leave_request = LeaveRequest::findOrFail($id);
        $user = Auth::user();

        if ($leave_request->user_id !== $user->id && $user->level > 1) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ลบใบลานี้'], 403);
        }

        if (!$leave_request->IsPending()) {
            return response()->json(['success' => false, 'message' => 'ไม่สามารถลบใบลาที่พิจารณาแล้ว'], 400);
        }

        $leave_request->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบใบลาเรียบร้อยแล้ว',
        ]);
    }
}
