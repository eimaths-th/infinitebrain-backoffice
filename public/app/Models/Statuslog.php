<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $student_id
 * @property string $nick_name
 * @property string $group
 * @property string $status
 * @property string $status_date
 * @property string $created_at
 * @property string $updated_at
 * @property Student $student
 */
class Statuslog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'status_log';

    /**
     * @var array
     */
    protected $fillable = ['student_id', 'group' ,'nick_name', 'status', 'status_date', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo('App\Models\Student');
    }
}
