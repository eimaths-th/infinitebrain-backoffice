<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $month
 * @property string $year
 * @property string $late_time_all
 * @property string $missing_work_all
 * @property string $created_at
 * @property string $updated_at
 */
class OverLate extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'over_late';

    /**
     * @var array
     */
    protected $fillable = ['month', 'year', 'late_time_all', 'missing_work_all', 'created_at', 'updated_at'];
}
