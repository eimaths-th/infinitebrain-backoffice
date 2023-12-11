<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $type_user
 * @property string $old_date
 * @property string $old_stime
 * @property string $old_etime
 * @property string $old_status
 * @property string $old_status_name
 * @property string $new_date
 * @property string $new_stime
 * @property string $new_etime
 * @property string $new_status
 * @property string $new_status_name
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
    protected $fillable = ['user_id', 'type_user', 'old_date', 'old_stime', 'old_etime', 'old_status', 'old_status_name', 'new_date', 'new_stime', 'new_etime', 'new_status', 'new_status_name', 'action', 'approv_id', 'created_at', 'updated_at'];
}
