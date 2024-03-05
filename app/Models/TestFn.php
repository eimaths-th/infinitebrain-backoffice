<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $fntest
 * @property string $created_at
 * @property string $updated_at
 */
class TestFn extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'testfn';

    /**
     * @var array
     */
    protected $fillable = ['fntest', 'created_at', 'updated_at'];
}
