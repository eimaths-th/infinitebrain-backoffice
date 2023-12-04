<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $explain
 * @property string $created_at
 * @property string $updated_at
 */
class Godeye extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'god_eye';

    /**
     * @var array
     */
    protected $fillable = ['user_id','precursor_id', 'explain', 'created_at', 'updated_at'];

    public function nextGod()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function precursor()
    {
        return $this->hasOne(User::class, 'id', 'precursor_id');
    }
}
