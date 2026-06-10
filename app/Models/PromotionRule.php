<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionRule extends Model
{
    use HasFactory;

    const TYPE_SUBJECT_COMBINATION = 'SUBJECT_COMBINATION';
    const TYPE_REFERRAL = 'REFERRAL';
    const TYPE_NEW_STUDENT_MIN_COURSES = 'NEW_STUDENT_MIN_COURSES';
    const TYPE_NEW_STUDENT = 'NEW_STUDENT';
    const TYPE_MIN_COURSES = 'MIN_COURSES';
    const TYPE_EXACT_COURSES = 'EXACT_COURSES';
    const TYPE_MIN_MONTHS = 'MIN_MONTHS';
    const TYPE_EXACT_MONTHS = 'EXACT_MONTHS';
    const TYPE_SUBJECT_GROUP_MONTHS = 'SUBJECT_GROUP_MONTHS';
    const TYPE_EXACT_SUBTOTAL = 'EXACT_SUBTOTAL';
    const TYPE_BRANCH_ELIGIBLE = 'BRANCH_ELIGIBLE';

    protected $fillable = [
        'promotion_campaign_id',
        'rule_type',
        'rule_config_json',
    ];

    protected $casts = [
        'rule_config_json' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(PromotionCampaign::class, 'promotion_campaign_id');
    }
}
