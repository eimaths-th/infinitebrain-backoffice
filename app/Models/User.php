<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'fingerprint_id',
        'days_off',
        'wfh_days',
        'is_disabled',
        'disabled_at',
        'signature',
        'signature_is_image',
        'name',
        'title_prefix',
        'position',
        'phone',
        'address',
        'email',
        'type_user',
        'password',
        'level',
    ];

    const TITLE_PREFIXES = [
        'นาย' => 'นาย',
        'นาง' => 'นาง',
        'นางสาว' => 'นางสาว',
        'other' => 'อื่นๆ',
    ];

    public function GetFullName()
    {
        $prefix = $this->title_prefix ?? '';
        return trim($prefix . ' ' . $this->name);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_disabled' => 'boolean',
        'disabled_at' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_disabled', false);
    }

    public function scopeDisabled($query)
    {
        return $query->where('is_disabled', true);
    }


    const ADMIN_TYPE = 1;
    const DEFAULT_TYPE = 0;
    /*public  function isAdmin()
    {
        return $this->level === self::ADMIN_TYPE;
    }*/

    public function hasRole()
    {
        switch ($this->level) {
            case 0:
                session(['level' => self::DEFAULT_TYPE]);
                return  self::DEFAULT_TYPE;
                break;
            case 1:
                session(['level' => self::DEFAULT_TYPE]);
                return  self::ADMIN_TYPE;
                break;
            case 2:
                session(['level' => self::DEFAULT_TYPE]);
                return  self::DEFAULT_TYPE;
                break;
            default:
                session(['level' => self::DEFAULT_TYPE]);
                return self::DEFAULT_TYPE;
        }
    }

    public function compensateDays()
    {
        return $this->hasMany(UserCompensateDay::class);
    }
}
