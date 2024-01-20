<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $user_id
 * @property string $status
 * @property string $comments
 * @property string $route
 * @property string $opening_time
 * @property string $closing_time
 * @property string $created_at
 * @property string $updated_at
 */
class OpeningClosingTime extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id','status','comments','route','opening_time', 'closing_time', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
