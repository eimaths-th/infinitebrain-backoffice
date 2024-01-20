<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'day_of_week',
        'in_time',
        'out_time',
    ];

    // Define the relationship with the Employee model
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
