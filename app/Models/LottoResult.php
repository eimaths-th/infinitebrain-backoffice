<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $date
 * @property string $1st
 * @property string $3front_1
 * @property string $3front_2
 * @property string $3back_1
 * @property string $3back_2
 * @property string $2numbers
 * @property string $created_at
 * @property string $updated_at
 */
class LottoResult extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['date', '1st', '3front_1', '3front_2', '3back_1', '3back_2', '2numbers', 'created_at', 'updated_at'];
}
