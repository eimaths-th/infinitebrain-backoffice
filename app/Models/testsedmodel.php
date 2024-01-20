<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $code
 * @property string $first_name
 * @property string $last_name
 * @property string $nick_name
 * @property string $birth_date
 * @property string $mobile
 * @property string $photo_att
 * @property string $grade
 * @property string $status
 * @property string $school_name
 * @property string $created_at
 * @property string $updated_at
 * @property string $sex
 * @property string $reason
 * @property integer $pid
 * @property FutureStudent[] $futureStudents
 * @property StatusLog[] $statusLogs
 */
class testsedmodel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'students';

    /**
     * @var array
     */
    protected $fillable = [
        'code', //
        'first_name', //
        'last_name',
        'nick_name',
        'birth_date',
        'mobile',
        'photo_att',
        'grade',
        'status',
        'school_name', 'created_at', 'updated_at', 'sex', 'reason', 'pid'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function futureStudents()
    {
        return $this->hasMany('App\Models\FutureStudent');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statusLogs()
    {
        return $this->hasMany('App\Models\StatusLog');
    }
}
