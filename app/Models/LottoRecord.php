<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property string $3numbers
 * @property string $value
 * @property string $created_at
 * @property string $updated_at
 */
class LottoRecord extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'lotto_record';

    /**
     * @var array
     */
    protected $fillable = ['name', '3numbers', 'value', 'created_at', 'updated_at'];
}
