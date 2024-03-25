<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property string $file_name
 * @property string $type
 * @property string $version
 * @property string $upload_by
 * @property string $created_at
 * @property string $updated_at
 */
class AppDownload extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'app_download';

    /**
     * @var array
     */
    protected $fillable = ['name', 'file_name', 'type', 'version', 'upload_by', 'created_at', 'updated_at'];
    

}
