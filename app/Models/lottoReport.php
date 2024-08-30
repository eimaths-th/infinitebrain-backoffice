<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property string $numbers
 * @property string $type
 * @property string $bet
 * @property string $status
 * @property string $win_lose
 * @property string $result
 * @property string $created_at
 * @property string $updated_at
 */
class lottoReport extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lotto_report';

    /**
     * @var array
     */
    protected $fillable = ['name', 'numbers', 'type', 'bet', 'status','win_lose', 'result', 'created_at', 'updated_at'];
}
