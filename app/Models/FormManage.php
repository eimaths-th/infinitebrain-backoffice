<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $sfield
 * @property string $form_name
 * @property string $form_route
 * @property string $brand
 * @property string $title
 * @property string $sub_title
 * @property string $head_title
 * @property string $registration_date_label
 * @property string $contest_date_label
 * @property string $registration_fee_label
 * @property string $admission_criteria_text_label
 * @property string $admission_criteria_image_label
 * @property string $registration_date
 * @property string $contest_date
 * @property string $registration_fee
 * @property string $admission_criteria_text
 * @property string $admission_criteria_image
 * @property string $image_payment
 * @property string $text_footer
 * @property string $text_warning
 * @property string $text_policy
 * @property string $student_first_name
 * @property string $student_last_name
 * @property string $student_nick_name
 * @property string $date_of_birth
 * @property string $student_school_name
 * @property string $gender
 * @property string $student_grade
 * @property string $package
 * @property string $student_address
 * @property string $student_zip
 * @property string $parent_name
 * @property string $parent_phone
 * @property string $contact_email
 * @property string $contact_email_detail
 * @property string $student_photo
 * @property string $student_slip_photo
 * @property string $news
 * @property integer $create_by
 * @property integer $update_by
 * @property string $created_at
 * @property string $updated_at
 */
class FormManage extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'forms_manage';

    /**
     * @var array
     */
    protected $fillable = [
        'sfield',
        'form_name',
        'form_route',
        'brand',
        'title',
        'sub_title',
        'head_title',
        'registration_date_label',
        'contest_date_label',
        'registration_fee_label',
        'admission_criteria_text_label',
        'admission_criteria_image_label',
        'registration_date',
        'contest_date',
        'registration_fee',
        'admission_criteria_text',
        'admission_criteria_image',
        'image_payment',
        'text_footer',
        'text_warning',
        'text_policy',
        'student_first_name',
        'student_last_name',
        'student_nick_name',
        'date_of_birth',
        'student_school_name',
        'gender',
        'student_grade',
        'package',
        'student_address',
        'student_zip',
        'parent_name',
        'parent_phone',
        'contact_email',
        'contact_email_detail',
        'student_photo',
        'student_slip_photo',
        'news',
        'create_by',
        'update_by',
        'created_at',
        'updated_at'
    ];
    public function package()
    {
        return $this->belongsTo(FormPakages::class);
    }
    public function grade()
    {
        return $this->belongsTo(FormGrades::class);
    }
}
