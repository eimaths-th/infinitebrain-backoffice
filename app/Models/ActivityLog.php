<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'model_type',
        'model_id',
        'data_before',
        'data_after',
    ];

    protected $casts = [
        'data_before' => 'array',
        'data_after'  => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
