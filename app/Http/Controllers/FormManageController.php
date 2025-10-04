<?php

namespace App\Http\Controllers;

use App\Models\FormGrades;
use App\Models\FormManage;
use App\Models\FormPakages;
use App\Models\OpeningClosingTime;
use App\Models\Receipt;
use App\Models\ReceiptForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        // foreach ($data as $key => $form) {
        //     $packages = FormPakages::where([
        //         'form_name' => $form->form_name,
        //         'form_route' => $form->form_route,
        //         ])->get();
        //     foreach ($packages as $key => $package) {
        //         $package->update([
        //             'form_id' => $form->id,
        //             'sfield' => $form->sfield,
        //         ]);
        //     }

        //     $grades = FormGrades::where([
        //         'form_name' => $form->form_name,
        //         'form_route' => $form->form_route,
        //         ])->get();
        //     foreach ($grades as $key => $grade) {
        //         $grade->update([
        //             'form_id' => $form->id,
        //             'sfield' => $form->sfield,
        //         ]);
        //     }
        // }

        $checkNull = FormManage::whereNull('form_route')->get();
        foreach ($checkNull as $key => $value) {
            $value->delete();
        }

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

        // dd($request->all());
        $validated = [];
        $fieldConfig = config('form_fields', []);

        if ($request->isMethod('post')) {
            $formNameInput = $request->input('form_name');
            $slugInput = $request->input('slug');

            if (!$slugInput && $formNameInput) {
                $slugInput = Str::slug($formNameInput);
                $request->merge(['slug' => $slugInput]);
            }

            if (!$request->filled('slug')) {
                $errorMessage = 'ระบบไม่สามารถสร้าง Slug ได้ กรุณากรอกชื่อฟอร์มใหม่อีกครั้ง';

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => ['slug' => [$errorMessage]],
                    ], 422);
                }

                return redirect()->back()->withErrors(['slug' => $errorMessage])->withInput();
            }

            $slugRule = 'required|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:forms_manage,slug';

            if ($request->filled('id')) {
                $slugRule = 'required|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:forms_manage,slug,' . $request->id;
            }

            $validator = Validator::make(
                $request->all(),
                [
                    'slug' => $slugRule,
                    'form_name' => 'required|string|max:255',
                ],
                [
                    'slug.required' => 'กรุณากรอก Slug',
                    'slug.string' => 'รูปแบบ Slug ไม่ถูกต้อง',
                    'slug.max' => 'Slug ต้องไม่เกิน 255 ตัวอักษร',
                    'slug.regex' => 'Slug ต้องเป็นตัวอักษรภาษาอังกฤษพิมพ์เล็ก ตัวเลข และเครื่องหมาย - เท่านั้น (เช่น my-form-name)',
                    'slug.unique' => 'Slug นี้ถูกใช้งานแล้ว กรุณาเลือก Slug อื่น',
                    'form_name.required' => 'กรุณากรอกชื่อฟอร์ม',
                    'form_name.string' => 'รูปแบบชื่อฟอร์มไม่ถูกต้อง',
                    'form_name.max' => 'ชื่อฟอร์มต้องไม่เกิน 255 ตัวอักษร',
                ]
            );

            if ($validator->fails()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                return redirect()->back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();
        }

        $existingForm = null;
        $slug = $slug ?? null;
        $form_route = $form_route ?? ($request->query('route') ?? null);

        if ($request->id) {
            $existingForm = FormManage::find($request->id);

            if ($existingForm) {
                $form_route = $existingForm->form_route;
                $form_name = $existingForm->form_name;
                $slug = $existingForm->slug ?: Str::slug($existingForm->form_route ?? $existingForm->form_name);
            }
        } else {
            if (!$slug && $form_route) {
                $slug = Str::slug($form_route);
            }
        }

        if (!$existingForm) {
            if ($slug) {
                $existingForm = FormManage::where(function ($query) use ($slug) {
                    $query->where('slug', $slug)
                        ->orWhere('form_route', $slug);
                })->first();
            } elseif (!empty($form_route)) {
                $existingForm = FormManage::where('form_route', $form_route)->first();
            }
        }

        if ($existingForm) {
            if (!$existingForm->field_settings) {
                $existingForm->field_settings = collect($fieldConfig)->map(function ($config) {
                    return [
                        'enabled' => $config['default_enabled'] ?? true,
                        'required' => $config['default_required'] ?? false,
                    ];
                })->toArray();
            }
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'โหลดข้อมูลฟอร์มสำเร็จ',
                    'redirect' => route('create-form', ['id' => $existingForm->id]),
                ]);
            }

            $data = $existingForm;

            return view('forms_manage.create', compact('form_route', 'data', 'fieldConfig'));
        }

        $form_name = $request->input('form_name');
        $slug = $request->input('slug');
        $form_route = $request->input('slug');

        $lastForm = FormManage::orderBy('id', 'desc')->first();
        $nextSfield = $lastForm ? ($lastForm->sfield + 1) : 1;

        $dataCreate = FormManage::create([
            'form_name' => $form_name,
            'form_route' => $form_route,
            'slug' => $slug,
            'sfield' => $nextSfield,
            'head_title' => $form_name . ' 2025 Registration Form',
            'news' => 'ท่านทราบข่าวสารการจัดสอบ ' . $form_name . ' จากช่องทางใด',
            'create_by' => Auth::user()->id,
        ]);

        $data = FormManage::find($dataCreate->id);
        $data->field_settings = collect($fieldConfig)->map(function ($config) {
            return [
                'enabled' => $config['default_enabled'] ?? true,
                'required' => $config['default_required'] ?? false,
            ];
        })->toArray();
        $data->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'สร้างฟอร์มสำเร็จ',
                'redirect' => route('create-form', ['id' => $data->id]),
            ]);
        }

        return view('forms_manage.create', compact('form_route', 'data', 'fieldConfig'));
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

    public function editOptionPackage(Request $request)
    {
        $id = $request->id;
        $value = $request->value;
        $data = FormPakages::find($id);
        $data->package = $value;
        $data->price = $request->price;
        $data->update_by = Auth::user()->id;
        $data->save();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function deleteOptionPackage(Request $request)
    {
        $id = $request->id;
        $index = $request->index;
        $data = FormPakages::find($id);
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
        $fieldConfig = config('form_fields', []);

        $fieldSettingsInput = $request->input('field_settings', []);
        $normalizedFieldSettings = [];

        foreach ($fieldConfig as $fieldKey => $config) {
            $setting = $fieldSettingsInput[$fieldKey] ?? [];

            $enabled = array_key_exists('enabled', $setting)
                ? (bool) $setting['enabled']
                : ($config['default_enabled'] ?? true);

            $required = array_key_exists('required', $setting)
                ? (bool) $setting['required']
                : ($config['default_required'] ?? false);

            if (!$enabled) {
                $required = false;
            }

            $normalizedFieldSettings[$fieldKey] = [
                'enabled' => $enabled,
                'required' => $required,
            ];
        }

        $fieldRule = function (string $configKey, string $ruleBody) use ($normalizedFieldSettings) {
            $setting = $normalizedFieldSettings[$configKey] ?? ['enabled' => true, 'required' => true];
            $prefix = ($setting['required'] ?? false) ? 'required' : 'nullable';

            if (!($setting['enabled'] ?? true)) {
                $prefix = 'nullable';
            }

            return $prefix . '|' . $ruleBody;
        };

        $rules = [
            'form_id' => 'required|exists:forms_manage,id',
            'form_route' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'form_name' => 'required|string|max:255',
            'referent_code' => 'nullable|string|max:50',
            'sfield' => 'required|string',
            'title' => 'required|string|max:255',
            'sub_title' => 'required|string|max:255',
            'head_title' => 'required|string|max:255',

            'registration_date_label' => 'required|string',
            'contest_date_label' => 'required|string',
            'registration_fee_label' => 'required|string',
            'admission_criteria_text_label' => 'nullable|string',
            'admission_criteria_image_label' => 'nullable|string',
            // 'registration_date' => 'required|string',
            'contest_date' => 'required|string',
            'admission_criteria_text' => 'nullable|string',
            'text_footer' => 'required|string',
            'text_warning' => 'required|string',
            'text_policy' => 'required|string',
            'student_first_name' => $fieldRule('student_first_name', 'string|max:50'),
            'student_last_name' => $fieldRule('student_last_name', 'string|max:50'),
            'student_nick_name' => $fieldRule('student_nick_name', 'string|max:50'),
            'date_of_birth' => $fieldRule('date_of_birth', 'string|max:50'),
            'student_school_name' => $fieldRule('student_school_name', 'string|max:100'),
            'student_grade' => $fieldRule('student_grade', 'string|max:50'),
            'package' => $fieldRule('package', 'string|max:100'),
            'student_address' => $fieldRule('student_address', 'string'),
            'student_zip' => $fieldRule('student_zip', 'string'),
            'parent_name' => $fieldRule('parent_name', 'string|max:100'),
            'parent_phone' => $fieldRule('parent_phone', 'string|max:100'),
            'contact_email' => $fieldRule('contact_email', 'string|max:100'),
            'contact_email_detail' => $fieldRule('contact_email_detail', 'string'),
            'news' => $fieldRule('news', 'string'),
            'optionGrade' => 'nullable|array',
            'optionGrade.*' => 'nullable|string|max:100',
            'optionValue' => 'nullable|array',
            'optionValue.*' => 'nullable|numeric',
            'optionName' => 'nullable|array',
            'optionName.*' => 'nullable|string|max:100',
            'field_settings' => 'nullable|array',
            'field_settings.*.enabled' => 'nullable|boolean',
            'field_settings.*.required' => 'nullable|boolean',
            // Image validation rules - only required if no old files exist
            'brand' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'image_payment' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'admission_criteria_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'student_photo' => $fieldRule('student_photo', 'string'),
            'student_slip_photo' => $fieldRule('student_slip_photo', 'string'),
        ];

        // $messages = [];

        $messages = [
            // core
            'form_id.required' => 'The form ID is required.',
            'form_id.exists' => 'The specified form ID was not found.',
            'form_route.required' => 'The form route is required.',
            'form_route.string' => 'The form route must be a string.',
            'form_route.max' => 'The form route may not be greater than 255 characters.',
            'form_name.required' => 'The form name is required.',
            'form_name.string' => 'The form name must be a string.',
            'form_name.max' => 'The form name may not be greater than 255 characters.',
            'referent_code.string' => 'The referent code must be a string.',
            'referent_code.max' => 'The referent code may not be greater than 50 characters.',
            'sfield.required' => 'The sfield is required.',
            'sfield.string' => 'The sfield must be a string.',
            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'sub_title.required' => 'The sub title is required.',
            'sub_title.string' => 'The sub title must be a string.',
            'sub_title.max' => 'The sub title may not be greater than 255 characters.',
            'head_title.required' => 'The head title is required.',
            'head_title.string' => 'The head title must be a string.',
            'head_title.max' => 'The head title may not be greater than 255 characters.',

            // labels and texts
            'registration_date_label.required' => 'The registration date label is required.',
            'registration_date_label.string' => 'The registration date label must be a string.',
            'contest_date_label.required' => 'The contest date label is required.',
            'contest_date_label.string' => 'The contest date label must be a string.',
            'registration_fee_label.required' => 'The registration fee label is required.',
            'registration_fee_label.string' => 'The registration fee label must be a string.',
            // 'admission_criteria_text_label.required' => 'The admission criteria text label is required.',
            // 'admission_criteria_text_label.string' => 'The admission criteria text label must be a string.',
            // 'admission_criteria_image_label.required' => 'The admission criteria image label is required.',
            // 'admission_criteria_image_label.string' => 'The admission criteria image label must be a string.',
            // 'registration_date.required' => 'The registration date is required.',
            // 'registration_date.string' => 'The registration date must be a string.',
            'contest_date.required' => 'The contest date is required.',
            'contest_date.string' => 'The contest date must be a string.',
            // 'admission_criteria_text.required' => 'The admission criteria text is required.',
            // 'admission_criteria_text.string' => 'The admission criteria text must be a string.',
            'text_footer.required' => 'The footer text is required.',
            'text_footer.string' => 'The footer text must be a string.',
            'text_warning.required' => 'The warning text is required.',
            'text_warning.string' => 'The warning text must be a string.',
            'text_policy.required' => 'The policy text is required.',
            'text_policy.string' => 'The policy text must be a string.',

            // student info
            'student_first_name.required' => 'The student first name is required.',
            'student_first_name.string' => 'The student first name must be a string.',
            'student_first_name.max' => 'The student first name may not be greater than 50 characters.',
            'student_last_name.required' => 'The student last name is required.',
            'student_last_name.string' => 'The student last name must be a string.',
            'student_last_name.max' => 'The student last name may not be greater than 50 characters.',
            'student_nick_name.required' => 'The student nick name is required.',
            'student_nick_name.string' => 'The student nick name must be a string.',
            'student_nick_name.max' => 'The student nick name may not be greater than 50 characters.',
            'date_of_birth.required' => 'The date of birth is required.',
            'date_of_birth.string' => 'The date of birth must be a string.',
            'date_of_birth.max' => 'The date of birth may not be greater than 50 characters.',
            'student_school_name.required' => 'The student school name is required.',
            'student_school_name.string' => 'The student school name must be a string.',
            'student_school_name.max' => 'The student school name may not be greater than 100 characters.',
            'student_grade.required' => 'The student grade is required.',
            'student_grade.string' => 'The student grade must be a string.',
            'student_grade.max' => 'The student grade may not be greater than 50 characters.',
            'package.required' => 'The package is required.',
            'package.string' => 'The package must be a string.',
            'package.max' => 'The package may not be greater than 100 characters.',
            'student_address.required' => 'The student address is required.',
            'student_address.string' => 'The student address must be a string.',
            'student_zip.required' => 'The student ZIP is required.',
            'student_zip.string' => 'The student ZIP must be a string.',

            // parent/contact
            'parent_name.required' => 'The parent name is required.',
            'parent_name.string' => 'The parent name must be a string.',
            'parent_name.max' => 'The parent name may not be greater than 100 characters.',
            'parent_phone.required' => 'The parent phone is required.',
            'parent_phone.string' => 'The parent phone must be a string.',
            'parent_phone.max' => 'The parent phone may not be greater than 100 characters.',
            'contact_email.required' => 'The contact email is required.',
            'contact_email.email' => 'The contact email must be a valid email address.',
            'contact_email.max' => 'The contact email may not be greater than 100 characters.',
            'contact_email_detail.required' => 'The contact email detail is required.',
            'contact_email_detail.string' => 'The contact email detail must be a string.',
            'news.required' => 'The news field is required.',
            'news.string' => 'The news field must be a string.',

            // dynamic options
            'optionGrade.array' => 'The grades must be an array.',
            'optionGrade.*.string' => 'Each grade must be a string.',
            'optionGrade.*.max' => 'Each grade may not be greater than 100 characters.',
            'optionValue.array' => 'The package values must be an array.',
            'optionValue.*.numeric' => 'Each package value must be a number.',
            'optionName.array' => 'The package names must be an array.',
            'optionName.*.string' => 'Each package name must be a string.',
            'optionName.*.max' => 'Each package name may not be greater than 100 characters.',

            // images (generic)
            'brand.image' => 'The brand must be an image file.',
            'brand.mimes' => 'The brand must be a file of type: jpeg, png, jpg, gif.',
            'brand.max' => 'The brand image must not exceed 5MB.',
            'image_payment.image' => 'The payment image must be an image file.',
            'image_payment.mimes' => 'The payment image must be a file of type: jpeg, png, jpg, gif.',
            'image_payment.max' => 'The payment image must not exceed 5MB.',
            'admission_criteria_image.image' => 'The admission criteria image must be an image file.',
            'admission_criteria_image.mimes' => 'The admission criteria image must be a file of type: jpeg, png, jpg, gif.',
            'admission_criteria_image.max' => 'The admission criteria image must not exceed 5MB.',

            // uploads meta
            'student_photo.required' => 'The student photo field is required.',
            'student_photo.string' => 'The student photo must be a string.',
            'student_slip_photo.required' => 'The student slip photo field is required.',
            'student_slip_photo.string' => 'The student slip photo must be a string.',
        ];

        $oldBrand = $request->oldBrand;
        $oldImagePayment = $request->oldImagePayment;
        $oldAdmissionCriteriaImage = $request->oldAdmissionCriteriaImage;
        // Conditional validation for image fields based on old files existence
        if (!$oldBrand && !$request->hasFile('brand')) {
            $rules['brand'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120';
            $messages['brand.required'] = 'The brand image is required when no previous image exists.';
            $messages['brand.image'] = 'The brand must be an image file.';
            $messages['brand.mimes'] = 'The brand must be a file of type: jpeg, png, jpg, gif.';
            $messages['brand.max'] = 'The brand must not exceed 5MB.';
        }

        if (!$oldImagePayment && !$request->hasFile('image_payment')) {
            $rules['image_payment'] = 'required|image|mimes:jpeg,png,jpg,gif|max:5120';
            $messages['image_payment.required'] = 'The image payment is required when no previous image exists.';
            $messages['image_payment.image'] = 'The image payment must be an image file.';
            $messages['image_payment.mimes'] = 'The image payment must be a file of type: jpeg, png, jpg, gif.';
            $messages['image_payment.max'] = 'The image payment must not exceed 5MB.';
        }

        if (!$oldAdmissionCriteriaImage && !$request->hasFile('admission_criteria_image')) {
            $rules['admission_criteria_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120';
            $messages['admission_criteria_image.required'] = 'The admission criteria image is required when no previous image exists.';
            $messages['admission_criteria_image.image'] = 'The admission criteria image must be an image file.';
            $messages['admission_criteria_image.mimes'] = 'The admission criteria image must be a file of type: jpeg, png, jpg, gif.';
            $messages['admission_criteria_image.max'] = 'The admission criteria image must not exceed 5MB.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ]);
        }


        $form_id = $request->form_id;
        $form_manage = FormManage::find($form_id);

        $incomingFieldSettings = $request->input('field_settings', []);
        $normalizedFieldSettings = [];

        foreach ($fieldConfig as $fieldKey => $config) {
            $incoming = $incomingFieldSettings[$fieldKey] ?? [];

            $enabledValue = array_key_exists('enabled', $incoming)
                ? filter_var($incoming['enabled'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
                : null;
            $requiredValue = array_key_exists('required', $incoming)
                ? filter_var($incoming['required'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
                : null;

            $enabled = $enabledValue !== null ? $enabledValue : ($config['default_enabled'] ?? true);
            $required = $requiredValue !== null ? $requiredValue : ($config['default_required'] ?? false);

            if (!$enabled) {
                $required = false;
            }

            $normalizedFieldSettings[$fieldKey] = [
                'enabled' => $enabled,
                'required' => $required,
            ];
        }

        foreach ($incomingFieldSettings as $fieldKey => $incoming) {
            if (!array_key_exists($fieldKey, $normalizedFieldSettings) && is_array($incoming)) {
                $enabledValue = array_key_exists('enabled', $incoming)
                    ? filter_var($incoming['enabled'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
                    : false;
                $requiredValue = array_key_exists('required', $incoming)
                    ? filter_var($incoming['required'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
                    : false;

                $enabled = $enabledValue ?? false;
                $required = $enabled ? ($requiredValue ?? false) : false;

                $normalizedFieldSettings[$fieldKey] = [
                    'enabled' => $enabled,
                    'required' => $required,
                ];
            }
        }

        $form_manage->update([
            'form_name' => $request->form_name,
            'form_route' => $request->form_route,
            'slug' => $request->slug ?? $form_manage->slug,
            'referent_code' => $request->referent_code,
            'sfield' => $request->sfield,
            'title' => $request->title,
            'sub_title' => $request->sub_title,
            'head_title' => $request->head_title,
            // 'brand' => $request->brand,
            'registration_fee_label' => $request->registration_fee_label,
            'registration_date_label' => $request->registration_date_label,
            // 'registration_date' => $request->registration_date,
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
            'field_settings' => $normalizedFieldSettings,
            'update_by' => Auth::user()->id,
        ]);

        //file image
        if ($request->hasFile('image_payment')) {
            $file = $request->file('image_payment');
            $filename = $request->form_route . '_' . date('Y-m-d') . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('form_manage/payment'), $filename);
            $form_manage->update([
                'image_payment' => $filename,
            ]);
        }
        //file image
        if ($request->hasFile('admission_criteria_image')) {
            $file = $request->file('admission_criteria_image');
            $filename = $request->form_route . '_' . date('Y-m-d') . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('form_manage/admission_criteria_image'), $filename);
            $form_manage->update([
                'admission_criteria_image' => $filename,
            ]);
        }
        //file image
        if ($request->hasFile('brand')) {
            $file = $request->file('brand');
            $filename = $request->form_route . '_' . date('Y-m-d') . '_' . time() . '.' . $file->getClientOriginalExtension();
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

        return response()->json([
            'status' => 'success',
            'message' => 'Form updated successfully',

        ]);
        // return redirect()->route('form-manage.index')->with('success', 'Form updated successfully');
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

    public function view($id)
    {
        //
        $form = FormManage::find($id);
        return view('forms_manage.view', compact('form'));
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
    public function destroy(Request $request)
    {
        //
        $form = FormManage::find($request->id);

        $sfield = $form->sfield;

        $rec_form = ReceiptForm::where('sfield', $sfield)->count();
        if ($rec_form > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'พบการสมัครสำหรับฟอร์มนี้ กรุณาตรวจสอบก่อนลบ'
            ], 409);
        }

        $receipt = Receipt::where('sfield', $sfield)->count();
        if ($receipt > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'พบการออกใบเสร็จสำหรับฟอร์มนี้ กรุณาตรวจสอบก่อนลบ'
            ], 409);
        }

        $package = FormPakages::where('form_id', $request->id)->delete();
        $grade = FormGrades::where('form_id', $request->id)->delete();
        $form->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Form deleted successfully'
        ]);
    }
}
