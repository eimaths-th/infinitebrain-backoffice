<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $type_user
 * @property string $date
 * @property string $stime
 * @property string $etime
 * @property string $status
 * @property string $status_name
 * @property string $created_at
 * @property string $updated_at
 */
class Recordtime extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'record_time';

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'type_user', 'date', 'stime', 'etime', 'status', 'status_name', 'created_at', 'updated_at'];

    public function getFullTimeAttribute()
    {
        if ($this->stime && $this->etime) {
            # code...
            return 'เข้า : ' . $this->stime . ' ออก : ' . $this->etime;
        } elseif (!$this->stime && $this->etime) {
            # code...
            return 'เข้า : ไม่พบข้อมูล ออก : ' . $this->etime;
        }  elseif ($this->stime && !$this->etime) {
            # code...
            return 'เข้า : ' . $this->stime . ' ออก : ไม่พบข้อมูล';
        }else {
            return 'ไม่พบข้อมูล';
            // return 'เข้า : ' . $this->stime . ' ออก : ' . $this->etime;
        }
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
