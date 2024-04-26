<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $student_id
 * @property string $receipt_school_id
 * @property string $school_price_id
 * @property string $check
 * @property string $date
 * @property string $created_at
 * @property string $updated_at
 */
class CheckReportStd extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'check_report_std';

    /**
     * @var array
     */
    protected $fillable = ['student_id', 'receipt_school_id', 'school_price_id', 'check', 'date', 'created_at', 'updated_at'];
}
