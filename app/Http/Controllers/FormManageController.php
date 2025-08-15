<?php

namespace App\Http\Controllers;

use App\Models\FormGrades;
use App\Models\FormManage;
use App\Models\FormPakages;
use App\Models\OpeningClosingTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormManageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $data = FormManage::all();
        return view('forms_manage.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDatetime($route)
    {
        $set_time = OpeningClosingTime::where('route', $route)->orderBy('id', 'desc')->first();
        if ($set_time->status === 'Online') {
            $start = (new \DateTime($set_time->opening_time))->format('d F Y');
            $closs = (new \DateTime($set_time->closing_time))->format('d F Y');
            $date = $start . ' - ' . $closs;
        } else {
            $start = 'null date';
            $closs = 'null date';
            $date = $start . ' to ' . $closs;
        }
        return $date;
    }

    public function testing_form_manage()
    {
        return view('forms_manage.testing_form_manage');
    }
    public function selectRoute()
    {

        return view('forms_manage.select_route');
    }

    public function create(Request $request)
    {

        $form_route = $request->route;

        $data = FormManage::where([
            'form_route' => $form_route,
            'form_name' => $request->form_name
        ])->first();
        // dd($data);
        if ($data) {
            return view('forms_manage.create', compact('form_route', 'data'));
        } else {
            $dataCreate = FormManage::create([
                'form_name' => $request->form_name,
                'form_route' => $form_route,
            ]);
            $data = FormManage::find($dataCreate->id);
            return view('forms_manage.create', compact('form_route', 'data'));
        }
    }

    public function editOptionGrade(Request $request)
    {
        $id = $request->id;
        $value = $request->value;
        $data = FormGrades::find($id);
        $data->grade = $value;
        $data->save();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
    public function deleteOptionGrade(Request $request)
    {
        $id = $request->id;
        $index = $request->index;
        $data = FormGrades::find($id);
        $data->delete();
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'index' => $index
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //validate form
        // dd($request->all());

        // $request->validate([
        //     // Hidden fields
        //     'form_id' => 'required|exists:form_manages,id',
        //     'form_route' => 'required|string|max:255',
        //     'form_name' => 'required|string|max:255',
        //     'referent_code' => 'nullable|string|max:50',
        //     'sfield' => 'required|string',
        //     // Form configuration fields
        //     'brand' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        //     'registration_fee_label' => 'nullable|string|max:100',
        //     'registration_date_label' => 'nullable|string|max:100',
        //     'registration_date' => 'nullable|string|max:50',
        //     'contest_date_label' => 'nullable|string|max:100',
        //     'contest_date' => 'nullable|string|max:50',
        //     'admission_criteria_text_label' => 'nullable|string|max:50',
        //     'admission_criteria_text' => 'nullable|string|max:50',
        //     'admission_criteria_image_label' => 'nullable|string|max:50',
        //     'admission_criteria_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        //     // Student information
        //     'student_first_name' => 'required|string|min:2|max:50',
        //     'student_last_name' => 'required|string|min:2|max:50',
        //     'student_nick_name' => 'required|string|min:1|max:50',
        //     'date_of_birth' => 'required|string|max:50',
        //     'student_school_name' => 'required|string|max:100',
        //     'student_grade' => 'required|string|max:50',
        //     'package' => 'required|string|max:100',
        //     // Address information
        //     'student_address' => 'required|string|min:2|max:150',
        //     'student_zip' => 'required|string|min:2|max:50',
        //     // Parent information
        //     'parent_name' => 'required|string|max:100',
        //     'parent_phone' => 'required|string|max:20',
        //     // Contact information
        //     'contact_email' => 'required|email|max:70',
        //     'contact_email_detail' => 'required|string',
        //     // Photos and documents
        //     'student_photo' => 'required|string|max:70',
        //     'image_payment' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        //     'student_slip_photo' => 'required|string|max:70',
        //     // Additional information
        //     'news' => 'required|string|max:70',
        //     // Text areas for form customization
        //     'text_warning' => 'nullable|string',
        //     'text_footer' => 'nullable|string',
        //     'text_policy' => 'nullable|string',
        //     // Dynamic options (arrays)
        //     'optionGrade' => 'nullable|array',
        //     'optionGrade.*' => 'nullable|string|max:100',
        //     'optionValue' => 'nullable|array',
        //     'optionValue.*' => 'nullable|numeric',
        //     'optionName' => 'nullable|array',
        //     'optionName.*' => 'nullable|string|max:100',
        // ],[
        //     //form manage
        //     'form_id.required' => 'The form ID is required.',
        //     'form_id.exists' => 'The form ID does not exist.',
        //     'form_name.required' => 'The form name is required.',
        //     'form_name.max' => 'The form name must be at most 255 characters.',
        //     'form_route.required' => 'The form route is required.',
        //     'form_route.max' => 'The form route must be at most 255 characters.',
        //     'referent_code.required' => 'The referent code is required.',
        //     'referent_code.max' => 'The referent code must be at most 50 characters.',
        //     'sfield.required' => 'The sfield is required.',
        //     'sfield.max' => 'The sfield must be at most 50 characters.',
        //     //form configuration
        //     'brand.required' => 'The brand is required.',
        //     'brand.max' => 'The brand must be at most 255 characters.',
        //     'registration_fee_label.required' => 'The registration fee label is required.',
        //     'registration_fee_label.max' => 'The registration fee label must be at most 100 characters.',
        //     'registration_date_label.required' => 'The registration date label is required.',
        //     'registration_date_label.max' => 'The registration date label must be at most 100 characters.',
        //     'registration_date.required' => 'The registration date is required.',
        //     'contest_date_label.required' => 'The contest date label is required.',
        //     'contest_date.required' => 'The contest date is required.',
        //     'admission_criteria_text_label.required' => 'The admission criteria text label is required.',
        //     'admission_criteria_text_label.max' => 'The admission criteria text label must be at most 100 characters.',
        //     'admission_criteria_text.required' => 'The admission criteria text is required.',
        //     'admission_criteria_text.max' => 'The admission criteria text must be at most 100 characters.',
        //     'admission_criteria_image_label.required' => 'The admission criteria image label is required.',
        //     'admission_criteria_image.required' => 'The admission criteria image is required.',
        //     'admission_criteria_image.max' => 'The admission criteria image must be at most 2048 characters.',
        //     'admission_criteria_image.max' => 'The admission criteria image must be at most 2048 characters.',
        //     //student information
        //     'student_first_name.required' => 'The student first name is required.',
        //     'student_first_name.max' => 'The student first name must be at most 50 characters.',
        //     'student_last_name.required' => 'The student last name is required.',
        //     'student_last_name.max' => 'The student last name must be at most 50 characters.',
        //     'student_nick_name.required' => 'The student nick name is required.',
        //     'student_nick_name.max' => 'The student nick name must be at most 50 characters.',
        //     'date_of_birth.required' => 'The date of birth is required.',
        //     'student_school_name.required' => 'The student school name is required.',
        //     'student_school_name.max' => 'The student school name must be at most 100 characters.',
        //     'student_grade.required' => 'The student grade is required.',
        //     'student_grade.max' => 'The student grade must be at most 50 characters.',
        //     'package.required' => 'The package is required.',
        //     //address information
        //     'student_address.required' => 'The student address is required.',
        //     'student_zip.required' => 'The student zip is required.',
        //     //parent information
        //     'parent_name.required' => 'The parent name is required.',
        //     'parent_name.max' => 'The parent name must be at most 100 characters.',
        //     'parent_phone.required' => 'The parent phone is required.',
        //     'parent_phone.max' => 'The parent phone must be at most 20 characters.',
        //     //contact information
        //     'contact_email.required' => 'The contact email is required.',
        //     'contact_email.max' => 'The contact email must be at most 70 characters.',
        //     'contact_email_detail.required' => 'The contact email detail is required.',
        //     //photos and documents
        //     'student_photo.required' => 'The student photo is required.',
        //     'image_payment.required' => 'The image payment is required.',
        //     'student_slip_photo.required' => 'The student slip photo is required.',
        //     //additional information
        //     'news.required' => 'The news is required.',
        //     'news.max' => 'The news must be at most 70 characters.',
        //     //text areas for form customization
        //     'text_warning.required' => 'The text warning is required.',
        //     'text_warning.max' => 'The text warning must be at most 70 characters.',
        //     'text_footer.required' => 'The text footer is required.',
        //     'text_footer.max' => 'The text footer must be at most 70 characters.',
        //     'text_policy.required' => 'The text policy is required.',
        //     'text_policy.max' => 'The text policy must be at most 70 characters.',
        //     //dynamic options (arrays)
        //     'optionGrade.required' => 'The option grade is required.',
        //     'optionValue.required' => 'The option value is required.',
        //     'optionName.required' => 'The option name is required.',
        // ]);

        $form_id = $request->form_id;
        $form_manage = FormManage::find($form_id);
        $form_manage->update([
            'form_name' => $request->form_name,
            'form_route' => $request->form_route,
            'referent_code' => $request->referent_code,
            'sfield' => $request->sfield,
            // 'brand' => $request->brand,
            'registration_fee_label' => $request->registration_fee_label,
            'registration_date_label' => $request->registration_date_label,
            'registration_date' => $request->registration_date,
            'contest_date_label' => $request->contest_date_label,
            'contest_date' => $request->contest_date,
            'admission_criteria_text_label' => $request->admission_criteria_text_label,
            'admission_criteria_text' => $request->admission_criteria_text,
            'admission_criteria_image_label' => $request->admission_criteria_image_label,
            // 'admission_criteria_image' => $request->admission_criteria_image,
            'student_first_name' => $request->student_first_name,
            'student_last_name' => $request->student_last_name,
            'student_nick_name' => $request->student_nick_name,
            'date_of_birth' => $request->date_of_birth,
            'student_school_name' => $request->student_school_name,
            'student_grade' => $request->student_grade,
            'package' => $request->package,
            'student_address' => $request->student_address,
            'student_zip' => $request->student_zip,
            'parent_name' => $request->parent_name,
            'parent_phone' => $request->parent_phone,
            'contact_email' => $request->contact_email,
            'contact_email_detail' => $request->contact_email_detail,
            'student_photo' => $request->student_photo,
            // 'image_payment' => $request->image_payment,
            'student_slip_photo' => $request->student_slip_photo,
            'news' => $request->news,
            'text_warning' => $request->text_warning,
            'text_footer' => $request->text_footer,
            'text_policy' => $request->text_policy,
        ]);

        //file image
        if ($request->hasFile('image_payment')) {
            $file = $request->file('image_payment');
            $filename = $request->form_name . '_' . date('Y-m-d') . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('form_manage/payment'), $filename);
            $form_manage->update([
                'image_payment' => $filename,
            ]);
        }
        //file image
        if ($request->hasFile('admission_criteria_image')) {
            $file = $request->file('admission_criteria_image');
            $filename = $request->form_name . '_' . date('Y-m-d') . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('form_manage/admission_criteria_image'), $filename);
            $form_manage->update([
                'admission_criteria_image' => $filename,
            ]);
        }
        //file image
        if ($request->hasFile('brand')) {
            $file = $request->file('brand');
            $filename = $request->form_name . '_' . date('Y-m-d') . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('form_manage/brand'), $filename);
            $form_manage->update([
                'brand' => $filename,
            ]);
        }

        if ($request->has('optionGrade')) {
            FormGrades::where('form_id', $form_id)->delete();
            foreach ($request->optionGrade as $key => $value) {
                $form_manage->grade()->create([
                    'form_id' => $form_id,
                    'sfield' => $form_manage->sfield,
                    'form_name' => $form_manage->form_name,
                    'form_route' => $form_manage->form_route,
                    'grade' => $value,
                    'create_by' => Auth::user()->id,
                    'update_by' => Auth::user()->id,
                ]);
            }
        }
        if ($request->has('optionPackage')) {
            FormPakages::where('form_id', $form_id)->delete();
            foreach ($request->optionPackage as $key => $package) {
                $form_manage->package()->create([
                    'form_id' => $form_id,
                    'sfield' => $form_manage->sfield,
                    'form_name' => $form_manage->form_name,
                    'form_route' => $form_manage->form_route,
                    'package' => $package['name'],
                    'price' => $package['value'],
                    'create_by' => Auth::user()->id,
                    'update_by' => Auth::user()->id,
                ]);
            }
        }

        return redirect()->route('form-manage.index')->with('success', 'Form updated successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
