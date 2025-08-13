<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $route
 * @property string $referent_url
 * @property string $referent_code
 * @property string $user_id
 * @property string $comments
 * @property string $created_at
 * @property string $updated_at
 */
class ReferentURL extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'referent_url';

    /**
     * @var array
     */
    protected $fillable = [
        'route',
        'referent_url',
        'referent_code',
        'user_id',
        'comments',
        'created_at',
        'updated_at'
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function code()
    {
        return $this->belongsTo(ReferentCode::class, 'referent_code', 'referent_code');
    }
}
