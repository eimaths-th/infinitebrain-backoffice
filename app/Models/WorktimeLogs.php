<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $rectime_id
 * @property string $type_user
 * @property string $old_date
 * @property string $old_stime
 * @property string $old_etime
 * @property string $old_status
 * @property string $old_status_name
 * @property string $old_explain
 * @property string $new_date
 * @property string $new_stime
 * @property string $new_etime
 * @property string $new_status
 * @property string $new_status_name
 * @property string $new_explain
 * @property string $action
 * @property integer $approv_id
 * @property string $created_at
 * @property string $updated_at
 */
class WorktimeLogs extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'rectime_id',
        'type_user',
        'old_date',
        'old_stime',
        'old_etime',
        'old_status',
        'old_status_name',
        'old_explain',
        'new_date',
        'new_stime',
        'new_etime',
        'new_status',
        'new_status_name',
        'new_explain',
        'action',
        'approv_id',
        'created_at',
        'updated_at'
    ];


    public function getOldFullTimeAttribute()
    {
        if ($this->old_stime && $this->old_etime) {
            # code...
            return 'เข้า : ' . $this->old_stime . ' ออก : ' . $this->old_etime;
        } elseif (!$this->old_stime && $this->old_etime) {
            # code...
            return 'เข้า : ไม่พบข้อมูล ออก : ' . $this->old_etime;
        } elseif ($this->old_stime && !$this->old_etime) {
            # code...
            return 'เข้า : ' . $this->old_stime . ' ออก : ไม่พบข้อมูล';
        } else {
            return 'ไม่พบข้อมูล';
            // return 'เข้า : ' . $this->stime . ' ออก : ' . $this->etime;
        }
    }
    public function getNewFullTimeAttribute()
    {
        if ($this->new_stime && $this->new_etime) {
            # code...
            return 'เข้า : ' . $this->new_stime . ' ออก : ' . $this->new_etime;
        } elseif (!$this->new_stime && $this->new_etime) {
            # code...
            return 'เข้า : ไม่พบข้อมูล ออก : ' . $this->new_etime;
        } elseif ($this->new_stime && !$this->new_etime) {
            # code...
            return 'เข้า : ' . $this->new_stime . ' ออก : ไม่พบข้อมูล';
        } else {
            return 'ไม่พบข้อมูล';
            // return 'เข้า : ' . $this->stime . ' ออก : ' . $this->etime;
        }
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function approv()
    {
        return $this->hasOne(User::class, 'id', 'approv_id');
    }
}
