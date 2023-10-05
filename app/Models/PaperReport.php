<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $no
 * @property integer $std_id
 * @property integer $rec_id
 * @property integer $schp_id
 * @property string $receipt_number
 * @property string $submission_date
 * @property string $created_at
 * @property string $updated_at
 */
class PaperReport extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['no', 'std_id', 'rec_id', 'schp_id', 'receipt_number', 'submission_date', 'created_at', 'updated_at'];

    public function rec_school()
    {
        // return $this->hasMany(Receipt_School::class, 'rec_id','id');
        return $this->hasOne(Receipt_School::class, 'id', 'rec_id');

        // return $this->hasOne(Receipt_School::class, 'pid','id');
    }
    public function student()
    {
        // return $this->hasMany(Student::class, 'std_id','id');
        return $this->hasOne(Student::class, 'id', 'std_id');
        // return $this->hasOne(Receipt_School::class, 'pid','id');
    }

    public function school_price()
    {
        // return $this->hasMany(school_price::class, 'schp_id','id');
        return $this->hasOne(SchoolPrice::class, 'id', 'schp_id');

        // return $this->hasOne(Receipt_School::class, 'pid','id');
    }
}
