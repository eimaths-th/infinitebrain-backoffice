<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $referent_code
 * @property string $referent_name
 * @property string $user_id
 * @property string $comments
 * @property string $created_at
 * @property string $updated_at
 */
class ReferentCode extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'referent_code';

    /**
     * @var array
     */
    protected $fillable = ['referent_code', 'referent_name', 'user_id', 'comments', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
