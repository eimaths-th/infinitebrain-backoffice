<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

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
        'branches',
        // Payroll fields
        'nickname',
        'gender',
        'nationality',
        'birth_date',
        'start_date',
        'end_date',
        'employee_type',
        'is_probation',
        'probation_end_date',
        'payroll_frequency',
        'department',
        'base_salary',
        'bank_account',
        'bank_name',
        'tax_id',
        'social_security_no',
    ];

    const TITLE_PREFIXES = [
        'นาย' => 'นาย',
        'นาง' => 'นาง',
        'นางสาว' => 'นางสาว',
        'other' => 'อื่นๆ',
    ];

    public function GetFullName()
    {

        return $this->name;
    }
    public function GetDisplayName()
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
        'branches' => 'array',
        // Payroll date fields
        'birth_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'probation_end_date' => 'date',
        'is_probation' => 'boolean',
        'base_salary' => 'decimal:2',
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

    /**
     * @deprecated ใช้ hasRole() จาก spatie/laravel-permission แทน
     * Legacy method สำหรับตรวจสอบ level เดิม - เปลี่ยนชื่อเพื่อไม่ให้ conflict กับ spatie
     */
    public function getLevelRole()
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

    /**
     * Payroll relationships
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get employee type label
     */
    public function GetEmployeeTypeLabel()
    {
        return Payroll::$employeeTypes[$this->employee_type] ?? $this->employee_type;
    }

    /**
     * Scope for payroll-eligible users (active employees)
     */
    public function scopePayrollEligible($query)
    {
        return $query->where('is_disabled', false)
            ->whereNotNull('base_salary')
            ->where('base_salary', '>', 0);
    }

    /**
     * Tags ของ User (เช่น วิชาที่สอน)
     */
    public function tags()
    {
        return $this->belongsToMany(UserTag::class, 'user_tag_pivot');
    }

    /**
     * รายการรายได้สำหรับ Part-time
     */
    public function salaryItems()
    {
        return $this->hasMany(UserSalaryItem::class)->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * ตรวจสอบว่าเป็น Part-time หรือไม่
     */
    public function IsParttime()
    {
        return $this->employee_type === 'parttime';
    }
    /**
     * Get the highest level among the user's assigned roles.
     * Default to 0 if no roles.
     */
    public function maxRoleLevel()
    {
        return $this->roles->max('level') ?? 0;
    }
}
