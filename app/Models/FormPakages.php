<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $form_id
 * @property string $sfield
 * @property string $form_name
 * @property string $form_route
 * @property string $package
 * @property string $price
 * @property integer $create_by
 * @property integer $update_by
 * @property string $created_at
 * @property string $updated_at
 */
class FormPakages extends Model
{
    /**
     * @var array
     */

    protected $table = 'form_pakages';

    protected $fillable = [
        'form_id',
        'sfield',
        'form_name',
        'form_route',
        'package',
        'price',
        'create_by',
        'update_by',
        'created_at',
        'updated_at'
    ];
}
