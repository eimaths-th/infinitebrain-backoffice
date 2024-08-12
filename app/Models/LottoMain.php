<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $numbers
 * @property string $length
 * @property string $type
 * @property string $max
 * @property string $date
 * @property string $created_at
 * @property string $updated_at
 */
class LottoMain extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lotto_main';

    /**
     * @var array
     */
    protected $fillable = ['numbers', 'length', 'type', 'max', 'date', 'created_at', 'updated_at'];
}
