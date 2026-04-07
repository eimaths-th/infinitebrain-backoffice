<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentApplicant;
use App\Models\RecruitmentApplicantLog;
use App\Models\RecruitmentApplicantAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RecruitmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of applicants.
     */
    public function index(Request $request)
    {
        $this->authorize('recruitment.view');

        $query = RecruitmentApplicant::with(['admin', 'reviewer', 'manager'])
            ->orderBy('created_at', 'desc');

        // Permissions filter
        if (!Auth::user()->can('recruitment.view_all')) {
            if (Auth::user()->can('recruitment.review')) {
                // Reviewer sees pending and what they reviewed
                $query->where(function ($q) {
                    $q->where('status', RecruitmentApplicant::STATUS_PENDING)
                        ->orWhere('reviewer_id', Auth::id());
                });
            } elseif (Auth::user()->can('recruitment.upload')) {
                // Admin sees what they uploaded
                $query->where('admin_id', Auth::id());
            }
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('position_applied', 'like', "%$search%");
            });
        }

        $applicants = $query->paginate(20);

        return view('recruitment.index', compact('applicants'));
    }

    /**
     * Show form to create new applicant.
     */
    public function create()
    {
        $this->authorize('recruitment.upload');
        return view('recruitment.create');
    }

    /**
     * Store new applicant.
     */
    public function store(Request $request)
    {
        $this->authorize('recruitment.upload');

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'position_applied' => 'nullable|string|max:255',
            'resumes' => 'required|array',
            'resumes.*' => 'file|mimes:pdf|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $applicant = RecruitmentApplicant::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position_applied' => $request->position_applied,
                'status' => RecruitmentApplicant::STATUS_PENDING,
                'admin_id' => Auth::id(),
            ]);

            if ($request->hasFile('resumes')) {
                foreach ($request->file('resumes') as $index => $file) {
                    $originalName = $file->getClientOriginalName();
                    $fileName = time() . '_' . $index . '_' . $originalName;
                    $path = $file->storeAs('recruitment/resumes', $fileName, 'public');

                    $attachment = RecruitmentApplicantAttachment::create([
                        'applicant_id' => $applicant->id,
                        'file_name' => $fileName,
                        'original_name' => $originalName,
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => Auth::id(),
                    ]);

                    // เก็บไฟล์แรกเป็น resume_path เพื่อความเข้ากันได้ย้อนหลัง
                    if ($index === 0) {
                        $applicant->update(['resume_path' => $path]);
                    }
                }
            }

            RecruitmentApplicantLog::create([
                'applicant_id' => $applicant->id,
                'user_id' => Auth::id(),
                'action' => 'uploaded',
                'to_status' => RecruitmentApplicant::STATUS_PENDING,
                'note' => 'อัปโหลดเอกสารผู้สมัครใหม่ (' . count($request->file('resumes')) . ' ฉบับ)',
            ]);

            DB::commit();

            return redirect()->route('recruitment.index')->with('success', 'เพิ่มผู้สมัครเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show applicant details.
     */
    public function show($id)
    {
        $this->authorize('recruitment.view');
        $applicant = RecruitmentApplicant::with(['admin', 'reviewer', 'manager', 'logs.user', 'attachments.uploader'])->findOrFail($id);
        return view('recruitment.show', compact('applicant'));
    }

    /**
     * Review applicant.
     */
    public function review(Request $request, $id)
    {
        $this->authorize('recruitment.review');

        $request->validate([
            'follow_up' => 'required|in:yes,no',
            'note' => 'nullable|string',
        ]);

        $applicant = RecruitmentApplicant::findOrFail($id);

        if ($applicant->status !== RecruitmentApplicant::STATUS_PENDING) {
            return back()->with('error', 'สถานะไม่ถูกต้องสำหรับการตรวจสอบ');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $applicant->status;
            $applicant->update([
                'status' => RecruitmentApplicant::STATUS_REVIEWED,
                'follow_up' => $request->follow_up,
                'reviewer_note' => $request->note,
                'reviewer_id' => Auth::id(),
            ]);

            RecruitmentApplicantLog::create([
                'applicant_id' => $applicant->id,
                'user_id' => Auth::id(),
                'action' => 'reviewed',
                'from_status' => $oldStatus,
                'to_status' => RecruitmentApplicant::STATUS_REVIEWED,
                'note' => ($request->follow_up == 'yes' ? 'ติดตาม: ' : 'ไม่ติดตาม: ') . $request->note,
            ]);

            DB::commit();

            return redirect()->route('recruitment.show', $applicant->id)->with('success', 'บันทึกการตรวจสอบเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Verify applicant.
     */
    public function verify(Request $request, $id)
    {
        $this->authorize('recruitment.verify');

        $request->validate([
            'status' => 'required|in:verified,rejected',
            'note' => 'nullable|string',
        ]);

        $applicant = RecruitmentApplicant::findOrFail($id);

        if ($applicant->status !== RecruitmentApplicant::STATUS_REVIEWED) {
            return back()->with('error', 'สถานะไม่ถูกต้องสำหรับการอนุมัติ');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $applicant->status;
            $applicant->update([
                'status' => $request->status,
                'manager_note' => $request->note,
                'manager_id' => Auth::id(),
            ]);

            RecruitmentApplicantLog::create([
                'applicant_id' => $applicant->id,
                'user_id' => Auth::id(),
                'action' => $request->status == RecruitmentApplicant::STATUS_VERIFIED ? 'verified' : 'rejected',
                'from_status' => $oldStatus,
                'to_status' => $request->status,
                'note' => $request->note,
            ]);

            DB::commit();

            return redirect()->route('recruitment.show', $applicant->id)->with('success', 'บันทึกผลการอนุมัติเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
