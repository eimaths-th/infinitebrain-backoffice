<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $form_id
 * @property string $sfield
 * @property string $form_name
 * @property string $form_route
 * @property string $grade
 * @property integer $create_by
 * @property integer $update_by
 * @property string $created_at
 * @property string $updated_at
 */
class FormGrades extends Model
{
    /**
     * @var array
     */
    protected $table = 'forms_grades';
    protected $fillable = [
        'form_id',
        'sfield',
        'form_name',
        'form_route',
        'grade',
        'create_by',
        'update_by',
        'created_at',
        'updated_at'
    ];
}
